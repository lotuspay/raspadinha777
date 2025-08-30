<?php
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
            $callbackUrl = $scheme . '://' . $host . '/webhook_bspay_novo.php';

            // Payload para gerar cobrança PIX via Lotuspay
            $payload = [
                'amount' => $valor,
                'external_id' => $external_id,
                'customer' => [
                    'name' => $user['name'] ?? 'Cliente',
                    'document' => $customerDocument,
                    'email' => $user['email'] ?? null,
                ],
                'callbackUrl' => $callbackUrl,
                'metadata' => [
                    'user_id' => $user['id'] ?? $user_id,
                    'name' => $user['name'] ?? null,
                    'email' => $user['email'] ?? null,
                ]
            ];
            
            // Solicita a cobrança PIX
            $response = $lotus->cashIn($payload);
            
            // Salva o depósito pendente no banco
            $stmt = $conn->prepare("INSERT INTO depositos (usuario_id, valor, status, external_id, qr_code, pix_code, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            
            if ($stmt === false) {
                $error = 'Erro na preparação da consulta: ' . $conn->error;
            } else {
                // Normaliza possíveis campos de retorno do código Pix
                $pix_code = $response['qrcode'] ?? ($response['pix_code'] ?? ($response['qr_code'] ?? ($response['emv'] ?? '')));
                $qr_code = '';
                $status = 'pendente';
                $stmt->bind_param("idssss", $user_id, $valor, $status, $external_id, $qr_code, $pix_code);
                $stmt->execute();
                
                // Gera QR Code visual
                if ($pix_code) {
                    $qr_code_image = QRGenerator::gerarQRCodePIX($pix_code);
                    $qr_code_data = [
                        'external_id' => $external_id,
                        'valor' => $valor,
                        'pix_code' => $pix_code,
                        'qr_image' => $qr_code_image
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

