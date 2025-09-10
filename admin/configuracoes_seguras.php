<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/lotuspay_api.php';
require_once '../includes/security.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verifica se é admin
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !$user['is_admin']) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!Security::validateCSRFToken($csrf_token)) {
        $error = 'Token de segurança inválido';
        Security::logSecurityEvent('csrf_token_invalid', [
            'user_id' => $_SESSION['usuario_id'],
            'action' => 'configuracoes_update'
        ]);
    } else {
        $action = Security::sanitizeInput($_POST['action'] ?? '');
        
        if ($action === 'update_lotuspay') {
            $client_id = Security::sanitizeInput($_POST['client_id'] ?? '');
            $client_secret = Security::sanitizeInput($_POST['client_secret'] ?? '');
            $webhook_url = Security::sanitizeInput($_POST['webhook_url'] ?? '');
            $base_url = Security::sanitizeInput($_POST['base_url'] ?? '');
            
            // Validações
            if (empty($client_id) || empty($client_secret) || empty($webhook_url) || empty($base_url)) {
                $error = 'Todos os campos são obrigatórios!';
            } elseif (!filter_var($webhook_url, FILTER_VALIDATE_URL)) {
                $error = 'URL do webhook inválida!';
            } elseif (!filter_var($base_url, FILTER_VALIDATE_URL)) {
                $error = 'URL base da API inválida!';
            } elseif (strlen($client_id) < 10 || strlen($client_secret) < 20) {
                $error = 'Credenciais LotusPay parecem inválidas!';
            } else {
                try {
                    LotusPayConfig::setClientId($client_id);
                    LotusPayConfig::setClientSecret($client_secret);
                    LotusPayConfig::setWebhookUrl($webhook_url);
                    LotusPayConfig::setBaseUrl($base_url);
                    
                    $success = 'Configurações LotusPay atualizadas com sucesso!';
                    Security::logSecurityEvent('lotuspay_config_updated', [
                        'user_id' => $_SESSION['usuario_id'],
                        'client_id' => substr($client_id, 0, 10) . '...',
                        'webhook_url' => $webhook_url,
                        'base_url' => $base_url
                    ]);
                } catch (Exception $e) {
                    $error = 'Erro ao salvar configurações: ' . $e->getMessage();
                    Security::logSecurityEvent('lotuspay_config_error', [
                        'user_id' => $_SESSION['usuario_id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        

    }
}

// Busca configurações atuais
$current_client_id = LotusPayConfig::getClientId();
$current_client_secret = LotusPayConfig::getClientSecret();
$current_webhook_url = LotusPayConfig::getWebhookUrl();
$current_base_url = LotusPayConfig::getBaseUrl();



// Gera novo token CSRF
$csrf_token = Security::generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciais - Painel RaspeAqui</title>
    
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
            --accent-yellow: #ffc107;
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

        /* Content Sections */
        .content-section {
            background: var(--dark-card);
            border-radius: 8px;
            padding: 1.5rem;
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
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            color: var(--dark-text);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            background: var(--dark-card);
            border: 1px solid #404040;
            color: var(--dark-text);
            border-radius: 6px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: var(--dark-card);
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.1);
        }

        .form-control::placeholder {
            color: var(--dark-text-secondary);
        }

        .form-select {
            background: var(--dark-card);
            border: 1px solid #404040;
            color: var(--dark-text);
            border-radius: 6px;
            padding: 0.75rem;
        }

        .form-select:focus {
            background: var(--dark-card);
            border-color: var(--accent-blue);
            color: var(--dark-text);
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.1);
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

        /* Alerts */
        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid;
        }

        .alert-danger {
            background: rgba(255, 87, 87, 0.1);
            color: var(--accent-red);
            border-color: rgba(255, 87, 87, 0.2);
        }

        .alert-success {
            background: rgba(0, 212, 170, 0.1);
            color: var(--accent-green);
            border-color: rgba(0, 212, 170, 0.2);
        }

        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            color: var(--accent-yellow);
            border-color: rgba(255, 193, 7, 0.2);
        }

        /* Security Badge */
        .security-badge {
            background: rgba(139, 92, 246, 0.1);
            color: var(--accent-purple);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(139, 92, 246, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Content Styles */
        .tab-content {
            background: var(--dark-card);
            border-radius: 8px;
            border: 1px solid #404040;
            min-height: 400px;
        }

        .tab-content-inner {
            padding: 2rem;
        }

        .file-info {
            font-size: 0.8rem;
            color: var(--dark-text-secondary);
            margin-top: 0.5rem;
        }

        .password-strength {
            font-size: 0.8rem;
            color: var(--dark-text-secondary);
            margin-top: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .image-grid {
                grid-template-columns: 1fr;
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
                    <h1 class="dashboard-title">Credenciais do Gateway</h1>
                    <p class="dashboard-subtitle">Gerencie as configurações do LotusPay com segurança</p>
                </div>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <!-- LotusPay Configuration -->
        <div class="content-section">
            <div class="tab-content-inner">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="section-title mb-0">
                                <i class="bi bi-key"></i>
                                Configurações LotusPay
                            </h4>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="action" value="update_lotuspay">
                            
                            <div class="form-group">
                                <label for="client_id" class="form-label">
                                    <i class="bi bi-credit-card"></i>
                                    Client ID
                                </label>
                                <input type="text" id="client_id" name="client_id" class="form-control"
                                       value="<?= htmlspecialchars($current_client_id) ?>" 
                                       required minlength="10" maxlength="100"
                                       placeholder="Insira o Client ID do LotusPay">
                                <div class="file-info">Identificador único fornecido pelo LotusPay</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="client_secret" class="form-label">
                                    <i class="bi bi-shield-lock"></i>
                                    Client Secret
                                </label>
                                <input type="password" id="client_secret" name="client_secret" class="form-control"
                                       value="<?= htmlspecialchars($current_client_secret) ?>" 
                                       required minlength="20" maxlength="200"
                                       placeholder="Insira o Client Secret do LotusPay">
                                <div class="password-strength">Chave secreta - mantenha em segurança</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="webhook_url" class="form-label">
                                    <i class="bi bi-globe"></i>
                                    URL do Webhook
                                </label>
                                <input type="url" id="webhook_url" name="webhook_url" class="form-control"
                                       value="<?= htmlspecialchars($current_webhook_url) ?>" 
                                       required pattern="https://.*"
                                       placeholder="https://seusite.com/webhook">
                                <div class="file-info">URL que receberá as notificações de pagamento (deve usar HTTPS)</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="base_url" class="form-label">
                                    <i class="bi bi-link-45deg"></i>
                                    URL Base da API
                                </label>
                                <input type="url" id="base_url" name="base_url" class="form-control"
                                       value="<?= htmlspecialchars($current_base_url) ?>" 
                                       required pattern="https://.*"
                                       placeholder="https://api.lotuspay.co/v2">
                                <div class="file-info">URL base da API do gateway (LotusPay: https://api.lotuspay.co/v2 | PixUp: https://api.pixup.com.br/v2)</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                Salvar Configurações LotusPay
                            </button>
                        </form>
                    </div>
        </div>
        
    </main>
    
    <script>
        // Validação em tempo real
        document.getElementById('client_secret').addEventListener('input', function() {
            const strength = this.nextElementSibling;
            const value = this.value;
            
            if (value.length < 20) {
                strength.textContent = 'Muito curto - mínimo 20 caracteres';
                strength.style.color = '#dc3545';
            } else if (value.length < 40) {
                strength.textContent = 'Comprimento adequado';
                strength.style.color = '#ffc107';
            } else {
                strength.textContent = 'Comprimento seguro';
                strength.style.color = '#28a745';
            }
        });
        
        // Validação de URL
        document.getElementById('webhook_url').addEventListener('input', function() {
            const info = this.nextElementSibling;
            const value = this.value;
            
            if (value && !value.startsWith('https://')) {
                info.textContent = '⚠️ URL deve usar HTTPS para segurança';
                info.style.color = '#dc3545';
            } else {
                info.textContent = 'URL que receberá as notificações de pagamento (deve usar HTTPS)';
                info.style.color = '#666';
            }
        });
        

    </script>
</body>
</html>

