<?php
session_start();
require_once '../config/database.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Configurar cabeçalhos para JSON
header('Content-Type: application/json');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'get_affiliate_details':
            $affiliateId = intval($input['affiliate_id']);
            $stmt = $conn->prepare("
                SELECT u.*, a.affiliate_code, a.commission_rate, a.total_earnings, 
                       a.total_paid, a.pending_balance, a.created_at as affiliate_since,
                       COUNT(DISTINCT r.id) as total_referrals,
                       COUNT(DISTINCT c.id) as total_commissions,
                       SUM(CASE WHEN c.status = 'approved' THEN c.amount ELSE 0 END) as approved_commissions
                FROM users u 
                LEFT JOIN affiliates a ON u.id = a.user_id 
                LEFT JOIN referrals r ON a.id = r.affiliate_id
                LEFT JOIN commissions c ON a.id = c.affiliate_id
                WHERE u.id = ? AND u.is_affiliate = 1
                GROUP BY u.id
            ");
            $stmt->bind_param('i', $affiliateId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($affiliate = $result->fetch_assoc()) {
                echo json_encode([
                    'success' => true, 
                    'data' => $affiliate
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Afiliado não encontrado'
                ]);
            }
            break;
            
        case 'toggle_affiliate_status':
            $affiliateId = intval($input['affiliate_id']);
            $newStatus = intval($input['new_status']);
            
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND is_affiliate = 1");
            $stmt->bind_param('ii', $newStatus, $affiliateId);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $statusText = $newStatus ? 'ativado' : 'desativado';
                echo json_encode([
                    'success' => true, 
                    'message' => "Afiliado {$statusText} com sucesso!",
                    'new_status' => $newStatus
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Erro ao alterar status do afiliado'
                ]);
            }
            break;
            
        case 'get_affiliate_referrals':
            $affiliateId = intval($input['affiliate_id']);
            $stmt = $conn->prepare("
                SELECT r.*, u.username, u.email, u.created_at as user_created_at
                FROM referrals r
                JOIN users u ON r.referred_user_id = u.id
                JOIN affiliates a ON r.affiliate_id = a.id
                WHERE a.user_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->bind_param('i', $affiliateId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $referrals = [];
            while ($row = $result->fetch_assoc()) {
                $referrals[] = $row;
            }
            
            echo json_encode([
                'success' => true, 
                'data' => $referrals
            ]);
            break;
            
        case 'get_affiliate_commissions':
            $affiliateId = intval($input['affiliate_id']);
            $stmt = $conn->prepare("
                SELECT c.*, u.username as referred_username
                FROM commissions c
                JOIN affiliates a ON c.affiliate_id = a.id
                LEFT JOIN users u ON c.referred_user_id = u.id
                WHERE a.user_id = ?
                ORDER BY c.created_at DESC
            ");
            $stmt->bind_param('i', $affiliateId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $commissions = [];
            while ($row = $result->fetch_assoc()) {
                $commissions[] = $row;
            }
            
            echo json_encode([
                'success' => true, 
                'data' => $commissions
            ]);
            break;
            
        case 'bulk_action':
            $bulkAction = $input['bulk_action'];
            $affiliateIds = $input['affiliate_ids'];
            
            if (empty($affiliateIds) || !is_array($affiliateIds)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Nenhum afiliado selecionado'
                ]);
                break;
            }
            
            $placeholders = str_repeat('?,', count($affiliateIds) - 1) . '?';
            $affected = 0;
            
            switch ($bulkAction) {
                case 'activate':
                    $stmt = $conn->prepare("UPDATE users SET status = 1 WHERE id IN ({$placeholders}) AND is_affiliate = 1");
                    $stmt->bind_param(str_repeat('i', count($affiliateIds)), ...$affiliateIds);
                    $stmt->execute();
                    $affected = $stmt->affected_rows;
                    $message = "{$affected} afiliados ativados com sucesso!";
                    break;
                    
                case 'deactivate':
                    $stmt = $conn->prepare("UPDATE users SET status = 0 WHERE id IN ({$placeholders}) AND is_affiliate = 1");
                    $stmt->bind_param(str_repeat('i', count($affiliateIds)), ...$affiliateIds);
                    $stmt->execute();
                    $affected = $stmt->affected_rows;
                    $message = "{$affected} afiliados desativados com sucesso!";
                    break;
                    
                default:
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Ação não reconhecida'
                    ]);
                    exit;
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'affected_rows' => $affected
            ]);
            break;
            
        case 'update_affiliate':
            $affiliateId = intval($input['affiliate_id']);
            $data = $input['data'];
            
            // Validar dados obrigatórios
            if (empty($data['username']) || empty($data['email'])) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Nome de usuário e email são obrigatórios'
                ]);
                break;
            }
            
            // Verificar se email já existe (exceto para o próprio usuário)
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param('si', $data['email'], $affiliateId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Este email já está em uso por outro usuário'
                ]);
                break;
            }
            
            // Atualizar dados do usuário
            $stmt = $conn->prepare("
                UPDATE users SET 
                    username = ?, 
                    email = ?, 
                    phone = ?, 
                    status = ?
                WHERE id = ? AND is_affiliate = 1
            ");
            $stmt->bind_param('sssii', 
                $data['username'], 
                $data['email'], 
                $data['phone'], 
                $data['status'], 
                $affiliateId
            );
            
            if ($stmt->execute()) {
                // Atualizar dados do afiliado se fornecidos
                if (isset($data['commission_rate'])) {
                    $stmt = $conn->prepare("
                        UPDATE affiliates SET commission_rate = ? 
                        WHERE user_id = ?
                    ");
                    $stmt->bind_param('di', $data['commission_rate'], $affiliateId);
                    $stmt->execute();
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Afiliado atualizado com sucesso!'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Erro ao atualizar afiliado'
                ]);
            }
            break;
            

            
        default:
            echo json_encode([
                'success' => false, 
                'message' => 'Ação não reconhecida'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro em affiliate_actions.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor'
    ]);
}

$conn->close();
?>