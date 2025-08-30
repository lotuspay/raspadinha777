<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/affiliate_functions.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

// Verifica se é admin
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !$user['is_admin']) {
    header("Location: ../login.php");
    exit();
}

$success_message = '';

// Processar ações POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_commission':
                $affiliate_id = (int)$_POST["affiliate_id"];
                $revshare_rate = (float)$_POST["revshare_rate"];
                $revshare_rate_admin = (float)$_POST["revshare_rate_admin"];
                $allow_sub_earnings = isset($_POST["allow_sub_earnings"]) ? 1 : 0;
                
                $stmt = $conn->prepare("UPDATE affiliates SET revshare_commission_rate = ?, revshare_commission_rate_admin = ?, allow_sub_affiliate_earnings = ? WHERE id = ?");
                $stmt->bind_param("ddii", $revshare_rate, $revshare_rate_admin, $allow_sub_earnings, $affiliate_id);
                if ($stmt->execute()) {
                    $success_message = "Comissões atualizadas com sucesso!";
                } else {
                    $success_message = "Erro ao atualizar comissões.";
                }
                break;
                
            case 'toggle_status':
                $affiliate_id = (int)$_POST['affiliate_id'];
                $new_status = (int)$_POST['new_status'];
                
                $stmt = $conn->prepare("UPDATE affiliates SET is_active = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_status, $affiliate_id);
                if ($stmt->execute()) {
                    $success_message = "Status do afiliado atualizado!";
                } else {
                    $success_message = "Erro ao atualizar status do afiliado.";
                }
                break;
                
            case 'add_simulated_data':
                $affiliate_id = (int)$_POST['affiliate_id'];
                $simulated_clicks = (int)$_POST['simulated_clicks'];
                $simulated_conversions = (int)$_POST['simulated_conversions'];
                $report_date = $_POST['report_date'];
                
                $stmt = $conn->prepare("INSERT INTO admin_simulated_reports (affiliate_id, simulated_clicks, simulated_conversions, report_date) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE simulated_clicks = ?, simulated_conversions = ?");
                $stmt->bind_param("iiisii", $affiliate_id, $simulated_clicks, $simulated_conversions, $report_date, $simulated_clicks, $simulated_conversions);
                if ($stmt->execute()) {
                    $success_message = "Dados simulados adicionados!";
                } else {
                    $success_message = "Erro ao adicionar dados simulados.";
                }
                break;
        }
    }
}

// Configuração de paginação
$itensPorPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Busca com filtro
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$whereClause = '';
$params = [];
$types = '';

if (!empty($busca)) {
    $whereClause = "WHERE u.name LIKE ? OR u.email LIKE ? OR a.affiliate_code LIKE ?";
    $params = ["%$busca%", "%$busca%", "%$busca%"];
    $types = 'sss';
}

// Contar total de registros
$countQuery = "SELECT COUNT(*) as total FROM affiliates a JOIN users u ON a.user_id = u.id $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRegistros = $countStmt->get_result()->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Buscar afiliados com paginação
$affiliates_query = "
    SELECT 
        a.id as affiliate_id,
        a.user_id,
        a.affiliate_code,
        a.cpa_commission_rate,
        a.revshare_commission_rate,
        a.cpa_commission_rate_admin,
        a.revshare_commission_rate_admin,
        a.fixed_commission_per_signup,
        a.allow_sub_affiliate_earnings,
        a.is_active,
        a.created_at,
        u.name,
        u.email,
        u.affiliate_balance,
        (SELECT COUNT(*) FROM affiliate_clicks ac WHERE ac.affiliate_id = a.id) as total_clicks,
        (SELECT COUNT(*) FROM affiliate_conversions conv WHERE conv.affiliate_id = a.id AND conv.conversion_type = 'signup') as total_signups,
        (SELECT COUNT(*) FROM affiliate_conversions conv WHERE conv.affiliate_id = a.id AND conv.conversion_type = 'deposit') as total_deposits,
        (SELECT COALESCE(SUM(amount), 0) FROM commissions c WHERE c.affiliate_id = a.id AND c.type = 'CPA') as total_cpa,
        (SELECT COALESCE(SUM(amount), 0) FROM commissions c WHERE c.affiliate_id = a.id AND c.type = 'RevShare') as total_revshare,
        (SELECT COUNT(*) FROM referrals r WHERE r.referrer_id = a.user_id) as total_referrals
    FROM affiliates a
    JOIN users u ON a.user_id = u.id
    $whereClause
    ORDER BY a.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($affiliates_query);
if (!empty($params)) {
    $params[] = $itensPorPagina;
    $params[] = $offset;
    $types .= 'ii';
} else {
    $params = [$itensPorPagina, $offset];
    $types = 'ii';
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$affiliates_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Afiliados - Admin</title>
    
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

        .stat-change {
            font-size: 0.7rem;
            padding: 0.15rem 0.4rem;
            border-radius: 3px;
            font-weight: 500;
        }

        .stat-change.positive {
            background: rgba(0, 212, 170, 0.1);
            color: var(--accent-green);
        }

        .stat-change.negative {
            background: rgba(255, 87, 87, 0.1);
            color: var(--accent-red);
        }

        .stat-card small {
            font-size: 0.7rem;
            color: var(--dark-text-secondary);
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

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: var(--accent-green);
            color: white;
        }

        .status-inactive {
            background: var(--accent-red);
            color: white;
        }

        /* Tables */
        .table-container {
            background: var(--dark-card);
            border-radius: 12px;
            border: 1px solid #404040;
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid #404040;
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-text);
        }

        /* Estilos para as abas do modal */
        .nav-tabs-dark {
            border-bottom: 1px solid #404040 !important;
        }
        .nav-tabs-dark .nav-link {
            background-color: #1a1a1a !important;
            border: 1px solid #404040 !important;
            color: var(--dark-text-secondary) !important;
            margin-right: 2px;
            border-bottom: none !important;
            transition: all 0.3s ease;
        }
        .nav-tabs-dark .nav-link:hover {
            background-color: var(--dark-card) !important;
            color: var(--dark-text) !important;
            border-color: #505050 !important;
        }
        .nav-tabs-dark .nav-link.active {
            background-color: var(--dark-card) !important;
            color: var(--dark-text) !important;
            border-color: #404040 #404040 var(--dark-card) !important;
            border-bottom: 1px solid var(--dark-card) !important;
        }
        .tab-content {
            background-color: transparent !important;
        }
        .badge.bg-primary {
            background-color: var(--accent-blue) !important;
        }
        /* Melhorar contraste dos cards de resumo financeiro */
        .border {
            border-color: #404040 !important;
        }
        .bg-dark {
            background-color: #1a1a1a !important;
            margin: 0;
        }

        .custom-table {
            width: 100%;
            margin: 0;
        }

        .custom-table th {
            background: transparent;
            color: var(--dark-text-secondary);
            font-weight: 500;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1.5rem;
            border: none;
            border-bottom: 1px solid #404040;
        }

        .custom-table td {
            color: var(--dark-text);
            padding: 1rem 1.5rem;
            border: none;
            border-bottom: 1px solid #353535;
        }

        .custom-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Buttons */
        .btn-primary {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .btn-primary:hover {
            background: #3a8bef;
            border-color: #3a8bef;
        }

        .btn-outline-primary {
            color: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .btn-outline-primary:hover {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .btn-outline-info {
            color: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .btn-outline-info:hover {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .btn-outline-success {
            color: var(--accent-green);
            border-color: var(--accent-green);
        }

        .btn-outline-success:hover {
            background: var(--accent-green);
            border-color: var(--accent-green);
        }

        .btn-outline-danger {
            color: var(--accent-red);
            border-color: var(--accent-red);
        }

        .btn-outline-danger:hover {
            background: var(--accent-red);
            border-color: var(--accent-red);
        }

        /* Modals */
        .modal-content {
            background: var(--dark-card) !important;
            border: 1px solid #404040 !important;
            color: var(--dark-text) !important;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--accent-blue), #3a8bef) !important;
            color: white !important;
            border-bottom: 1px solid #404040 !important;
        }

        .modal-body {
            background: var(--dark-card) !important;
            color: var(--dark-text) !important;
        }

        .modal-footer {
            background: var(--dark-card) !important;
            border-top: 1px solid #404040 !important;
        }

        /* Modal Tables */
        .modal .table {
            background: transparent !important;
            color: var(--dark-text) !important;
        }

        .modal .table th {
            background: rgba(255, 255, 255, 0.05) !important;
            color: var(--dark-text) !important;
            border-color: #404040 !important;
        }

        .modal .table td {
            background: transparent !important;
            color: var(--dark-text) !important;
            border-color: #353535 !important;
        }

        .modal .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02) !important;
        }

        /* Modal Text Elements */
        .modal h6 {
            color: var(--dark-text) !important;
            font-weight: 600;
            margin-bottom: 1rem;
            border-bottom: 1px solid #404040;
            padding-bottom: 0.5rem;
        }
        
        /* Exceção para títulos com fundo branco - forçar texto preto */
        .modal .bg-white h6.text-dark {
            color: #000000 !important;
        }

        .modal p {
            color: var(--dark-text) !important;
        }

        .modal code {
            background: rgba(74, 158, 255, 0.2) !important;
            color: var(--accent-blue) !important;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }

        /* Modal Badges */
        .modal .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        /* Modal Alert */
        .modal .alert {
            background: rgba(255, 193, 7, 0.1) !important;
            border: 1px solid rgba(255, 193, 7, 0.3) !important;
            color: #ffc107 !important;
        }

        /* Modal Cards */
        .modal .card {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid #404040 !important;
        }

        .modal .card-header {
            border-bottom: 1px solid #404040 !important;
        }

        .modal .card-body {
            background: transparent !important;
        }

        /* Modal Spinner */
        .modal .spinner-border {
            color: var(--accent-blue) !important;
        }

        .modal .text-muted {
            color: var(--dark-text-secondary) !important;
        }

        /* Forms */
        .form-control {
            background: #1a1a1a;
            border: 1px solid #404040;
            color: var(--dark-text);
        }

        .form-control:focus {
            background: #1a1a1a;
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25);
        }

        .form-label {
            color: var(--dark-text);
            font-weight: 500;
        }

        .form-select {
            background: #1a1a1a;
            border: 1px solid #404040;
            color: var(--dark-text);
        }

        .form-select:focus {
            background: #1a1a1a;
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25);
        }

        /* Alerts */
        .alert-success {
            background: rgba(0, 212, 170, 0.1);
            border-color: var(--accent-green);
            color: var(--accent-green);
        }

        /* Pagination */
        .pagination {
            justify-content: center;
            margin-top: 1.5rem;
        }

        .page-link {
            background: var(--dark-card);
            border: 1px solid #404040;
            color: var(--dark-text);
            padding: 0.5rem 0.75rem;
        }

        .page-link:hover {
            background: var(--dark-bg);
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        /* Busca global disponível no cabeçalho */

        /* Modal Improvements */
        .modal-dialog {
            max-width: 600px;
        }

        .modal-content {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-radius: 0 0 12px 12px;
        }

        .btn-close {
            filter: invert(1);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
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
                padding: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .custom-table {
                font-size: 0.875rem;
            }
            
            .custom-table th,
            .custom-table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>
     <?php include 'components/header.php'; ?>

     <?php include 'components/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">


        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Total Afiliados -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-change positive">+<?php echo $affiliates_result->num_rows; ?></div>
                </div>
                <h3 class="stat-value"><?php echo $affiliates_result->num_rows; ?></h3>
                <p class="stat-label">Total Afiliados</p>
                <small class="text-white">Afiliados cadastrados no sistema</small>
            </div>

            <!-- Afiliados Ativos -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="stat-change positive">+<?php 
                        $active_count = 0;
                        $temp_result = $conn->query("SELECT COUNT(*) as count FROM affiliates WHERE is_active = 1");
                        $active_count = $temp_result->fetch_assoc()['count'];
                        echo $active_count;
                    ?></div>
                </div>
                <h3 class="stat-value"><?php echo $active_count; ?></h3>
                <p class="stat-label">Afiliados Ativos</p>
                <small class="text-white">Afiliados com status ativo</small>
            </div>

            <!-- Total Comissões -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-orange);">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-change positive">+R$ <?php 
                        $total_commissions = 0;
                        $temp_result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM commissions");
                        $total_commissions = $temp_result->fetch_assoc()['total'];
                        echo number_format($total_commissions, 2, ',', '.');
                    ?></div>
                </div>
                <h3 class="stat-value">R$ <?php echo number_format($total_commissions, 2, ',', '.'); ?></h3>
                <p class="stat-label">Total Comissões</p>
                <small class="text-white">Comissões pagas aos afiliados</small>
            </div>

            <!-- Total Indicações -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="bi bi-share"></i>
                    </div>
                    <div class="stat-change positive">+<?php 
                        $total_referrals = 0;
                        $temp_result = $conn->query("SELECT COUNT(*) as count FROM referrals");
                        $total_referrals = $temp_result->fetch_assoc()['count'];
                        echo $total_referrals;
                    ?></div>
                </div>
                <h3 class="stat-value"><?php echo $total_referrals; ?></h3>
                <p class="stat-label">Total Indicações</p>
                <small class="text-white">Indicações realizadas pelos afiliados</small>
            </div>
        </div>

        <!-- Tabela de Afiliados -->
        <div class="table-container">
            <div class="table-header">
                <h4 class="table-title">Lista de Afiliados</h4>
            </div>
            
            <!-- Busca global disponível no cabeçalho -->
            <div class="table-responsive">
                <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Afiliado</th>
                                <th class="d-none d-md-table-cell">Código</th>
                                <th>Status</th>
                                <th class="d-none d-lg-table-cell">Estatísticas</th>
                                <th class="d-none d-lg-table-cell">Comissões</th>
                                <th>Saldo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php while ($affiliate = $affiliates_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($affiliate['name']); ?></strong>
                                    <br>
                                    <small class="text-white"><?php echo htmlspecialchars($affiliate['email']); ?></small>
                                    <!-- Mostrar código em telas pequenas -->
                                    <div class="d-md-none mt-1">
                                        <code class="small"><?php echo htmlspecialchars($affiliate['affiliate_code']); ?></code>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <code><?php echo htmlspecialchars($affiliate['affiliate_code']); ?></code>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $affiliate['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $affiliate['is_active'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                                <!-- Mostrar estatísticas resumidas em telas pequenas -->
                                <div class="d-lg-none mt-1">
                                    <small class="text-muted">
                                        <?php echo $affiliate['total_clicks']; ?> cliques | <?php echo $affiliate['total_signups']; ?> cadastros
                                    </small>
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <small>
                                    <strong><?php echo $affiliate['total_clicks']; ?></strong> cliques<br>
                                    <strong><?php echo $affiliate['total_signups']; ?></strong> cadastros<br>
                                    <strong><?php echo $affiliate['total_deposits']; ?></strong> depósitos
                                </small>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <small>
                                    CPA: <strong>R$ <?php echo number_format($affiliate['total_cpa'], 2, ',', '.'); ?></strong><br>
                                    RevShare: <strong>R$ <?php echo number_format($affiliate['total_revshare'], 2, ',', '.'); ?></strong>
                                </small>
                            </td>
                            <td>
                                <strong class="text-success">R$ <?php echo number_format($affiliate['affiliate_balance'], 2, ',', '.'); ?></strong>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline-primary btn-sm" onclick="editAffiliate(<?php echo $affiliate['affiliate_id']; ?>)" data-bs-toggle="modal" data-bs-target="#editAffiliateModal" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="viewDetails(<?php echo $affiliate['affiliate_id']; ?>)" data-bs-toggle="modal" data-bs-target="#detailsModal" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="affiliate_id" value="<?php echo $affiliate['affiliate_id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo $affiliate['is_active'] ? 0 : 1; ?>">
                                        <button type="submit" class="btn btn-outline-<?php echo $affiliate['is_active'] ? 'danger' : 'success'; ?> btn-sm" onclick="return confirm('Confirma a alteração de status?')" title="<?php echo $affiliate['is_active'] ? 'Desativar' : 'Ativar'; ?>">
                                            <i class="bi bi-<?php echo $affiliate['is_active'] ? 'pause' : 'play'; ?>"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
                
                <!-- Pagination -->
                <?php if ($totalPaginas > 1): ?>
                    <nav style="padding: 1.5rem;">
                        <ul class="pagination">
                            <?php if ($paginaAtual > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $paginaAtual - 2); $i <= min($totalPaginas, $paginaAtual + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $paginaAtual ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($paginaAtual < $totalPaginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal de Edição de Afiliado -->
    <div class="modal fade" id="editAffiliateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Afiliado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editAffiliateForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_commission">
                        <input type="hidden" name="affiliate_id" id="edit_affiliate_id">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Comissões Configuradas (Visível ao Afiliado)</h6>
                                <div class="mb-3">
                                    <label class="form-label">Taxa RevShare (%)</label>
                                    <input type="number" class="form-control" name="revshare_rate" id="edit_revshare_rate" step="0.01" min="0" max="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-danger">Comissões Reais (Admin)</h6>
                                <div class="mb-3">
                                    <label class="form-label">Taxa RevShare Real (%)</label>
                                    <input type="number" class="form-control" name="revshare_rate_admin" id="edit_revshare_rate_admin" step="0.01" min="0" max="100">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="allow_sub_earnings" id="edit_allow_sub_earnings">
                                        <label class="form-check-label" for="edit_allow_sub_earnings">
                                            Permitir ganhos de subindicados
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-lines-fill me-2"></i>Detalhes do Afiliado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2 text-muted">Carregando detalhes do afiliado...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Ações em Lote -->
    <div class="modal fade" id="bulkActionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Dados Simulados</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_simulated_data">
                        
                        <div class="mb-3">
                            <label class="form-label">Afiliado</label>
                            <select class="form-select" name="affiliate_id" required>
                                <option value="">Selecione um afiliado</option>
                                <?php 
                                $affiliates_result->data_seek(0);
                                while ($affiliate = $affiliates_result->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $affiliate['affiliate_id']; ?>">
                                    <?php echo htmlspecialchars($affiliate['name']) . ' (' . $affiliate['affiliate_code'] . ')'; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Data do Relatório</label>
                            <input type="date" class="form-control" name="report_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Cliques Simulados</label>
                                    <input type="number" class="form-control" name="simulated_clicks" min="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Conversões Simuladas</label>
                                    <input type="number" class="form-control" name="simulated_conversions" min="0" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dados dos afiliados para JavaScript
        const affiliatesData = <?php 
        $affiliates_result->data_seek(0);
        $affiliates_array = [];
        while ($affiliate = $affiliates_result->fetch_assoc()) {
            $affiliates_array[] = $affiliate;
        }
        echo json_encode($affiliates_array);
        ?>;

        function editAffiliate(affiliateId) {
            const affiliate = affiliatesData.find(a => a.affiliate_id == affiliateId);
            if (affiliate) {
                document.getElementById('edit_affiliate_id').value = affiliate.affiliate_id;
                document.getElementById("edit_revshare_rate").value = affiliate.revshare_commission_rate;
                document.getElementById("edit_revshare_rate_admin").value = affiliate.revshare_commission_rate_admin;
                document.getElementById("edit_allow_sub_earnings").checked = affiliate.allow_sub_affiliate_earnings == 1;
            }
        }

        function viewDetails(affiliateId) {
            const affiliate = affiliatesData.find(a => a.affiliate_id == affiliateId);
            if (affiliate) {
                // Criar estrutura com abas
                const tabsContent = `
                    <!-- Navegação das Abas -->
                    <ul class="nav nav-tabs nav-tabs-dark mb-4" id="affiliateDetailsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-content" type="button" role="tab">
                                Informações do Afiliado
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-content" type="button" role="tab">
                                Usuários Cadastrados
                            </button>
                        </li>
                    </ul>

                    <!-- Conteúdo das Abas -->
                    <div class="tab-content" id="affiliateDetailsTabContent">
                        <!-- Aba 1: Informações do Afiliado -->
                        <div class="tab-pane fade show active" id="info-content" role="tabpanel">
                            <!-- Resumo Financeiro - Movido para o início -->
                            <div class="row g-4 mb-4">
                                <div class="col-12">
                                    <div class="card bg-white text-white border">
                                        <div class="card-header bg-white text-dark border-bottom">
                                            <h6 class="mb-0 text-dark fw-bold">Resumo Financeiro</h6>
                                        </div>
                                        <div class="card-body bg-white">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="text-center p-3 bg-white border rounded text-dark">
                                                        <h5 class="mb-1 text-dark">R$ ${parseFloat(affiliate.total_revshare).toLocaleString("pt-BR", {minimumFractionDigits: 2})}</h5>
                                                        <small class="text-secondary">Total RevShare</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-3 bg-white border rounded text-dark">
                                                        <h5 class="mb-1 text-dark">R$ ${parseFloat(affiliate.affiliate_balance).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h5>
                                                        <small class="text-secondary">Saldo Disponível</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-3 bg-white border rounded text-dark">
                                                        <h5 class="mb-1 text-dark">${affiliate.total_signups}</h5>
                                                        <small class="text-secondary">Conversões Totais</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h6 class="mb-0">Informações Básicas</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td><strong>Nome:</strong></td><td>${affiliate.name}</td></tr>
                                                <tr><td><strong>Email:</strong></td><td>${affiliate.email}</td></tr>
                                                <tr><td><strong>Código:</strong></td><td>${affiliate.affiliate_code}</td></tr>
                                                <tr><td><strong>Status:</strong></td><td>${affiliate.is_active == 1 ? 'Ativo' : 'Inativo'}</td></tr>
                                                <tr><td><strong>Cadastrado em:</strong></td><td>${new Date(affiliate.created_at).toLocaleDateString('pt-BR')}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h6 class="mb-0">Estatísticas de Performance</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td><strong>Total de Cliques:</strong></td><td>${affiliate.total_clicks}</td></tr>
                                                <tr><td><strong>Total de Cadastros:</strong></td><td>${affiliate.total_signups}</td></tr>
                                                <tr><td><strong>Total de Depósitos:</strong></td><td>${affiliate.total_deposits}</td></tr>
                                                <tr><td><strong>Total de Indicações:</strong></td><td>${affiliate.total_referrals}</td></tr>
                                                <tr><td><strong>Saldo Atual:</strong></td><td><strong>R$ ${parseFloat(affiliate.affiliate_balance).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4 mt-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Comissões Configuradas</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td><strong>Taxa RevShare:</strong></td><td>${affiliate.revshare_commission_rate}%</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Comissões Reais</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td><strong>Taxa RevShare Real:</strong></td><td>${affiliate.revshare_commission_rate_admin}%</td></tr>
                                                <tr><td><strong>Ganhos de Subindicados:</strong></td><td>${affiliate.allow_sub_affiliate_earnings == 1 ? 'Permitido' : 'Bloqueado'}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Aba 2: Usuários Cadastrados -->
                        <div class="tab-pane fade" id="users-content" role="tabpanel">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Usuários Cadastrados via Link de Afiliado</h6>
                                        <small class="text-muted">Lista completa de usuários que geraram CPA e RevShare</small>
                                    </div>
                                    <div id="usersCount" class="badge bg-primary">Carregando...</div>
                                </div>
                                <div class="card-body" id="usersTableContainer">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando usuários...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Carregando lista de usuários...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('detailsContent').innerHTML = tabsContent;

                // Carregar usuários quando a aba for clicada
                document.getElementById('users-tab').addEventListener('click', function() {
                    loadAffiliateUsers(affiliateId);
                });
            }
        }

        function loadAffiliateUsers(affiliateId) {
            const usersContainer = document.getElementById('usersTableContainer');
            const usersCount = document.getElementById('usersCount');
            
            // Fazer requisição AJAX para buscar usuários cadastrados pelo afiliado
            console.log('Carregando usuários para affiliate ID:', affiliateId);
            fetch(`ajax/get_affiliate_users.php?affiliate_id=${affiliateId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                })
                .then(data => {
                    console.log('Parsed data:', data);
                    if (data.success && data.users.length > 0) {
                        usersCount.textContent = `${data.users.length} usuários`;
                        
                        let usersTable = `
                            <div class="table-responsive">
                                <table class="table table-sm table-dark table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Usuário</th>
                                            <th>Email</th>
                                            <th>Data de Cadastro</th>
                                            <th>Primeiro Depósito</th>
                                            <th>Status CPA</th>
                                            <th>Valor CPA</th>
                                            <th>RevShare Gerado</th>
                                            <th>Última Atividade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        data.users.forEach(user => {
                            const cadastroDate = new Date(user.created_at).toLocaleDateString('pt-BR');
                            const depositoDate = user.first_deposit_date ? new Date(user.first_deposit_date).toLocaleDateString('pt-BR') : '-';
                            const ultimaAtividade = user.last_activity ? new Date(user.last_activity).toLocaleDateString('pt-BR') : '-';
                            const statusCPA = user.first_deposit_date ? 'Convertido' : 'Pendente';
                            const statusClass = user.first_deposit_date ? 'text-success' : 'text-warning';
                            const valorCPA = user.first_deposit_date ? 'R$ 10,00' : '-'; // Valor padrão de CPA
                            const revshareGerado = user.total_revshare ? `R$ ${parseFloat(user.total_revshare).toLocaleString('pt-BR', {minimumFractionDigits: 2})}` : 'R$ 0,00';
                            
                            usersTable += `
                                <tr>
                                    <td>
                                        <div>
                                            <strong>${user.name}</strong>
                                            <br><small class="text-muted">ID: ${user.id}</small>
                                        </div>
                                    </td>
                                    <td><small>${user.email}</small></td>
                                    <td>${cadastroDate}</td>
                                    <td>${depositoDate}</td>
                                    <td><span class="${statusClass}">${statusCPA}</span></td>
                                    <td><strong>${valorCPA}</strong></td>
                                    <td><strong>${revshareGerado}</strong></td>
                                    <td>${ultimaAtividade}</td>
                                </tr>
                            `;
                        });
                        
                        usersTable += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 p-3 bg-dark rounded">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <h6 class="text-success mb-1">${data.users.filter(u => u.first_deposit_date).length}</h6>
                                        <small class="text-muted">Convertidos</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="text-warning mb-1">${data.users.filter(u => !u.first_deposit_date).length}</h6>
                                        <small class="text-muted">Pendentes</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="text-primary mb-1">R$ ${(data.users.filter(u => u.first_deposit_date).length * 10).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h6>
                                        <small class="text-muted">Total CPA</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="text-info mb-1">R$ ${data.users.reduce((sum, user) => sum + (parseFloat(user.total_revshare) || 0), 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</h6>
                                        <small class="text-muted">Total RevShare</small>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        usersContainer.innerHTML = usersTable;
                    } else {
                        usersCount.textContent = '0 usuários';
                        usersContainer.innerHTML = `
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <svg width="64" height="64" fill="currentColor" class="text-muted" viewBox="0 0 16 16">
                                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                        <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"/>
                                        <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                                    </svg>
                                </div>
                                <h6 class="text-muted">Nenhum usuário cadastrado ainda</h6>
                                <p class="text-muted mb-0">Os usuários aparecerão aqui quando se cadastrarem usando o link de afiliado.</p>
                                <small class="text-muted">Compartilhe o link de afiliado para começar a gerar conversões.</small>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar usuários do afiliado:', error);
                    usersCount.textContent = 'Erro';
                    usersContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <h6>Erro ao carregar usuários</h6>
                            <p class="mb-0">Não foi possível carregar a lista de usuários cadastrados. Tente novamente.</p>
                        </div>
                    `;
                });
        }
    </script>
    </div> <!-- Fechamento da main-content -->
</body>
</html>


