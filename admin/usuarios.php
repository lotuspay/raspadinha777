<?php
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

// Verificar se é uma requisição AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Processar alterações de saldo ou permissão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        if (isset($_POST['editar_saldo'])) {
            $id = intval($_POST['id']);
            $novoSaldo = floatval($_POST['saldo']);
            $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->bind_param("di", $novoSaldo, $id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Saldo atualizado com sucesso!';
                $response['new_balance'] = @number_format($novoSaldo, 2, ',', '.');
            } else {
                $response['message'] = 'Erro ao atualizar saldo.';
            }
        }

        if (isset($_POST['promover'])) {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Usuário promovido a administrador com sucesso!';
                $response['action'] = 'promote';
            } else {
                $response['message'] = 'Erro ao promover usuário.';
            }
        }

        if (isset($_POST['rebaixar'])) {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE users SET is_admin = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Usuário rebaixado com sucesso!';
                $response['action'] = 'demote';
            } else {
                $response['message'] = 'Erro ao rebaixar usuário.';
            }
        }

        if (isset($_POST['resetar_saldos'])) {
            if ($conn->query("UPDATE users SET balance = 0")) {
                $response['success'] = true;
                $response['message'] = 'Todos os saldos foram resetados com sucesso!';
                $response['action'] = 'reset_all';
            } else {
                $response['message'] = 'Erro ao resetar saldos.';
            }
        }

        if (isset($_POST['criar_usuario'])) {
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            
            // Verificar se o email já existe
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                $response['message'] = 'Este email já está cadastrado.';
            } else {
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, balance, is_admin) VALUES (?, ?, ?, 0, 0)");
                $stmt->bind_param("sss", $nome, $email, $senha);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Usuário criado com sucesso!';
                    $response['action'] = 'create_user';
                } else {
                    $response['message'] = 'Erro ao criar usuário.';
                }
            }
        }
    } catch (Exception $e) {
        $response['message'] = 'Erro interno: ' . $e->getMessage();
    }

    // Se for uma requisição AJAX, retornar JSON
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    // Se não for AJAX, redirecionar (comportamento original)
    if ($response['success']) {
        header("Location: usuarios.php?success=" . urlencode($response['message']));
    } else {
        header("Location: usuarios.php?error=" . urlencode($response['message']));
    }
    exit();
}

// Configuração da paginação
$users_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$current_page = intval($current_page); // Garantir que seja inteiro
$offset = ($current_page - 1) * $users_per_page;

// Buscar total de usuários para paginação
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_result = $conn->query($total_users_query);
$total_users = intval($total_result->fetch_assoc()['total']);
$total_pages = intval(ceil($total_users / $users_per_page));

// Buscar usuários da página atual com informações de afiliado
$users_query = "SELECT u.*, 
    CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END as is_affiliate,
    COALESCE(u.affiliate_balance, 0) as affiliate_balance
    FROM users u 
    LEFT JOIN affiliates a ON u.id = a.user_id AND a.is_active = 1
    ORDER BY u.id ASC 
    LIMIT $users_per_page OFFSET $offset";
$users_result = $conn->query($users_query);

// Buscar estatísticas gerais (para os cards)
$stats_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN is_admin = 1 THEN 1 ELSE 0 END) as admin_count,
    SUM(CASE WHEN is_admin = 0 THEN 1 ELSE 0 END) as user_count,
    SUM(balance) as total_balance
    FROM users";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS Components -->
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/header.css">
    <style>
        /* Dark Theme Variables */
        :root {
            --dark-bg: #1a1a1a;
            --dark-card: #2d2d2d;
            --dark-sidebar: #252525;
            --dark-text: #ffffff;
            --dark-text-secondary: #b0b0b0;
            --accent-blue: #4a9eff;
            --accent-green: #00d4aa;
            --accent-orange: #ff9500;
            --accent-purple: #8b5cf6;
            --accent-red: #ff5757;
            --sidebar-width: 280px;
        }

        /* Global Dark Theme */
        body {
            background: var(--dark-bg);
            color: var(--dark-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Sidebar Dark Theme */
        .sidebar {
            background: var(--dark-sidebar);
            border-right: 1px solid #404040;
        }

        .sidebar-brand {
            color: var(--accent-blue);
        }

        .nav-link {
            color: var(--dark-text-secondary);
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(74, 158, 255, 0.1);
            color: var(--accent-blue);
            border-left-color: var(--accent-blue);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 60px;
            padding: 2rem;
            min-height: calc(100vh - 60px);
            background: var(--dark-bg);
            transition: margin-left 0.3s ease;
        }

        /* Main content when sidebar is collapsed */
        .main-content.sidebar-collapsed {
            margin-left: 70px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
                margin-top: 60px;
            }
            
            .main-content.sidebar-collapsed {
                margin-left: 0;
            }
        }

        /* Modal Styles */
        .user-info-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-label {
            display: block;
            font-size: 0.85rem;
            color: var(--dark-text-secondary);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .info-value {
            display: block;
            color: var(--dark-text);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .modal-content {
            border: 1px solid #404040;
        }

        .modal-header {
            border-bottom: 1px solid #404040;
        }

        .input-group-text {
            background: var(--dark-card);
            border-color: #404040;
            color: var(--dark-text);
        }

        .form-control {
            background: var(--dark-card);
            border-color: #404040;
            color: var(--dark-text);
        }

        .form-control:focus {
            background: var(--dark-card);
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25);
        }

        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding: 1rem;
            background: var(--dark-card);
            border-radius: 8px;
            border: 1px solid #404040;
        }

        .pagination {
            margin: 0;
        }

        .page-link {
            background: var(--dark-card);
            border-color: #404040;
            color: var(--dark-text-secondary);
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .page-link:hover {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
            transform: translateY(-1px);
        }

        .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(74, 158, 255, 0.3);
        }

        .page-item.disabled .page-link {
            background: transparent;
            border-color: transparent;
            color: var(--dark-text-secondary);
            cursor: default;
        }

        .pagination-info {
            color: var(--dark-text-secondary);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .pagination-info {
                order: 2;
            }
        }

        /* Header */
        .dashboard-header {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #404040;
        }

        .dashboard-title {
            color: var(--dark-text);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .dashboard-subtitle {
            color: var(--dark-text-secondary);
            margin: 0;
            font-size: 0.85rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #404040;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-height: 100px;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: #505050;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0;
            line-height: 1.2;
        }

        .stat-label {
            color: var(--dark-text-secondary);
            font-size: 0.8rem;
            margin: 0;
        }

        /* Tables */
        .table-container {
            background: var(--dark-card);
            border-radius: 12px;
            border: 1px solid #404040;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .table-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #404040;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-text);
            margin: 0;
        }

        .table-info {
            color: var(--dark-text-secondary);
            font-size: 0.85rem;
        }

        .table-wrapper {
            overflow-x: auto;
            overflow-y: hidden;
        }

        .custom-table {
            width: 100%;
            margin: 0;
            min-width: 1000px; /* Garante scroll horizontal em telas pequenas */
        }

        .custom-table th {
            background: transparent;
            color: var(--dark-text-secondary);
            font-weight: 500;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 0.75rem 0.5rem;
            border: none;
            border-bottom: 1px solid #404040;
            white-space: nowrap;
            text-align: left;
        }

        .custom-table td {
            color: var(--dark-text);
            padding: 0.75rem 0.5rem;
            border: none;
            border-bottom: 1px solid #353535;
            vertical-align: middle;
            white-space: nowrap;
            text-align: left;
            font-size: 0.85rem;
        }

        /* Centralizar apenas a coluna de ações */
        .custom-table th:last-child,
        .custom-table td:last-child {
            text-align: center;
        }

        .custom-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Colunas específicas com larguras otimizadas */
        .custom-table th:nth-child(1), .custom-table td:nth-child(1) { width: 60px; } /* ID */
        .custom-table th:nth-child(2), .custom-table td:nth-child(2) { width: 150px; } /* Nome */
        .custom-table th:nth-child(3), .custom-table td:nth-child(3) { width: 200px; } /* Email */
        .custom-table th:nth-child(4), .custom-table td:nth-child(4) { width: 100px; } /* Saldo */
        .custom-table th:nth-child(5), .custom-table td:nth-child(5) { width: 80px; } /* Tipo */
        .custom-table th:nth-child(6), .custom-table td:nth-child(6) { width: 120px; } /* Data */
        .custom-table th:nth-child(7), .custom-table td:nth-child(7) { width: 80px; } /* Referrer */
        .custom-table th:nth-child(8), .custom-table td:nth-child(8) { width: 100px; } /* Status */
        .custom-table th:nth-child(9), .custom-table td:nth-child(9) { width: 110px; } /* Saldo Afiliado */

        /* Paginação */
        .pagination-container {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #404040;
            margin-top: 1rem;
        }

        .pagination {
            margin: 0;
            justify-content: center;
        }

        .page-link {
            background: transparent;
            border: 1px solid #404040;
            color: var(--dark-text-secondary);
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            border-radius: 4px;
        }

        .page-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #505050;
            color: var(--dark-text);
        }

        .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .page-item.disabled .page-link {
            background: transparent;
            border-color: #2a2a2a;
            color: #666;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 2px;
            }
            
            .action-buttons .btn {
                font-size: 0.75rem;
                padding: 0.2rem 0.4rem;
            }
        }

        /* Badges */
        .badge-success {
            background: var(--accent-green);
            color: white;
        }

        .badge-warning {
            background: var(--accent-orange);
            color: white;
        }

        .badge-danger {
            background: var(--accent-red);
            color: white;
        }

        .badge-info {
            background: var(--accent-blue);
            color: white;
        }

        .badge-admin {
            background: var(--accent-orange);
            color: white;
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
        }

        .badge-user {
            background: var(--accent-blue);
            color: white;
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
        }

        .badge-affiliate-active {
            background: var(--accent-green);
            color: white;
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
        }

        .badge-affiliate-inactive {
            background: #718096;
            color: white;
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
        }

        .admin-badge {
            background: var(--accent-orange);
            color: white;
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
        }

        .user-badge {
            background: var(--accent-blue);
            color: white;
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Buttons */
        .btn-primary {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .btn-primary:hover {
            background: #3a8bef;
            border-color: #3a8bef;
        }

        .btn-success {
            background: var(--accent-green);
            border-color: var(--accent-green);
        }

        .btn-success:hover {
            background: #00c49a;
            border-color: #00c49a;
        }

        .btn-outline-light {
            border-color: #404040;
            color: var(--dark-text-secondary);
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #505050;
            color: var(--dark-text);
        }

        /* Table Responsive */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .custom-table {
            min-width: 800px; /* Força scroll horizontal em telas pequenas */
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .dashboard-header {
                padding: 0.75rem 1rem;
            }
            
            .table-header {
                padding: 0.75rem;
            }

            .custom-table th,
            .custom-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
                white-space: nowrap;
            }

            /* Melhor visualização em mobile */
            .table-container {
                margin: 0 -1rem;
                border-radius: 0;
            }

            .action-buttons {
                display: flex;
                gap: 0.25rem;
                flex-wrap: nowrap;
            }

            .btn-sm {
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .custom-table {
                min-width: 700px;
            }

            .custom-table th,
            .custom-table td {
                padding: 0.5rem 0.375rem;
                font-size: 0.75rem;
            }

            .badge {
                font-size: 0.65rem;
                padding: 0.25rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/components/header.php'; ?>
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <h3 class="stat-value" data-stat="total_users"><?php echo $stats['total_users']; ?></h3>
                <p class="stat-label">Total de Usuários</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-orange);">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
                <h3 class="stat-value" data-stat="admin_count"><?php echo $stats['admin_count']; ?></h3>
                <p class="stat-label">Administradores</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <h3 class="stat-value" data-stat="user_count"><?php echo $stats['user_count']; ?></h3>
                <p class="stat-label">Usuários Comuns</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <h3 class="stat-value" data-stat="total_balance">R$ <?php echo @number_format($stats['total_balance'], 2, ',', '.'); ?></h3>
                <p class="stat-label">Saldo Total</p>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">Lista de Usuários</h3>
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Saldo</th>
                                    <th>Tipo</th>
                                    <th>Status Afiliado</th>
                                    <th>Saldo Afiliado</th>
                                    <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                        <?php if ($users_result && $users_result->num_rows > 0): ?>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr data-user-id="<?php echo $user['id']; ?>">
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>R$ <?php echo @number_format($user['balance'], 2, ',', '.'); ?></td>
                                    <td class="user-type-cell">
                                        <?php if ($user['is_admin'] == 1): ?>
                                            <span class="admin-badge"><i class="fas fa-crown me-1"></i>Admin</span>
                                        <?php else: ?>
                                            <span class="user-badge"><i class="fas fa-user me-1"></i>Usuário</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($user['is_affiliate']) && $user['is_affiliate'] == 1): ?>
                                            <span class="badge badge-affiliate-active">Afiliado Ativo</span>
                                        <?php else: ?>
                                            <span class="badge badge-affiliate-inactive">Não Afiliado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>R$ <?php echo @number_format(isset($user['affiliate_balance']) ? $user['affiliate_balance'] : 0, 2, ',', '.'); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Botão Editar Saldo -->
                        <button class="btn btn-sm btn-primary edit-balance-btn" 
                                data-id="<?php echo $user['id']; ?>"
                                data-name="<?php echo htmlspecialchars($user['name']); ?>"
                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                data-balance="<?php echo $user['balance']; ?>"
                                data-is-admin="<?php echo $user['is_admin']; ?>"
                                data-is-affiliate="<?php echo isset($user['is_affiliate']) ? $user['is_affiliate'] : 0; ?>"
                                title="Editar Saldo">
                            <i class="fas fa-edit"></i>
                        </button>
                                        
                                        <!-- Botão Promover/Rebaixar -->
                                        <?php if ($user['is_admin'] == 1): ?>
                                            <button class="btn btn-sm btn-warning demote-btn" 
                                                    data-user-id="<?php echo $user['id']; ?>"
                                                    title="Rebaixar para Usuário">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-success promote-btn" 
                                                    data-user-id="<?php echo $user['id']; ?>"
                                                    title="Promover para Admin">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center" style="padding: 2rem; color: var(--dark-text-secondary);">
                                    <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                    Nenhum usuário encontrado
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
        </div>
        
        <!-- Paginação -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                <span class="text-muted">
                    Mostrando <?php echo ($offset + 1); ?> a <?php echo min($offset + $users_per_page, $stats['total_users']); ?> 
                    de <?php echo $stats['total_users']; ?> usuários
                </span>
            </div>
            <nav aria-label="Navegação de páginas">
                <ul class="pagination">
                    <!-- Primeira página -->
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo intval($current_page) - 1; ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Reticências inicial se necessário -->
                    <?php if ($current_page > 3): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">1</a>
                        </li>
                        <?php if ($current_page > 4): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Páginas numeradas -->
                    <?php
                    $start_page = max(1, intval($current_page) - 2);
                    $end_page = min($total_pages, intval($current_page) + 2);
                    
                    // Ajustar para sempre mostrar 5 páginas quando possível
                    if ($end_page - $start_page < 4) {
                        if ($start_page == 1) {
                            $end_page = min($total_pages, $start_page + 4);
                        } else {
                            $start_page = max(1, $end_page - 4);
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Reticências final se necessário -->
                    <?php if (intval($current_page) < intval($total_pages) - 2): ?>
                        <?php if (intval($current_page) < intval($total_pages) - 3): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Última página -->
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo intval($current_page) + 1; ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Modal para Editar Saldo -->
    <div class="modal fade" id="editBalanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: var(--dark-bg); border: 1px solid var(--dark-border); border-radius: 12px;">
                <div class="modal-header" style="border-bottom: 1px solid var(--dark-border); padding: 1.5rem;">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon me-3" style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-edit" style="color: white; font-size: 1.2rem;"></i>
                        </div>
                        <h5 class="modal-title mb-0" style="color: var(--dark-text); font-weight: 600;">Editar Saldo do Usuário</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <!-- Card de Informações do Usuário -->
                    <div class="user-info-card mb-4" style="background: var(--card-bg); border: 1px solid var(--dark-border); border-radius: 12px; padding: 1.5rem;">
                        <div class="d-flex align-items-center mb-3">
                            <div class="user-avatar me-3" style="width: 50px; height: 50px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user" style="color: white; font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h6 class="mb-1" style="color: var(--dark-text); font-weight: 600;" id="modalUserName">-</h6>
                                <p class="mb-0" style="color: var(--dark-text-secondary); font-size: 0.9rem;" id="modalUserEmail">-</p>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="info-card" style="background: rgba(255,255,255,0.02); border-radius: 8px; padding: 1rem; text-align: center;">
                                    <div class="info-icon mb-2" style="color: var(--accent-blue);">
                                        <i class="fas fa-hashtag"></i>
                                    </div>
                                    <div class="info-label" style="color: var(--dark-text-secondary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">ID</div>
                                    <div class="info-value" style="color: var(--dark-text); font-weight: 600; font-size: 1.1rem;" id="modalUserId">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card" style="background: rgba(255,255,255,0.02); border-radius: 8px; padding: 1rem; text-align: center;">
                                    <div class="info-icon mb-2" style="color: var(--accent-orange);">
                                        <i class="fas fa-user-tag"></i>
                                    </div>
                                    <div class="info-label" style="color: var(--dark-text-secondary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Tipo</div>
                                    <div class="info-value" style="color: var(--dark-text); font-weight: 600; font-size: 1.1rem;" id="modalUserType">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card" style="background: rgba(255,255,255,0.02); border-radius: 8px; padding: 1rem; text-align: center;">
                                    <div class="info-icon mb-2" style="color: var(--accent-green);">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div class="info-label" style="color: var(--dark-text-secondary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Saldo Atual</div>
                                    <div class="info-value" style="color: var(--accent-green); font-weight: 600; font-size: 1.1rem;" id="modalCurrentBalance">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="affiliate-status" style="display: flex; align-items: center; justify-content: center; padding: 0.75rem; background: rgba(255,255,255,0.02); border-radius: 8px;">
                                <i class="fas fa-handshake me-2" style="color: var(--accent-purple);"></i>
                                <span style="color: var(--dark-text-secondary); font-size: 0.9rem;">Status Afiliado:</span>
                                <span class="ms-2" style="color: var(--dark-text); font-weight: 500;" id="modalAffiliateStatus">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formulário de Edição -->
                    <form id="editBalanceForm">
                        <input type="hidden" id="editUserId" name="id">
                        <div class="form-group mb-4">
                            <label for="editBalance" class="form-label" style="color: var(--dark-text); font-weight: 500; margin-bottom: 0.75rem;">Novo Saldo:</label>
                            <div class="input-group" style="border-radius: 8px; overflow: hidden;">
                                <span class="input-group-text" style="background: var(--dark-border); color: var(--dark-text); border: 1px solid var(--dark-border);">R$</span>
                                <input type="number" class="form-control" id="editBalance" name="saldo" step="0.01" min="0" required 
                                       style="background: var(--card-bg); color: var(--dark-text); border: 1px solid var(--dark-border); border-left: none;" 
                                       placeholder="0,00">
                            </div>
                            <div class="form-text" style="color: var(--dark-text-secondary); font-size: 0.85rem; margin-top: 0.5rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                Digite o novo saldo para este usuário
                            </div>
                        </div>
                        
                        <div class="modal-actions d-flex justify-content-end gap-3">
                            <button type="button" class="btn" data-bs-dismiss="modal" 
                                    style="background: var(--dark-border); color: var(--dark-text); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500;">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </button>
                            <button type="submit" class="btn" 
                                    style="background: var(--primary-color); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500;">
                                <i class="fas fa-save me-2"></i>Atualizar Saldo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Criar Usuário -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: var(--dark-bg); border: 1px solid var(--dark-border); border-radius: 12px;">
                <div class="modal-header" style="border-bottom: 1px solid var(--dark-border); padding: 1.5rem;">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon me-3" style="width: 40px; height: 40px; background: var(--accent-green); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-plus" style="color: white; font-size: 1.2rem;"></i>
                        </div>
                        <h5 class="modal-title mb-0" style="color: var(--dark-text); font-weight: 600;">Criar Novo Usuário</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <form id="createUserForm">
                        <div class="form-group mb-3">
                            <label for="userName" class="form-label" style="color: var(--dark-text); font-weight: 500; margin-bottom: 0.75rem;">
                                <i class="fas fa-user me-2" style="color: var(--accent-blue);"></i>Nome:
                            </label>
                            <input type="text" class="form-control" id="userName" name="nome" required 
                                   style="background: var(--card-bg); color: var(--dark-text); border: 1px solid var(--dark-border); border-radius: 8px; padding: 0.75rem;" 
                                   placeholder="Digite o nome completo">
                        </div>
                        <div class="form-group mb-3">
                            <label for="userEmail" class="form-label" style="color: var(--dark-text); font-weight: 500; margin-bottom: 0.75rem;">
                                <i class="fas fa-envelope me-2" style="color: var(--accent-purple);"></i>Email:
                            </label>
                            <input type="email" class="form-control" id="userEmail" name="email" required 
                                   style="background: var(--card-bg); color: var(--dark-text); border: 1px solid var(--dark-border); border-radius: 8px; padding: 0.75rem;" 
                                   placeholder="exemplo@email.com">
                        </div>
                        <div class="form-group mb-4">
                            <label for="userPassword" class="form-label" style="color: var(--dark-text); font-weight: 500; margin-bottom: 0.75rem;">
                                <i class="fas fa-lock me-2" style="color: var(--accent-orange);"></i>Senha:
                            </label>
                            <input type="password" class="form-control" id="userPassword" name="senha" required 
                                   style="background: var(--card-bg); color: var(--dark-text); border: 1px solid var(--dark-border); border-radius: 8px; padding: 0.75rem;" 
                                   placeholder="Digite uma senha segura">
                            <div class="form-text" style="color: var(--dark-text-secondary); font-size: 0.85rem; margin-top: 0.5rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                A senha deve ter pelo menos 6 caracteres
                            </div>
                        </div>
                        
                        <div class="modal-actions d-flex justify-content-end gap-3">
                            <button type="button" class="btn" data-bs-dismiss="modal" 
                                    style="background: var(--dark-border); color: var(--dark-text); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500;">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </button>
                            <button type="submit" class="btn" 
                                    style="background: var(--accent-green); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500;">
                                <i class="fas fa-user-plus me-2"></i>Criar Usuário
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // Aguardar o DOM estar completamente carregado
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado, inicializando event listeners...');
        
        // Restaurar estado da sidebar ao carregar a página
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
        }

        // Configurar SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Função para fazer requisições AJAX
        async function makeAjaxRequest(url, formData) {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Resposta não é JSON:', text);
                    throw new Error('Resposta inválida do servidor');
                }
                
                return await response.json();
            } catch (error) {
                console.error('Erro na requisição AJAX:', error);
                throw error;
            }
        }

        // Função para mostrar mensagem de sucesso ou erro
        function showMessage(success, message) {
            if (success) {
                Toast.fire({
                    icon: 'success',
                    title: message
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: message
                });
            }
        }

        // Editar Saldo
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-balance-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.edit-balance-btn');
                const userId = btn.dataset.id;
                const userName = btn.dataset.name;
                const userEmail = btn.dataset.email;
                const currentBalance = btn.dataset.balance;
                const isAdmin = btn.dataset.isAdmin;
                const isAffiliate = btn.dataset.isAffiliate;
                
                console.log('Abrindo modal para usuário:', userId);
                
                // Preencher informações do usuário
                document.getElementById('modalUserId').textContent = userId;
                document.getElementById('modalUserName').textContent = userName;
                document.getElementById('modalUserEmail').textContent = userEmail;
                document.getElementById('modalCurrentBalance').textContent = 'R$ ' + parseFloat(currentBalance).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                
                // Tipo de usuário
                const userTypeElement = document.getElementById('modalUserType');
                if (isAdmin == '1') {
                    userTypeElement.innerHTML = '<span class="badge badge-admin">Administrador</span>';
                } else {
                    userTypeElement.innerHTML = '<span class="badge badge-user">Usuário</span>';
                }
                
                // Status de afiliado
                const affiliateStatusElement = document.getElementById('modalAffiliateStatus');
                if (isAffiliate == '1') {
                    affiliateStatusElement.innerHTML = '<span class="badge badge-affiliate-active">Afiliado Ativo</span>';
                } else {
                    affiliateStatusElement.innerHTML = '<span class="badge badge-affiliate-inactive">Não Afiliado</span>';
                }
                
                // Preencher formulário
                document.getElementById('editUserId').value = userId;
                document.getElementById('editBalance').value = currentBalance;
                
                // Abrir modal com múltiplas tentativas
                const modalElement = document.getElementById('editBalanceModal');
                if (modalElement) {
                    // Aguardar um pouco para garantir que o DOM está pronto
                    setTimeout(() => {
                        // Primeiro, tentar com Bootstrap 5
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            try {
                                // Verificar se já existe uma instância do modal
                                let modal = bootstrap.Modal.getInstance(modalElement);
                                if (!modal) {
                                    modal = new bootstrap.Modal(modalElement, {
                                        backdrop: 'static',
                                        keyboard: false
                                    });
                                }
                                modal.show();
                                console.log('Modal aberto com Bootstrap 5');
                                return;
                            } catch (error) {
                                console.error('Erro com Bootstrap 5:', error);
                            }
                        }
                        
                        // Fallback para jQuery/Bootstrap 4
                        if (typeof $ !== 'undefined' && $.fn.modal) {
                            try {
                                $(modalElement).modal('show');
                                console.log('Modal aberto com jQuery');
                                return;
                            } catch (error) {
                                console.error('Erro com jQuery:', error);
                            }
                        }
                        
                        // Fallback manual - mostrar modal diretamente
                        console.log('Usando fallback manual para abrir modal');
                        modalElement.style.display = 'block';
                        modalElement.classList.add('show');
                        document.body.classList.add('modal-open');
                        
                        // Criar backdrop manualmente se não existir
                        if (!document.getElementById('manual-backdrop')) {
                            const backdrop = document.createElement('div');
                            backdrop.className = 'modal-backdrop fade show';
                            backdrop.id = 'manual-backdrop';
                            document.body.appendChild(backdrop);
                            
                            // Adicionar evento para fechar modal ao clicar no backdrop
                            backdrop.addEventListener('click', function() {
                                modalElement.style.display = 'none';
                                modalElement.classList.remove('show');
                                document.body.classList.remove('modal-open');
                                backdrop.remove();
                            });
                        }
                    }, 100);
                } else {
                    console.error('Modal element não encontrado');
                }
            }
        });

        // Event listeners para fechar modal manualmente
        document.addEventListener('click', function(e) {
            if (e.target.matches('[data-bs-dismiss="modal"]') || e.target.closest('[data-bs-dismiss="modal"]')) {
                const modalElement = e.target.closest('.modal');
                if (modalElement) {
                    // Tentar fechar com Bootstrap 5
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        try {
                            const modal = bootstrap.Modal.getInstance(modalElement);
                            if (modal) {
                                modal.hide();
                                return;
                            }
                        } catch (error) {
                            console.error('Erro ao fechar modal com Bootstrap 5:', error);
                        }
                    }
                    
                    // Fallback para jQuery
                    if (typeof $ !== 'undefined' && $.fn.modal) {
                        try {
                            $(modalElement).modal('hide');
                            return;
                        } catch (error) {
                            console.error('Erro ao fechar modal com jQuery:', error);
                        }
                    }
                    
                    // Fallback manual
                    modalElement.style.display = 'none';
                    modalElement.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.getElementById('manual-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            }
        });

        // Submeter formulário de edição de saldo
        document.getElementById('editBalanceForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('editar_saldo', '1');
            
            try {
                const result = await makeAjaxRequest(window.location.href, formData);
                showMessage(result.success, result.message);
                
                if (result.success) {
                    // Tentar fechar modal com múltiplas abordagens
                    const modalElement = document.getElementById('editBalanceModal');
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        try {
                            const modal = bootstrap.Modal.getInstance(modalElement);
                            if (modal) {
                                modal.hide();
                            }
                        } catch (error) {
                            // Fallback manual
                            modalElement.style.display = 'none';
                            modalElement.classList.remove('show');
                            document.body.classList.remove('modal-open');
                            const backdrop = document.getElementById('manual-backdrop');
                            if (backdrop) backdrop.remove();
                        }
                    } else {
                        // Fallback manual
                        modalElement.style.display = 'none';
                        modalElement.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.getElementById('manual-backdrop');
                        if (backdrop) backdrop.remove();
                    }
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (error) {
                showMessage(false, 'Erro ao atualizar saldo');
            }
        });

        // Função para atualizar estatísticas
        async function updateStats() {
            try {
                const response = await fetch(window.location.href);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const totalUsers = doc.querySelector('[data-stat="total_users"]');
                const adminCount = doc.querySelector('[data-stat="admin_count"]');
                const userCount = doc.querySelector('[data-stat="user_count"]');
                const totalBalance = doc.querySelector('[data-stat="total_balance"]');
                
                if (totalUsers) {
                    const currentElement = document.querySelector('[data-stat="total_users"]');
                    if (currentElement) currentElement.textContent = totalUsers.textContent;
                }
                if (adminCount) {
                    const currentElement = document.querySelector('[data-stat="admin_count"]');
                    if (currentElement) currentElement.textContent = adminCount.textContent;
                }
                if (userCount) {
                    const currentElement = document.querySelector('[data-stat="user_count"]');
                    if (currentElement) currentElement.textContent = userCount.textContent;
                }
                if (totalBalance) {
                    const currentElement = document.querySelector('[data-stat="total_balance"]');
                    if (currentElement) currentElement.textContent = totalBalance.textContent;
                }
            } catch (error) {
                console.error('Erro ao atualizar estatísticas:', error);
            }
        }

        // Função para atualizar tipo de usuário na tabela
        function updateUserType(userId, isAdmin) {
            console.log('Atualizando tipo de usuário:', userId, 'isAdmin:', isAdmin);
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (!row) {
                console.error('Linha do usuário não encontrada:', userId);
                return;
            }

            const typeCell = row.querySelector('.user-type-cell');
            const actionCell = row.querySelector('.action-buttons');
            
            // Obter dados do usuário da linha
            const userName = row.querySelector('td:nth-child(2)').textContent;
            const userEmail = row.querySelector('td:nth-child(3)').textContent;
            const userBalance = row.querySelector('td:nth-child(4)').textContent;
            const affiliateStatus = row.querySelector('td:nth-child(6)').textContent;
            const affiliateBalance = row.querySelector('td:nth-child(7)').textContent;

            if (isAdmin) {
                if (typeCell) typeCell.innerHTML = '<span class="admin-badge"><i class="fas fa-crown me-1"></i>Admin</span>';
                if (actionCell) {
                    actionCell.innerHTML = `
                        <button class="btn btn-primary btn-sm me-1 edit-balance-btn" 
                                data-user-id="${userId}" 
                                data-user-name="${userName}" 
                                data-user-email="${userEmail}" 
                                data-user-balance="${userBalance.replace('R$ ', '')}" 
                                data-user-type="admin" 
                                data-affiliate-status="${affiliateStatus}" 
                                data-affiliate-balance="${affiliateBalance.replace('R$ ', '')}" 
                                title="Editar saldo">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-warning btn-sm demote-btn" 
                                data-user-id="${userId}" 
                                title="Rebaixar para usuário">
                            <i class="fas fa-user-minus"></i>
                        </button>
                    `;
                }
            } else {
                if (typeCell) typeCell.innerHTML = '<span class="user-badge"><i class="fas fa-user me-1"></i>Usuário</span>';
                if (actionCell) {
                    actionCell.innerHTML = `
                        <button class="btn btn-primary btn-sm me-1 edit-balance-btn" 
                                data-user-id="${userId}" 
                                data-user-name="${userName}" 
                                data-user-email="${userEmail}" 
                                data-user-balance="${userBalance.replace('R$ ', '')}" 
                                data-user-type="usuario" 
                                data-affiliate-status="${affiliateStatus}" 
                                data-affiliate-balance="${affiliateBalance.replace('R$ ', '')}" 
                                title="Editar saldo">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-success btn-sm promote-btn" 
                                data-user-id="${userId}" 
                                title="Promover para admin">
                            <i class="fas fa-user-plus"></i>
                        </button>
                    `;
                }
            }
            
            console.log('Tipo de usuário atualizado com sucesso');
        }

        // Função para anexar event listeners aos botões de ação
        function attachActionButtonListeners() {
            console.log('Anexando event listeners aos botões de ação...');
            
            // Usar delegação de eventos para evitar problemas com elementos dinâmicos
            document.removeEventListener('click', handleActionButtons);
            document.addEventListener('click', handleActionButtons);
        }
        
        // Handler para botões de ação usando delegação de eventos
        async function handleActionButtons(e) {
            const promoteBtn = e.target.closest('.promote-btn');
            const demoteBtn = e.target.closest('.demote-btn');
            
            if (promoteBtn) {
                e.preventDefault();
                await handlePromoteUser(promoteBtn);
            } else if (demoteBtn) {
                e.preventDefault();
                await handleDemoteUser(demoteBtn);
            }
        }
        
        // Função para promover usuário
        async function handlePromoteUser(btn) {
            const userId = btn.dataset.userId;
            const userRow = btn.closest('tr');
            const userName = userRow.querySelector('td:nth-child(2)').textContent;
            
            // Confirmação antes de promover
            const result = await Swal.fire({
                title: '👑 Promover Usuário',
                html: `<div style="text-align: center; color: var(--dark-text); padding: 1rem;">
                    <div style="background: var(--dark-card); padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #404040;">
                        <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <div style="width: 32px; height: 32px; background: var(--accent-blue); border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                                <i class="fas fa-crown" style="color: white; font-size: 1rem;"></i>
                            </div>
                            <div style="text-align: left;">
                                <h4 style="color: var(--dark-text); margin: 0; font-weight: 600; font-size: 1.1rem;">${userName}</h4>
                                <p style="color: var(--dark-text-secondary); margin: 0; font-size: 0.85rem;">ID: ${userId}</p>
                            </div>
                        </div>
                    </div>
                    <div style="background: rgba(74, 158, 255, 0.1); padding: 1rem; border-radius: 8px; border: 1px solid rgba(74, 158, 255, 0.2);">
                        <p style="margin: 0; color: var(--dark-text); font-size: 0.9rem;">
                            <i class="fas fa-info-circle" style="color: var(--accent-blue); margin-right: 0.5rem;"></i>
                            Este usuário receberá privilégios administrativos completos
                        </p>
                    </div>
                </div>`,
                icon: null,
                background: 'var(--dark-bg)',
                color: 'var(--dark-text)',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-crown me-2"></i>Confirmar Promoção',
                cancelButtonText: '<i class="fas fa-arrow-left me-2"></i>Voltar',
                confirmButtonColor: 'var(--accent-blue)',
                cancelButtonColor: '#6c757d',
                buttonsStyling: true,
                customClass: {
                    popup: 'custom-swal-popup',
                    confirmButton: 'custom-swal-confirm',
                    cancelButton: 'custom-swal-cancel'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeInUp animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutDown animate__faster'
                }
            });
            
            if (!result.isConfirmed) return;
            
            const originalContent = btn.innerHTML;
            
            if (btn.disabled) return;
            
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                const formData = new FormData();
                formData.append('promover', '1');
                formData.append('id', userId);
                
                console.log('Promovendo usuário:', userId);
                const response = await makeAjaxRequest(window.location.href, formData);
                console.log('Resultado da promoção:', response);
                
                if (response.success) {
                    updateUserType(userId, true);
                    await updateStats();
                    
                    // Notificação de sucesso melhorada
                    Swal.fire({
                        title: '🎉 Promoção Realizada!',
                        html: `<div style="text-align: center; color: var(--dark-text); padding: 1rem;">
                            <div style="background: var(--dark-card); padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #404040;">
                                <div style="width: 48px; height: 48px; background: var(--accent-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fas fa-crown" style="color: white; font-size: 1.5rem;"></i>
                                </div>
                                <h4 style="color: var(--dark-text); margin: 0; font-weight: 600; margin-bottom: 0.5rem;">${userName}</h4>
                                <p style="color: var(--dark-text-secondary); margin: 0; font-size: 0.9rem;">Agora é um administrador</p>
                            </div>
                            <div style="background: rgba(0, 212, 170, 0.1); padding: 1rem; border-radius: 8px; border: 1px solid rgba(0, 212, 170, 0.2);">
                                <p style="margin: 0; color: var(--dark-text); font-size: 0.9rem;">
                                    <i class="fas fa-check-circle" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                                    Privilégios administrativos concedidos com sucesso
                                </p>
                            </div>
                        </div>`,
                        icon: null,
                        background: 'var(--dark-bg)',
                        color: 'var(--dark-text)',
                        confirmButtonColor: 'var(--accent-green)',
                        confirmButtonText: '<i class="fas fa-check me-2"></i>Entendi',
                        timer: 4000,
                        timerProgressBar: true,
                        showClass: {
                            popup: 'animate__animated animate__bounceIn animate__faster'
                        }
                    });
                } else {
                    throw new Error(response.message || 'Erro desconhecido');
                }
            } catch (error) {
                console.error('Erro ao promover usuário:', error);
                Swal.fire({
                    title: '❌ Ops! Algo deu errado',
                    html: `<div style="text-align: center; color: var(--dark-text); padding: 1rem;">
                        <div style="background: rgba(255, 87, 87, 0.1); padding: 1rem; border-radius: 8px; border: 1px solid rgba(255, 87, 87, 0.2);">
                            <p style="margin: 0; color: var(--dark-text); font-size: 0.9rem;">
                                <i class="fas fa-exclamation-triangle" style="color: var(--accent-red); margin-right: 0.5rem;"></i>
                                ${error.message || 'Não foi possível promover o usuário. Tente novamente.'}
                            </p>
                        </div>
                    </div>`,
                    icon: null,
                    background: 'var(--dark-bg)',
                    color: 'var(--dark-text)',
                    confirmButtonColor: 'var(--accent-red)',
                    confirmButtonText: '<i class="fas fa-redo me-2"></i>Tentar Novamente'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }
        
        // Função para rebaixar usuário
        async function handleDemoteUser(btn) {
            const userId = btn.dataset.userId;
            const userRow = btn.closest('tr');
            const userName = userRow.querySelector('td:nth-child(2)').textContent;
            
            // Confirmação antes de rebaixar
            const result = await Swal.fire({
                title: '👤 Alterar Privilégios',
                html: `<div style="text-align: center; color: var(--dark-text); padding: 1rem;">
                    <div style="background: var(--dark-card); padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #404040;">
                        <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <div style="width: 32px; height: 32px; background: var(--accent-orange); border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                                <i class="fas fa-user-minus" style="color: white; font-size: 1rem;"></i>
                            </div>
                            <div style="text-align: left;">
                                <h4 style="color: var(--dark-text); margin: 0; font-weight: 600; font-size: 1.1rem;">${userName}</h4>
                                <p style="color: var(--dark-text-secondary); margin: 0; font-size: 0.85rem;">ID: ${userId}</p>
                            </div>
                        </div>
                    </div>
                    <div style="background: rgba(255, 149, 0, 0.1); padding: 1rem; border-radius: 8px; border: 1px solid rgba(255, 149, 0, 0.2);">
                        <p style="margin: 0; color: var(--dark-text); font-size: 0.9rem;">
                            <i class="fas fa-info-circle" style="color: var(--accent-orange); margin-right: 0.5rem;"></i>
                            Os privilégios administrativos serão removidos
                        </p>
                    </div>
                </div>`,
                icon: null,
                background: 'var(--dark-bg)',
                color: 'var(--dark-text)',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-user-check me-2"></i>Confirmar Alteração',
                cancelButtonText: '<i class="fas fa-arrow-left me-2"></i>Voltar',
                confirmButtonColor: 'var(--accent-orange)',
                cancelButtonColor: '#6c757d',
                buttonsStyling: true,
                customClass: {
                    popup: 'custom-swal-popup',
                    confirmButton: 'custom-swal-confirm',
                    cancelButton: 'custom-swal-cancel'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeInUp animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutDown animate__faster'
                }
            });
            
            if (!result.isConfirmed) return;
            
            const originalContent = btn.innerHTML;
            
            if (btn.disabled) return;
            
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                const formData = new FormData();
                formData.append('rebaixar', '1');
                formData.append('id', userId);
                
                console.log('Rebaixando usuário:', userId);
                const response = await makeAjaxRequest(window.location.href, formData);
                console.log('Resultado do rebaixamento:', response);
                
                if (response.success) {
                    updateUserType(userId, false);
                    await updateStats();
                    
                    // Notificação de sucesso melhorada
                    Swal.fire({
                        title: '✅ Alteração Concluída!',
                        html: `<div style="text-align: center; color: var(--dark-text); padding: 1rem;">
                            <div style="background: var(--dark-card); padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #404040;">
                                <div style="width: 48px; height: 48px; background: var(--accent-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fas fa-user" style="color: white; font-size: 1.5rem;"></i>
                                </div>
                                <h4 style="color: var(--dark-text); margin: 0; font-weight: 600; margin-bottom: 0.5rem;">${userName}</h4>
                                <p style="color: var(--dark-text-secondary); margin: 0; font-size: 0.9rem;">Agora é um usuário comum</p>
                            </div>
                            <div style="background: rgba(0, 212, 170, 0.1); padding: 1rem; border-radius: 8px; border: 1px solid rgba(0, 212, 170, 0.2);">
                                <p style="margin: 0; color: var(--dark-text); font-size: 0.9rem;">
                                    <i class="fas fa-check-circle" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                                    Privilégios administrativos removidos com sucesso
                                </p>
                            </div>
                        </div>`,
                        icon: null,
                        background: 'var(--dark-bg)',
                        color: 'var(--dark-text)',
                        confirmButtonColor: 'var(--accent-green)',
                        confirmButtonText: '<i class="fas fa-check me-2"></i>Entendi',
                        timer: 4000,
                        timerProgressBar: true,
                        showClass: {
                            popup: 'animate__animated animate__bounceIn animate__faster'
                        }
                    });
                } else {
                    throw new Error(response.message || 'Erro desconhecido');
                }
            } catch (error) {
                console.error('Erro ao rebaixar usuário:', error);
                Swal.fire({
                    title: '❌ Ops! Algo deu errado',
                    html: `<div style="text-align: center; color: var(--dark-text); padding: 1rem;">
                        <div style="background: rgba(255, 87, 87, 0.1); padding: 1rem; border-radius: 8px; border: 1px solid rgba(255, 87, 87, 0.2);">
                            <p style="margin: 0; color: var(--dark-text); font-size: 0.9rem;">
                                <i class="fas fa-exclamation-triangle" style="color: var(--accent-red); margin-right: 0.5rem;"></i>
                                ${error.message || 'Não foi possível alterar os privilégios do usuário. Tente novamente.'}
                            </p>
                        </div>
                    </div>`,
                    icon: null,
                    background: 'var(--dark-bg)',
                    color: 'var(--dark-text)',
                    confirmButtonColor: 'var(--accent-red)',
                    confirmButtonText: '<i class="fas fa-redo me-2"></i>Tentar Novamente'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }

        // Criar usuário
        document.getElementById('createUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('criar_usuario', '1');
            
            try {
                const result = await makeAjaxRequest(window.location.href, formData);
                showMessage(result.success, result.message);
                
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
                    this.reset();
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (error) {
                showMessage(false, 'Erro ao criar usuário');
            }
        });

        // Resetar todos os saldos
        const resetAllBalancesBtn = document.getElementById('resetAllBalances');
        if (resetAllBalancesBtn) {
            resetAllBalancesBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Resetar Todos os Saldos',
                text: 'Esta ação irá zerar o saldo de TODOS os usuários. Esta ação não pode ser desfeita!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, resetar tudo',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const formData = new FormData();
                        formData.append('resetar_saldos', '1');
                        
                        const response = await makeAjaxRequest(window.location.href, formData);
                        showMessage(response.success, response.message);
                        
                        if (response.success) {
                            setTimeout(() => location.reload(), 1000);
                        }
                    } catch (error) {
                        showMessage(false, 'Erro ao resetar saldos');
                    }
                }
            });
            });
        }

        // Anexar event listeners aos botões de ação
        attachActionButtonListeners();

        // Verificar mensagens na URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            Toast.fire({
                icon: 'success',
                title: urlParams.get('success')
            });
            // Limpar a URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        if (urlParams.has('error')) {
            Toast.fire({
                icon: 'error',
                title: urlParams.get('error')
            });
            // Limpar a URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        console.log('Todos os event listeners foram registrados com sucesso!');
    }); // Fim do DOMContentLoaded
    </script>
</body>
</html>

