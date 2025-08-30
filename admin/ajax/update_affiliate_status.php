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
    if (!isset($_POST['affiliate_id']) || !isset($_POST['status'])) {
        throw new Exception('Parâmetros obrigatórios não fornecidos');
    }
    
    $affiliate_id = (int)$_POST['affiliate_id'];
    $status = (int)$_POST['status'];
    
    // Validar status
    if (!in_array($status, [0, 1])) {
        throw new Exception('Status inválido');
    }
    
    // Verificar se o afiliado existe
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND affiliate_code IS NOT NULL");
    $check_stmt->bind_param("i", $affiliate_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Afiliado não encontrado');
    }
    
    // Atualizar status do afiliado
    $update_stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $status, $affiliate_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Erro ao atualizar status do afiliado');
    }
    
    // Log da ação
    $admin_id = $_SESSION['user_id'];
    $action = $status == 1 ? 'ativou' : 'desativou';
    $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
    $details = "Admin {$action} o afiliado ID: {$affiliate_id}";
    $log_stmt->bind_param("iss", $admin_id, $action, $details);
    $log_stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Status do afiliado atualizado com sucesso'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>