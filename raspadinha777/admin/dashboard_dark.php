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

// Busca estatísticas do banco
$stmt = $conn->query("SELECT COUNT(*) as total FROM users");
$total_usuarios = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT SUM(valor) as total FROM depositos WHERE status = 'aprovado'");
$result = $stmt->fetch_assoc();
$total_depositos = $result['total'] ?? 0;

$stmt = $conn->query("SELECT COUNT(*) as total FROM raspadinha_jogadas WHERE DATE(criado_em) = CURDATE()");
$raspadinhas_hoje = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM raspadinha_jogadas WHERE ganhou = 1 AND DATE(criado_em) = CURDATE()");
$raspadinhas_ganhas = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT valor FROM configuracoes WHERE chave = 'rtp'");
$result = $stmt->fetch_assoc();
$rtp_atual = $result['valor'] ?? 85;

// Calcular estatísticas adicionais
$stmt = $conn->query("SELECT SUM(valor) as total FROM depositos WHERE status = 'pendente'");
$result = $stmt->fetch_assoc();
$depositos_pendentes = $result['total'] ?? 0;

$stmt = $conn->query("SELECT SUM(valor_premio) as total FROM raspadinha_jogadas WHERE ganhou = 1 AND DATE(criado_em) = CURDATE()");
$result = $stmt->fetch_assoc();
$premios_pagos = $result['total'] ?? 0;

$stmt = $conn->query("SELECT COUNT(*) as total FROM raspadinha_jogadas WHERE DATE(criado_em) = CURDATE()");
$result = $stmt->fetch_assoc();
$total_jogadas_hoje = $result['total'] ?? 0;

$stmt = $conn->query("SELECT SUM(aposta) as total FROM raspadinha_jogadas WHERE DATE(criado_em) = CURDATE()");
$result = $stmt->fetch_assoc();
$receita_total = $result['total'] ?? 0;

$lucro_liquido = $receita_total - $premios_pagos;

// Dados para gráficos - últimos 15 dias
$depositos_chart = [];
$raspadinhas_chart = [];
$labels_chart = [];

for ($i = 14; $i >= 0; $i--) {
    $data = date('Y-m-d', strtotime("-$i days"));
    $labels_chart[] = date('d/m', strtotime("-$i days"));
    
    // Depósitos por dia
    $stmt = $conn->prepare("SELECT COALESCE(SUM(valor), 0) as total FROM depositos WHERE DATE(data_criacao) = ? AND status = 'aprovado'");
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $depositos_chart[] = floatval($result['total']);
    
    // Raspadinhas por dia
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM jogadas_raspadinha WHERE DATE(data_jogada) = ?");
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $raspadinhas_chart[] = intval($result['total']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel RaspeAqui - Dashboard</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Sidebar Component CSS -->
    <link href="components/sidebar_dark.css" rel="stylesheet">
    
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
            padding: 2rem;
            min-height: 100vh;
            background: var(--dark-bg);
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

        /* Chart Container */
        .chart-container {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #404040;
            margin-bottom: 1.5rem;
        }

        .chart-container canvas {
            max-height: 250px !important;
            height: 250px !important;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-text);
            margin: 0;
        }

        .chart-period {
            color: var(--dark-text-secondary);
            font-size: 0.75rem;
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="dashboard-title">Resumo dos Últimos 3 Dias</h1>
                    <p class="dashboard-subtitle">Performance dos últimos 3 dias</p>
                </div>
                <div class="text-end">
                    <div class="badge badge-success">Nova raspadinha jogada!</div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Novos Cadastros -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="stat-change positive">+5</div>
                </div>
                <h3 class="stat-value"><?php echo number_format($total_usuarios, 0, ',', '.'); ?></h3>
                <p class="stat-label">Novos Cadastros</p>
                <small class="text-white">3 cadastros nos últimos 7 dias</small>
            </div>

            <!-- Depósitos Pagos -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-change positive">+R$ 50,00</div>
                </div>
                <h3 class="stat-value">R$ <?php echo number_format($total_depositos, 2, ',', '.'); ?></h3>
                <p class="stat-label">Depósitos Pagos</p>
                <small class="text-white">R$ 15,00 nos últimos 7 dias</small>
            </div>

            <!-- Saques Pagos -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-orange);">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <div class="stat-change negative">R$ 0,00</div>
                </div>
                <h3 class="stat-value">R$ 0,00</h3>
                <p class="stat-label">Saques Pagos</p>
                <small class="text-white">Total pendente: R$ 50,00</small>
            </div>

            <!-- Raspadinhas Jogadas -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="bi bi-dice-6"></i>
                    </div>
                    <div class="stat-change positive">+10</div>
                </div>
                <h3 class="stat-value"><?php echo number_format($raspadinhas_hoje, 0, ',', '.'); ?></h3>
                <p class="stat-label">Raspadinhas Jogadas</p>
                <small class="text-white">40 jogadas nos últimos 7 dias</small>
            </div>

            <!-- Total de Depósitos Pendentes -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-orange);">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="stat-change positive">+R$ 420,00</div>
                </div>
                <h3 class="stat-value">R$ <?php echo number_format($depositos_pendentes, 2, ',', '.'); ?></h3>
                <p class="stat-label">Total de Depósitos Pendentes</p>
                <small class="text-white">Total de depósitos pendentes</small>
            </div>

            <!-- Depósitos - Saques -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="stat-change positive">+R$ 161,00</div>
                </div>
                <h3 class="stat-value">R$ <?php echo number_format($lucro_liquido, 2, ',', '.'); ?></h3>
                <p class="stat-label">Depósitos - Saques</p>
                <small class="text-white">Depósitos - Saques</small>
            </div>

            <!-- Total de Prêmios por Raspadinhas -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="stat-change positive">+R$ 36,00</div>
                </div>
                <h3 class="stat-value">R$ <?php echo number_format($receita_total, 2, ',', '.'); ?></h3>
                <p class="stat-label">Total de Prêmios por Raspadinhas</p>
                <small class="text-white">Total de prêmios por raspadinhas</small>
            </div>

            <!-- Valor Comprado - Prêmios -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <div class="stat-change positive">+R$ 164,00</div>
                </div>
                <h3 class="stat-value">R$ <?php echo number_format($premios_pagos, 2, ',', '.'); ?></h3>
                <p class="stat-label">Valor Comprado - Prêmios</p>
                <small class="text-white">Valor comprado - Prêmios</small>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <h4 class="chart-title">Depósitos Pagos (R$)</h4>
                        <span class="chart-period">Performance dos últimos 15 dias</span>
                    </div>
                    <canvas id="depositChart"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <h4 class="chart-title">Raspadinhas Compradas</h4>
                        <span class="chart-period">Performance dos últimos 15 dias</span>
                    </div>
                    <canvas id="scratchChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="table-container">
                    <div class="table-header">
                        <h4 class="table-title">Últimos 5 Cadastros</h4>
                    </div>
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Email</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("SELECT name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
                            while ($row = $stmt->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="table-container">
                    <div class="table-header">
                        <h4 class="table-title">Últimas 5 Raspadinhas</h4>
                    </div>
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Usuário / Jogo</th>
                                <th>Prêmio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("
                                SELECT u.name, r.valor_premio, r.ganhou 
                                FROM raspadinha_jogadas r 
                                JOIN users u ON r.user_id = u.id 
                                ORDER BY r.criado_em DESC 
                                LIMIT 5
                            ");
                            while ($row = $stmt->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['name']) . "<br><small class='text-white'>Raspadinha de Ouro</small></td>";
                                if ($row['ganhou']) {
                                    echo "<td><span class='badge badge-success'>R$ " . number_format($row['valor_premio'], 2, ',', '.') . "</span></td>";
                                } else {
                                    echo "<td><span class='badge badge-danger'>R$ 0,00</span></td>";
                                }
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Chart.js configuration
        Chart.defaults.color = '#b0b0b0';
        Chart.defaults.borderColor = '#404040';
        Chart.defaults.backgroundColor = 'rgba(74, 158, 255, 0.1)';

        // Deposit Chart
        const depositCtx = document.getElementById('depositChart').getContext('2d');
        new Chart(depositCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels_chart); ?>,
                datasets: [{
                    label: 'Depósitos (R$)',
                    data: <?php echo json_encode($depositos_chart); ?>,
                    borderColor: '#00d4aa',
                    backgroundColor: 'rgba(0, 212, 170, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#00d4aa',
                    pointBorderColor: '#00d4aa',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#404040'
                        }
                    },
                    x: {
                        grid: {
                            color: '#404040'
                        }
                    }
                }
            }
        });

        // Scratch Chart
        const scratchCtx = document.getElementById('scratchChart').getContext('2d');
        new Chart(scratchCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels_chart); ?>,
                datasets: [{
                    label: 'Raspadinhas',
                    data: <?php echo json_encode($raspadinhas_chart); ?>,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#8b5cf6',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#404040'
                        }
                    },
                    x: {
                        grid: {
                            color: '#404040'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>