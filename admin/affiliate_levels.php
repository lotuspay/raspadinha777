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

// Buscar estrutura de níveis
$levels_query = "
    SELECT 
        r.level,
        COUNT(*) as total_referrals,
        COUNT(DISTINCT r.referrer_id) as unique_referrers,
        COALESCE(SUM(c.amount), 0) as total_commissions
    FROM referrals r
    LEFT JOIN affiliates a ON r.referrer_id = a.user_id
    LEFT JOIN commissions c ON a.id = c.affiliate_id AND c.level = r.level
    GROUP BY r.level
    ORDER BY r.level
";

$levels_result = $conn->query($levels_query);

// Buscar top afiliados por nível
$top_affiliates_query = "
    SELECT 
        r.level,
        u.name,
        u.email,
        a.affiliate_code,
        COUNT(r.referred_id) as referrals_count,
        COALESCE(SUM(c.amount), 0) as level_commissions
    FROM referrals r
    JOIN users u ON r.referrer_id = u.id
    JOIN affiliates a ON r.referrer_id = a.user_id
    LEFT JOIN commissions c ON a.id = c.affiliate_id AND c.level = r.level
    GROUP BY r.level, r.referrer_id
    HAVING referrals_count > 0
    ORDER BY r.level, level_commissions DESC
";

$top_affiliates_result = $conn->query($top_affiliates_query);

// Organizar dados por nível
$top_by_level = [];
while ($row = $top_affiliates_result->fetch_assoc()) {
    $top_by_level[$row['level']][] = $row;
}
// Dados para gráfico de área - últimos 30 dias de comissões por nível
$area_chart_labels = [];
$area_chart_data = [];
$levels_for_area = [1, 2, 3, 4];

for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $area_chart_labels[] = date('d/m', strtotime("-$i days"));
    
    foreach ($levels_for_area as $level) {
        if (!isset($area_chart_data[$level])) {
            $area_chart_data[$level] = [];
        }
        
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(c.amount), 0) as daily_commission
            FROM commissions c
            JOIN affiliates a ON c.affiliate_id = a.id
            WHERE DATE(c.created_at) = ? AND c.level = ?
        ");
        $stmt->bind_param("si", $date, $level);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $area_chart_data[$level][] = floatval($result['daily_commission']);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Níveis de Afiliados - Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Sidebar Component CSS -->
    <link href="components/sidebar.css" rel="stylesheet">
    <!-- Header Component CSS -->
    <link href="components/header.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            padding: 2rem;
            min-height: 100vh;
            background: var(--dark-bg);
        }

        /* Header */
        .dashboard-header {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #404040;
        }

        .dashboard-title {
            color: var(--dark-text);
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .dashboard-subtitle {
            color: var(--dark-text-secondary);
            font-size: 0.95rem;
            margin: 0;
        }

        /* Statistics Grid */
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

        /* Content Cards */
        .content-card {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #404040;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #404040;
        }

        .card-title {
            color: var(--dark-text);
            font-size: 1.4rem;
            font-weight: 600;
            margin: 0;
        }

        .card-subtitle {
            color: var(--dark-text-secondary);
            font-size: 0.9rem;
            margin: 0;
        }

        /* Level Cards */
        .level-card {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #404040;
            transition: all 0.3s ease;
        }

        .level-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .level-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .level-number {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark-text);
            background: #404040;
            border: 1px solid #505050;
        }

        .level-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-text);
            margin: 0;
        }

        .level-description {
            color: var(--dark-text-secondary);
            margin: 0;
            font-size: 0.9rem;
        }

        .level-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            background: #3a3a3a;
            border-radius: 6px;
            padding: 1rem;
            text-align: center;
            border: 1px solid #505050;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--dark-text);
        }

        .stat-label {
            color: var(--dark-text-secondary);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
 


        .top-affiliates {
            background: #3a3a3a;
            border-radius: 6px;
            padding: 1rem;
            border: 1px solid #505050;
        }

        .top-affiliates h6 {
            color: var(--dark-text);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .affiliate-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #505050;
        }

        .affiliate-item:last-child {
            border-bottom: none;
        }

        .affiliate-info {
            flex: 1;
        }

        .affiliate-name {
            font-weight: 500;
            color: var(--dark-text);
            font-size: 0.9rem;
        }

        .affiliate-code {
            font-size: 0.8rem;
            color: var(--dark-text-secondary);
            font-family: monospace;
        }

        .affiliate-stats {
            text-align: right;
        }

        .commission-amount {
            font-weight: 600;
            color: var(--accent-green);
            font-size: 0.9rem;
        }

        .referrals-count {
            font-size: 0.8rem;
            color: var(--dark-text-secondary);
        }

        .chart-container {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #404040;
            margin-bottom: 1.5rem;
            max-height: 400px;
            height: 400px;
        }

        .chart-container canvas {
            max-height: 400px !important;
            height: 400px !important;
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-text);
            margin: 0;
        }

        /* Badges */
        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
            border-radius: 4px;
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
            background: #dc3545;
            color: white;
        }

        .badge-info {
            background: var(--accent-blue);
            color: white;
        }

        /* Table Styles */
        .table-container {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 0;
            border: 1px solid #404040;
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid #404040;
        }

        .table-title {
            color: var(--dark-text);
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }

        .custom-table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            background: transparent;
            min-width: 1000px;
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
            padding: 1rem 0.5rem;
            border: none;
            border-bottom: 1px solid #2a2a2a;
            color: var(--dark-text);
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .custom-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .chart-period {
            color: var(--dark-text-secondary);
            font-size: 0.75rem;
        }

        /* Search Bar removida - utilizando busca global do cabeçalho */

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
            
            .chart-container {
                padding: 0.75rem;
            }
            
            .table-header {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
     <?php include 'components/header.php'; ?>

     <?php include 'components/sidebar.php'; ?>

    <div class="main-content">

        <!-- Statistics Cards -->
        <?php 
        $total_referrals = 0;
        $total_commissions = 0;
        $total_affiliates = 0;
        // Verifica se $levels_result é um objeto mysqli_result antes de chamar data_seek
        if ($levels_result instanceof mysqli_result) {
            $levels_result->data_seek(0);
            while ($level = $levels_result->fetch_assoc()) {
                $total_referrals += $level['total_referrals'];
                $total_commissions += $level['total_commissions'];
                $total_affiliates += $level['unique_referrers'];
            }
            // Volta o ponteiro para o início para o próximo loop
            $levels_result->data_seek(0);
        }
        ?>
        <div class="stats-grid">
            <!-- Total de Indicações -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-change positive">+12</div>
                </div>
                <h3 class="stat-value"><?php echo @number_format($total_referrals); ?></h3>
                <p class="stat-label">Total de Indicações</p>
                <small class="text-white">5 indicações nos últimos 7 dias</small>
            </div>

            <!-- Total de Comissões -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-change positive">+R$ 25,50</div>
                </div>
                <h3 class="stat-value">R$ <?php echo @number_format($total_commissions, 2, ',', '.'); ?></h3>
                <p class="stat-label">Total de Comissões</p>
                <small class="text-white">R$ 45,00 nos últimos 7 dias</small>
            </div>

            <!-- Níveis Ativos -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-orange);">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <div class="stat-change positive">+1</div>
                </div>
                <h3 class="stat-value">4</h3>
                <p class="stat-label">Níveis Ativos</p>
                <small class="text-white">Sistema de 4 níveis</small>
            </div>

            <!-- Total de Afiliados -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="stat-change positive">+3</div>
                </div>
                <h3 class="stat-value"><?php echo @number_format($total_affiliates); ?></h3>
                <p class="stat-label">Total de Afiliados</p>
                <small class="text-white">2 novos afiliados esta semana</small>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <h4 class="chart-title">Evolução de Comissões por Nível</h4>
                        <span class="chart-period">Performance dos últimos 30 dias</span>
                    </div>
                    <canvas id="commissionsAreaChart"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <h4 class="chart-title">Indicações por Nível</h4>
                        <span class="chart-period">Performance dos últimos 30 dias</span>
                    </div>
                    <canvas id="referralsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Levels Table -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">Níveis de Afiliados</h3>
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nível</th>
                        <th>Descrição</th>
                        <th>Indicações</th>
                        <th>Afiliados Ativos</th>
                        <th>Comissões Pagas</th>
                        <th>Média por Afiliado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Verifica novamente se $levels_result é um objeto mysqli_result antes de iterar
                    if ($levels_result instanceof mysqli_result) {
                        while ($level = $levels_result->fetch_assoc()): 
                            $level_num = $level['level'];
                            $avg_commission = $level['unique_referrers'] > 0 ? $level['total_commissions'] / $level['unique_referrers'] : 0;
                    ?>
                    <tr data-level-id="<?php echo $level_num; ?>">
                        <td>
                            <span class="badge badge-success">
                                <i class="fas fa-layer-group me-1"></i>Nível <?php echo $level_num; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            switch($level_num) {
                                case 1: echo "Indicações diretas - Primeira linha"; break;
                                case 2: echo "Indicações de segundo nível"; break;
                                case 3: echo "Indicações de terceiro nível"; break;
                                case 4: echo "Indicações de quarto nível"; break;
                            }
                            ?>
                        </td>
                        <td><?php echo @number_format($level['total_referrals']); ?></td>
                        <td><?php echo @number_format($level['unique_referrers']); ?></td>
                        <td>R$ <?php echo @number_format($level['total_commissions'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo @number_format($avg_commission, 2, ',', '.'); ?></td>
                    </tr>
                    <?php 
                        endwhile; 
                    } else {
                    ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 2rem; color: var(--dark-text-secondary);">
                            <i class="fas fa-layer-group" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                            Nenhum nível encontrado
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Configuração global do Chart.js para tema escuro
        Chart.defaults.color = '#b0b0b0';
        Chart.defaults.borderColor = '#404040';
        Chart.defaults.backgroundColor = 'rgba(74, 158, 255, 0.1)';

        // Gráfico de Área - Evolução de Comissões por Nível
        const areaCtx = document.getElementById('commissionsAreaChart').getContext('2d');
        new Chart(areaCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($area_chart_labels); ?>,
                datasets: [
                    {
                        label: 'Nível 1',
                        data: <?php echo json_encode($area_chart_data[1] ?? []); ?>,
                        borderColor: '#4a9eff',
                        backgroundColor: 'rgba(74, 158, 255, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#4a9eff',
                        pointBorderColor: '#4a9eff',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 3
                    },
                    {
                        label: 'Nível 2',
                        data: <?php echo json_encode($area_chart_data[2] ?? []); ?>,
                        borderColor: '#00d4aa',
                        backgroundColor: 'rgba(0, 212, 170, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#00d4aa',
                        pointBorderColor: '#00d4aa',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 3
                    },
                    {
                        label: 'Nível 3',
                        data: <?php echo json_encode($area_chart_data[3] ?? []); ?>,
                        borderColor: '#ff9500',
                        backgroundColor: 'rgba(255, 149, 0, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ff9500',
                        pointBorderColor: '#ff9500',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 3
                    },
                    {
                        label: 'Nível 4',
                        data: <?php echo json_encode($area_chart_data[4] ?? []); ?>,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#8b5cf6',
                        pointBorderColor: '#8b5cf6',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            color: '#ffffff',
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(45, 45, 45, 0.95)',
                        titleColor: '#ffffff',
                        bodyColor: '#b0b0b0',
                        borderColor: '#404040',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#404040',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#b0b0b0',
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            },
                            padding: 10
                        }
                    },
                    x: {
                        grid: {
                            color: '#404040',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#b0b0b0',
                            font: {
                                size: 11
                            },
                            padding: 10
                        }
                    }
                }
            }
        });

        // Gráfico de Barras - Indicações por Nível
         const referralsCtx = document.getElementById('referralsChart').getContext('2d');
         new Chart(referralsCtx, {
             type: 'bar',
             data: {
                 labels: [
                     <?php 
                     if ($levels_result instanceof mysqli_result) {
                         $levels_result->data_seek(0);
                         $labels = [];
                         while ($level = $levels_result->fetch_assoc()) {
                             $labels[] = '"Nível ' . $level['level'] . '"';
                         }
                         echo implode(', ', $labels);
                         $levels_result->data_seek(0);
                     }
                     ?>
                 ],
                 datasets: [{
                     label: 'Total de Indicações',
                     data: [
                         <?php 
                         if ($levels_result instanceof mysqli_result) {
                             $data = [];
                             while ($level = $levels_result->fetch_assoc()) {
                                 $data[] = $level['total_referrals'];
                             }
                             echo implode(', ', $data);
                         }
                         ?>
                     ],
                     backgroundColor: 'rgba(0, 212, 170, 0.8)',
                     borderColor: '#00d4aa'
                 }]
             },
             options: {
                 responsive: true,
                 maintainAspectRatio: false,
                 interaction: {
                     intersect: false,
                     mode: 'index'
                 },
                 plugins: {
                     legend: {
                         position: 'bottom',
                         labels: {
                             padding: 15,
                             font: {
                                 size: 12,
                                 weight: '500'
                             },
                             color: '#ffffff',
                             usePointStyle: false
                         }
                     },
                     tooltip: {
                         backgroundColor: 'rgba(45, 45, 45, 0.95)',
                         titleColor: '#ffffff',
                         bodyColor: '#b0b0b0',
                         borderColor: '#404040',
                         borderWidth: 1,
                         cornerRadius: 8,
                         displayColors: true,
                         callbacks: {
                             label: function(context) {
                                 return context.dataset.label + ': ' + context.parsed.y + ' indicações';
                             }
                         }
                     }
                 },
                 scales: {
                     y: {
                         beginAtZero: true,
                         grid: {
                             color: '#404040',
                             drawBorder: false
                         },
                         ticks: {
                             color: '#b0b0b0',
                             font: {
                                 size: 11
                             },
                             padding: 10,
                             stepSize: 1
                         }
                     },
                     x: {
                         grid: {
                             display: false
                         },
                         ticks: {
                             color: '#b0b0b0',
                             font: {
                                 size: 11
                             },
                             padding: 10
                         }
                     }
                 },
                 elements: {
                     bar: {
                         borderWidth: 2,
                         borderRadius: 8,
                         borderSkipped: false
                     }
                 },
                 datasets: {
                     bar: {
                         categoryPercentage: 0.5,
                         barPercentage: 0.6
                     }
                 }
             }
         });
    </script>
</body>
</html>


