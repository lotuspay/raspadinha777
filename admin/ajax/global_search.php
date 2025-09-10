<?php
// Global Search API
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Obter dados da requisição (suporta tanto POST JSON quanto POST form-data)
$query = '';
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
    $query = trim($input['query'] ?? '');
} else {
    $query = trim($_POST['query'] ?? '');
}

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$results = [];
$searchTerm = '%' . $query . '%';

try {
    // Buscar usuários
    $stmt = $conn->prepare("
        SELECT id, name, email, 'user' as type 
        FROM users 
        WHERE (name LIKE ? OR email LIKE ?) 
        AND id != ? 
        LIMIT 5
    ");
    $stmt->bind_param('ssi', $searchTerm, $searchTerm, $_SESSION['usuario_id']);
    $stmt->execute();
    $users = $stmt->get_result();
    
    while ($user = $users->fetch_assoc()) {
        $results[] = [
            'type' => 'user',
            'title' => $user['name'],
            'description' => $user['email'],
            'url' => 'usuarios.php?busca=' . urlencode($user['name']),
            'icon' => 'bi-person-circle'
        ];
    }
    
    // Buscar transações/depósitos
    $stmt = $conn->prepare("
        SELECT d.id, d.valor, d.status, u.name as user_name, 'deposit' as type
        FROM deposits d
        JOIN users u ON d.user_id = u.id
        WHERE (d.id LIKE ? OR u.name LIKE ? OR d.status LIKE ?)
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $deposits = $stmt->get_result();
    
    while ($deposit = $deposits->fetch_assoc()) {
        $results[] = [
            'type' => 'transaction',
            'title' => 'Depósito #' . $deposit['id'],
            'description' => 'R$ ' . @number_format($deposit['valor'], 2, ',', '.') . ' - ' . $deposit['user_name'] . ' (' . ucfirst($deposit['status']) . ')',
            'url' => 'depositos.php?id=' . $deposit['id'],
            'icon' => 'bi-arrow-down-circle'
        ];
    }
    
    // Buscar saques
    $stmt = $conn->prepare("
        SELECT s.id, s.valor, s.status, u.name as user_name, 'withdrawal' as type
        FROM saques s
        JOIN users u ON s.user_id = u.id
        WHERE (s.id LIKE ? OR u.name LIKE ? OR s.status LIKE ?)
        ORDER BY s.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $withdrawals = $stmt->get_result();
    
    while ($withdrawal = $withdrawals->fetch_assoc()) {
        $results[] = [
            'type' => 'withdrawal',
            'title' => 'Saque #' . $withdrawal['id'],
            'description' => 'R$ ' . @number_format($withdrawal['valor'], 2, ',', '.') . ' - ' . $withdrawal['user_name'] . ' (' . ucfirst($withdrawal['status']) . ')',
            'url' => 'saques_pix.php?id=' . $withdrawal['id'],
            'icon' => 'bi-arrow-up-circle'
        ];
    }
    
    // Buscar páginas/funcionalidades do sistema
    $systemPages = [
        'usuarios' => ['title' => 'Gerenciamento de Usuários', 'url' => 'usuarios.php'],
        'depositos' => ['title' => 'Depósitos do Sistema', 'url' => 'depositos.php'],
        'saques' => ['title' => 'Saques PIX', 'url' => 'saques_pix.php'],
        'afiliados' => ['title' => 'Gestão de Afiliados', 'url' => 'affiliates.php'],
        'niveis' => ['title' => 'Níveis de Afiliados', 'url' => 'affiliate_levels.php'],
        'relatorios' => ['title' => 'Relatórios e Analytics', 'url' => 'relatorio.php'],
        'configuracoes' => ['title' => 'Configurações Globais', 'url' => 'global_settings.php'],
        'raspadinha' => ['title' => 'Controle de Raspadinha', 'url' => 'controle_raspadinha.php'],
        'premios' => ['title' => 'Configuração de Prêmios', 'url' => 'forcar_premio.php'],
        'influenciadores' => ['title' => 'Gerenciar Influenciadores', 'url' => 'influencer_management.php'],
        'pagamentos' => ['title' => 'Gestão de Pagamentos', 'url' => 'payout_management.php']
    ];
    
    foreach ($systemPages as $key => $page) {
        if (stripos($key, $query) !== false || stripos($page['title'], $query) !== false) {
            $results[] = [
                'type' => 'page',
                'title' => $page['title'],
                'description' => 'Página do sistema',
                'url' => $page['url'],
                'icon' => 'bi-gear'
            ];
        }
    }
    
    // Limitar resultados totais
    $results = array_slice($results, 0, 10);
    
    echo json_encode(['results' => $results]);
    
} catch (Exception $e) {
    error_log('Erro na busca global: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>