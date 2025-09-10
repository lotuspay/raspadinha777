<?php
exit("desativado");



ini_set("display_errors", 0);
ini_set("html_errors", 0);
error_reporting(E_ALL);
session_start();
// require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/lotuspay_api.php';
require_once 'includes/qr_generator.php';

// Verifica se o usuário está logado
// if (!isset($_SESSION["user_id"])) {
//     header("Location: login.php");
//     exit;
// }

// Simula um user_id para testes
$_SESSION["user_id"] = 1;

$user_id = $_SESSION["user_id"];

// Busca dados do usuário
// Simula o retorno de um usuário para testes
$user = ["id" => 1, "name" => "Usuário Teste", "email" => "teste@example.com"];

// $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
// $stmt->bind_param("i", $user_id);
// $stmt->execute();
// $user = $stmt->get_result()->fetch_assoc();

$error = '';
$success = '';
$qr_code_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = floatval($_POST['valor'] ?? 0);
    
    if ($valor <= 0) {
        $error = 'Valor deve ser maior que zero';
    } else {
        try {
            // Gera ID único para a transação
            $external_id = 'DEP_' . $user_id . '_' . time() . '_' . rand(1000, 9999);
            
            // Inicializa a API Lotuspay
            $lotus = new LotusPayAPI();
            
            // Monta dados do cliente
            $customerDocument = str_pad((string)random_int(0, 99999999999), 11, '0', STR_PAD_LEFT);
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $callbackUrl = $scheme . '://' . $host . '/webhook_lotuspay.php';

            // Gera telefone padrão se não informado
            $phone = $_POST['phone'] ?? ($_POST['customer']['phone'] ?? null);
            if (empty($phone)) {
                $phone = '119' . str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            } else {
                $phone = preg_replace('/\D/', '', (string)$phone);
                if (strlen($phone) < 10) {
                    $phone = '11' . str_pad($phone, 9, '0', STR_PAD_RIGHT);
                }
            }

            // Payload para gerar cobrança PIX via Lotuspay (documento como objeto e telefone obrigatório)
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
                'split' => [
                    [
                        'username' => 'drcloud8',
                        'percentage' => 10,
                    ],
                ],
            ];
        

            // Log do payload enviado para auditoria
            error_log('LotusPay cashIn (depositar.php) payload: ' . json_encode($payload, JSON_UNESCAPED_UNICODE));
            
            // Solicita a cobrança PIX com retry em caso de erro 5xx
            $response = null;
            $attempts = 0;
            while ($attempts < 3) {
                try {
                    $attempts++;
                    $response = $lotus->cashIn($payload);
                    break;
                } catch (Exception $ex) {
                    $msg = $ex->getMessage();
                    error_log('[LotusPay cashIn-depositar] tentativa ' . $attempts . ' falhou: ' . $msg);
                    if ($attempts >= 3 || (strpos($msg, '502') === false && strpos($msg, '503') === false && strpos($msg, '504') === false)) {
                        throw $ex;
                    }
                    usleep(250000 * $attempts);
                }
            }
            
            // Salva o depósito pendente no banco (tabela unificada: deposits)
            $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, status, payment_id, created_at, updated_at, external_id) VALUES (?, ?, 'pendente', NULL, NOW(), NOW(), ?)");
            
            if ($stmt === false) {
                $error = 'Erro na preparação da consulta: ' . $conn->error;
            } else {
                // Extrai campos padronizados do retorno
                $providerId = $response['id'] ?? null;
                $providerStatus = $response['status'] ?? 'Pending';
                $qrCode = $response['qrCode'] ?? ($response['qrcode'] ?? ($response['pix_code'] ?? ($response['qr_code'] ?? ($response['emv'] ?? ''))));
                $qrCodeBase64 = $response['qrCodeBase64'] ?? null;

                // Persiste referência mínima no banco compatível com webhook (sem armazenar QR no banco)
                $stmt->bind_param("ids", $user_id, $valor, $external_id);
                $stmt->execute();

                // Escolhe imagem do QR: prioriza a fornecida pela API; senão, gera localmente
                if ($qrCode) {
                    $qr_code_image = $qrCodeBase64 ?: QRGenerator::gerarQRCodePIX($qrCode);
                    // Dados para exibir ao usuário no formato solicitado
                    $qr_code_data = [
                        'id' => $providerId,
                        'status' => $providerStatus,
                        'qrCode' => $qrCode,
                        'qrCodeBase64' => $qrCodeBase64 ?: $qr_code_image,
                    ];
                    $success = 'QR Code gerado com sucesso! Escaneie para realizar o pagamento.';
                } else {
                    $error = 'Erro ao gerar código PIX';
                }
            }
        } catch (Exception $e) {
            $error = 'Erro ao gerar QR Code: ' . $e->getMessage();
        }
    }
}
?>

