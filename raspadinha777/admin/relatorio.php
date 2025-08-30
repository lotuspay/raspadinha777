<?php
session_start();
require '../includes/db.php';

// Verificar se é admin
if (!isset($_SESSION['usuario_id'])) {
    // Para teste, permitir acesso direto se o parâmetro test=1 estiver presente
    if (isset($_GET['test']) && $_GET['test'] == '1') {
        $_SESSION['usuario_id'] = 1;
        // Garantir que o usuário 1 seja admin
        $stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = 1");
        $stmt->execute();
    } else {
        header("Location: login.php");
        exit;
    }
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !$user['is_admin']) {
    header("Location: ../perfil.php");
    exit;
}

// Parâmetros de filtro e paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$resultado_filter = isset($_GET['resultado']) ? $_GET['resultado'] : '';

// Construir query com filtros
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "u.name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(j.data_jogada) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(j.data_jogada) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if ($resultado_filter !== '') {
    $where_conditions[] = "j.resultado = ?";
    $params[] = $resultado_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query principal
$query = "
  SELECT j.*, u.name 
  FROM jogadas_raspadinha j 
  JOIN users u ON j.usuario_id = u.id 
  $where_clause
  ORDER BY j.data_jogada DESC
  LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Contar total de registros
$count_query = "
  SELECT COUNT(*) as total
  FROM jogadas_raspadinha j 
  JOIN users u ON j.usuario_id = u.id 
  $where_clause
";

if (!empty($where_conditions)) {
    $count_stmt = $conn->prepare($count_query);
    $count_types = substr($types, 0, -2); // Remove os últimos 'ii' do limit/offset
    $count_params = array_slice($params, 0, -2); // Remove limit/offset
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_records = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $limit);

// Estatísticas
$stats_query = "
  SELECT 
    COUNT(*) as total_jogadas,
    SUM(j.valor_aposta) as total_apostado,
    SUM(j.premio) as total_premios,
    SUM(CASE WHEN j.resultado = 'ganhou' THEN 1 ELSE 0 END) as total_vitorias
  FROM jogadas_raspadinha j 
  JOIN users u ON j.usuario_id = u.id 
  $where_clause
";

if (!empty($where_conditions)) {
    $stats_stmt = $conn->prepare($stats_query);
    if (!empty($count_params)) {
        $stats_stmt->bind_param($count_types, ...$count_params);
    }
    $stats_stmt->execute();
    $stats = $stats_stmt->get_result()->fetch_assoc();
} else {
    $stats = $conn->query($stats_query)->fetch_assoc();
}

// Tratar valores null para evitar warnings
$stats['total_jogadas'] = $stats['total_jogadas'] ?? 0;
$stats['total_apostado'] = $stats['total_apostado'] ?? 0;
$stats['total_premios'] = $stats['total_premios'] ?? 0;
$stats['total_vitorias'] = $stats['total_vitorias'] ?? 0;

// Calcular lucro da casa
$lucro_casa = $stats['total_apostado'] - $stats['total_premios'];
$taxa_vitoria = $stats['total_jogadas'] > 0 ? ($stats['total_vitorias'] / $stats['total_jogadas']) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel RaspeAqui - Relatório de Jogadas</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            --accent-yellow: #fbbf24;
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
            grid-template-columns: repeat(5, 1fr);
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

        .stat-card small {
            font-size: 0.7rem;
            color: var(--dark-text-secondary);
        }

        /* Content Sections */
        .content-section {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #404040;
            margin-bottom: 1.5rem;
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

        /* Form Elements */
        .form-label {
            color: var(--dark-text);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            background: var(--dark-card);
            border: 1px solid #404040;
            color: var(--dark-text);
            border-radius: 6px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: var(--dark-card);
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.1);
        }

        .form-control::placeholder {
            color: var(--dark-text-secondary);
        }

        /* Buttons */
        .btn {
            border-radius: 6px;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--accent-blue);
            color: white;
        }

        .btn-primary:hover {
            background: #3a8bef;
            color: white;
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            background: transparent;
            border: 1px solid #404040;
            color: var(--dark-text-secondary);
        }

        .btn-outline-secondary:hover {
            background: var(--dark-card);
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-outline-primary {
            background: transparent;
            border: 1px solid var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-outline-primary:hover {
            background: var(--accent-blue);
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        /* Bootstrap Table Dark Theme Override */
        .table-dark {
            --bs-table-bg: var(--dark-card);
            --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
            --bs-table-hover-bg: rgba(74, 158, 255, 0.1);
            --bs-table-border-color: #404040;
            color: var(--dark-text);
        }

        .table-dark th {
            background: #333333 !important;
            border-color: #404040 !important;
            color: var(--dark-text) !important;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-dark td {
            border-color: #404040 !important;
            color: var(--dark-text) !important;
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

        /* Status Badges */
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
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

        .badge-danger {
            background: var(--accent-red);
            color: white;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
                margin-top: 60px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .dashboard-header {
                padding: 0.75rem 1rem;
            }
            
            .content-section {
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
        <!-- Header -->
        <div class="dashboard-header">
            <h1 class="dashboard-title">Relatório de Jogadas</h1>
            <p class="dashboard-subtitle">Visualize e analise todas as jogadas realizadas no sistema</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Total de Jogadas -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="bi bi-dice-6"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?= number_format($stats['total_jogadas'], 0, ',', '.') ?></h3>
                <p class="stat-label">Total de Jogadas</p>
                <small>Jogadas realizadas no sistema</small>
            </div>

            <!-- Total Apostado -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-orange);">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
                <h3 class="stat-value">R$ <?= number_format($stats['total_apostado'], 2, ',', '.') ?></h3>
                <p class="stat-label">Total Apostado</p>
                <small>Valor total das apostas</small>
            </div>

            <!-- Total em Prêmios -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="bi bi-trophy"></i>
                    </div>
                </div>
                <h3 class="stat-value">R$ <?= number_format($stats['total_premios'], 2, ',', '.') ?></h3>
                <p class="stat-label">Total em Prêmios</p>
                <small>Valor total dos prêmios pagos</small>
            </div>

            <!-- Lucro da Casa -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="bi bi-bank"></i>
                    </div>
                </div>
                <h3 class="stat-value">R$ <?= number_format($lucro_casa, 2, ',', '.') ?></h3>
                <p class="stat-label">Lucro da Casa</p>
                <small>Diferença entre apostas e prêmios</small>
            </div>

            <!-- Taxa de Vitória -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-yellow);">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?= number_format($taxa_vitoria, 1) ?>%</h3>
                <p class="stat-label">Taxa de Vitória</p>
                <small>Percentual de jogadas vencedoras</small>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="content-section">
            <h4 class="section-title">
                <i class="fas fa-filter"></i>
                Filtros
            </h4>
            
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar Usuário</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nome do usuário">
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Data Final</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-md-2">
                    <label for="resultado" class="form-label">Resultado</label>
                    <select class="form-select" id="resultado" name="resultado">
                        <option value="">Todos</option>
                        <option value="ganhou" <?= $resultado_filter === 'ganhou' ? 'selected' : '' ?>>Ganhou</option>
                        <option value="perdeu" <?= $resultado_filter === 'perdeu' ? 'selected' : '' ?>>Perdeu</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Filtrar
                    </button>
                    <a href="relatorio.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="content-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="section-title">
                    <i class="fas fa-table"></i>
                    Lista de Jogadas
                </h4>
                <span class="badge bg-primary"><?= number_format($total_records, 0, ',', '.') ?> registros encontrados</span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-dark table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="width: 80px;">ID</th>
                            <th scope="col">USUÁRIO</th>
                            <th scope="col" style="width: 120px;">APOSTA</th>
                            <th scope="col" style="width: 120px;">RESULTADO</th>
                            <th scope="col" style="width: 120px;">PRÊMIO</th>
                            <th scope="col" style="width: 180px;">DATA/HORA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Executar query novamente para a tabela
                        $table_stmt = $conn->prepare($query);
                        if (!empty($params)) {
                            $table_types = $types;
                            $table_params = $params;
                            $table_stmt->bind_param($table_types, ...$table_params);
                        } else {
                            $table_stmt->bind_param('ii', $limit, $offset);
                        }
                        $table_stmt->execute();
                        $table_result = $table_stmt->get_result();
                        
                        if ($table_result && $table_result->num_rows > 0): 
                        ?>
                            <?php while ($row = $table_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['id']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><span class="text-warning">R$ <?= number_format($row['valor_aposta'], 2, ',', '.') ?></span></td>
                                    <td>
                                        <?php if ($row['resultado'] === 'ganhou'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i>
                                                Ganhou
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i>
                                                Perdeu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="text-success">R$ <?= number_format($row['premio'], 2, ',', '.') ?></span></td>
                                    <td><small><?= date('d/m/Y H:i:s', strtotime($row['data_jogada'])) ?></small></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                        <h5>Nenhuma jogada encontrada</h5>
                                        <p>Não há registros com os filtros aplicados.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Mostrando <?= ($offset + 1) ?> a <?= min($offset + $limit, $total_records) ?> de <?= number_format($total_records, 0, ',', '.') ?> registros
                </div>
                
                <nav aria-label="Navegação de páginas">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($date_from) ? '&date_from=' . urlencode($date_from) : '' ?><?= !empty($date_to) ? '&date_to=' . urlencode($date_to) : '' ?><?= !empty($resultado_filter) ? '&resultado=' . urlencode($resultado_filter) : '' ?>">
                                    <i class="fas fa-chevron-left"></i>
                                    Anterior
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($date_from) ? '&date_from=' . urlencode($date_from) : '' ?><?= !empty($date_to) ? '&date_to=' . urlencode($date_to) : '' ?><?= !empty($resultado_filter) ? '&resultado=' . urlencode($resultado_filter) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($date_from) ? '&date_from=' . urlencode($date_from) : '' ?><?= !empty($date_to) ? '&date_to=' . urlencode($date_to) : '' ?><?= !empty($resultado_filter) ? '&resultado=' . urlencode($resultado_filter) : '' ?>">
                                    Próximo
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

