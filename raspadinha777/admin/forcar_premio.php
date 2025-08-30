<?php
/**
 * Sistema de Configuração de Prêmios - Raspadinha
 * Versão refatorada com melhorias de segurança e estrutura
 * Versão completa integrada com todos os recursos
 */

session_start();
require '../includes/db.php';

// Classe para gerenciar configurações de prêmios
class PremioManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Verifica se o usuário tem permissão de administrador
     */
    public function verificarPermissaoAdmin() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['is_admin'] != 1) {
            throw new Exception("Acesso negado. Você precisa ser administrador para acessar esta página.");
        }
    }
    
    /**
     * Valida os dados de entrada
     */
    private function validarDados($valor, $max) {
        $erros = [];
        
        if (!is_numeric($valor) || $valor < 0) {
            $erros[] = "O valor do prêmio deve ser um número positivo.";
        }
        
        if (!is_numeric($max) || $max < 0 || $max > 999999) {
            $erros[] = "O máximo de prêmios deve ser um número entre 0 e 999.999.";
        }
        
        return $erros;
    }
    
    /**
     * Obtém a configuração atual
     */
    public function obterConfiguracao() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM raspadinha_config LIMIT 1");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            } else {
                return [
                    'ativo' => 0,
                    'valor_premio' => 0.00,
                    'max_premios' => 0,
                    'premios_pagos' => 0
                ];
            }
        } catch (Exception $e) {
            error_log("Erro ao obter configuração: " . $e->getMessage());
            throw new Exception("Erro ao carregar configurações.");
        }
    }
    
    /**
     * Atualiza ou insere configuração
     */
    public function atualizarConfiguracao($ativo, $valor, $max) {
        // Validar dados
        $erros = $this->validarDados($valor, $max);
        if (!empty($erros)) {
            throw new Exception(implode(" ", $erros));
        }
        
        try {
            // Verificar se já existe configuração
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM raspadinha_config");
            $stmt->execute();
            $result = $stmt->get_result();
            $total = $result->fetch_assoc()['total'];
            
            if ($total == 0) {
                // Inserir nova configuração
                $stmt = $this->conn->prepare("INSERT INTO raspadinha_config (ativo, valor_premio, max_premios, premios_pagos) VALUES (?, ?, ?, 0)");
                $stmt->bind_param("idi", $ativo, $valor, $max);
            } else {
                // Atualizar configuração existente
                $stmt = $this->conn->prepare("UPDATE raspadinha_config SET ativo = ?, valor_premio = ?, max_premios = ? WHERE id = (SELECT id FROM (SELECT id FROM raspadinha_config LIMIT 1) as temp)");
                $stmt->bind_param("idi", $ativo, $valor, $max);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao salvar configuração.");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao atualizar configuração: " . $e->getMessage());
            throw new Exception("Erro ao salvar configuração. Tente novamente.");
        }
    }
    
    /**
     * Zera os prêmios pagos
     */
    public function zerarPremiosPagos() {
        try {
            $stmt = $this->conn->prepare("UPDATE raspadinha_config SET premios_pagos = 0");
            if (!$stmt->execute()) {
                throw new Exception("Erro ao zerar prêmios pagos.");
            }
            return true;
        } catch (Exception $e) {
            error_log("Erro ao zerar prêmios: " . $e->getMessage());
            throw new Exception("Erro ao zerar prêmios pagos. Tente novamente.");
        }
    }
}

// Inicializar gerenciador de prêmios
$premioManager = new PremioManager($conn);
$mensagem = '';
$tipoMensagem = '';

try {
    // Verificar permissões
    $premioManager->verificarPermissaoAdmin();
    
    // Processar ações
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['zerar_premios'])) {
            // Zerar prêmios pagos
            if ($premioManager->zerarPremiosPagos()) {
                // Redirecionar para evitar reenvio do formulário
                header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=premios_zerados');
                exit;
            }
        } elseif (isset($_POST['salvar_config'])) {
            // Atualizar configuração
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            $valor = isset($_POST['valor']) ? floatval($_POST['valor']) : 0;
            $max = isset($_POST['max']) ? intval($_POST['max']) : 0;
            
            if ($premioManager->atualizarConfiguracao($ativo, $valor, $max)) {
                // Redirecionar para evitar reenvio do formulário
                header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=config_salva');
                exit;
            }
        }
    }
    
    // Processar mensagens via GET (após redirecionamento)
    if (isset($_GET['msg'])) {
        switch ($_GET['msg']) {
            case 'premios_zerados':
                $mensagem = "Prêmios pagos zerados com sucesso!";
                $tipoMensagem = "success";
                break;
            case 'config_salva':
                $mensagem = "Configuração salva com sucesso!";
                $tipoMensagem = "success";
                break;
        }
    }
    
    // Obter configuração atual
    $config = $premioManager->obterConfiguracao();
    
} catch (Exception $e) {
    $mensagem = $e->getMessage();
    $tipoMensagem = "error";
    
    // Se for erro de permissão, redirecionar ou mostrar página de erro
    if (strpos($mensagem, "Acesso negado") !== false) {
        http_response_code(403);
        die("
        <!DOCTYPE html>
        <html lang='pt-br'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Acesso Negado</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f8f9fa; }
                .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-icon { font-size: 48px; color: #dc3545; margin-bottom: 20px; }
                h1 { color: #dc3545; margin-bottom: 20px; }
                p { color: #6c757d; margin-bottom: 30px; }
                .btn { padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <div class='error-icon'>🚫</div>
                <h1>Acesso Negado</h1>
                <p>Você não tem permissão para acessar esta página. É necessário ser administrador.</p>
                <a href='../index.php' class='btn'>Voltar ao Início</a>
            </div>
        </body>
        </html>
        ");
    }
    
    // Para outros erros, definir configuração padrão
    $config = [
        'ativo' => 0,
        'valor_premio' => 0.00,
        'max_premios' => 0,
        'premios_pagos' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração de Prêmios - Raspadinha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="components/sidebar.css" rel="stylesheet">
    <link href="components/header.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #1a1a1a;
            --dark-card: #2d2d2d;
            --dark-text: #ffffff;
            --dark-text-secondary: #b0b0b0;
            --accent-blue: #00d4ff;
            --accent-green: #00d4aa;
            --accent-red: #ff4757;
            --sidebar-width: 250px;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--dark-text);
            margin: 0;
            padding: 0;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 60px;
            padding: 2rem;
            background-color: var(--dark-bg);
            min-height: calc(100vh - 60px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
                margin-top: 60px;
            }
        }

        .dashboard-header {
            background-color: var(--dark-card);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #404040;
        }

        .dashboard-title {
            color: var(--dark-text);
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .dashboard-subtitle {
            color: var(--dark-text-secondary);
            margin: 0;
            font-size: 0.85rem;
        }

        .content-section {
            background-color: var(--dark-card);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #404040;
        }

        .section-title {
            color: var(--dark-text);
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--accent-blue);
        }
        
        .card {
            background-color: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        
        .card-header {
            background-color: var(--dark-card);
            color: var(--dark-text);
            border-bottom: 1px solid #404040;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
        }
        
        .card-header h2, .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-check-input {
            background-color: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            color: var(--dark-text);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 212, 255, 0.25);
            background-color: var(--dark-card);
            color: var(--dark-text);
        }

        .form-text {
            color: var(--dark-text-secondary);
        }

        .input-group-text {
            background-color: var(--dark-card);
            border: 1px solid #404040;
            color: var(--dark-text);
            border-radius: 8px 0 0 8px;
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid #404040;
        }
        
        .btn-primary {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
            color: var(--dark-bg);
        }
        
        .btn-primary:hover {
            background-color: #00b8e6;
            border-color: #00b8e6;
            color: var(--dark-bg);
        }
        
        .btn-danger {
            background-color: var(--accent-red);
            border-color: var(--accent-red);
            color: var(--dark-text);
        }
        
        .btn-danger:hover {
            background-color: #ff3742;
            border-color: #ff3742;
            color: var(--dark-text);
        }
        
        .btn-secondary {
            background-color: var(--dark-card);
            border-color: #404040;
            color: var(--dark-text);
        }
        
        .btn-secondary:hover {
            background-color: #404040;
            border-color: #505050;
            color: var(--dark-text);
        }
        
        .alert {
            background-color: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            color: var(--dark-text);
        }
        
        .alert-success {
            border-left: 4px solid var(--accent-green);
        }
        
        .alert-danger {
            border-left: 4px solid var(--accent-red);
        }
        
        .alert-warning {
            border-left: 4px solid #ffc107;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            padding: 1rem;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0 0 0.5rem 0;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--dark-text-secondary);
            margin: 0;
            font-weight: 500;
        }
        
        .stat-card small {
            color: var(--dark-text-secondary);
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: block;
        }
        
        .text-white {
            color: var(--dark-text-secondary) !important;
            font-size: 0.8rem;
        }
        
        .form-check {
            padding: 1rem;
            background-color: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-check-input:checked {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
        }
        
        .form-check-label {
            color: var(--dark-text);
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-active {
            background-color: var(--accent-green);
        }
        
        .status-inactive {
            background-color: var(--accent-red);
        }
        


        
        /* Responsividade */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .row.g-3 {
                flex-direction: column;
            }
            
            .row.g-3 .col-md-6 {
                margin-bottom: 0.75rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0.75rem;
            }
            
            .dashboard-header {
                padding: 1rem;
            }
            
            .content-section {
                padding: 0.75rem;
            }
            
            .stats-grid {
                gap: 0.75rem;
            }
            
            .stat-card {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
     <?php include 'components/header.php'; ?>
     <?php include 'components/sidebar.php'; ?>
    
    <div class="main-content">
        
        <div class="container-fluid">


                <!-- Mensagens de feedback -->
                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-<?= $tipoMensagem === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?= $tipoMensagem === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
                        <?= htmlspecialchars($mensagem) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Estatísticas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon" style="background: var(--accent-green);">
                                <i class="bi bi-trophy"></i>
                            </div>
                        </div>
                        <h3 class="stat-value"><?= number_format($config['premios_pagos']) ?></h3>
                        <p class="stat-label">Prêmios Pagos</p>
                        <small class="text-white">Total de prêmios distribuídos</small>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon" style="background: var(--accent-blue);">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                        <h3 class="stat-value valor-preview">R$ <?= number_format($config['valor_premio'], 2, ',', '.') ?></h3>
                        <p class="stat-label">Valor do Prêmio</p>
                        <small class="text-white">Valor configurado por prêmio</small>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon" style="background: var(--accent-red);">
                                <i class="bi bi-hash"></i>
                            </div>
                        </div>
                        <h3 class="stat-value"><?= number_format($config['max_premios']) ?></h3>
                        <p class="stat-label">Máximo de Prêmios</p>
                        <small class="text-white">Limite configurado de prêmios</small>
                    </div>
                </div>

                <!-- Formulário de Configuração -->
                <div class="content-section">
                    <h3 class="section-title"><i class="bi bi-gear-fill"></i>Configurações Gerais</h3>
                    <form method="POST" id="configForm">
                            <input type="hidden" name="salvar_config" value="1">
                            
                            <!-- Status Ativo -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="ativo" id="ativo" 
                                       <?= $config['ativo'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="ativo">
                                    <span class="status-indicator <?= $config['ativo'] ? 'status-active' : 'status-inactive' ?>"></span>
                                    <strong>Sistema Ativo</strong>
                                    <small class="d-block" style="color: var(--dark-text-secondary);">Marque para ativar o sistema de prêmios</small>
                                </label>
                            </div>

                            <!-- Valor do Prêmio -->
                            <div class="mb-3">
                                <label for="valor" class="form-label">
                                    <i class="bi bi-currency-dollar me-1"></i>Valor do Prêmio
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" name="valor" id="valor" 
                                           step="0.01" min="0" max="999999.99" 
                                           value="<?= number_format($config['valor_premio'], 2, '.', '') ?>" 
                                           required aria-describedby="valor-help">
                                </div>
                                <div class="form-text" id="valor-help">Valor em reais que será pago como prêmio</div>
                            </div>

                            <!-- Máximo de Prêmios -->
                            <div class="mb-4">
                                <label for="max" class="form-label">
                                    <i class="bi bi-hash me-1"></i>Máximo de Prêmios
                                </label>
                                <input type="number" class="form-control" name="max" id="max" 
                                       min="0" max="999999" value="<?= $config['max_premios'] ?>" 
                                       required aria-describedby="max-help">
                                <div class="form-text" id="max-help">Número máximo de prêmios que podem ser pagos</div>
                            </div>

                        <!-- Botões de Ação -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-save me-2"></i>Salvar Configuração
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-danger btn-lg w-100" 
                                        onclick="confirmarZerarPremios()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Zerar Prêmios Pagos
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Formulário oculto para zerar prêmios -->
                    <form method="POST" id="zerarForm" style="display: none;">
                        <input type="hidden" name="zerar_premios" value="1">
                    </form>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Atenção:</strong> A ação "Zerar Prêmios Pagos" irá resetar o contador de prêmios distribuídos. Use com cuidado!
                    </div>
                </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Elementos do formulário
            const valorInput = document.getElementById('valor');
            const maxInput = document.getElementById('max');
            const ativoCheckbox = document.getElementById('ativo');
            const statusIndicator = document.querySelector('.status-indicator');
            const valorPreview = document.querySelector('.valor-preview');

            // Validação em tempo real
            function validarCampo(input, min, max, tipo) {
                const valor = parseFloat(input.value) || 0;
                const isValid = valor >= min && valor <= max;
                
                input.classList.toggle('is-valid', isValid && input.value !== '');
                input.classList.toggle('is-invalid', !isValid && input.value !== '');
                
                // Feedback visual
                let feedback = input.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    input.parentNode.appendChild(feedback);
                }
                
                if (!isValid && input.value !== '') {
                    feedback.textContent = `${tipo} deve estar entre ${min.toLocaleString('pt-BR')} e ${max.toLocaleString('pt-BR')}`;
                }
                
                return isValid;
            }

            // Preview das configurações
            function atualizarPreview() {
                const valor = parseFloat(valorInput.value) || 0;
                const ativo = ativoCheckbox.checked;
                
                // Atualizar preview do valor
                if (valorPreview) {
                    valorPreview.textContent = `R$ ${valor.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                }
                
                // Atualizar indicador de status
                if (statusIndicator) {
                    statusIndicator.className = `status-indicator ${ativo ? 'status-active' : 'status-inactive'}`;
                }
            }

            // Event listeners
            valorInput.addEventListener('input', function() {
                validarCampo(this, 0, 999999.99, 'Valor');
                atualizarPreview();
            });

            maxInput.addEventListener('input', function() {
                validarCampo(this, 0, 999999, 'Máximo de prêmios');
            });

            ativoCheckbox.addEventListener('change', atualizarPreview);

            // Formatação automática de moeda
            valorInput.addEventListener('blur', function() {
                const valor = parseFloat(this.value) || 0;
                this.value = valor.toFixed(2);
                atualizarPreview();
            });

            // Validação do formulário
            document.getElementById('configForm').addEventListener('submit', function(e) {
                const valor = parseFloat(valorInput.value);
                const max = parseInt(maxInput.value);
                
                if (valor < 0 || valor > 999999.99) {
                    e.preventDefault();
                    alert('❌ O valor do prêmio deve estar entre R$ 0,00 e R$ 999.999,99');
                    return false;
                }
                
                if (max < 0 || max > 999999) {
                    e.preventDefault();
                    alert('❌ O máximo de prêmios deve estar entre 0 e 999.999');
                    return false;
                }
                
                // Adicionar loading ao botão
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Salvando...';
                submitBtn.disabled = true;
                
                return true;
            });

            // Atalhos de teclado
            document.addEventListener('keydown', function(e) {
                // Ctrl + S para salvar
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    document.getElementById('configForm').dispatchEvent(new Event('submit'));
                }
                
                // Esc para fechar alertas
                if (e.key === 'Escape') {
                    const alerts = document.querySelectorAll('.alert .btn-close');
                    alerts.forEach(btn => btn.click());
                }

                // Alt + Backspace para voltar
                if (e.altKey && e.key === 'Backspace') {
                    e.preventDefault();
                    history.back();
                }
            });

            // Auto-hide alerts após 5 segundos
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // Inicializar
            atualizarPreview();

            console.log('🎯 Sistema de Prêmios carregado com sucesso!');
        });

        // Confirmação para zerar prêmios
        function confirmarZerarPremios() {
            if (confirm('⚠️ Tem certeza que deseja zerar os prêmios pagos?\n\nEsta ação não pode ser desfeita!')) {
                document.getElementById('zerarForm').submit();
            }
        }

        // Função para voltar com confirmação se houver alterações não salvas
        function voltarComConfirmacao() {
            const form = document.getElementById('configForm');
            const formData = new FormData(form);
            let hasChanges = false;

            // Verificar se há alterações não salvas
            const originalValues = {
                ativo: <?= $config['ativo'] ? 'true' : 'false' ?>,
                valor: '<?= number_format($config['valor_premio'], 2, '.', '') ?>',
                max: '<?= $config['max_premios'] ?>'
            };

            if (document.getElementById('ativo').checked !== originalValues.ativo ||
                document.getElementById('valor').value !== originalValues.valor ||
                document.getElementById('max').value !== originalValues.max) {
                hasChanges = true;
            }

            if (hasChanges) {
                if (confirm('⚠️ Você tem alterações não salvas. Deseja realmente sair sem salvar?')) {
                    history.back();
                }
            } else {
                history.back();
            }
        }
    </script>
</body>
</html>

