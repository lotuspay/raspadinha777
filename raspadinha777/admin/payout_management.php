<?php
session_start();
require_once '../includes/db.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Verificar se o admin tem 2FA configurado
$stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE id = ? AND is_admin = 1");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($user['two_factor_secret'])) {
    header("Location: setup_2fa.php");
    exit();
}

$message = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'approve_payout':
                $payout_id = (int)$_POST['payout_id'];
                $stmt = $conn->prepare("UPDATE payouts SET status = 'paid', paid_date = NOW() WHERE id = ?");
                $stmt->bind_param("i", $payout_id);
                if ($stmt->execute()) {
                    $message = "Pagamento aprovado com sucesso!";
                } else {
                    $message = "Erro ao aprovar pagamento.";
                }
                break;
                
            case 'reject_payout':
                $payout_id = (int)$_POST['payout_id'];
                $stmt = $conn->prepare("UPDATE payouts SET status = 'rejected' WHERE id = ?");
                $stmt->bind_param("i", $payout_id);
                if ($stmt->execute()) {
                    $message = "Pagamento rejeitado.";
                } else {
                    $message = "Erro ao rejeitar pagamento.";
                }
                break;
                
            case 'bulk_approve':
                if (isset($_POST['selected_payouts']) && is_array($_POST['selected_payouts'])) {
                    $ids = implode(',', array_map('intval', $_POST['selected_payouts']));
                    $stmt = $conn->prepare("UPDATE payouts SET status = 'paid', paid_date = NOW() WHERE id IN ($ids)");
                    if ($stmt->execute()) {
                        $count = count($_POST['selected_payouts']);
                        $message = "$count pagamentos aprovados em lote!";
                    }
                }
                break;
        }
    }
}

// Buscar estatísticas de pagamentos
$stats_query = "
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_requests,
        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
        SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount
    FROM payouts
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Buscar solicitações de pagamento
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$email_filter = isset($_GET['email_filter']) ? trim($_GET['email_filter']) : '';

$where_conditions = [];

// Filtro de status
if ($filter == 'pending') {
    $where_conditions[] = "p.status = 'pending'";
} elseif ($filter == 'paid') {
    $where_conditions[] = "p.status = 'paid'";
} elseif ($filter == 'rejected') {
    $where_conditions[] = "p.status = 'rejected'";
}

// Filtro de email
if (!empty($email_filter)) {
    $where_conditions[] = "u.email LIKE '%" . $conn->real_escape_string($email_filter) . "%'";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$payouts_query = "
    SELECT p.*, u.name as affiliate_name, u.email as affiliate_email, a.affiliate_code
    FROM payouts p
    JOIN affiliates a ON p.affiliate_id = a.id
    JOIN users u ON a.user_id = u.id
    $where_clause
    ORDER BY p.request_date DESC
    LIMIT 50
";
$payouts_result = $conn->query($payouts_query);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Pagamentos - Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Sidebar Component CSS -->
    <link href="components/sidebar.css" rel="stylesheet">
    <!-- Header Component CSS -->
    <link href="components/header.css" rel="stylesheet">
    
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
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
                margin-top: 60px;
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

        /* Colunas específicas com larguras otimizadas para pagamentos */
        .custom-table th:nth-child(1), .custom-table td:nth-child(1) { width: 60px; } /* Checkbox */
        .custom-table th:nth-child(2), .custom-table td:nth-child(2) { width: 150px; } /* Nome */
        .custom-table th:nth-child(3), .custom-table td:nth-child(3) { width: 200px; } /* Email */
        .custom-table th:nth-child(4), .custom-table td:nth-child(4) { width: 100px; } /* Código */
        .custom-table th:nth-child(5), .custom-table td:nth-child(5) { width: 100px; } /* Valor */
        .custom-table th:nth-child(6), .custom-table td:nth-child(6) { width: 130px; } /* Data Solicitação */
        .custom-table th:nth-child(7), .custom-table td:nth-child(7) { width: 100px; } /* Status */
        .custom-table th:nth-child(8), .custom-table td:nth-child(8) { width: 130px; } /* Data Pagamento */
        .custom-table th:nth-child(9), .custom-table td:nth-child(9) { width: 110px; } /* Ações */

        /* Melhorar visibilidade dos elementos da tabela */
        .custom-table .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
        }

        .custom-table .btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .custom-table input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent-blue);
        }

        .custom-table input[type="checkbox"]:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* Estilos para o filtro de email */
        .form-control {
            background: var(--dark-card);
            border: 1px solid #404040;
            color: var(--dark-text);
        }

        .form-control:focus {
            background: var(--dark-card);
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25);
        }

        .form-control::placeholder {
            color: var(--dark-text-secondary);
        }

        /* Melhorar contraste dos textos */
        .custom-table td strong {
            color: #ffffff;
        }

        .custom-table td small {
            color: #b0b0b0;
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

        /* Filter Tabs */
        .filter-tabs {
            margin-bottom: 1.5rem;
        }

        .filter-tabs .nav-pills .nav-link {
            background: var(--dark-card);
            border: 1px solid #404040;
            color: var(--dark-text-secondary);
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .filter-tabs .nav-pills .nav-link:hover {
            background: rgba(74, 158, 255, 0.1);
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .filter-tabs .nav-pills .nav-link.active {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        /* Bulk Actions */
        .bulk-actions {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .bulk-actions-inline {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bulk-actions-inline .btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
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

        .btn-danger {
            background: var(--accent-red);
            border-color: var(--accent-red);
        }

        .btn-danger:hover {
            background: #ff4747;
            border-color: #ff4747;
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

        /* Alerts */
        .alert-info {
            background: rgba(74, 158, 255, 0.1);
            border-color: var(--accent-blue);
            color: var(--accent-blue);
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
            }

            .filter-tabs .nav-pills .nav-link {
                font-size: 0.85rem;
                padding: 0.4rem 0.8rem;
            }

            .table-header .d-flex {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .bulk-actions-inline {
                width: 100%;
                justify-content: flex-start;
            }

            .bulk-actions-inline .btn {
                font-size: 0.7rem;
                padding: 0.25rem 0.5rem;
            }

            .pagination-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .pagination-info {
                order: 2;
            }
        }
    </style>
</head>
<body>
     <?php include 'components/header.php'; ?>
     <?php include 'components/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">

        <!-- Mensagens -->
        <?php if ($message): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Total de Solicitações -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="bi bi-list-ul"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?php echo number_format($stats['total_requests']); ?></h3>
                <p class="stat-label">Total de Solicitações</p>
            </div>

            <!-- Pendentes -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-orange);">
                        <i class="bi bi-clock"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?php echo number_format($stats['pending_requests']); ?></h3>
                <p class="stat-label">Pendentes</p>
            </div>

            <!-- Pagos -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?php echo number_format($stats['paid_requests']); ?></h3>
                <p class="stat-label">Pagos</p>
            </div>

            <!-- Valor Pendente -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
                <h3 class="stat-value">R$ <?php echo number_format($stats['pending_amount'], 2, ',', '.'); ?></h3>
                <p class="stat-label">Valor Pendente</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">Filtros</h2>
            </div>
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="filter-tabs">
                        <ul class="nav nav-pills flex-wrap">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'all' ? 'active' : ''; ?>" href="?filter=all<?php echo isset($_GET['email_filter']) ? '&email_filter=' . urlencode($_GET['email_filter']) : ''; ?>">
                                    <i class="bi bi-list"></i> <span class="d-none d-sm-inline">Todos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'pending' ? 'active' : ''; ?>" href="?filter=pending<?php echo isset($_GET['email_filter']) ? '&email_filter=' . urlencode($_GET['email_filter']) : ''; ?>">
                                    <i class="bi bi-clock"></i> <span class="d-none d-sm-inline">Pendentes</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'paid' ? 'active' : ''; ?>" href="?filter=paid<?php echo isset($_GET['email_filter']) ? '&email_filter=' . urlencode($_GET['email_filter']) : ''; ?>">
                                    <i class="bi bi-check-circle"></i> <span class="d-none d-sm-inline">Pagos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'rejected' ? 'active' : ''; ?>" href="?filter=rejected<?php echo isset($_GET['email_filter']) ? '&email_filter=' . urlencode($_GET['email_filter']) : ''; ?>">
                                    <i class="bi bi-x-circle"></i> <span class="d-none d-sm-inline">Rejeitados</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <form method="GET" class="d-flex">
                        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                        <input type="text" class="form-control form-control-sm" name="email_filter" 
                               placeholder="Filtrar por email..." 
                               value="<?php echo isset($_GET['email_filter']) ? htmlspecialchars($_GET['email_filter']) : ''; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-search"></i>
                        </button>
                        <?php if (isset($_GET['email_filter']) && $_GET['email_filter'] != ''): ?>
                            <a href="?filter=<?php echo htmlspecialchars($filter); ?>" class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabela de Pagamentos -->
        <div class="table-container">
            <div class="table-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h3 class="table-title mb-0">
                        <i class="bi bi-cash-stack me-2"></i> Solicitações de Pagamento
                    </h3>
                    <div class="bulk-actions-inline">
                        <form method="POST" id="bulkForm" class="d-flex align-items-center gap-2 flex-wrap">
                            <input type="hidden" name="action" value="bulk_approve">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()" title="Selecionar todos os pagamentos pendentes">
                                <i class="bi bi-check-all"></i> <span class="d-none d-lg-inline">Selecionar Todos</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()" title="Desmarcar todos">
                                <i class="bi bi-x"></i> <span class="d-none d-lg-inline">Desmarcar</span>
                            </button>
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Tem certeza que deseja aprovar os pagamentos selecionados?')" title="Aprovar pagamentos selecionados">
                                <i class="bi bi-check-circle"></i> <span class="d-none d-lg-inline">Aprovar Selecionados</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
                
            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll()">
                        </th>
                        <th>Afiliado</th>
                        <th class="d-none d-md-table-cell">Email</th>
                        <th class="d-none d-lg-table-cell">Código</th>
                        <th>Valor</th>
                        <th class="d-none d-lg-table-cell">Solicitação</th>
                        <th>Status</th>
                        <th class="d-none d-lg-table-cell">Pagamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payouts_result->num_rows > 0): ?>
                        <?php while ($payout = $payouts_result->fetch_assoc()): ?>
                        <tr data-payout-id="<?php echo $payout['id']; ?>">
                            <td>
                                <input type="checkbox" name="selected_payouts[]" value="<?php echo $payout['id']; ?>" form="bulkForm" 
                                       <?php echo $payout['status'] != 'pending' ? 'disabled' : ''; ?>>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($payout['affiliate_name']); ?>
                                <div class="d-md-none">
                                    <small class="text-muted"><?php echo htmlspecialchars($payout['affiliate_email']); ?></small>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo htmlspecialchars($payout['affiliate_email']); ?>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <code><?php echo htmlspecialchars($payout['affiliate_code']); ?></code>
                            </td>
                            <td>
                                <strong class="text-success">R$ <?php echo number_format($payout['amount'], 2, ',', '.'); ?></strong>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <?php echo date('d/m/Y H:i', strtotime($payout['request_date'])); ?>
                            </td>
                            <td>
                                <span class="badge <?php 
                                    echo $payout['status'] == 'paid' ? 'badge-success' : 
                                         ($payout['status'] == 'pending' ? 'badge-warning' : 'badge-danger'); 
                                ?>">
                                    <?php 
                                    echo $payout['status'] == 'paid' ? 'Pago' : 
                                         ($payout['status'] == 'pending' ? 'Pendente' : 'Rejeitado'); 
                                    ?>
                                </span>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <?php 
                                echo isset($payout['paid_date']) && $payout['paid_date'] ? date('d/m/Y H:i', strtotime($payout['paid_date'])) : '-'; 
                                ?>
                            </td>
                            <td>
                                <?php if ($payout['status'] == 'pending'): ?>
                                    <div class="action-buttons">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve_payout">
                                            <input type="hidden" name="payout_id" value="<?php echo $payout['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Aprovar este pagamento?')"
                                                    title="Aprovar">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="reject_payout">
                                            <input type="hidden" name="payout_id" value="<?php echo $payout['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Rejeitar este pagamento?')"
                                                    title="Rejeitar">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center" style="padding: 2rem; color: var(--dark-text-secondary);">
                                <i class="bi bi-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                Nenhuma solicitação encontrada
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Atalho de teclado Alt + Backspace para voltar
            document.addEventListener('keydown', function(e) {
                if (e.altKey && e.key === 'Backspace') {
                    e.preventDefault();
                    history.back();
                }
            });

            console.log('🎯 Gestão de Pagamentos carregada com sucesso!');
        });

        function selectAll() {
            const checkboxes = document.querySelectorAll('input[name="selected_payouts[]"]:not(:disabled)');
            checkboxes.forEach(cb => cb.checked = true);
            document.getElementById('selectAllCheckbox').checked = true;
        }

        function deselectAll() {
            const checkboxes = document.querySelectorAll('input[name="selected_payouts[]"]:not(:disabled)');
            checkboxes.forEach(cb => cb.checked = false);
            document.getElementById('selectAllCheckbox').checked = false;
        }

        function toggleAll() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const checkboxes = document.querySelectorAll('input[name="selected_payouts[]"]:not(:disabled)');
            checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
        }

        // Atualizar estado do checkbox "Selecionar Todos" baseado nos checkboxes individuais
        function updateSelectAllState() {
            const checkboxes = document.querySelectorAll('input[name="selected_payouts[]"]:not(:disabled)');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const checkedCount = document.querySelectorAll('input[name="selected_payouts[]"]:not(:disabled):checked').length;
            
            if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === checkboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }

        // Adicionar event listeners aos checkboxes individuais
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_payouts[]"]');
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelectAllState);
            });
            updateSelectAllState();
        });
    </script>
</body>
</html>

