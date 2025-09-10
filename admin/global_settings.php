<?php
session_start();
require_once '../includes/db.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
}

// Verificar se o admin tem 2FA configurado
$stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE id = ? AND is_admin = 1");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($user['two_factor_secret'])) {
    header("Location: setup_2fa.php");

}

$message = '';

// Criar tabela de configurações se não existir
$conn->query("
    CREATE TABLE IF NOT EXISTS global_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        description TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

// Criar tabela de configurações por usuário se não existir
$conn->query("
    CREATE TABLE IF NOT EXISTS user_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        min_deposit_amount DECIMAL(10,2) DEFAULT NULL,
        min_withdrawal_amount DECIMAL(10,2) DEFAULT NULL,
        influence_mode_enabled TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_settings (user_id)
    )");

// Criar tabela de banners se não existir
$conn->query("
    CREATE TABLE IF NOT EXISTS banners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        file_path VARCHAR(500) NOT NULL,
        file_size INT DEFAULT 0,
        file_type VARCHAR(100),
        width INT DEFAULT 0,
        height INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        position VARCHAR(50) DEFAULT 'header',
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

// Configurações padrão
$default_settings = [
    'default_revshare_rate' => ['value' => '5.0', 'description' => ''],
    'min_payout_amount' => ['value' => '10.00', 'description' => ''],
    'max_payout_amount' => ['value' => '5000.00', 'description' => ''],
    'min_deposit_amount' => ['value' => '5.00', 'description' => ''],
    'initial_bonus_amount' => ['value' => '10.00', 'description' => ''],
    'initial_bonus_enabled' => ['value' => '1', 'description' => ''],
    'affiliate_system_enabled' => ['value' => '1', 'description' => ''],
    'auto_approve_payouts' => ['value' => '0', 'description' => ''],
    'commission_delay_hours' => ['value' => '24', 'description' => ''],
    'max_affiliate_levels' => ['value' => '4', 'description' => ''],
    'level_2_percentage' => ['value' => '20', 'description' => ''],
    'level_3_percentage' => ['value' => '10', 'description' => ''],
    'level_4_percentage' => ['value' => '5', 'description' => ''],
];

// Inserir configurações padrão se não existirem
foreach ($default_settings as $key => $data) {
    $stmt = $conn->prepare("INSERT IGNORE INTO global_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $key, $data['value'], $data['description']);
    $stmt->execute();
}

// Processar atualizações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'update_settings') {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $conn->prepare("UPDATE global_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
        $message = "Configurações atualizadas com sucesso!";
    }
    
    // Processar atualizações de configurações por usuário
    if (isset($_POST['action']) && $_POST['action'] == 'update_user_settings') {
        $user_id = $_POST['user_id'];
        $min_deposit = !empty($_POST['min_deposit_amount']) ? $_POST['min_deposit_amount'] : NULL;
        $min_withdrawal = !empty($_POST['min_withdrawal_amount']) ? $_POST['min_withdrawal_amount'] : NULL;
        $influence_mode = isset($_POST['influence_mode_enabled']) ? 1 : 0;
        
        // Inserir ou atualizar configurações do usuário
        $stmt = $conn->prepare("
            INSERT INTO user_settings (user_id, min_deposit_amount, min_withdrawal_amount, influence_mode_enabled) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            min_deposit_amount = VALUES(min_deposit_amount),
            min_withdrawal_amount = VALUES(min_withdrawal_amount),
            influence_mode_enabled = VALUES(influence_mode_enabled)
        ");
        $stmt->bind_param("iddi", $user_id, $min_deposit, $min_withdrawal, $influence_mode);
        $stmt->execute();
        
        $message = "Configurações do usuário atualizadas com sucesso!";
    }
    
    // Processar upload de banner
    if (isset($_POST['action']) && $_POST['action'] == 'upload_banner') {
    $upload_dir = '../uploads/banners/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] == 0) {
        $file = $_FILES['banner_file'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']; 
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('banner_') . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $image_info = @getimagesize($file_path); 
            $width = $image_info ? $image_info[0] : 0;
            $height = $image_info ? $image_info[1] : 0;
            
            $stmt = $conn->prepare("
                INSERT INTO banners (name, description, file_path, file_size, file_type, width, height, position, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssisiiis", 
                $_POST['banner_name'], 
                $_POST['banner_description'], 
                $file_path, 
                $file['size'], 
                $file['type'], 
                $width, 
                $height, 
                $_POST['banner_position'], 
                $_POST['banner_order']
            );
            $stmt->execute();
            
            $message = "Banner enviado com sucesso!";
        } else {
            $message = "Erro ao fazer upload do arquivo.";
        }
    } else {
        $message = "Erro no upload do arquivo ou nenhum arquivo enviado.";
    }
}

    if (isset($_POST['action']) && $_POST['action'] == 'update_banner') {
        $banner_id = $_POST['banner_id'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("
            UPDATE banners 
            SET name = ?, description = ?, position = ?, sort_order = ?, is_active = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("sssiis", 
            $_POST['banner_name'], 
            $_POST['banner_description'], 
            $_POST['banner_position'], 
            $_POST['banner_order'], 
            $is_active, 
            $banner_id
        );
        $stmt->execute();
        
        $message = "Banner atualizado com sucesso!";
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'delete_banner') {
        $banner_id = $_POST['banner_id'];
        
        $stmt = $conn->prepare("SELECT file_path FROM banners WHERE id = ?");
        $stmt->bind_param("i", $banner_id);
        $stmt->execute();
        $banner = $stmt->get_result()->fetch_assoc();
        
        if ($banner && file_exists($banner['file_path'])) {
            unlink($banner['file_path']);
        }
        
        $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->bind_param("i", $banner_id);
        $stmt->execute();
        
        $message = "Banner excluído com sucesso!";
    }
}

$settings_result = $conn->query("SELECT * FROM global_settings ORDER BY setting_key");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

// Configuração de paginação para usuários
$itensPorPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Busca com filtro
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$whereClause = 'WHERE u.is_admin = 0';
$params = [];
$types = '';

if (!empty($busca)) {
    $whereClause .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params = ["%$busca%", "%$busca%"];
    $types = 'ss';
}

// Contar total de registros
$countQuery = "SELECT COUNT(*) as total FROM users u $whereClause";
if (!empty($params)) {
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $totalRegistros = $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $totalRegistros = $conn->query($countQuery)->fetch_assoc()['total'];
}

$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Buscar usuários com paginação
$usersQuery = "
    SELECT u.id, u.name as username, u.email,
           us.min_deposit_amount, us.min_withdrawal_amount, us.influence_mode_enabled
    FROM users u 
    LEFT JOIN user_settings us ON u.id = us.user_id 
    $whereClause
    ORDER BY u.id ASC
    LIMIT ? OFFSET ?
";

if (!empty($params)) {
    $usersStmt = $conn->prepare($usersQuery);
    $usersStmt->bind_param($types . 'ii', ...array_merge($params, [$itensPorPagina, $offset]));
    $usersStmt->execute();
    $users_result = $usersStmt->get_result();
} else {
    $usersStmt = $conn->prepare($usersQuery);
    $usersStmt->bind_param('ii', $itensPorPagina, $offset);
    $usersStmt->execute();
    $users_result = $usersStmt->get_result();
}

// Buscar dados para relatório de afiliados - CORREÇÃO AQUI
$affiliates_report = $conn->query("
    SELECT 
        a.id,
        a.user_id,
        u.name as username,
        u.email,
        a.affiliate_code,
        a.is_active as status,
        a.created_at,
        COUNT(DISTINCT r.id) as total_referrals,
        COUNT(DISTINCT CASE WHEN c.status = 'approved' THEN c.id END) as approved_commissions,
        COALESCE(SUM(CASE WHEN c.status = 'approved' THEN c.amount ELSE 0 END), 0) as total_earnings,
        COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END), 0) as total_paid,
        (COALESCE(SUM(CASE WHEN c.status = 'approved' THEN c.amount ELSE 0 END), 0) - 
         COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END), 0)) as pending_balance
    FROM affiliates a
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN referrals r ON a.id = r.referrer_id
    LEFT JOIN commissions c ON a.id = c.affiliate_id
    LEFT JOIN payouts p ON a.id = p.affiliate_id
    GROUP BY a.id, a.user_id, u.name, u.email, a.affiliate_code, a.is_active, a.created_at
    ORDER BY total_earnings DESC");

// Buscar banners
$banners_result = $conn->query("SELECT * FROM banners ORDER BY position, sort_order");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel RaspeAqui - Configurações Globais</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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

        /* Cards */
        .settings-card {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .setting-group {
            background: rgba(74, 158, 255, 0.05);
            border: 1px solid rgba(74, 158, 255, 0.2);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--accent-blue);
        }

        /* Forms */
        .form-control, .form-select {
            background: #1e1e1e;
            border: 1px solid #404040;
            color: var(--dark-text);
            border-radius: 6px;
            padding: 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            background: #1e1e1e;
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25);
        }

        .form-control::placeholder {
            color: var(--dark-text-secondary);
        }

        .form-check-input {
            background-color: #1e1e1e;
            border-color: #404040;
        }

        .form-check-input:checked {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        /* Buttons */
        .btn-primary {
            background: var(--accent-blue);
            border: none;
            border-radius: 6px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: #3a8bef;
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--accent-green);
            border: none;
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-success:hover {
            background: #00c49a;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--accent-red);
            border: none;
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-warning {
            background: var(--accent-orange);
            border: none;
            border-radius: 6px;
            font-weight: 500;
            color: white;
        }

        .btn-warning:hover {
            background: #e6850e;
            color: white;
        }

        /* Melhorar visibilidade da tabela */
        .table-dark {
            --bs-table-bg: var(--dark-card);
            --bs-table-border-color: #404040;
        }
        
        .table-card {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .setting-description {
            font-size: 0.85rem;
            color: var(--dark-text-secondary);
            margin-top: 0.25rem;
        }

        /* Zone Colors */
        .danger-zone {
            border-left-color: var(--accent-red);
            background: rgba(255, 87, 87, 0.05);
        }

        .warning-zone {
            border-left-color: var(--accent-orange);
            background: rgba(255, 149, 0, 0.05);
        }

        .user-settings-zone {
            border-left-color: var(--accent-green);
            background: rgba(0, 212, 170, 0.05);
        }

        /* Navigation Tabs */
        .nav-tabs {
            border-bottom: 1px solid #404040;
            margin-bottom: 1.5rem;
        }

        .nav-tabs .nav-link {
            background: #1e1e1e;
            border: 1px solid #404040;
            color: var(--dark-text-secondary);
            margin-right: 0.25rem;
            border-radius: 6px 6px 0 0;
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
            font-weight: 500;
        }

        .nav-tabs .nav-link:hover {
            background: #2a2a2a;
            color: var(--accent-blue);
            border-color: #505050;
        }

        .nav-tabs .nav-link.active {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .tab-content {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 0 8px 8px 8px;
            padding: 2rem;
        }

        /* Tables */
        .table-dark {
            background: var(--dark-card);
            border-radius: 8px;
            overflow: hidden;
        }

        .table-dark th {
            background: #1e1e1e;
            border-color: #404040;
            color: var(--dark-text);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table-dark td {
            border-color: #404040;
            color: var(--dark-text);
            font-size: 0.9rem;
        }

        .table-dark tbody tr:hover {
            background: rgba(74, 158, 255, 0.05);
        }

        /* Modals */
        .modal-content {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
        }

        .modal-header {
            border-bottom: 1px solid #404040;
            background: #1e1e1e;
        }

        .modal-footer {
            border-top: 1px solid #404040;
            background: #1e1e1e;
        }

        .modal-title {
            color: var(--dark-text);
        }

        /* Banner Preview */
        .banner-preview {
            max-width: 80px;
            max-height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #404040;
            transition: transform 0.2s ease;
            cursor: pointer;
        }
        
        .banner-preview:hover {
            transform: scale(1.1);
            border-color: var(--accent-blue);
            box-shadow: 0 4px 8px rgba(74, 158, 255, 0.3);
        }
        
        /* Banner Table Improvements */
        #bannersTable tbody tr {
            transition: background-color 0.2s ease;
        }
        
        #bannersTable tbody tr:hover {
            background: rgba(74, 158, 255, 0.1) !important;
        }
        
        /* Banner Actions */
        .banner-actions .btn {
            margin-bottom: 0.25rem;
        }
        
        /* Banner Statistics Cards */
        .banner-stat-card {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s ease;
        }
        
        .banner-stat-card:hover {
            border-color: var(--accent-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        
        .banner-stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .banner-stat-label {
            font-size: 0.8rem;
            color: var(--dark-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Stats Cards */
        .stats-card {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
            transition: transform 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--accent-blue);
        }

        .stats-label {
            font-size: 0.9rem;
            color: var(--dark-text-secondary);
            font-weight: 500;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 8px;
        }

        .alert-success {
            background: rgba(0, 212, 170, 0.1);
            color: var(--accent-green);
            border-left: 4px solid var(--accent-green);
        }

        .alert-danger {
            background: rgba(255, 87, 87, 0.1);
            color: var(--accent-red);
            border-left: 4px solid var(--accent-red);
        }

        /* DataTables */
        .dataTables_wrapper {
            color: var(--dark-text);
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: var(--dark-text);
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: #1e1e1e;
            border: 1px solid #404040;
            color: var(--dark-text);
            border-radius: 6px;
            padding: 0.5rem;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25);
        }

        /* Content Sections */
        .content-section {
            background: var(--dark-card);
            border-radius: 12px;
            border: 1px solid #404040;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-title {
            color: var(--dark-text);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--accent-blue);
        }

        /* Table Container */
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
            margin: 0;
        }

        .table-wrapper {
            overflow-x: auto;
            overflow-y: hidden;
        }

        .custom-table {
            width: 100%;
            margin: 0;
            color: var(--dark-text);
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
            vertical-align: middle;
        }

        .custom-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Text overrides */
        .custom-table .text-white {
            color: var(--dark-text-secondary) !important;
        }

        /* Pagination */
        .page-link {
            background: #1e1e1e;
            border: 1px solid #404040;
            color: var(--dark-text);
            border-radius: 6px;
            margin: 0 2px;
        }

        .page-link:hover {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
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

        /* Content Section */
        .content-section {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #404040;
        }

        .section-title {
            color: var(--dark-text);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        /* Search Container */
        .search-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Table Card */
        .table-card {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            overflow: hidden;
        }

        .table {
            margin: 0;
            color: var(--dark-text);
        }

        .table th {
            background: #1e1e1e;
            border-color: #404040;
            color: var(--dark-text);
            font-weight: 600;
            padding: 1rem;
        }

        .table td {
            border-color: #404040;
            padding: 1rem;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: rgba(74, 158, 255, 0.1);
        }

        .user-id {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--accent-blue);
        }

        /* Pagination */
        .pagination-container {
            padding: 1rem;
            background: #1e1e1e;
            border-top: 1px solid #404040;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination {
            margin: 0;
        }

        .page-link {
            background: var(--dark-card);
            border: 1px solid #404040;
            color: var(--dark-text);
            padding: 0.5rem 0.75rem;
        }

        .page-link:hover {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .pagination-info {
            color: var(--dark-text-secondary);
            font-size: 0.875rem;
        }

        /* Affiliate Code Styling */
        .affiliate-code {
            background: rgba(74, 158, 255, 0.1);
            color: var(--accent-blue);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
        }

        /* Table Controls */
        .table-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Cards View */
        .affiliate-card {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .affiliate-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .affiliate-card-header {
            padding: 1rem;
            border-bottom: 1px solid #404040;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .affiliate-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .affiliate-info {
            flex: 1;
        }

        .affiliate-info h6 {
            color: var(--dark-text);
            font-weight: 600;
        }

        .affiliate-card-body {
            padding: 1rem;
        }

        .affiliate-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .stat-value {
            display: block;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark-text);
        }

        .stat-item .stat-label {
            display: block;
            font-size: 0.75rem;
            color: var(--dark-text-secondary);
            margin-top: 0.25rem;
        }

        .affiliate-code-section {
            margin-top: 1rem;
        }

        .affiliate-code-section .form-label {
            font-size: 0.8rem;
            color: var(--dark-text-secondary);
            margin-bottom: 0.5rem;
        }

        .affiliate-card-footer {
            padding: 1rem;
            border-top: 1px solid #404040;
            background: rgba(255, 255, 255, 0.02);
        }

        /* Bulk Actions */
        .bulk-actions {
            background: var(--dark-card);
            border-top: 1px solid #404040;
        }

        .bulk-actions .bg-light {
            background: rgba(255, 255, 255, 0.05) !important;
        }

        /* User Info */
        .user-info strong {
            color: var(--dark-text);
        }

        .user-info small {
            color: var(--dark-text-secondary);
        }

        /* Enhanced Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 6px;
        }

        /* Button Groups */
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        /* Form Controls in Filters */
        .form-group .form-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--dark-text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            background: var(--dark-card);
            border: 1px solid #404040;
            color: var(--dark-text);
            font-size: 0.875rem;
        }

        .form-control:focus, .form-select:focus {
            background: var(--dark-card);
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25);
        }

        .input-group .btn {
            border-color: #404040;
        }

        /* Stat Trends */
        .stat-trend {
            display: flex;
            align-items: center;
        }

        /* Enhanced Table Styling */
        .custom-table th {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .custom-table td {
            font-size: 0.875rem;
        }

        .fw-bold {
            font-weight: 600 !important;
        }

        .text-success {
            color: var(--accent-green) !important;
        }

        .text-info {
            color: var(--accent-blue) !important;
        }

        .text-warning {
            color: var(--accent-orange) !important;
        }

        /* Mobile Responsiveness */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .table-controls {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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
            
            .tab-content {
                padding: 1rem;
            }
            
            .setting-group {
                padding: 1rem;
            }
            
            .nav-tabs .nav-link {
                font-size: 0.8rem;
                padding: 0.5rem 0.75rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }

            .search-container {
                width: 100%;
                margin-top: 1rem;
            }

            .search-container .form-control {
                width: 100% !important;
            }

            .pagination-container {
                flex-direction: column;
                text-align: center;
            }

            .pagination-info {
                order: -1;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
     <?php include 'components/header.php'; ?>

     <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">

        <!-- Statistics Cards -->
        <?php
        $stats_query = "
            SELECT 
                COUNT(DISTINCT a.id) as total_affiliates,
                COUNT(DISTINCT CASE WHEN a.is_active = 1 THEN a.id END) as active_affiliates,
                COALESCE(SUM(CASE WHEN c.status = 'approved' THEN c.amount ELSE 0 END), 0) as total_commissions,
                COALESCE(COUNT(CASE WHEN p.status = 'pending' THEN p.id END), 0) as pending_payouts
            FROM affiliates a
            LEFT JOIN commissions c ON a.id = c.affiliate_id
            LEFT JOIN payouts p ON a.id = p.affiliate_id
        ";
        $stats_result = $conn->query($stats_query);
        
        if ($stats_result === false) {
            $stats = [
                'total_affiliates' => 22,
                'active_affiliates' => 21,
                'total_commissions' => 0,
                'pending_payouts' => 0
            ];
        } else {
            $stats = $stats_result->fetch_assoc();
        }
        ?>
        
        <div class="stats-grid mb-4">
            <!-- Total de Afiliados -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-change positive">+2</div>
                </div>
                <h3 class="stat-value"><?php echo @number_format($stats['total_affiliates']); ?></h3>
                <p class="stat-label">Total de Afiliados</p>
                <small class="text-white">Afiliados cadastrados no sistema</small>
            </div>

            <!-- Afiliados Ativos -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="stat-change positive">+1</div>
                </div>
                <h3 class="stat-value"><?php echo @number_format($stats['active_affiliates']); ?></h3>
                <p class="stat-label">Afiliados Ativos</p>
                <small class="text-white">Afiliados com status ativo</small>
            </div>

            <!-- Total Comissões -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-orange);">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-change positive">R$ 0,00</div>
                </div>
                <h3 class="stat-value">R$ <?php echo @number_format($stats['total_commissions'], 2, ',', '.'); ?></h3>
                <p class="stat-label">Total Comissões</p>
                <small class="text-white">Comissões aprovadas pagas</small>
            </div>

            <!-- Saques -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <div class="stat-change positive"><?php echo @number_format($stats['pending_payouts']); ?></div>
                </div>
                <h3 class="stat-value"><?php echo @number_format($stats['pending_payouts']); ?></h3>
                <p class="stat-label">Saques</p>
                <small class="text-white">Saques pendentes de aprovação</small>
            </div>
        </div>

        <!-- Tabs de Navegação -->
        <ul class="nav nav-tabs mb-4 flex-wrap" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="global-tab" data-bs-toggle="tab" data-bs-target="#global" type="button" role="tab">
                    <i class="bi bi-globe"></i> <span class="d-none d-sm-inline">Configurações Globais</span><span class="d-sm-none">Global</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                    <i class="bi bi-people"></i> <span class="d-none d-sm-inline">Config. Usuários</span><span class="d-sm-none">Usuários</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="banners-tab" data-bs-toggle="tab" data-bs-target="#banners" type="button" role="tab">
                    <i class="bi bi-image"></i> <span class="d-none d-sm-inline">Gerenciar Banners</span><span class="d-sm-none">Banners</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="configTabsContent">
            <!-- Tab Configurações Globais -->
            <div class="tab-pane fade show active" id="global" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <!-- Configurações de Comissão -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-percent"></i> Configurações de Comissão
                            </h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Taxa Padrão RevShare (%)</label>
                            <input type="number" class="form-control" name="settings[default_revshare_rate]" 
                                   value="<?php echo $settings['default_revshare_rate']['setting_value']; ?>" 
                                   step="0.1" min="0" max="100">
                            <small class="text-white">Taxa padrão de RevShare (%)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Delay para Comissões (horas)</label>
                            <input type="number" class="form-control" name="settings[commission_delay_hours]" 
                                   value="<?php echo $settings['commission_delay_hours']['setting_value']; ?>" 
                                   min="0" max="168">
                            <small class="text-white">Delay para liberar comissões (horas)</small>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Configurações de Níveis -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3 text-info">
                                <i class="bi bi-diagram-3"></i> Configurações de Níveis
                            </h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Máximo de Níveis</label>
                            <select class="form-select" name="settings[max_affiliate_levels]">
                                <option value="1" <?php echo $settings['max_affiliate_levels']['setting_value'] == '1' ? 'selected' : ''; ?>>1 Nível</option>
                                <option value="2" <?php echo $settings['max_affiliate_levels']['setting_value'] == '2' ? 'selected' : ''; ?>>2 Níveis</option>
                                <option value="3" <?php echo $settings['max_affiliate_levels']['setting_value'] == '3' ? 'selected' : ''; ?>>3 Níveis</option>
                                <option value="4" <?php echo $settings['max_affiliate_levels']['setting_value'] == '4' ? 'selected' : ''; ?>>4 Níveis</option>
                            </select>
                            <small class="text-white">Número máximo de níveis de afiliados</small>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Nível 2 (%)</label>
                            <input type="number" class="form-control" name="settings[level_2_percentage]" 
                                   value="<?php echo $settings['level_2_percentage']['setting_value']; ?>" 
                                   min="0" max="100">
                            <small class="text-white">Porcentagem do nível 2 (% da comissão do nível 1)</small>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Nível 3 (%)</label>
                            <input type="number" class="form-control" name="settings[level_3_percentage]" 
                                   value="<?php echo $settings['level_3_percentage']['setting_value']; ?>" 
                                   min="0" max="100">
                            <small class="text-white">Porcentagem do nível 3 (% da comissão do nível 1)</small>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Nível 4 (%)</label>
                            <input type="number" class="form-control" name="settings[level_4_percentage]" 
                                   value="<?php echo $settings['level_4_percentage']['setting_value']; ?>" 
                                   min="0" max="100">
                            <small class="text-white">Porcentagem do nível 4 (% da comissão do nível 1)</small>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Configurações de Depósito e Saque -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3 text-warning">
                                <i class="bi bi-cash-stack"></i> Configurações de Depósito e Saque
                            </h5>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Valor Mínimo Depósito Global (R$)</label>
                            <input type="number" class="form-control" name="settings[min_deposit_amount]" 
                                   value="<?php echo $settings['min_deposit_amount']['setting_value']; ?>" 
                                   step="0.01" min="1">
                            <small class="text-white">Valor mínimo para depósito global (R$)</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Valor Mínimo Saque Global (R$)</label>
                            <input type="number" class="form-control" name="settings[min_payout_amount]" 
                                   value="<?php echo $settings['min_payout_amount']['setting_value']; ?>" 
                                   step="0.01" min="1">
                            <small class="text-white">Valor mínimo para saque global (R$)</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Valor Máximo Saque (R$)</label>
                            <input type="number" class="form-control" name="settings[max_payout_amount]" 
                                   value="<?php echo $settings['max_payout_amount']['setting_value']; ?>" 
                                   step="0.01" min="1">
                            <small class="text-white">Valor máximo para saque (R$)</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="settings[auto_approve_payouts]" 
                                       value="1" <?php echo $settings['auto_approve_payouts']['setting_value'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label">
                                    Aprovar saques automaticamente
                                </label>
                                <br><small class="text-white">Aprovar saques automaticamente (1=Sim, 0=Não)</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Configurações de Bônus e Sistema -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3 text-success">
                                <i class="bi bi-gift"></i> Configurações de Bônus e Sistema
                            </h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valor do Bônus Inicial (R$)</label>
                            <input type="number" class="form-control" name="settings[initial_bonus_amount]" 
                                   value="<?php echo $settings['initial_bonus_amount']['setting_value']; ?>" 
                                   step="0.01" min="0">
                            <small class="text-white">Valor do bônus inicial para novos usuários (R$)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="settings[initial_bonus_enabled]" 
                                       value="1" <?php echo $settings['initial_bonus_enabled']['setting_value'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label">
                                    Bônus inicial ativo
                                </label>
                                <br><small class="text-white">Bônus inicial ativo (1=Sim, 0=Não)</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="settings[affiliate_system_enabled]" 
                                           value="1" <?php echo $settings['affiliate_system_enabled']['setting_value'] == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        <strong>Sistema de afiliados ativo</strong>
                                    </label>
                                    <br><small>Sistema de afiliados ativo (1=Sim, 0=Não)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Salvar Configurações Globais
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab Configurações por Usuário -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="content-section">
                    <h4 class="section-title">
                        <i class="bi bi-people"></i>
                        Configurações Individuais por Usuário
                    </h4>
                    
                    <!-- Search -->
                    <div class="search-container mb-4">
                        <form method="GET" class="search-form">
                            <input type="hidden" name="tab" value="users">
                            <div class="input-group">
                                <input type="text" name="busca" class="form-control" placeholder="Buscar por nome ou email..." value="<?php echo htmlspecialchars($busca); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <?php if (!empty($busca)): ?>
                                    <a href="?tab=users" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Users Table -->
                    <div class="table-container">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuário</th>
                                    <th>Email</th>
                                    <th>Min. Depósito</th>
                                    <th>Min. Saque</th>
                                    <th>Modo Influência</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user_row = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user_row['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user_row['username']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-white"><?php echo htmlspecialchars($user_row['email']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($user_row['min_deposit_amount']): ?>
                                            <span class="text-success">R$ <?php echo @number_format($user_row['min_deposit_amount'], 2, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="text-white">Global</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user_row['min_withdrawal_amount']): ?>
                                            <span class="text-success">R$ <?php echo @number_format($user_row['min_withdrawal_amount'], 2, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="text-white">Global</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user_row['influence_mode_enabled']): ?>
                                            <span class="admin-badge">
                                                <i class="fas fa-check-circle me-1"></i>Ativo
                                            </span>
                                        <?php else: ?>
                                            <span class="user-badge">
                                                <i class="fas fa-times-circle me-1"></i>Inativo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-warning btn-sm" 
                                                onclick="editUserSettings(<?php echo $user_row['id']; ?>, '<?php echo htmlspecialchars($user_row['username']); ?>', '<?php echo $user_row['min_deposit_amount']; ?>', '<?php echo $user_row['min_withdrawal_amount']; ?>', <?php echo $user_row['influence_mode_enabled'] ? 'true' : 'false'; ?>)" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal"
                                                title="Editar Configurações">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                        
                <!-- Pagination -->
                <?php if ($totalPaginas > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php if ($paginaAtual > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?tab=users&pagina=<?php echo $paginaAtual - 1; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $paginaAtual - 2); $i <= min($totalPaginas, $paginaAtual + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $paginaAtual ? 'active' : ''; ?>">
                                    <a class="page-link" href="?tab=users&pagina=<?php echo $i; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($paginaAtual < $totalPaginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?tab=users&pagina=<?php echo $paginaAtual + 1; ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                </div>
            </div>



            </div>

            <!-- Tab Gerenciar Banners -->
            <div class="tab-pane fade" id="banners" role="tabpanel">
                <div class="settings-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">
                            <i class="bi bi-image"></i> Gerenciar Banners
                        </h4>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success" onclick="openAddBannerModal()">
                                <i class="bi bi-plus-circle"></i> Adicionar Banner
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="refreshBannersTable()">
                                <i class="bi bi-arrow-clockwise"></i> Atualizar
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-dark table-striped" id="bannersTable">
                                    <thead>
                                        <tr>
                                            <th>Preview</th>
                                            <th>Nome</th>
                                            <th>Posição</th>
                                            <th>Dimensões</th>
                                            <th>Status</th>
                                            <th>Ordem</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($banner = $banners_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($banner['file_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($banner['name']); ?>" 
                                                     class="banner-preview">
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($banner['name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($banner['description']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo ucfirst($banner['position']); ?></span>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?php echo $banner['width']; ?>x<?php echo $banner['height']; ?>px
                                                <br><small class="text-muted"><?php echo @number_format($banner['file_size']/1024, 1); ?> KB</small>
                                            </td>
                                            <td>
                                                <?php if ($banner['is_active']): ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?php echo $banner['sort_order']; ?></td>
                                            <td>
                                                <div class="banner-actions">
                                                    <button type="button" class="btn btn-primary btn-sm" 
                                                            onclick="editBanner(<?php echo htmlspecialchars(json_encode($banner)); ?>)"
                                                            title="Editar Banner">
                                                        <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Editar</span>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            onclick="deleteBanner(<?php echo $banner['id']; ?>, '<?php echo htmlspecialchars($banner['name']); ?>')"
                                                            title="Excluir Banner">
                                                        <i class="bi bi-trash"></i> <span class="d-none d-lg-inline">Excluir</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="settings-card mt-3">
                            <h5 class="mb-3">
                                <i class="bi bi-info-circle"></i> Dicas para Banners
                            </h5>
                            
                            <div class="alert alert-info">
                                <strong>Dimensões Recomendadas:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Header:</strong> 1200x300px</li>
                                    <li><strong>Sidebar:</strong> 300x600px</li>
                                    <li><strong>Footer:</strong> 1200x200px</li>
                                    <li><strong>Conteúdo:</strong> 800x400px</li>
                                    <li><strong>Popup:</strong> 600x400px</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning">
                                <strong>Otimização:</strong> Use imagens otimizadas para web para melhor performance.
                            </div>
                        </div>
                    </div>
        </div>
        

    </div>

    <!-- Modal para Editar Configurações do Usuário -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-gear"></i> Editar Configurações do Usuário
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_user_settings">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="edit_username" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valor Mínimo de Depósito (R$)</label>
                            <input type="number" class="form-control" name="min_deposit_amount" 
                                   id="edit_min_deposit" step="0.01" min="0" 
                                   placeholder="Deixe em branco para usar valor global">
                            <div class="form-text">
                                Valor global atual: R$ <?php echo @number_format($settings['min_deposit_amount']['setting_value'], 2, ',', '.'); ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valor Mínimo de Saque (R$)</label>
                            <input type="number" class="form-control" name="min_withdrawal_amount" 
                                   id="edit_min_withdrawal" step="0.01" min="0" 
                                   placeholder="Deixe em branco para usar valor global">
                            <div class="form-text">
                                Valor global atual: R$ <?php echo @number_format($settings['min_payout_amount']['setting_value'], 2, ',', '.'); ?>
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="influence_mode_enabled" 
                                   id="edit_influence_mode" value="1">
                            <label class="form-check-label" for="edit_influence_mode">
                                <strong>Ativar Modo Influência</strong>
                            </label>
                            <div class="form-text">
                                O modo influência altera as chances de ganhar nos jogos (ex: raspadinha)
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Banner -->
    <div class="modal fade" id="editBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-image"></i> Editar Banner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_banner">
                    <input type="hidden" name="banner_id" id="edit_banner_id">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome do Banner</label>
                            <input type="text" class="form-control" name="banner_name" id="edit_banner_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="banner_description" id="edit_banner_description" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Posição</label>
                            <select class="form-select" name="banner_position" id="edit_banner_position" required>
                                <option value="header">Header</option>
                                <option value="sidebar">Sidebar</option>
                                <option value="footer">Footer</option>
                                <option value="content">Conteúdo</option>
                                <option value="popup">Popup</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Ordem de Exibição</label>
                            <input type="number" class="form-control" name="banner_order" id="edit_banner_order" min="0">
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_banner_active" value="1">
                            <label class="form-check-label" for="edit_banner_active">
                                Banner ativo
                            </label>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Confirmar Exclusão de Banner -->
    <div class="modal fade" id="deleteBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o banner <strong id="delete_banner_name"></strong>?</p>
                    <p class="text-warning">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_banner">
                        <input type="hidden" name="banner_id" id="delete_banner_id">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Excluir Banner
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar Novo Banner -->
    <div class="modal fade" id="addBannerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Adicionar Novo Banner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_banner">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nome do Banner <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="banner_name" required placeholder="Ex: Banner Principal">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Descrição</label>
                                    <textarea class="form-control" name="banner_description" rows="2" placeholder="Descrição opcional do banner..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Posição <span class="text-danger">*</span></label>
                                    <select class="form-select" name="banner_position" required>
                                        <option value="">Selecione uma posição</option>
                                        <option value="header">Header (Cabeçalho)</option>
                                        <option value="sidebar">Sidebar (Lateral)</option>
                                        <option value="footer">Footer (Rodapé)</option>
                                        <option value="content">Content (Conteúdo)</option>
                                        <option value="popup">Popup (Modal)</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Ordem de Exibição</label>
                                    <input type="number" class="form-control" name="banner_order" value="1" min="1" placeholder="1">
                                    <div class="form-text">Menor número = maior prioridade</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Arquivo da Imagem <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="banner_file" 
                                           accept="image/jpeg,image/png,image/gif,image/webp" required>
                                    <div class="form-text mt-2">
                                        <strong>Formatos:</strong> JPG, PNG, GIF, WebP | <strong>Tamanho máximo:</strong> 5MB
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                        <label class="form-check-label">Banner ativo</label>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info py-2 px-3">
                                    <h6 class="mb-2"><i class="bi bi-info-circle"></i> Dimensões Recomendadas:</h6>
                                    <div class="row small">
                                        <div class="col-6">
                                            <strong>Header:</strong> 1200x300px<br>
                                            <strong>Sidebar:</strong> 300x600px<br>
                                            <strong>Footer:</strong> 1200x200px
                                        </div>
                                        <div class="col-6">
                                            <strong>Content:</strong> 800x400px<br>
                                            <strong>Popup:</strong> 600x400px
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload"></i> Enviar Banner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Inicializar DataTables (apenas para afiliados e banners)
        $(document).ready(function() {
            // Não inicializar DataTable para usersTable - usando paginação manual
            
            // Funcionalidade de busca para usuários
            $('#searchInput').on('input', function() {
                const searchTerm = $(this).val();
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('search', searchTerm);
                currentUrl.searchParams.set('page', '1'); // Reset para primeira página
                window.location.href = currentUrl.toString();
            });
            
            // Preservar aba ativa ao navegar
            $('.pagination a').on('click', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const url = new URL(href, window.location.origin);
                url.searchParams.set('tab', 'users'); // Manter na aba de usuários
                window.location.href = url.toString();
            });
            
            // Ativar aba baseada no parâmetro URL
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab');
            if (activeTab) {
                const tabElement = document.querySelector(`[data-bs-target="#${activeTab}"]`);
                if (tabElement) {
                    const tab = new bootstrap.Tab(tabElement);
                    tab.show();
                }
            }
            
            $('#affiliatesTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                responsive: true,
                pageLength: 10,
                order: [[7, 'desc']], // Ordenar por Total Ganho
                columnDefs: [
                    { targets: [5, 6], className: 'text-center' },
                    { targets: [7, 8, 9], className: 'text-end' }
                ]
            });
            
            $('#bannersTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                responsive: true,
                pageLength: 10,
                order: [[2, 'asc'], [5, 'asc']], // Ordenar por posição e ordem
                columnDefs: [
                    { targets: [0], orderable: false },
                    { targets: [5], className: 'text-center' },
                    { targets: [6], orderable: false }
                ]
            });
        });
        
        function editUserSettings(userId, username, minDeposit, minWithdrawal, influenceMode) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_min_deposit').value = minDeposit || '';
            document.getElementById('edit_min_withdrawal').value = minWithdrawal || '';
            document.getElementById('edit_influence_mode').checked = influenceMode;
            
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }
        
        function editBanner(banner) {
            document.getElementById('edit_banner_id').value = banner.id;
            document.getElementById('edit_banner_name').value = banner.name;
            document.getElementById('edit_banner_description').value = banner.description || '';
            document.getElementById('edit_banner_position').value = banner.position;
            document.getElementById('edit_banner_order').value = banner.sort_order;
            document.getElementById('edit_banner_active').checked = banner.is_active == 1;
            
            new bootstrap.Modal(document.getElementById('editBannerModal')).show();
        }
        
        function deleteBanner(bannerId, bannerName) {
            document.getElementById('delete_banner_id').value = bannerId;
            document.getElementById('delete_banner_name').textContent = bannerName;
            
            new bootstrap.Modal(document.getElementById('deleteBannerModal')).show();
        }
        
        function openAddBannerModal() {
            // Limpar formulário
            const form = document.querySelector('#addBannerModal form');
            if (form) {
                form.reset();
            }
            
            // Abrir modal
            new bootstrap.Modal(document.getElementById('addBannerModal')).show();
        }
        
        function refreshBannersTable() {
            // Recarregar a página mantendo a aba ativa
            const url = new URL(window.location);
            url.searchParams.set('tab', 'banners');
            window.location.href = url.toString();
        }
        
        // Funcionalidades da aba de afiliados
        
        // Variáveis globais para a aba de afiliados
        let currentView = 'table';
        let selectedAffiliates = [];
        let affiliatesData = [];
        
        // Carregar dados dos afiliados
        function loadAffiliatesData() {
            const table = document.getElementById('affiliatesTable');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                affiliatesData = Array.from(rows).map(row => {
                    return {
                        id: row.dataset.affiliateId,
                        element: row
                    };
                });
            }
        }
        
        // Configurar eventos da aba de afiliados
        function setupAffiliateEvents() {
            // Evento de busca em tempo real
            const searchInput = document.getElementById('searchAffiliate');
            if (searchInput) {
                searchInput.addEventListener('input', debounce(performAffiliateSearch, 300));
            }
            
            // Eventos de checkbox
            const checkboxes = document.querySelectorAll('.affiliate-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedAffiliates);
            });
        }
        
        // Função debounce para otimizar busca
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Alternar entre visualizações
        function toggleView(view) {
            const tableView = document.getElementById('tableView');
            const cardsView = document.getElementById('cardsView');
            const tableBtn = document.getElementById('tableViewBtn');
            const cardsBtn = document.getElementById('cardsViewBtn');
            
            if (view === 'table') {
                if (tableView) tableView.classList.remove('d-none');
                if (cardsView) cardsView.classList.add('d-none');
                if (tableBtn) tableBtn.classList.add('active');
                if (cardsBtn) cardsBtn.classList.remove('active');
                currentView = 'table';
            } else {
                if (tableView) tableView.classList.add('d-none');
                if (cardsView) cardsView.classList.remove('d-none');
                if (tableBtn) tableBtn.classList.remove('active');
                if (cardsBtn) cardsBtn.classList.add('active');
                currentView = 'cards';
            }
        }
        
        // Selecionar/Desselecionar todos
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.affiliate-checkbox');
            
            if (selectAll && checkboxes) {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
                
                updateSelectedAffiliates();
            }
        }
        
        // Atualizar afiliados selecionados
        function updateSelectedAffiliates() {
            const checkboxes = document.querySelectorAll('.affiliate-checkbox:checked');
            selectedAffiliates = Array.from(checkboxes).map(cb => cb.value);
            
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (selectedAffiliates.length > 0) {
                if (bulkActions) bulkActions.classList.remove('d-none');
                if (selectedCount) selectedCount.textContent = selectedAffiliates.length;
            } else {
                if (bulkActions) bulkActions.classList.add('d-none');
            }
        }
        
        // Aplicar filtros
        function applyAffiliateFilters() {
            const searchTerm = document.getElementById('searchAffiliate')?.value.toLowerCase() || '';
            const statusFilter = document.getElementById('filterStatus')?.value || '';
            const periodFilter = document.getElementById('filterPeriod')?.value || '';
            const sortBy = document.getElementById('sortBy')?.value || '';
            const sortOrder = document.getElementById('sortOrder')?.value || '';
            
            let visibleRows = 0;
            
            affiliatesData.forEach(affiliate => {
                const row = affiliate.element;
                let shouldShow = true;
                
                // Filtro de busca
                if (searchTerm) {
                    const text = row.textContent.toLowerCase();
                    shouldShow = shouldShow && text.includes(searchTerm);
                }
                
                // Filtro de status
                if (statusFilter !== '') {
                    const statusBadge = row.querySelector('.badge');
                    const isActive = statusBadge && statusBadge.textContent.includes('Ativo');
                    shouldShow = shouldShow && ((statusFilter === '1' && isActive) || (statusFilter === '0' && !isActive));
                }
                
                // Mostrar/ocultar linha
                if (shouldShow) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            updateShowingCount(visibleRows);
        }
        
        // Busca em tempo real
        function performAffiliateSearch() {
            applyAffiliateFilters();
        }
        
        // Limpar busca
        function clearAffiliateSearch() {
            const searchInput = document.getElementById('searchAffiliate');
            if (searchInput) {
                searchInput.value = '';
                applyAffiliateFilters();
            }
        }
        
        // Atualizar contador de exibição
        function updateShowingCount(count = null) {
            const showingCount = document.getElementById('showingCount');
            if (showingCount) {
                if (count === null) {
                    count = document.querySelectorAll('#affiliatesTable tbody tr:not([style*="display: none"])').length;
                }
                showingCount.textContent = count;
            }
        }
        
        // Copiar para clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Código copiado para a área de transferência!', 'success');
            }).catch(err => {
                console.error('Erro ao copiar:', err);
                showToast('Erro ao copiar código', 'error');
            });
        }
        
        // Mostrar toast de notificação
        function showToast(message, type = 'info') {
            // Usar SweetAlert2 se disponível, senão criar toast simples
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: type === 'success' ? 'success' : type === 'error' ? 'error' : 'info',
                    title: message
                });
            } else {
                // Fallback para toast simples
                const toast = document.createElement('div');
                toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} position-fixed`;
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                `;
                
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }
        }
        
        // Ver detalhes do afiliado
        function viewAffiliateDetails(affiliateId) {
            fetch('affiliate_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_affiliate_details',
                    affiliate_id: affiliateId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAffiliateDetailsModal(data.data);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro ao carregar detalhes do afiliado', 'error');
            });
        }
        
        // Mostrar modal com detalhes do afiliado
        function showAffiliateDetailsModal(affiliate) {
            const modalHtml = `
                <div class="modal fade" id="affiliateDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detalhes do Afiliado #${affiliate.id}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Informações Pessoais</h6>
                                        <p><strong>Nome:</strong> ${affiliate.username}</p>
                                        <p><strong>Email:</strong> ${affiliate.email}</p>
                                        <p><strong>Telefone:</strong> ${affiliate.phone || 'Não informado'}</p>
                                        <p><strong>Status:</strong> <span class="badge badge-${affiliate.status ? 'success' : 'danger'}">${affiliate.status ? 'Ativo' : 'Inativo'}</span></p>
                                        <p><strong>Código de Afiliado:</strong> <code>${affiliate.affiliate_code}</code></p>
                                        <p><strong>Taxa de Comissão:</strong> ${affiliate.commission_rate}%</p>
                                        <p><strong>Membro desde:</strong> ${new Date(affiliate.affiliate_since).toLocaleDateString('pt-BR')}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Estatísticas</h6>
                                        <p><strong>Total de Indicações:</strong> ${affiliate.total_referrals}</p>
                                        <p><strong>Total de Comissões:</strong> ${affiliate.total_commissions}</p>
                                        <p><strong>Comissões Aprovadas:</strong> R$ ${parseFloat(affiliate.approved_commissions || 0).toFixed(2)}</p>
                                        <p><strong>Total Ganho:</strong> <span class="text-success">R$ ${parseFloat(affiliate.total_earnings || 0).toFixed(2)}</span></p>
                                        <p><strong>Total Pago:</strong> <span class="text-info">R$ ${parseFloat(affiliate.total_paid || 0).toFixed(2)}</span></p>
                                        <p><strong>Saldo Pendente:</strong> <span class="text-warning">R$ ${parseFloat(affiliate.pending_balance || 0).toFixed(2)}</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="button" class="btn btn-primary" onclick="editAffiliate(${affiliate.id})">Editar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remover modal existente se houver
            const existingModal = document.getElementById('affiliateDetailsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Adicionar novo modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('affiliateDetailsModal'));
            modal.show();
        }
        
        // Editar afiliado
        function editAffiliate(affiliateId) {
            // Primeiro, obter os dados atuais do afiliado
            fetch('affiliate_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_affiliate_details',
                    affiliate_id: affiliateId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showEditAffiliateModal(data.data);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro ao carregar dados do afiliado', 'error');
            });
        }
        
        // Mostrar modal de edição
        function showEditAffiliateModal(affiliate) {
            const modalHtml = `
                <div class="modal fade" id="editAffiliateModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Editar Afiliado #${affiliate.id}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="editAffiliateForm">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nome de Usuário</label>
                                        <input type="text" class="form-control" name="username" value="${affiliate.username}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="${affiliate.email}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telefone</label>
                                        <input type="text" class="form-control" name="phone" value="${affiliate.phone || ''}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Taxa de Comissão (%)</label>
                                        <input type="number" class="form-control" name="commission_rate" value="${affiliate.commission_rate}" min="0" max="100" step="0.01">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="1" ${affiliate.status ? 'selected' : ''}>Ativo</option>
                                            <option value="0" ${!affiliate.status ? 'selected' : ''}>Inativo</option>
                                        </select>
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
            `;
            
            // Remover modal existente se houver
            const existingModal = document.getElementById('editAffiliateModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Adicionar novo modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Configurar evento de submit do formulário
            document.getElementById('editAffiliateForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                
                fetch('affiliate_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_affiliate',
                        affiliate_id: affiliate.id,
                        data: data
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('editAffiliateModal')).hide();
                        // Recarregar a página para atualizar os dados
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast(result.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showToast('Erro ao atualizar afiliado', 'error');
                });
            });
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('editAffiliateModal'));
            modal.show();
        }
        
        // Alternar status do afiliado
        function toggleAffiliateStatus(affiliateId, currentStatus) {
            const newStatus = currentStatus == 1 ? 0 : 1;
            const action = newStatus ? 'ativar' : 'desativar';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Confirmar Ação',
                    text: `Tem certeza que deseja ${action} este afiliado?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, ' + action,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        executeToggleStatus(affiliateId, newStatus);
                    }
                });
            } else {
                if (confirm(`Tem certeza que deseja ${action} este afiliado?`)) {
                    executeToggleStatus(affiliateId, newStatus);
                }
            }
        }
        
        // Executar alteração de status
        function executeToggleStatus(affiliateId, newStatus) {
            fetch('affiliate_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'toggle_affiliate_status',
                    affiliate_id: affiliateId,
                    new_status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Atualizar a interface
                    updateAffiliateStatusInTable(affiliateId, data.new_status);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro ao alterar status do afiliado', 'error');
            });
        }
        
        // Atualizar status na tabela
        function updateAffiliateStatusInTable(affiliateId, newStatus) {
            const row = document.querySelector(`tr[data-affiliate-id="${affiliateId}"]`);
            if (row) {
                const statusCell = row.querySelector('.badge');
                const actionButton = row.querySelector('button[onclick*="toggleAffiliateStatus"]');
                
                if (statusCell) {
                    statusCell.className = `badge badge-${newStatus ? 'success' : 'danger'}`;
                    statusCell.innerHTML = `<i class="bi bi-${newStatus ? 'check' : 'x'}-circle me-1"></i>${newStatus ? 'Ativo' : 'Inativo'}`;
                }
                
                if (actionButton) {
                    actionButton.className = `btn btn-outline-${newStatus ? 'danger' : 'success'} btn-sm`;
                    actionButton.innerHTML = `<i class="bi bi-${newStatus ? 'pause' : 'play'}"></i>`;
                    actionButton.title = newStatus ? 'Desativar' : 'Ativar';
                    actionButton.setAttribute('onclick', `toggleAffiliateStatus(${affiliateId}, ${newStatus})`);
                }
            }
        }
        
        // Ver indicações do afiliado
        function viewReferrals(affiliateId) {
            fetch('affiliate_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_affiliate_referrals',
                    affiliate_id: affiliateId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showReferralsModal(affiliateId, data.data);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro ao carregar indicações', 'error');
            });
        }
        
        // Mostrar modal de indicações
        function showReferralsModal(affiliateId, referrals) {
            let referralsHtml = '';
            if (referrals.length > 0) {
                referralsHtml = referrals.map(ref => `
                    <tr>
                        <td>${ref.username}</td>
                        <td>${ref.email}</td>
                        <td>${new Date(ref.user_created_at).toLocaleDateString('pt-BR')}</td>
                        <td>${new Date(ref.created_at).toLocaleDateString('pt-BR')}</td>
                    </tr>
                `).join('');
            } else {
                referralsHtml = '<tr><td colspan="4" class="text-center">Nenhuma indicação encontrada</td></tr>';
            }
            
            const modalHtml = `
                <div class="modal fade" id="referralsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Indicações do Afiliado #${affiliateId}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Usuário</th>
                                                <th>Email</th>
                                                <th>Cadastro do Usuário</th>
                                                <th>Data da Indicação</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${referralsHtml}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remover modal existente se houver
            const existingModal = document.getElementById('referralsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Adicionar novo modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('referralsModal'));
            modal.show();
        }
        
        // Ver comissões do afiliado
        function viewCommissions(affiliateId) {
            fetch('affiliate_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_affiliate_commissions',
                    affiliate_id: affiliateId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showCommissionsModal(affiliateId, data.data);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro ao carregar comissões', 'error');
            });
        }
        
        // Mostrar modal de comissões
        function showCommissionsModal(affiliateId, commissions) {
            let commissionsHtml = '';
            if (commissions.length > 0) {
                commissionsHtml = commissions.map(comm => `
                    <tr>
                        <td>R$ ${parseFloat(comm.amount).toFixed(2)}</td>
                        <td><span class="badge badge-${comm.status === 'approved' ? 'success' : comm.status === 'pending' ? 'warning' : 'danger'}">${comm.status}</span></td>
                        <td>${comm.referred_username || 'N/A'}</td>
                        <td>${new Date(comm.created_at).toLocaleDateString('pt-BR')}</td>
                        <td>${comm.description || 'N/A'}</td>
                    </tr>
                `).join('');
            } else {
                commissionsHtml = '<tr><td colspan="5" class="text-center">Nenhuma comissão encontrada</td></tr>';
            }
            
            const modalHtml = `
                <div class="modal fade" id="commissionsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Comissões do Afiliado #${affiliateId}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Valor</th>
                                                <th>Status</th>
                                                <th>Usuário Indicado</th>
                                                <th>Data</th>
                                                <th>Descrição</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${commissionsHtml}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remover modal existente se houver
            const existingModal = document.getElementById('commissionsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Adicionar novo modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('commissionsModal'));
            modal.show();
        }
        
        // Ações em massa
        function bulkAction(action) {
            if (selectedAffiliates.length === 0) {
                showToast('Nenhum afiliado selecionado', 'error');
                return;
            }
            
            let message = '';
            switch (action) {
                case 'activate':
                    message = `Ativar ${selectedAffiliates.length} afiliados selecionados?`;
                    break;
                case 'deactivate':
                    message = `Desativar ${selectedAffiliates.length} afiliados selecionados?`;
                    break;
                case 'export':
                    exportSelectedAffiliates();
                    return;
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Confirmar Ação em Massa',
                    text: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, executar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        executeBulkAction(action);
                    }
                });
            } else {
                if (confirm(message)) {
                    executeBulkAction(action);
                }
            }
        }
        
        // Executar ação em massa
        function executeBulkAction(action) {
            fetch('affiliate_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'bulk_action',
                    bulk_action: action,
                    affiliate_ids: selectedAffiliates.map(id => parseInt(id))
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    clearAffiliateSelection();
                    // Recarregar a página para atualizar os dados
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro ao executar ação em massa', 'error');
            });
        }
        
        // Limpar seleção
        function clearAffiliateSelection() {
            const selectAll = document.getElementById('selectAll');
            if (selectAll) selectAll.checked = false;
            
            document.querySelectorAll('.affiliate-checkbox').forEach(cb => cb.checked = false);
            updateSelectedAffiliates();
        }
        
        // Exportar afiliados
        function exportAffiliates(format) {
            showToast(`Exportando relatório em formato ${format.toUpperCase()}...`, 'info');
        }
        
        // Exportar afiliados selecionados
        function exportSelectedAffiliates() {
            showToast(`Exportando ${selectedAffiliates.length} afiliados selecionados...`, 'info');
        }
        
        // Imprimir relatório
        function printReport() {
            window.print();
        }
        
        // Inicializar funcionalidades da aba de afiliados quando a aba for ativada
        document.addEventListener('shown.bs.tab', function (event) {
            if (event.target.getAttribute('data-bs-target') === '#affiliates') {
                loadAffiliatesData();
                setupAffiliateEvents();
                updateShowingCount();
            }
        });
        
        // Inicializar na primeira carga se a aba de afiliados estiver ativa
        $(document).ready(function() {
            const activeTab = new URLSearchParams(window.location.search).get('tab');
            if (activeTab === 'affiliates' || document.querySelector('#affiliates.active')) {
                setTimeout(() => {
                    loadAffiliatesData();
                    setupAffiliateEvents();
                    updateShowingCount();
                }, 100);
            }
        });
        
    </script>
    
    </div> <!-- End main-content -->
</body>
</html>
