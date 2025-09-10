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
$error = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_influencer':
                $user_id = $_POST['user_id'];
                $win_percentage = $_POST['win_percentage'];
                $prize_value = $_POST['prize_value'];
                $max_wins = !empty($_POST['max_wins']) ? $_POST['max_wins'] : NULL;
                
                // Verificar se o usuário existe
                $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows == 0) {
                    $error = "Usuário não encontrado!";
                    break;
                }
                
                // Inserir ou atualizar influencer_mode
                $stmt = $conn->prepare("
                    INSERT INTO influencer_mode (user_id, is_active, win_percentage, prize_value, max_wins) 
                    VALUES (?, 1, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    is_active = 1,
                    win_percentage = VALUES(win_percentage),
                    prize_value = VALUES(prize_value),
                    max_wins = VALUES(max_wins)
                ");
                $stmt->bind_param("iddi", $user_id, $win_percentage, $prize_value, $max_wins);
                
                if ($stmt->execute()) {
                    // Ativar influence_mode_enabled na tabela user_settings
                    $stmt = $conn->prepare("
                        INSERT INTO user_settings (user_id, influence_mode_enabled) 
                        VALUES (?, 1)
                        ON DUPLICATE KEY UPDATE influence_mode_enabled = 1
                    ");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    
                    $message = "Influenciador adicionado/atualizado com sucesso!";
                } else {
                    $error = "Erro ao adicionar influenciador: " . $conn->error;
                }
                break;
                
            case 'update_influencer':
                $influencer_id = $_POST['influencer_id'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $win_percentage = $_POST['win_percentage'];
                $prize_value = $_POST['prize_value'];
                $max_wins = !empty($_POST['max_wins']) ? $_POST['max_wins'] : NULL;
                
                $stmt = $conn->prepare("
                    UPDATE influencer_mode 
                    SET is_active = ?, win_percentage = ?, prize_value = ?, max_wins = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("iddii", $is_active, $win_percentage, $prize_value, $max_wins, $influencer_id);
                
                if ($stmt->execute()) {
                    $message = "Influenciador atualizado com sucesso!";
                } else {
                    $error = "Erro ao atualizar influenciador: " . $conn->error;
                }
                break;
                
            case 'delete_influencer':
                $influencer_id = $_POST['influencer_id'];
                
                $stmt = $conn->prepare("DELETE FROM influencer_mode WHERE id = ?");
                $stmt->bind_param("i", $influencer_id);
                
                if ($stmt->execute()) {
                    $message = "Influenciador removido com sucesso!";
                } else {
                    $error = "Erro ao remover influenciador: " . $conn->error;
                }
                break;
        }
    }
}

// Sincronizar usuários com influence_mode_enabled mas sem registro em influencer_mode
$sync_query = "
    INSERT INTO influencer_mode (user_id, is_active, win_percentage, prize_value, max_wins, current_wins)
    SELECT 
        us.user_id,
        1,
        100.00,
        10.00,
        NULL,
        0
    FROM user_settings us
    JOIN users u ON us.user_id = u.id
    WHERE us.influence_mode_enabled = 1
    AND us.user_id NOT IN (SELECT user_id FROM influencer_mode)
    AND u.is_admin = 0
";
$conn->query($sync_query);

// Buscar todos os influenciadores
$influencers_query = "
    SELECT 
        im.id,
        im.user_id,
        im.is_active,
        im.win_percentage,
        im.prize_value,
        im.max_wins,
        im.current_wins,
        im.created_at,
        im.updated_at,
        u.name as username,
        u.email,
        u.balance,
        us.influence_mode_enabled
    FROM influencer_mode im
    JOIN users u ON im.user_id = u.id
    LEFT JOIN user_settings us ON u.id = us.user_id
    ORDER BY im.created_at DESC
";
$influencers_result = $conn->query($influencers_query);

// Buscar usuários que não são influenciadores para o dropdown
$non_influencers_query = "
    SELECT u.id, u.name as username, u.email 
    FROM users u 
    WHERE u.id NOT IN (SELECT user_id FROM influencer_mode)
    AND u.is_admin = 0
    ORDER BY u.name
";
$non_influencers_result = $conn->query($non_influencers_query);

// Estatísticas gerais
$stats_query = "
    SELECT 
        COUNT(*) as total_influencers,
        COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_influencers,
        AVG(win_percentage) as avg_rtp,
        SUM(current_wins) as total_wins,
        AVG(prize_value) as avg_prize
    FROM influencer_mode
";
$stats = $conn->query($stats_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Influenciadores - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="components/sidebar.css" rel="stylesheet">
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
            font-size: 0.9rem;
            margin: 0;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #404040;
            transition: all 0.3s ease;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: #505050;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.2rem;
            color: white;
        }

        .stat-icon.total { background: var(--accent-blue); }
        .stat-icon.active { background: var(--accent-green); }
        .stat-icon.rtp { background: var(--accent-orange); }
        .stat-icon.wins { background: var(--accent-purple); }
        .stat-icon.prize { background: var(--accent-red); }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0 0 0.5rem 0;
            line-height: 1.2;
        }

        .stat-label {
            color: var(--dark-text-secondary);
            font-size: 0.8rem;
            margin: 0;
        }

        /* Content Cards */
        .content-card {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #404040;
            margin-bottom: 1.5rem;
        }

        .content-card h4 {
            color: var(--dark-text);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* Table Container */
        .table-container {
            background: var(--dark-bg-secondary);
            border-radius: 8px;
            border: 1px solid #404040;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .table-header {
            background: var(--dark-bg-tertiary);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #404040;
        }

        .table-title {
            color: var(--dark-text-primary);
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        /* Custom Table */
        .custom-table {
            background: var(--dark-bg-secondary);
            border: none;
            margin: 0;
        }

        .custom-table th {
            background: var(--dark-bg-tertiary);
            color: var(--dark-text-primary) !important;
            border-bottom: 2px solid #404040;
            padding: 12px;
            font-weight: 600;
            border: none;
        }

        .custom-table td {
            background: var(--dark-bg-secondary);
            color: var(--dark-text-primary) !important;
            border-bottom: 1px solid #404040;
            padding: 12px;
            vertical-align: middle;
            border: none;
        }

        .custom-table tbody tr:hover {
            background: var(--dark-bg-tertiary);
        }

        /* Table Text Colors */
        .custom-table td strong {
            color: var(--accent-green) !important;
        }

        .custom-table .text-center {
            color: var(--dark-text-secondary) !important;
        }

        .custom-table .text-white {
            color: var(--dark-text-secondary) !important;
        }

        /* Buttons */
        .btn-custom {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .btn-primary-custom {
            background: var(--accent-blue);
            border: 1px solid var(--accent-blue);
            color: white;
        }

        .btn-primary-custom:hover {
            background: #3a8eef;
            border-color: #3a8eef;
            transform: translateY(-1px);
        }

        .btn-danger-custom {
            background: var(--accent-red);
            border: 1px solid var(--accent-red);
            color: white;
        }

        .btn-danger-custom:hover {
            background: #e54747;
            border-color: #e54747;
            transform: translateY(-1px);
        }

        /* Badges */
        .badge-custom {
            border-radius: 4px;
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Modals */
        .modal-content {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
        }

        .modal-header {
            background: var(--accent-blue);
            color: white;
            border-bottom: 1px solid #404040;
            border-radius: 8px 8px 0 0;
        }

        .modal-body {
            background: var(--dark-card);
            color: var(--dark-text);
        }

        .modal-footer {
            background: var(--dark-card);
            border-top: 1px solid #404040;
            border-radius: 0 0 8px 8px;
        }

        /* Form Controls */
        .form-control, .form-select {
            background: #404040;
            border: 1px solid #505050;
            color: var(--dark-text);
        }

        .form-control:focus, .form-select:focus {
            background: #404040;
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25);
        }

        .form-label {
            color: var(--dark-text);
            font-weight: 500;
        }

        .form-text {
            color: var(--dark-text-secondary);
        }

        /* Alerts */
        .alert {
            border-radius: 6px;
            border: none;
        }

        .alert-success {
            background: rgba(0, 212, 170, 0.1);
            color: var(--accent-green);
            border-left: 3px solid var(--accent-green);
        }

        .alert-danger {
            background: rgba(255, 87, 87, 0.1);
            color: var(--accent-red);
            border-left: 3px solid var(--accent-red);
        }

        .alert-info {
            background: rgba(74, 158, 255, 0.1);
            color: var(--accent-blue);
            border-left: 3px solid var(--accent-blue);
        }

        .alert-warning {
            background: rgba(255, 149, 0, 0.1);
            color: var(--accent-orange);
            border-left: 3px solid var(--accent-orange);
        }

        /* Responsive */
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
            
            .content-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
     <?php include 'components/header.php'; ?>
     <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="dashboard-title">Gerenciar Influenciadores</h1>
                    <p class="dashboard-subtitle">Configure RTP e controle influenciadores do sistema</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary-custom btn-custom" data-bs-toggle="modal" data-bs-target="#addInfluencerModal">
                        <i class="bi bi-plus-circle"></i> Adicionar Influenciador
                    </button>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value"><?php echo @number_format($stats['total_influencers']); ?></div>
                <div class="stat-label">Total de Influenciadores</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo @number_format($stats['active_influencers']); ?></div>
                <div class="stat-label">Influenciadores Ativos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon rtp">
                    <i class="bi bi-percent"></i>
                </div>
                <div class="stat-value"><?php echo @number_format($stats['avg_rtp'] ?? 0, 1); ?>%</div>
                <div class="stat-label">RTP Médio</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon wins">
                    <i class="bi bi-trophy"></i>
                </div>
                <div class="stat-value"><?php echo @number_format($stats['total_wins'] ?? 0); ?></div>
                <div class="stat-label">Total de Vitórias</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon prize">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-value">R$ <?php echo @number_format($stats['avg_prize'] ?? 0, 2, ',', '.'); ?></div>
                <div class="stat-label">Prêmio Médio</div>
            </div>
        </div>

        <!-- Influencers Table -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">Lista de Influenciadores</h3>
            </div>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>RTP (%)</th>
                            <th>Valor Prêmio</th>
                            <th>Vitórias</th>
                            <th>Limite</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($influencers_result->num_rows > 0): ?>
                            <?php while ($influencer = $influencers_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $influencer['id']; ?></strong></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($influencer['username']); ?></strong>
                                        <br><small class="text-white">ID: <?php echo $influencer['user_id']; ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($influencer['email']); ?></td>
                                <td>
                                    <?php if ($influencer['is_active']): ?>
                                        <span class="badge badge-custom" style="background: var(--accent-green); color: white;">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge badge-custom" style="background: var(--accent-red); color: white;">Inativo</span>
                                    <?php endif; ?>
                                    <?php if ($influencer['influence_mode_enabled']): ?>
                                        <br><span class="badge badge-custom mt-1" style="background: var(--accent-blue); color: white;">Modo Ativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $rtp = $influencer['win_percentage'];
                                    $color = 'var(--accent-red)';
                                    if ($rtp >= 10) $color = 'var(--accent-green)';
                                    elseif ($rtp >= 5) $color = 'var(--accent-orange)';
                                    ?>
                                    <span class="badge badge-custom" style="background: <?php echo $color; ?>; color: white;">
                                        <?php echo @number_format($influencer['win_percentage'], 2); ?>%
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: var(--accent-green);">R$ <?php echo @number_format($influencer['prize_value'], 2, ',', '.'); ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-custom" style="background: var(--accent-purple); color: white;">
                                        <?php echo @number_format($influencer['current_wins']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($influencer['max_wins']): ?>
                                        <span class="badge badge-custom" style="background: #505050; color: white;">
                                            <?php echo @number_format($influencer['max_wins']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--dark-text-secondary);">Ilimitado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($influencer['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm">
                                        <button type="button" class="btn btn-primary-custom btn-sm mb-1" 
                                             onclick="editInfluencer(<?php echo htmlspecialchars(json_encode($influencer)); ?>)">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-danger-custom btn-sm" 
                                             onclick="deleteInfluencer(<?php echo $influencer['id']; ?>, '<?php echo htmlspecialchars($influencer['username']); ?>')">
                                        <i class="bi bi-trash"></i> Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 text-white"></i>
                                    <p class="text-white mt-2">Nenhum influenciador cadastrado ainda.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Influenciador -->
    <div class="modal fade" id="addInfluencerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Adicionar Novo Influenciador
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_influencer">
                    
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Atenção:</strong> Certifique-se de que o usuário existe no sistema antes de adicioná-lo como influenciador.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Usuário</label>
                                <select class="form-select" name="user_id" required>
                                    <option value="">Selecione um usuário...</option>
                                    <?php while ($user = $non_influencers_result->fetch_assoc()): ?>
                                        <option value="<?php echo $user['id']; ?>">
                                            <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">RTP - Taxa de Vitória (%)</label>
                                <input type="number" class="form-control" name="win_percentage" 
                                       step="0.01" min="0" max="100" value="5.00" required>
                                <div class="form-text">Porcentagem de chance de ganhar (0-100%)</div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Valor do Prêmio (R$)</label>
                                <input type="number" class="form-control" name="prize_value" 
                                       step="0.01" min="0" value="10.00" required>
                                <div class="form-text">Valor em reais quando o influenciador ganhar</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Limite de Vitórias</label>
                                <input type="number" class="form-control" name="max_wins" 
                                       min="1" placeholder="Deixe vazio para ilimitado">
                                <div class="form-text">Máximo de vitórias permitidas (opcional)</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="bi bi-check-circle"></i> Adicionar Influenciador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Influenciador -->
    <div class="modal fade" id="editInfluencerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i> Editar Influenciador
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_influencer">
                    <input type="hidden" name="influencer_id" id="edit_influencer_id">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="edit_username" readonly>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" 
                                   id="edit_is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                <strong>Influenciador Ativo</strong>
                            </label>
                            <div class="form-text">Desmarque para desativar temporariamente</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">RTP - Taxa de Vitória (%)</label>
                                <input type="number" class="form-control" name="win_percentage" 
                                       id="edit_win_percentage" step="0.01" min="0" max="100" required>
                                <div class="form-text">Porcentagem de chance de ganhar (0-100%)</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Valor do Prêmio (R$)</label>
                                <input type="number" class="form-control" name="prize_value" 
                                       id="edit_prize_value" step="0.01" min="0" required>
                                <div class="form-text">Valor em reais quando ganhar</div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Limite de Vitórias</label>
                                <input type="number" class="form-control" name="max_wins" 
                                       id="edit_max_wins" min="1" placeholder="Deixe vazio para ilimitado">
                                <div class="form-text">Máximo de vitórias permitidas (opcional)</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Vitórias Atuais</label>
                                <input type="text" class="form-control" id="edit_current_wins" readonly>
                                <div class="form-text">Número atual de vitórias (somente leitura)</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="bi bi-check-circle"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Exclusão -->
    <div class="modal fade" id="deleteInfluencerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--accent-red); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete_influencer">
                    <input type="hidden" name="influencer_id" id="delete_influencer_id">
                    
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Atenção!</strong> Esta ação não pode ser desfeita.
                        </div>
                        
                        <p>Tem certeza que deseja excluir o influenciador <strong id="delete_username"></strong>?</p>
                        
                        <p class="text-white">Isso removerá todas as configurações de RTP específicas para este usuário.</p>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger-custom">
                            <i class="bi bi-trash"></i> Confirmar Exclusão
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editInfluencer(influencer) {
            document.getElementById('edit_influencer_id').value = influencer.id;
            document.getElementById('edit_username').value = influencer.username;
            document.getElementById('edit_is_active').checked = influencer.is_active == 1;
            document.getElementById('edit_win_percentage').value = influencer.win_percentage;
            document.getElementById('edit_prize_value').value = influencer.prize_value;
            document.getElementById('edit_max_wins').value = influencer.max_wins || '';
            document.getElementById('edit_current_wins').value = influencer.current_wins;
            
            new bootstrap.Modal(document.getElementById('editInfluencerModal')).show();
        }
        
        function deleteInfluencer(id, username) {
            document.getElementById('delete_influencer_id').value = id;
            document.getElementById('delete_username').textContent = username;
            
            new bootstrap.Modal(document.getElementById('deleteInfluencerModal')).show();
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>