<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Verificar se é admin
$stmt_admin = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt_admin->execute([$_SESSION['usuario_id']]);
$user = $stmt_admin->fetch();

if (!$user || $user['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado - apenas administradores']);
    exit;
}

// Verificar se o affiliate_id foi fornecido
if (!isset($_GET['affiliate_id']) || empty($_GET['affiliate_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do afiliado não fornecido']);
    exit;
}

$affiliate_id = intval($_GET['affiliate_id']);

try {
    // Buscar usuários cadastrados pelo afiliado
    $query = "
        SELECT 
            u.id,
            u.name,
            u.email,
            u.created_at,
            u.created_at as last_activity,
            (
                SELECT MIN(data_criacao) 
                FROM depositos d 
                WHERE d.usuario_id = u.id 
                AND d.status = 'aprovado'
            ) as first_deposit_date
        FROM users u 
        WHERE u.referrer_id = ?
        ORDER BY u.created_at DESC
        LIMIT 100
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$affiliate_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar os dados para o frontend
    $formatted_users = [];
    foreach ($users as $user) {
        $formatted_users[] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'created_at' => $user['created_at'],
            'last_activity' => $user['last_activity'],
            'first_deposit_date' => $user['first_deposit_date']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $formatted_users,
        'total' => count($formatted_users)
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao buscar usuários do afiliado: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}
?>