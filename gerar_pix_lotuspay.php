<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lotuspay_api.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$valor = floatval($input['valor'] ?? ($input['amount'] ?? 0));

if ($valor < 10) {
    echo json_encode(['erro' => 'Valor mínimo R$ 10,00']);
    exit;
}

$user_id = $_SESSION['usuario_id'];

// Busca nome e email do usuário
$stmt = $conn->prepare("SELECT name, email, document FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Gera um ID externo único para rastrear a transação
$external_id = 'DEP_' . $user_id . '_' . time() . '_' . rand(1000, 9999);

try {
    $lotus = new LotusPayAPI();

    // Monta dados do cliente (gera documento se não houver)
    $customerDocument = preg_replace('/\D/', '', $user['document'] ?? '') ?: str_pad((string)random_int(0, 99999999999), 11, '0', STR_PAD_LEFT);

    // Callback URL para receber notificações (webhook)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $callbackUrl = $scheme . '://' . $host . '/webhook_lotuspay.php';

    // Garante telefone válido (string) para a API
    $phone = $input['customer']['phone'] ?? ($input['phone'] ?? null);
    if (empty($phone)) {
        $phone = '119' . str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    } else {
        $phone = preg_replace('/\D/', '', (string)$phone);
        if (strlen($phone) < 10) {
            // completa se vier curto
            $phone = '11' . str_pad($phone, 9, '0', STR_PAD_RIGHT);
        }
    }

    // Payload LotusPay com campos obrigatórios (documento como objeto e telefone opcional)
    $payload = [
        'customer' => [
            'document' => [
                'type' => 'cpf',
                'number' => $customerDocument,
            ],
            'name' => $user['name'] ?? 'Cliente Anônimo',
            'email' => $user['email'] ?? 'email@exemplo.com',
            'phone' => $phone,
        ],
        'amount' => $valor,
        'callbackUrl' => $callbackUrl,
    ];

    // Loga o payload enviado
    error_log('LotusPay cashIn payload: ' . json_encode($payload, JSON_UNESCAPED_UNICODE));

    // Tenta até 2 vezes em caso de erro 5xx transitório
    $response = null;
    $attempts = 0;
    $lastErr = null;
    while ($attempts < 2) {
        try {
            $attempts++;
            $response = $lotus->cashIn($payload);
            break;
        } catch (Exception $ex) {
            $lastErr = $ex;
            $msg = $ex->getMessage();
            error_log('[LotusPay cashIn] tentativa ' . $attempts . ' falhou: ' . $msg);
            if ($attempts >= 3 || (strpos($msg, '502') === false && strpos($msg, '503') === false && strpos($msg, '504') === false)) {
                throw $ex; // não é 5xx ou esgotou tentativas
            }
            usleep(250000 * $attempts); // backoff 250ms, 500ms, 750ms
        }
    }

    // Log básico
    error_log("Resposta da LotusPay para Pix: " . json_encode($response));

    // Normaliza campos do retorno
    $providerId = $response['id'] ?? null;
    $providerStatus = $response['status'] ?? null;
    $qrCode = $response['qrCode'] ?? ($response['qrcode'] ?? ($response['pix_code'] ?? ($response['qr_code'] ?? ($response['emv'] ?? null))));
    $qrCodeBase64 = $response['qrCodeBase64'] ?? null;

    if (!empty($qrCode)) {
        if (empty($qrCodeBase64)) {
            $qrCodeBase64 = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCode);
        }

        // Insere o depósito como pendente na tabela deposits
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, status, payment_id, created_at, updated_at, external_id) VALUES (?, ?, 'pendente', NULL, NOW(), NOW(), ?)");
        $stmt->bind_param("ids", $user_id, $valor, $external_id);
        $stmt->execute();

        echo json_encode([
            'id' => $providerId,
            'status' => $providerStatus,
            // Campos originais
            'qrCode' => $qrCode,
            'qrCodeBase64' => $qrCodeBase64,
            // Aliases para compatibilidade com o frontend atual
            'qrcode' => $qrCode,
            'qrcode_base64' => $qrCodeBase64,
        ]);
    } else {
        echo json_encode([
            'erro' => 'Falha ao gerar Pix: ' . ($response['message'] ?? ($response['error'] ?? 'Erro desconhecido'))
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'erro' => 'Erro ao gerar QR Code: ' . $e->getMessage()
    ]);
}
