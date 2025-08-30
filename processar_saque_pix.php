<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';
require_once __DIR__ . '/includes/lotuspay_api.php';

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

// Verificar se o usuário está logado
$userId = $_SESSION['usuario_id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Receber dados do formulário
    $valor = floatval($_POST['valor'] ?? 0);
    $tipoChave = trim((string)($_POST['tipo_chave'] ?? ''));
    $chavePix = trim((string)($_POST['chave_pix'] ?? ''));
    $nomeCompleto = trim((string)($_POST['nome_completo'] ?? ''));
    $cpf = trim((string)($_POST['cpf'] ?? ''));

    // Validações básicas
    if ($valor < 10) {
        echo json_encode(['success' => false, 'message' => 'Valor mínimo para saque é R$ 10,00']);
        exit;
    }

    if (empty($tipoChave) || empty($chavePix) || empty($nomeCompleto) || empty($cpf)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
        exit;
    }

    // Validar CPF (formato básico)
    $cpfLimpo = preg_replace('/\D/', '', $cpf);
    if (strlen($cpfLimpo) !== 11) {
        echo json_encode(['success' => false, 'message' => 'CPF deve ter 11 dígitos']);
        exit;
    }

    // Buscar saldo atual do usuário
    $stmt = $conn->prepare("SELECT balance, name, email, document FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }

    $saldoAtual = floatval($usuario['balance']);
    $docUsuario = preg_replace('/\D/', '', $usuario['document'] ?? '') ?: $cpfLimpo;

    // Verificar se o usuário tem saldo suficiente
    if ($valor > $saldoAtual) {
        echo json_encode(['success' => false, 'message' => 'Saldo insuficiente']);
        exit;
    }

    // Criar tabela de saques se não existir
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS saques_pix (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            tipo_chave VARCHAR(20) NOT NULL,
            chave_pix VARCHAR(255) NOT NULL,
            nome_completo VARCHAR(255) NOT NULL,
            cpf VARCHAR(14) NOT NULL,
            status ENUM('pendente', 'processando', 'concluido', 'cancelado') DEFAULT 'pendente',
            data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_processamento TIMESTAMP NULL,
            observacoes TEXT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ";
    $conn->query($createTableQuery);

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // Inserir solicitação de saque
        $stmt = $conn->prepare("
            INSERT INTO saques_pix (user_id, valor, tipo_chave, chave_pix, nome_completo, cpf) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("idssss", $userId, $valor, $tipoChave, $chavePix, $nomeCompleto, $cpf);
        $stmt->execute();

        $saqueId = $conn->insert_id;

        // Montar payload para LotusPay cashOut
        $externalId = 'WD_' . $userId . '_' . time() . '_' . random_int(1000, 9999);

        // Mapear tipo de chave para padrão esperado
        $tipoChave = strtolower($tipoChave);
        $allowedTypes = ['cpf', 'email', 'phone', 'telefone', 'celular', 'aleatoria', 'random', 'evp'];
        if (!in_array($tipoChave, $allowedTypes, true)) {
            $tipoChave = 'cpf';
        }
        // Normalizar tipos comuns
        $keyType = match ($tipoChave) {
            'telefone', 'celular', 'phone' => 'phone',
            'aleatoria', 'random', 'evp' => 'random',
            default => $tipoChave, // cpf, email
        };

        // Callback URL
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $callbackUrl = $scheme . '://' . $host . '/webhook_bspay_novo.php';

        $payload = [
            'amount' => $valor,
            'external_id' => $externalId,
            'recipient' => [
                'name' => $nomeCompleto ?: ($usuario['name'] ?? 'Cliente'),
                'document' => $docUsuario,
                'pix' => [
                    'key_type' => $keyType,
                    'key' => $chavePix,
                ],
            ],
            'metadata' => [
                'user_id' => $userId,
                'withdraw_id' => $saqueId,
                'origin' => 'site',
            ],
            'callbackUrl' => $callbackUrl,
        ];

        // Chamar API LotusPay (pode lançar exceção)
        $lotus = new LotusPayAPI();
        $cashoutRes = $lotus->cashOut($payload);

        // Atualizar registro do saque com informações da provedora
        $obs = 'LotusPay cashOut response: ' . json_encode($cashoutRes, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("UPDATE saques_pix SET status = 'processando', observacoes = CONCAT(COALESCE(observacoes,''), ?) WHERE id = ?");
        $sep = (function() use ($conn, $saqueId) {
            // adiciona quebra de linha antes se já houver conteúdo
            $q = $conn->prepare("SELECT observacoes FROM saques_pix WHERE id = ?");
            $q->bind_param("i", $saqueId);
            $q->execute();
            $r = $q->get_result()->fetch_assoc();
            return (!empty($r['observacoes']) ? "\n" : "") ;
        })();
        $obsToAppend = $sep . $obs;
        $stmt->bind_param("si", $obsToAppend, $saqueId);
        $stmt->execute();

        // Debitar saldo após criar o cashout com sucesso
        $novoSaldo = $saldoAtual - $valor;
        $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $stmt->bind_param("di", $novoSaldo, $userId);
        $stmt->execute();

        // Confirmar transação
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Solicitação de saque enviada com sucesso! Em processamento.',
            'saque_id' => $saqueId,
            'external_id' => $externalId,
            'novo_saldo' => $novoSaldo,
        ]);

    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Erro ao processar saque: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>

