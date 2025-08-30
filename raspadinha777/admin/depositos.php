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

// Não há processamento POST - apenas visualização

// Configuração da paginação
$registros_por_pagina = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $registros_por_pagina;

// Buscar total de depósitos para paginação
$total_deposits_query = "SELECT COUNT(*) as total FROM deposits";
$total_result = $conn->query($total_deposits_query);
$total_registros = intval($total_result->fetch_assoc()['total']);
$total_pages = intval(ceil($total_registros / $registros_por_pagina));



// Buscar depósitos da página atual
$deposits_query = "SELECT d.*, u.name as usuario_nome 
    FROM deposits d 
    LEFT JOIN users u ON d.user_id = u.id 
    ORDER BY d.id DESC 
    LIMIT $registros_por_pagina OFFSET $offset";
$deposits_result = $conn->query($deposits_query);

// Buscar estatísticas gerais
$stats_query = "SELECT 
    COUNT(*) as total_deposits,
    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'aprovado' OR status = 'pago' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN status = 'aprovado' OR status = 'pago' THEN amount ELSE 0 END) as total_approved_amount
    FROM deposits";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depósitos - Admin</title>
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
        .stats-container {
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
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0;
            line-height: 1.2;
        }

        .stat-label {
            color: var(--dark-text-secondary);
            font-size: 0.85rem;
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
            min-width: 800px;
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

        .custom-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Colunas específicas com larguras otimizadas */
        .custom-table th:nth-child(1), .custom-table td:nth-child(1) { width: 60px; } /* ID */
        .custom-table th:nth-child(2), .custom-table td:nth-child(2) { width: 150px; } /* Usuário */
        .custom-table th:nth-child(3), .custom-table td:nth-child(3) { width: 100px; } /* Valor */
        .custom-table th:nth-child(4), .custom-table td:nth-child(4) { width: 100px; } /* Status */
        .custom-table th:nth-child(5), .custom-table td:nth-child(5) { width: 120px; } /* Data */
        .custom-table th:nth-child(6), .custom-table td:nth-child(6) { width: 200px; } /* Payment ID */

        /* Badges */
        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

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

        /* Buttons */
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            border-radius: 6px;
            margin: 0 0.2rem;
        }

        .btn-success {
            background: var(--accent-green);
            border: none;
            color: white;
        }

        .btn-danger {
            background: var(--accent-red);
            border: none;
            color: white;
        }

        /* Paginação */
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
            background: var(--accent-blue) !important;
            border-color: var(--accent-blue) !important;
            color: white !important;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(74, 158, 255, 0.3);
            cursor: default;
        }

        .page-item.active .page-link:hover {
            background: var(--accent-blue) !important;
            border-color: var(--accent-blue) !important;
            color: white !important;
            transform: none;
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
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
                margin-top: 60px;
            }
            
            .main-content.sidebar-collapsed {
                margin-left: 0;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Header -->
    <?php include 'components/header.php'; ?>

    <!-- Main Content -->
    <main class="main-content">

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?= number_format($stats['total_deposits']) ?></h3>
                <p class="stat-label">Total de Depósitos</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-orange);">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?= number_format($stats['pending_count']) ?></h3>
                <p class="stat-label">Pendentes</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?= number_format($stats['approved_count']) ?></h3>
                <p class="stat-label">Aprovados</p>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <h3 class="stat-value">R$ <?= number_format($stats['total_approved_amount'], 2, ',', '.') ?></h3>
                <p class="stat-label">Valor Total Aprovado</p>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">Lista de Depósitos</h3>
                <div class="table-info">
                    <?php if ($deposits_result && $deposits_result->num_rows > 0): ?>
                        Mostrando <?= ($offset + 1) ?> a <?= min($offset + $registros_por_pagina, $total_registros) ?> de <?= $total_registros ?> depósitos
                    <?php else: ?>
                        Nenhum depósito encontrado
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Payment ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($deposits_result && $deposits_result->num_rows > 0): ?>
                            <?php while($deposit = $deposits_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($deposit['id']) ?></td>
                                <td><?= htmlspecialchars($deposit['usuario_nome'] ?? 'Usuário não encontrado') ?></td>
                                <td>R$ <?= number_format($deposit['amount'], 2, ',', '.') ?></td>
                                <td>
                                    <?php 
                                    $status = strtolower($deposit['status']);
                                    if ($status == 'aprovado' || $status == 'pago'): 
                                    ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> <?= ucfirst($deposit['status']) ?>
                                        </span>
                                    <?php elseif ($status == 'pendente'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Pendente
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> <?= ucfirst($deposit['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($deposit['created_at'])) ?></td>
                                <td>
                                    <?= $deposit['payment_id'] ? htmlspecialchars(substr($deposit['payment_id'], 0, 15) . '...') : '-' ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 2rem; color: var(--dark-text-secondary);">
                                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                    Nenhum depósito encontrado
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginação -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                <span class="text-white">
                    Mostrando <?php echo ($offset + 1); ?> a <?php echo min($offset + $registros_por_pagina, $total_registros); ?> 
                    de <?php echo $total_registros; ?> depósitos
                </span>
            </div>
            <nav aria-label="Navegação de páginas">
                <ul class="pagination">
                    <!-- Navegação inteligente baseada na página atual -->
                    <?php if ($current_page > 1): ?>
                        <?php if ($current_page > 2): ?>
                            <!-- Botão para primeira página (dupla seta esquerda) -->
                            <li class="page-item">
                                <a class="page-link" href="?page=1" title="Primeira página">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        <!-- Botão página anterior (seta esquerda simples) -->
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo max(1, intval($current_page) - 1); ?>" title="Página anterior">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Páginas numeradas -->
                    <?php
                    // Lógica simplificada para exibir todas as páginas quando há poucas
                    if ($total_pages <= 7) {
                        // Mostrar todas as páginas se há 7 ou menos
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $active_class = ($i == $current_page) ? ' active' : '';
                            echo '<li class="page-item' . $active_class . '">';
                            
                            if ($i == $current_page) {
                                echo '<span class="page-link">' . $i . '</span>';
                            } else {
                                echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                            }
                            
                            echo '</li>';
                        }
                    } else {
                        // Lógica para muitas páginas com reticências
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        // Ajustar para sempre mostrar 5 páginas quando possível
                        if ($end_page - $start_page < 4) {
                            if ($start_page == 1) {
                                $end_page = min($total_pages, $start_page + 4);
                            } else {
                                $start_page = max(1, $end_page - 4);
                            }
                        }
                        
                        // Mostrar primeira página e reticências se necessário
                        if ($start_page > 1) {
                            echo '<li class="page-item">';
                            echo '<a class="page-link" href="?page=1">1</a>';
                            echo '</li>';
                            
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled">';
                                echo '<span class="page-link">...</span>';
                                echo '</li>';
                            }
                        }
                        
                        // Páginas no range atual
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            $active_class = ($i == $current_page) ? ' active' : '';
                            echo '<li class="page-item' . $active_class . '">';
                            
                            if ($i == $current_page) {
                                echo '<span class="page-link">' . $i . '</span>';
                            } else {
                                echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                            }
                            
                            echo '</li>';
                        }
                        
                        // Mostrar última página e reticências se necessário
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled">';
                                echo '<span class="page-link">...</span>';
                                echo '</li>';
                            }
                            
                            echo '<li class="page-item">';
                            echo '<a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a>';
                            echo '</li>';
                        }
                    }
                    ?>
                    
                    <!-- Navegação inteligente para próximas páginas -->
                    <?php if ($current_page < $total_pages): ?>
                        <!-- Botão próxima página (seta direita simples) -->
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo intval($current_page) + 1; ?>" title="Próxima página">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <?php if ($current_page < $total_pages - 1): ?>
                            <!-- Botão para última página (dupla seta direita) -->
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?>" title="Última página">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animações de carregamento
        document.addEventListener('DOMContentLoaded', function() {
            // Animar cards de estatísticas
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Animar linhas da tabela
            const tableRows = document.querySelectorAll('.custom-table tbody tr');
            tableRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-10px)';
                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, 300 + (index * 50));
            });
        });
    </script>
</body>
</html>
