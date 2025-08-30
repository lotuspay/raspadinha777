<?php
session_start();
require_once '../config/database.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

try {
    if (!isset($_POST['affiliate_id']) || !isset($_POST['affiliate_code'])) {
        throw new Exception('Parâmetros obrigatórios não fornecidos');
    }
    
    $affiliate_id = (int)$_POST['affiliate_id'];
    $affiliate_code = trim($_POST['affiliate_code']);
    
    // Validar código do afiliado
    if (empty($affiliate_code)) {
        throw new Exception('O código do afiliado não pode estar vazio');
    }
    
    if (strlen($affiliate_code) < 3) {
        throw new Exception('O código do afiliado deve ter pelo menos 3 caracteres');
    }
    
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $affiliate_code)) {
        throw new Exception('O código do afiliado deve conter apenas letras, números, hífens e underscores');
    }
    
    // Verificar se o afiliado existe
    $check_stmt = $conn->prepare("SELECT id, affiliate_code FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $affiliate_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Afiliado não encontrado');
    }
    
    $affiliate_data = $result->fetch_assoc();
    
    // Verificar se o código já está em uso por outro usuário
    $duplicate_stmt = $conn->prepare("SELECT id FROM users WHERE affiliate_code = ? AND id != ?");
    $duplicate_stmt->bind_param("si", $affiliate_code, $affiliate_id);
    $duplicate_stmt->execute();
    $duplicate_result = $duplicate_stmt->get_result();
    
    if ($duplicate_result->num_rows > 0) {
        throw new Exception('Este código de afiliado já está sendo usado por outro usuário');
    }
    
    // Atualizar código do afiliado
    $update_stmt = $conn->prepare("UPDATE users SET affiliate_code = ? WHERE id = ?");
    $update_stmt->bind_param("si", $affiliate_code, $affiliate_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Erro ao atualizar código do afiliado');
    }
    
    // Log da ação
    $admin_id = $_SESSION['user_id'];
    $old_code = $affiliate_data['affiliate_code'];
    $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
    $action = 'update_affiliate_code';
    $details = "Admin alterou código do afiliado ID: {$affiliate_id} de '{$old_code}' para '{$affiliate_code}'";
    $log_stmt->bind_param("iss", $admin_id, $action, $details);
    $log_stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Código do afiliado atualizado com sucesso'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>