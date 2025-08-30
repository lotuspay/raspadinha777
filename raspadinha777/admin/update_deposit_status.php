<?php
session_start();
if (!isset($_SESSION["usuario_id"]) || $_SESSION["is_admin"] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

if (!isset($_POST['action']) || $_POST['action'] !== 'update_status') {
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    exit();
}

if (!isset($_POST['deposit_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros obrigatórios não fornecidos']);
    exit();
}

$deposit_id = intval($_POST['deposit_id']);
$status = $_POST['status'];

// Validar status
$valid_statuses = ['aprovado', 'rejeitado', 'pendente', 'pago'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit();
}

try {
    // Verificar se o depósito existe
    $check_stmt = $conn->prepare("SELECT id, user_id, amount, status FROM deposits WHERE id = ?");
    $check_stmt->bind_param("i", $deposit_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Depósito não encontrado']);
        exit();
    }
    
    $deposit = $result->fetch_assoc();
    $check_stmt->close();
    
    // Atualizar status do depósito
    $update_stmt = $conn->prepare("UPDATE deposits SET status = ?, updated_at = NOW() WHERE id = ?");
    $update_stmt->bind_param("si", $status, $deposit_id);
    
    if ($update_stmt->execute()) {
        // Se o status for 'aprovado' ou 'pago', adicionar o valor ao saldo do usuário
        if (($status === 'aprovado' || $status === 'pago') && $deposit['status'] !== 'aprovado' && $deposit['status'] !== 'pago') {
            $balance_stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $balance_stmt->bind_param("di", $deposit['amount'], $deposit['user_id']);
            $balance_stmt->execute();
            $balance_stmt->close();
        }
        // Se o status anterior era 'aprovado' ou 'pago' e agora não é, remover o valor do saldo
        elseif (($deposit['status'] === 'aprovado' || $deposit['status'] === 'pago') && $status !== 'aprovado' && $status !== 'pago') {
            $balance_stmt = $conn->prepare("UPDATE users SET balance = GREATEST(0, balance - ?) WHERE id = ?");
            $balance_stmt->bind_param("di", $deposit['amount'], $deposit['user_id']);
            $balance_stmt->execute();
            $balance_stmt->close();
        }
        
        $update_stmt->close();
        echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
    }
    
} catch (Exception $e) {
    error_log("Erro ao atualizar status do depósito: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>