<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

// Verificar se é admin
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
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

// Processar ações de administração
$mensagem = ''; // Inicializa a variável mensagem para evitar warnings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $saqueId = intval($_POST['saque_id']);
    $acao = $_POST['acao'];
    
    if ($acao === 'aprovar') {
        $stmt = $conn->prepare("UPDATE saques_pix SET status = 'concluido', data_processamento = NOW() WHERE id = ?");
        $stmt->bind_param("i", $saqueId);
        $stmt->execute();
        $mensagem = "Saque aprovado com sucesso!";
    } elseif ($acao === 'cancelar') {
        // Cancelar saque e devolver o valor ao saldo
        $conn->begin_transaction();
        try {
            // Buscar dados do saque
            $stmt = $conn->prepare("SELECT user_id, valor FROM saques_pix WHERE id = ? AND status = 'pendente'");
            $stmt->bind_param("i", $saqueId);
            $stmt->execute();
            $result = $stmt->get_result();
            $saque = $result->fetch_assoc();
            
            if ($saque) {
                // Devolver valor ao saldo
                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->bind_param("di", $saque['valor'], $saque['user_id']);
                $stmt->execute();
                
                // Atualizar status do saque
                $stmt = $conn->prepare("UPDATE saques_pix SET status = 'cancelado', data_processamento = NOW() WHERE id = ?");
                $stmt->bind_param("i", $saqueId);
                $stmt->execute();
                
                $conn->commit();
                $mensagem = "Saque cancelado e valor devolvido ao usuário!";
            } else {
                $mensagem = "Erro: Saque não encontrado ou não está pendente.";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $mensagem = "Erro ao cancelar saque: " . $e->getMessage();
        }
    }
}

// Buscar todos os saques
$query = "
    SELECT s.*, u.name as usuario_nome, u.email as usuario_email 
    FROM saques_pix s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.data_solicitacao DESC
";
$result = $conn->query($query);
$saques = $result->fetch_all(MYSQLI_ASSOC);

// Estatísticas
$stats = [
    'total' => 0,
    'pendente' => 0, // Chave 'pendente' inicializada
    'concluido' => 0, // Chave 'concluido' inicializada
    'cancelado' => 0, // Chave 'cancelado' inicializada
    'processando' => 0, // Adicionado caso você tenha este status no BD
    'valor_total' => 0
];

foreach ($saques as $saque) {
    $stats['total']++;
    
    // Verifica se a chave de status existe antes de incrementar
    if (isset($stats[$saque['status']])) { // Linha 86 corrigida
        $stats[$saque['status']]++;
    } else {
        // Se um novo status aparecer, você pode inicializá-lo aqui (opcional)
        $stats[$saque['status']] = 1;
        error_log("Novo status de saque encontrado: " . $saque['status']); // Para depuração
    }

    if ($saque['status'] !== 'cancelado') {
        $stats['valor_total'] += $saque['valor'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel RaspeAqui - Saques PIX</title>
    
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

        .btn-success {
            background: var(--accent-green);
            color: white;
        }

        .btn-success:hover {
            background: #00c4a0;
            color: white;
        }

        .btn-warning {
            background: var(--accent-orange);
            color: white;
        }

        .btn-warning:hover {
            background: #e68500;
            color: white;
        }

        .btn-danger {
            background: var(--accent-red);
            color: white;
        }

        .btn-danger:hover {
            background: #ef3f3f;
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        /* Table */
        .table-container {
            background: var(--dark-card);
            border-radius: 12px;
            border: 1px solid #404040;
            overflow: hidden;
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

        /* Status Badges */
        .status-pendente {
            background: var(--accent-yellow);
            color: #92400e;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-processando {
            background: var(--accent-blue);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-concluido {
            background: var(--accent-green);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-cancelado {
            background: var(--accent-red);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Alert */
        .alert {
            background: var(--dark-card);
            border: 1px solid var(--accent-green);
            color: var(--dark-text);
            border-radius: 8px;
        }

        .alert-success {
            background: rgba(0, 212, 170, 0.1);
            border-color: var(--accent-green);
            color: var(--accent-green);
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
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
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="dashboard-title">Gerenciamento de Saques PIX</h1>
                    <p class="dashboard-subtitle">Administração de solicitações de saque via PIX</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Total de Saques -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-blue);">
                        <i class="bi bi-list-ul"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?= @number_format($stats['total'], 0, ',', '.') ?></h3>
                <p class="stat-label">Total de Saques</p>
                <small>Solicitações registradas no sistema</small>
            </div>

            <!-- Saques Pendentes -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-yellow);">
                        <i class="bi bi-clock"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?= @number_format($stats['pendente'], 0, ',', '.') ?></h3>
                <p class="stat-label">Pendentes</p>
                <small>Aguardando aprovação</small>
            </div>

            <!-- Saques Concluídos -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-green);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?= @number_format($stats['concluido'], 0, ',', '.') ?></h3>
                <p class="stat-label">Concluídos</p>
                <small>Saques processados com sucesso</small>
            </div>

            <!-- Saques Cancelados -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-red);">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
                <h3 class="stat-value"><?= @number_format($stats['cancelado'], 0, ',', '.') ?></h3>
                <p class="stat-label">Cancelados</p>
                <small>Saques cancelados pelo admin</small>
            </div>

            <!-- Valor Total -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: var(--accent-purple);">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
                <h3 class="stat-value">R$ <?= @number_format($stats['valor_total'], 2, ',', '.') ?></h3>
                <p class="stat-label">Valor Total</p>
                <small>Soma dos valores solicitados</small>
            </div>
        </div>

        <!-- Saques List Section -->
        <div class="content-section">
            <h4 class="section-title">
                <i class="fas fa-money-bill-transfer"></i>
                Solicitações de Saque
            </h4>
            
            <!-- Saques Table -->
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Valor</th>
                            <th>Chave PIX</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($saques as $saque): ?>
                            <tr>
                                <td><strong>#<?= $saque['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($saque['usuario_nome']) ?></strong><br>
                                    <span class="text-white"><?= htmlspecialchars($saque['usuario_email']) ?></span>
                                </td>
                                <td><strong>R$ <?= @number_format($saque['valor'], 2, ',', '.') ?></strong></td>
                                <td>
                                    <strong><?= ucfirst($saque['tipo_chave']) ?></strong><br>
                                    <span class="text-white"><?= htmlspecialchars($saque['chave_pix']) ?></span>
                                </td>
                                <td>
                                    <span class="status-<?= $saque['status'] ?>">
                                        <?= ucfirst($saque['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($saque['data_solicitacao'])) ?></td>
                                <td>
                                    <?php if ($saque['status'] === 'pendente'): ?>
                                        <div class="d-flex gap-2">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="saque_id" value="<?= $saque['id'] ?>">
                                                <input type="hidden" name="acao" value="aprovar">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Aprovar este saque?')">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="saque_id" value="<?= $saque['id'] ?>">
                                                <input type="hidden" name="acao" value="cancelar">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancelar este saque? O valor será devolvido ao usuário.')">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-white">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>