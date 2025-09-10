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

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // Inserir solicitação de saque
        $stmt = $conn->prepare("
            INSERT INTO saques_pix (user_id, valor, tipo_chave, chave_pix, nome_completo, cpf) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("id", $userId, $valor, $tipoChave, $chavePix, $nomeCompleto, $cpf);
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
        $callbackUrl = $scheme . '://' . $host . '/webhook_lotuspay.php';

        // Garante telefone válido
        $phone = $_POST['phone'] ?? null;
        if (empty($phone)) {
            $phone = '119' . str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } else {
            $phone = preg_replace('/\D/', '', (string)$phone);
            if (strlen($phone) < 10) {
                $phone = '11' . str_pad($phone, 9, '0', STR_PAD_RIGHT);
            }
        }

        $payload = [
            // Formato Lotuspay solicitado
            'pixKeyType' => $keyType, // cpf | email | phone | random
            'pixKey' => $chavePix,
            'customer' => [
                'document' => [
                    'type' => 'cpf',
                    'number' => $docUsuario,
                ],
                'name' => $nomeCompleto ?: ($usuario['name'] ?? 'Cliente'),
                'email' => $usuario['email'] ?? null,
                'phone' => $phone,
            ],
            'amount' => $valor,
            'callbackUrl' => $callbackUrl,
        ];

        // Loga o payload de cashOut
        error_log('LotusPay cashOut payload: ' . json_encode($payload, JSON_UNESCAPED_UNICODE));

        // Chamar API LotusPay com retry simples para 5xx
        $lotus = new LotusPayAPI();
        $cashoutRes = null;
        $attempts = 0;
        while ($attempts < 3) {
            try {
                $attempts++;
                $cashoutRes = $lotus->cashOut($payload);
                break;
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                error_log('[LotusPay cashOut] tentativa ' . $attempts . ' falhou: ' . $msg);
                if ($attempts >= 3 || (strpos($msg, '502') === false && strpos($msg, '503') === false && strpos($msg, '504') === false)) {
                    throw $ex;
                }
                usleep(250000 * $attempts);
            }
        }

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

        // Resposta padronizada do saque: apenas id e status
        $cashoutId = $cashoutRes['id'] ?? null;
        $cashoutStatus = $cashoutRes['status'] ?? null;
        echo json_encode([
            'id' => $cashoutId,
            'status' => $cashoutStatus,
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

