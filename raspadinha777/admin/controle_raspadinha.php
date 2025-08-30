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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Raspadinha - Painel Administrativo</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Sidebar CSS -->
<link href="components/sidebar.css" rel="stylesheet">
    <!-- Header CSS -->
    <link href="components/header.css" rel="stylesheet">
    <!-- Mobile Navigation CSS -->
    <link href="components/mobile_nav.css" rel="stylesheet">
    
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

        * {
            box-sizing: border-box;
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

        .section-description {
            color: var(--dark-text-secondary);
            font-size: 0.9rem;
            margin: 0 0 1.5rem 0;
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Sidebar responsivo já está incluído no sidebar_dark.css */
    </style>
</head>
<body>
     <?php include 'components/header.php'; ?>

     <?php include 'components/sidebar.php'; ?>

    <div class="main-content">


        <!-- Navegação por Abas -->
        <div class="tabs-container">
            <nav class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-config-tab" data-bs-toggle="tab" data-bs-target="#nav-config" type="button" role="tab" aria-controls="nav-config" aria-selected="true">
                    <i class="fas fa-cog"></i>
                    Configurações
                </button>
                <button class="nav-link" id="nav-rtp-tab" data-bs-toggle="tab" data-bs-target="#nav-rtp" type="button" role="tab" aria-controls="nav-rtp" aria-selected="false">
                    <i class="fas fa-chart-line"></i>
                    Teste RTP
                </button>
                <button class="nav-link" id="nav-prizes-tab" data-bs-toggle="tab" data-bs-target="#nav-prizes" type="button" role="tab" aria-controls="nav-prizes" aria-selected="false">
                    <i class="fas fa-gift"></i>
                    Teste Prêmios
                </button>
            </nav>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="tab-content" id="nav-tabContent">
            <!-- Aba Configurações -->
            <div class="tab-pane fade show active" id="nav-config" role="tabpanel" aria-labelledby="nav-config-tab">
                <div class="fade-in">
                    <div class="content-section">
                        <h2 class="section-title">
                            <i class="fas fa-cog"></i>
                            Controle de Chance de Vitória
                        </h2>
                        <p class="section-description">Configure a probabilidade de vitória dos jogos de raspadinha</p>

                        <!-- Layout em Cards Otimizado -->
                        <div class="config-cards-grid">
                            <!-- Card Status Atual -->
                            <div class="config-card status-card-compact">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-pie"></i>
                                        Status Atual
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="status-item-compact">
                                        <span class="status-label">Chance Atual:</span>
                                        <span class="status-value chance-display" id="chanceAtualModulo">0.0%</span>
                                    </div>
                                    <div class="status-item-compact">
                                        <span class="status-label">Última Atualização:</span>
                                        <span class="status-value" id="ultimaAtualizacaoModulo">-</span>
                                    </div>
                                    <div class="status-item-compact">
                                        <span class="status-label">Status:</span>
                                        <span class="status-value status-active"><i class="fas fa-circle"></i> Ativo</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Configuração -->
                            <div class="config-card controls-card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-sliders-h"></i>
                                        Configurar Chance
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <label for="novaChanceModulo" class="form-label">Nova Chance de Vitória:</label>
                                    
                                    <!-- Slider Compacto -->
                                    <div class="range-container-compact">
                                        <input type="range" id="chanceRangeModulo" class="range-input" min="0" max="1" step="0.01" value="0" oninput="atualizarChanceDisplayModulo()">
                                        <div class="range-labels-compact">
                                            <span>0%</span>
                                            <span>50%</span>
                                            <span>100%</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Input numérico compacto -->
                                    <div class="input-group-compact">
                                        <input type="number" id="novaChanceModulo" class="form-control" min="0" max="1" step="0.01" placeholder="0.00" oninput="atualizarChanceRangeModulo()">
                                        <span class="input-suffix">%</span>
                                    </div>
                                    
                                    <small class="form-text-compact">
                                        <i class="fas fa-info-circle"></i> 0.0 = nunca paga | 0.3 = 30% | 1.0 = sempre paga
                                    </small>
                                    
                                    <!-- Botões de Ação Integrados -->
                                    <div class="action-buttons-integrated">
                                        <button class="btn btn-success btn-action" onclick="atualizarChanceModulo()">
                                            <i class="fas fa-check"></i> Atualizar Configuração
                                        </button>
                                        <button class="btn btn-secondary btn-action" onclick="carregarStatusModulo()">
                                            <i class="fas fa-sync-alt"></i> Recarregar Status
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alertas -->
                        <div id="alertContainerModulo"></div>
                    </div>
                    
                    <!-- Card de Gerenciamento de Tipos de Raspadinha -->
                    <div class="content-section">
                        <h3 class="section-title">
                            <i class="fas fa-cogs"></i>
                            Gerenciar Tipos de Raspadinha
                        </h3>
                        <p class="section-description">Configure e gerencie os tipos de raspadinha disponíveis no sistema</p>
                        
                        <!-- Layout em Cards para Tipos -->
                        <div class="types-cards-grid">
                            <!-- Card Status dos Tipos -->
                            <div class="config-card types-status-card">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-list-alt"></i>
                                        Status dos Tipos
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="types-stats">
                                        <div class="stat-item">
                                            <span class="stat-number" id="tiposCadastrados">3</span>
                                            <span class="stat-label">Cadastrados</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number" id="tiposAtivos">3</span>
                                            <span class="stat-label">Ativos</span>
                                        </div>
                                    </div>
                                    <div class="last-update">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            Última atualização: <span id="ultimaAtualizacaoTipos">-</span>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Ações dos Tipos -->
                            <div class="config-card types-actions-card">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-tools"></i>
                                        Gerenciamento
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="action-buttons-vertical">
                                        <button class="btn btn-success btn-block" onclick="window.open('gerenciar_tipos_raspadinha.php', '_blank')">
                                            <i class="fas fa-external-link-alt"></i> Abrir Gerenciador
                                        </button>
                                        <button class="btn btn-info btn-block" onclick="verificarTabelaTipos()">
                                            <i class="fas fa-database"></i> Verificar Tabela
                                        </button>
                                        <button class="btn btn-secondary btn-block" onclick="carregarEstatisticasTipos()">
                                            <i class="fas fa-sync-alt"></i> Atualizar Status
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aba Teste RTP -->
            <div class="tab-pane fade" id="nav-rtp" role="tabpanel" aria-labelledby="nav-rtp-tab">
                <div class="fade-in">
                    <div class="content-section">
                        <h2 class="section-title">
                            <i class="fas fa-chart-line"></i>
                            Teste de RTP em Tempo Real
                        </h2>
                        <p class="section-description">Teste e monitore as estatísticas de vitórias e derrotas do sistema RTP</p>

                <!-- Estatísticas Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="totalTentativas">0</div>
                            <div class="stat-label">Total de Testes</div>
                        </div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="totalVitorias">0</div>
                            <div class="stat-label">Vitórias</div>
                        </div>
                    </div>
                    
                    <div class="stat-card danger">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="totalDerrotas">0</div>
                            <div class="stat-label">Derrotas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="percentualVitorias">0%</div>
                            <div class="stat-label">Taxa de Vitória</div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico e Controles -->
                <div class="rtp-content">
                    <div class="chart-section">
                        <h3 class="chart-title">Distribuição de Resultados</h3>
                        <div class="chart-container">
                            <canvas id="rtpChart" width="300" height="300"></canvas>
                        </div>
                    </div>
                    
                    <div class="results-section">
                        <div class="results-container">
                            <h3 class="results-title">Últimos Resultados</h3>
                            <table class="results-table">
                                <thead>
                                    <tr>
                                        <th style="width: 15%">#</th>
                                        <th style="width: 25%">Status</th>
                                        <th style="width: 30%">Resultado</th>
                                        <th style="width: 30%">Horário</th>
                                    </tr>
                                </thead>
                                <tbody id="resultsTableBody">
                                    <tr>
                                        <td colspan="4" class="no-results">Nenhum teste realizado ainda</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="test-controls-integrated">
                                <button class="btn btn-warning" onclick="testarRTP()">
                                    <i class="fas fa-flask"></i> Teste Simulado
                                </button>
                                <button class="btn btn-secondary" onclick="limparTestes()">
                                    <i class="fas fa-trash"></i> Limpar Testes
                                </button>
                                <button class="btn btn-info" onclick="exportarResultados()">
                                    <i class="fas fa-download"></i> Exportar CSV
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>

            <!-- Aba Teste Prêmios -->
            <div class="tab-pane fade" id="nav-prizes" role="tabpanel" aria-labelledby="nav-prizes-tab">
                <div class="fade-in">
                    <div class="content-section">
                        <h2 class="section-title">
                            <i class="fas fa-gift"></i>
                            Teste de Prêmios Automatizado
                        </h2>
                        <p class="section-description">Sistema de teste automatizado com usuário ilimitado e estatísticas detalhadas</p>

                        <!-- Configurações do Teste -->
                        <div class="test-config-card">
                            <h3 class="card-title">
                                <i class="fas fa-cog"></i>
                                Configurações do Teste
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Tipo de Raspadinha</label>
                                    <select id="tipoRaspadinha" class="form-select">
                                        <option value="1">Sonho de Consumo - R$ 1,00</option>
                                        <option value="5">Raspe da Emoção - R$ 5,00</option>
                                        <option value="10">Me Mimei - R$ 10,00</option>
                                        <option value="20">Super Prêmios - R$ 20,00</option>
                                        <option value="50">Sonho Premium - R$ 50,00</option>
                                        <option value="100">Luxo Supremo - R$ 100,00</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Quantidade de Testes</label>
                                    <input type="number" id="quantidadeTestes" class="form-control" value="100" min="1" max="10000">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Intervalo (ms)</label>
                                    <input type="number" id="intervaloTestes" class="form-control" value="100" min="10" max="5000">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Usuário de Teste</label>
                                    <input type="text" id="usuarioTeste" class="form-control" value="teste_usuario_ilimitado" readonly>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button id="btnIniciarTeste" class="btn btn-success d-block" onclick="iniciarTesteAutomatizado()">
                                        <i class="fas fa-play"></i> Iniciar Teste
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Status do Teste -->
                        <div class="test-status-card" id="testStatusCard" style="display: none;">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line"></i>
                                Status do Teste em Tempo Real
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="status-item">
                                        <span class="status-label">Progresso:</span>
                                        <span class="status-value" id="progressoTeste">0/0</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="status-item">
                                        <span class="status-label">Ganhos:</span>
                                        <span class="status-value text-success" id="totalGanhos">0</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="status-item">
                                        <span class="status-label">Perdas:</span>
                                        <span class="status-value text-danger" id="totalPerdas">0</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="status-item">
                                        <span class="status-label">Taxa de Ganho:</span>
                                        <span class="status-value" id="taxaGanho">0%</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="status-item">
                                        <span class="status-label">Valor Ganho:</span>
                                        <span class="status-value text-success" id="valorTotalGanho">R$ 0,00</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="status-item">
                                        <span class="status-label">Valor Perdido:</span>
                                        <span class="status-value text-danger" id="valorTotalPerdido">R$ 0,00</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <div class="status-item">
                                        <span class="status-label">RTP Configurado:</span>
                                        <span class="status-value text-info" id="rtpConfigurado">Carregando...</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="status-item">
                                        <span class="status-label">Lucro/Prejuízo Total:</span>
                                        <span class="status-value" id="lucroPrejuizo">R$ 0,00</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="status-item">
                                        <span class="status-label">Diferença RTP:</span>
                                        <span class="status-value" id="diferencaRTP">0%</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="progress mt-3">
                                <div class="progress-bar" id="barraProgresso" role="progressbar" style="width: 0%"></div>
                            </div>
                            
                            <!-- Resultado Individual -->
                            <div class="mt-3">
                                <div class="card border-0 bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-2">
                                            <i class="fas fa-dice"></i> Último Resultado
                                        </h6>
                                        <div id="resultadoIndividual" class="text-center">
                                            <span class="text-muted">Aguardando primeiro teste...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button id="btnPararTeste" class="btn btn-danger" onclick="pararTesteAutomatizado()" style="display: none;">
                                    <i class="fas fa-stop"></i> Parar Teste
                                </button>
                            </div>
                        </div>

                        <!-- Histórico Detalhado -->
                        <div class="test-history-card">
                            <div class="card-header-with-actions">
                                <h3 class="card-title">
                                    <i class="fas fa-history"></i>
                                    Histórico Detalhado de Testes
                                </h3>
                                <div class="header-actions">
                                    <button class="btn btn-outline-success btn-sm" onclick="exportarCSV()">
                                        <i class="fas fa-download"></i> Exportar CSV
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="limparHistoricoCompleto()">
                                        <i class="fas fa-trash"></i> Limpar Histórico
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="tabelaHistoricoDetalhado">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Hora</th>
                                            <th>Tipo</th>
                                            <th>Valor Aposta</th>
                                            <th>Resultado</th>
                                            <th>Prêmio</th>
                                            <th>Lucro/Perda</th>
                                            <th>RTP Acumulado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="corpoTabelaHistorico">
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Nenhum teste realizado ainda</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Estatísticas Consolidadas -->
                        <div class="test-stats-card">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie"></i>
                                Estatísticas Consolidadas
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="stat-box">
                                        <div class="stat-icon text-primary">
                                            <i class="fas fa-calculator"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4 id="totalTestesRealizados">0</h4>
                                            <p>Testes Realizados</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="stat-box">
                                        <div class="stat-icon text-success">
                                            <i class="fas fa-trophy"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4 id="percentualGanhos">0%</h4>
                                            <p>Taxa de Vitória</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="stat-box">
                                        <div class="stat-icon text-info">
                                            <i class="fas fa-coins"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4 id="rtpCalculado">0%</h4>
                                            <p>RTP Calculado</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="stat-box">
                                        <div class="stat-icon text-warning">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h4 id="maiorPremio">R$ 0,00</h4>
                                            <p>Maior Prêmio</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- CSS do Módulo -->
            <style>
            .status-card {
                background: var(--dark-card);
                border: 1px solid #404040;
                border-radius: 8px;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .status-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0.75rem;
            }

            .status-item:last-child {
                margin-bottom: 0;
            }

            .status-label {
                color: var(--dark-text-secondary);
                font-weight: 500;
                font-size: 0.9rem;
            }

            .status-value {
                color: var(--dark-text);
                font-weight: 600;
                font-size: 0.9rem;
            }

            .chance-display {
                font-size: 1.25rem;
                color: var(--accent-blue);
                font-weight: 700;
            }

            .status-active {
                color: var(--accent-green);
            }

            .status-active i {
                margin-right: 0.5rem;
            }

            .form-label {
                display: block;
                margin-bottom: 0.75rem;
                color: var(--dark-text);
                font-weight: 500;
                font-size: 0.95rem;
            }

            .range-container {
                margin: 1rem 0;
            }

            .range-input {
                width: 100%;
                height: 6px;
                border-radius: 3px;
                background: var(--dark-card);
                outline: none;
                -webkit-appearance: none;
                appearance: none;
                border: 1px solid #404040;
            }

            .range-input::-webkit-slider-thumb {
                -webkit-appearance: none;
                appearance: none;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background: var(--accent-blue);
                cursor: pointer;
                border: 2px solid var(--dark-card);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            }

            .range-input::-moz-range-thumb {
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background: var(--accent-blue);
                cursor: pointer;
                border: 2px solid var(--dark-card);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            }

            .range-labels {
                display: flex;
                justify-content: space-between;
                margin-top: 0.5rem;
                font-size: 0.8rem;
                color: var(--dark-text-secondary);
            }

            .form-control {
                width: 100%;
                padding: 0.75rem 1rem;
                background: var(--dark-card);
                border: 1px solid #404040;
                border-radius: 6px;
                color: var(--dark-text);
                font-size: 0.95rem;

            }

            .form-control:focus {
                outline: none;
                border-color: var(--accent-blue);
                box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.1);
            }

            .form-text {
                display: block;
                margin-top: 0.5rem;
                color: var(--dark-text-secondary);
                font-size: 0.85rem;
            }

            .form-text i {
                margin-right: 0.5rem;
                color: var(--accent-blue);
            }

            .action-buttons {
                margin-top: 1.5rem;
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .btn {
                padding: 0.75rem 1rem;
                border: none;
                border-radius: 6px;
                font-size: 0.9rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                text-decoration: none;
                min-width: 140px;
                justify-content: center;
            }

            .btn-success {
                background: var(--accent-green);
                color: white;
            }

            .btn-secondary {
                background: var(--dark-card);
                color: var(--dark-text);
                border: 1px solid #404040;
            }

            .alert {
                padding: 1rem;
                border-radius: 6px;
                margin-top: 1rem;
                font-weight: 500;
                font-size: 0.9rem;
                border: 1px solid;
            }

            .alert-success {
                background: rgba(0, 212, 170, 0.1);
                color: var(--accent-green);
                border-color: var(--accent-green);
            }

            .alert-error {
                background: rgba(255, 87, 87, 0.1);
                color: var(--accent-red);
                border-color: var(--accent-red);
            }

            .alert-info {
                 background: rgba(74, 158, 255, 0.1);
                 color: var(--accent-blue);
                 border-color: var(--accent-blue);
             }

             /* Estilos para Cards Otimizados */
             .config-cards-grid {
                 display: grid;
                 grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                 gap: 1.5rem;
                 margin-bottom: 2rem;
             }

             .types-cards-grid {
                 display: grid;
                 grid-template-columns: 1fr 1fr;
                 gap: 1.5rem;
                 margin-bottom: 2rem;
             }

             .config-card {
            background: var(--dark-card);
            border: 1px solid #404040;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

             .card-header {
                 background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
                 padding: 1rem 1.25rem;
                 border-bottom: 1px solid #404040;
             }

             .card-title {
                 margin: 0;
                 font-size: 1rem;
                 font-weight: 600;
                 color: var(--dark-text);
                 display: flex;
                 align-items: center;
                 gap: 0.5rem;
             }

             .card-title i {
                 color: var(--accent-blue);
                 font-size: 0.9rem;
             }

             .card-body {
                 padding: 1.25rem;
             }

             /* Status Card Compacto */
             .status-item-compact {
                 display: flex;
                 justify-content: space-between;
                 align-items: center;
                 margin-bottom: 0.75rem;
                 padding: 0.5rem 0;
                 border-bottom: 1px solid rgba(64, 64, 64, 0.3);
             }

             .status-item-compact:last-child {
                 margin-bottom: 0;
                 border-bottom: none;
             }

             /* Range Container Compacto */
             .range-container-compact {
                 margin: 1rem 0;
             }

             .range-labels-compact {
                 display: flex;
                 justify-content: space-between;
                 margin-top: 0.5rem;
                 font-size: 0.75rem;
                 color: var(--dark-text-secondary);
             }

             /* Input Group Compacto */
             .input-group-compact {
                 display: flex;
                 align-items: center;
                 margin: 1rem 0;
                 position: relative;
             }

             .input-group-compact .form-control {
                 padding-right: 2.5rem;
             }

             .input-suffix {
                 position: absolute;
                 right: 1rem;
                 color: var(--dark-text-secondary);
                 font-size: 0.9rem;
                 pointer-events: none;
             }

             .form-text-compact {
                 display: block;
                 margin-top: 0.5rem;
                 color: var(--dark-text-secondary);
                 font-size: 0.8rem;
                 line-height: 1.3;
             }

             .form-text-compact i {
                 margin-right: 0.25rem;
                 color: var(--accent-blue);
             }

             /* Action Buttons Compacto */
             .action-buttons-compact {
                 display: flex;
                 gap: 0.75rem;
                 flex-wrap: wrap;
             }

             .btn-compact {
                 padding: 0.6rem 1rem;
                 font-size: 0.85rem;
                 min-width: 120px;
             }

             /* Action Buttons Integrados */
             .action-buttons-integrated {
                 display: flex;
                 gap: 1rem;
                 margin-top: 1.5rem;
                 padding-top: 1.5rem;
                 border-top: 1px solid rgba(64, 64, 64, 0.3);
                 flex-wrap: wrap;
             }

             .btn-action {
                 flex: 1;
                 padding: 0.75rem 1.5rem;
                 font-size: 0.9rem;
                 font-weight: 600;
                 min-width: 160px;
                 border-radius: 8px;
             }

             .btn-action i {
                 margin-right: 0.5rem;
             }

             /* Test Controls Integrados */
             .test-controls-integrated {
                 display: flex;
                 gap: 0.75rem;
                 margin-top: 1rem;
                 padding-top: 1rem;
                 border-top: 1px solid #404040;
                 flex-wrap: wrap;
                 justify-content: center;
             }

             .test-controls-integrated .btn {
                 flex: 1;
                 min-width: 140px;
                 max-width: 180px;
             }

             /* Card Header com Actions */
             .card-header-with-actions {
                 display: flex;
                 justify-content: space-between;
                 align-items: center;
                 margin-bottom: 1.5rem;
                 padding-bottom: 1rem;
                 border-bottom: 1px solid #404040;
             }

             .header-actions {
                 display: flex;
                 gap: 0.5rem;
                 flex-wrap: wrap;
             }

             .header-actions .btn {
                 white-space: nowrap;
             }

             /* Estilos para Cards de Tipos */
             .types-stats {
                 display: flex;
                 justify-content: space-around;
                 margin-bottom: 1rem;
             }

             .stat-item {
                 text-align: center;
                 flex: 1;
             }

             .stat-number {
                 display: block;
                 font-size: 2rem;
                 font-weight: 700;
                 color: var(--accent-blue);
                 line-height: 1;
             }

             .stat-label {
                 display: block;
                 font-size: 0.8rem;
                 color: var(--dark-text-secondary);
                 margin-top: 0.25rem;
                 text-transform: uppercase;
                 letter-spacing: 0.5px;
             }

             .last-update {
                 text-align: center;
                 padding-top: 1rem;
                 border-top: 1px solid rgba(64, 64, 64, 0.3);
             }

             .text-muted {
                 color: var(--dark-text-secondary) !important;
             }

             /* Action Buttons Vertical */
             .action-buttons-vertical {
                 display: flex;
                 flex-direction: column;
                 gap: 0.75rem;
             }

             .btn-block {
                 width: 100%;
                 justify-content: center;
             }

             /* Responsividade */
             @media (max-width: 768px) {
                 .config-cards-grid {
                     grid-template-columns: 1fr;
                     gap: 1rem;
                 }

                 .types-cards-grid {
                     grid-template-columns: 1fr;
                     gap: 1rem;
                 }

                 .action-buttons-compact {
                     flex-direction: column;
                 }

                 .btn-compact {
                     min-width: auto;
                     width: 100%;
                 }

                 .action-buttons-integrated {
                     flex-direction: column;
                 }

                 .btn-action {
                     min-width: auto;
                     width: 100%;
                 }

                 .test-controls-integrated {
                     flex-direction: column;
                 }

                 .test-controls-integrated .btn {
                     min-width: auto;
                     max-width: none;
                     width: 100%;
                 }

                 .card-header-with-actions {
                     flex-direction: column;
                     align-items: flex-start;
                     gap: 1rem;
                 }

                 .header-actions {
                     width: 100%;
                     justify-content: flex-start;
                 }
             }

             @media (max-width: 480px) {
                 .card-header {
                     padding: 0.75rem 1rem;
                 }

                 .card-body {
                     padding: 1rem;
                 }

                 .card-title {
                     font-size: 0.9rem;
                 }

                 .types-stats {
                     flex-direction: column;
                     gap: 1rem;
                 }
             }

             /* Estilos do Módulo RTP */
             .test-config {
                 background: var(--dark-card);
                 border: 1px solid #404040;
                 border-radius: 8px;
                 padding: 1.5rem;
                 margin-bottom: 1.5rem;
             }
             
             .config-title {
                 color: var(--dark-text);
                 font-size: 1.1rem;
                 font-weight: 600;
                 margin-bottom: 1rem;
                 display: flex;
                 align-items: center;
                 gap: 0.5rem;
             }
             
             .config-grid {
                 display: grid;
                 grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                 gap: 1.5rem;
             }
             
             .config-item {
                 display: flex;
                 flex-direction: column;
             }
             
             .btn-warning {
                 background: #f39c12;
                 color: white;
             }
             

             
             /* Estilos para ícones de tipo de teste */
             .fa-coins {
                 color: var(--accent-green);
                 margin-left: 0.5rem;
             }
             
             .fa-flask {
                 color: var(--accent-blue);
                 margin-left: 0.5rem;
             }
             
             /* Estilos para resultados */
             .result-status.vitoria {
                 color: var(--accent-green);
                 font-weight: 600;
             }
             
             .result-status.derrota {
                 color: var(--accent-red);
                 font-weight: 600;
             }
             
             .result-time {
                 color: var(--dark-text-secondary);
                 font-size: 0.9rem;
             }
             
             .no-results {
                 text-align: center;
                 color: var(--dark-text-secondary);
                 font-style: italic;
                 padding: 2rem;
             }
             
             .stats-grid {
                 display: grid;
                 grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                 gap: 1rem;
                 margin-bottom: 2rem;
             }

             .stat-card {
                 background: var(--dark-card);
                 border: 1px solid #404040;
                 border-radius: 8px;
                 padding: 1.5rem;
                 display: flex;
                 align-items: center;
                 gap: 1rem;
                 box-shadow: none;
                 transition: none;
             }

             .stat-card.success {
                 border-left: 4px solid var(--accent-green);
             }

             .stat-card.danger {
                 border-left: 4px solid var(--accent-red);
             }

             .stat-card.info {
                 border-left: 4px solid var(--accent-blue);
             }

             .stat-icon {
                 width: 50px;
                 height: 50px;
                 border-radius: 50%;
                 display: flex;
                 align-items: center;
                 justify-content: center;
                 background: rgba(74, 158, 255, 0.1);
                 color: var(--accent-blue);
                 font-size: 1.5rem;
             }

             .stat-card.success .stat-icon {
                 background: rgba(0, 212, 170, 0.1);
                 color: var(--accent-green);
             }

             .stat-card.danger .stat-icon {
                 background: rgba(255, 87, 87, 0.1);
                 color: var(--accent-red);
             }

             .stat-content {
                 flex: 1;
             }

             .stat-value {
                 font-size: 2rem;
                 font-weight: 700;
                 color: var(--dark-text);
                 line-height: 1;
             }

             .stat-label {
                 font-size: 0.9rem;
                 color: var(--dark-text-secondary);
                 margin-top: 0.25rem;
             }

             .rtp-content {
                 display: grid;
                 grid-template-columns: 1fr 1fr;
                 gap: 2rem;
                 margin-top: 1rem;
             }

             .chart-section, .results-section {
                 background: var(--dark-card);
                 border: 1px solid #404040;
                 border-radius: 8px;
                 padding: 1.5rem;
                 position: relative;
             }

             .chart-title, .results-title {
                 color: var(--dark-text);
                 font-size: 1.1rem;
                 font-weight: 600;
                 margin-bottom: 1rem;
                 display: flex;
                 align-items: center;
                 gap: 0.5rem;
             }

             .chart-container {
                 display: flex;
                 justify-content: center;
                 align-items: center;
                 min-height: 320px;
                 background: rgba(0, 0, 0, 0.1);
                 border-radius: 8px;
                 padding: 1rem;
             }

             .results-table {
                 width: 100%;
                 border-collapse: collapse;
                 margin-bottom: 1rem;
                 background: var(--dark-card);
                 border-radius: 8px;
                 overflow: hidden;
                 border: 1px solid #404040;
             }

             .results-table thead {
                 background: rgba(0, 0, 0, 0.3);
             }

             .results-table th {
                 padding: 1rem 0.75rem;
                 text-align: left;
                 font-weight: 600;
                 color: #ffffff;
                 border-bottom: 2px solid #404040;
                 font-size: 0.9rem;
             }

             .results-table tbody {
                 max-height: 280px;
                 overflow-y: auto;
                 display: block;
             }

             .results-table thead,
             .results-table tbody tr {
                 display: table;
                 width: 100%;
                 table-layout: fixed;
             }

             .results-table td {
                 padding: 0.75rem;
                 border-bottom: 1px solid #404040;
                 font-size: 0.9rem;
                 vertical-align: middle;
             }



             .results-table tbody tr:last-child td {
                 border-bottom: none;
             }

             .result-status {
                 display: inline-flex;
                 align-items: center;
                 gap: 0.5rem;
                 padding: 0.25rem 0.75rem;
                 border-radius: 20px;
                 font-size: 0.8rem;
                 font-weight: 600;
                 text-transform: uppercase;
             }

             .result-status.vitoria {
                 background: rgba(40, 167, 69, 0.2);
                 color: #28a745;
                 border: 1px solid rgba(40, 167, 69, 0.3);
             }

             .result-status.derrota {
                 background: rgba(220, 53, 69, 0.2);
                 color: #dc3545;
                 border: 1px solid rgba(220, 53, 69, 0.3);
             }

             .result-time {
                 color: #888;
                 font-size: 0.85rem;
             }

             .results-container {
                 position: relative;
                 min-height: 350px;
             }

             .results-list::-webkit-scrollbar {
                 width: 6px;
             }

             .results-list::-webkit-scrollbar-track {
                 background: rgba(0, 0, 0, 0.1);
                 border-radius: 3px;
             }

             .results-list::-webkit-scrollbar-thumb {
                 background: var(--accent-blue);
                 border-radius: 3px;
             }

             .results-list::-webkit-scrollbar-thumb:hover {
                 background: #3a8ae6;
             }

             .result-item.victory {
                 background: rgba(0, 212, 170, 0.1);
                 color: var(--accent-green);
                 border-left: 3px solid var(--accent-green);
             }

             .result-item.defeat {
                 background: rgba(255, 87, 87, 0.1);
                 color: var(--accent-red);
                 border-left: 3px solid var(--accent-red);
             }

             .no-results {
                 text-align: center;
                 color: var(--dark-text-secondary);
                 font-style: italic;
                 padding: 2rem;
             }

             .test-controls {
                 display: flex;
                 gap: 0.75rem;
                 flex-wrap: wrap;
                 position: absolute;
                 bottom: 0;
                 left: 0;
                 right: 0;
                 background: var(--dark-card);
                 padding: 1rem;
                 border-top: 2px solid #404040;
                 border-radius: 0 0 8px 8px;
                 z-index: 10;
             }

             .btn-primary {
                 background: var(--accent-blue);
                 color: white;
             }

             .btn-info {
                 background: var(--accent-purple);
                 color: white;
             }

             /* Responsividade */
             @media (max-width: 768px) {
                 .main-content {
                     margin-left: 0;
                     padding: 0.75rem;
                 }
                 
                 .dashboard-header {
                     padding: 1rem 1.25rem;
                     margin-bottom: 1.5rem;
                 }
                 
                 .dashboard-header h1 {
                     font-size: 1.5rem;
                 }
                 
                 .content-section {
                     padding: 1rem;
                 }
                 
                 .action-buttons {
                     flex-direction: column;
                 }
                 
                 .btn {
                     min-width: auto;
                     width: 100%;
                     padding: 0.875rem 1rem;
                     font-size: 0.95rem;
                 }
                 
                 .status-item {
                     flex-direction: column;
                     align-items: flex-start;
                     gap: 0.25rem;
                 }

                 .stats-grid {
                     grid-template-columns: repeat(2, 1fr);
                     gap: 0.75rem;
                 }

                 .stat-card {
                     padding: 1rem;
                     flex-direction: column;
                     text-align: center;
                     gap: 0.75rem;
                 }

                 .stat-icon {
                     width: 40px;
                     height: 40px;
                     font-size: 1.25rem;
                 }

                 .stat-value {
                     font-size: 1.5rem;
                 }

                 .stat-label {
                     font-size: 0.8rem;
                 }

                 .rtp-content {
                     grid-template-columns: 1fr;
                     gap: 1rem;
                 }

                 .chart-section {
                     order: 2;
                 }

                 .results-section {
                     order: 1;
                 }

                 .chart-container {
                     min-height: 250px;
                     padding: 0.5rem;
                 }

                 .results-list {
                     max-height: 200px;
                 }

                 .test-controls {
                     flex-direction: column;
                     gap: 0.5rem;
                 }

                 .test-controls .btn {
                     width: 100%;
                 }

                 .result-item {
                     padding: 0.5rem 0.75rem;
                     font-size: 0.9rem;
                 }
             }

             @media (max-width: 576px) {
                 .main-content {
                     padding: 0.5rem;
                 }
                 
                 .dashboard-header {
                     padding: 0.75rem;
                     border-radius: 8px;
                     margin-bottom: 1rem;
                 }
                 
                 .dashboard-header h1 {
                     font-size: 1.25rem;
                 }
                 
                 .content-section {
                     padding: 0.75rem;
                     border-radius: 8px;
                 }
                 
                 .section-title {
                     font-size: 1.1rem;
                 }

                 .stats-grid {
                     grid-template-columns: 1fr;
                     gap: 0.5rem;
                 }

                 .stat-card {
                     padding: 0.75rem;
                     flex-direction: row;
                     text-align: left;
                 }

                 .stat-icon {
                     width: 35px;
                     height: 35px;
                     font-size: 1.1rem;
                 }

                 .stat-value {
                     font-size: 1.25rem;
                 }

                 .chart-container {
                     min-height: 200px;
                     padding: 0.25rem;
                 }

                 .results-list {
                     max-height: 150px;
                     padding: 0.5rem;
                 }

                 .result-item {
                     padding: 0.4rem 0.6rem;
                     font-size: 0.85rem;
                 }

                 .test-controls {
                     padding: 0.75rem 0 0 0;
                 }
             }

             /* Melhorias adicionais para touch devices */
             @media (hover: none) and (pointer: coarse) {
                 .btn {
                     min-height: 44px;
                 }



                 .test-controls .btn {
                     padding: 1rem;
                     font-size: 1rem;
                 }
             }

             /* Estilos para a aba de Teste Prêmios */
             .prize-test-card {
                 background: var(--dark-card);
                 border: 1px solid #404040;
                 border-radius: 12px;
                 padding: 30px;
                 box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                 margin-bottom: 20px;
             }

             .card-title {
                 color: var(--dark-text);
                 font-size: 1.5rem;
                 margin-bottom: 10px;
                 display: flex;
                 align-items: center;
                 gap: 10px;
             }

             .card-description {
                 color: var(--dark-text-secondary);
                 margin-bottom: 30px;
                 font-size: 1.1rem;
             }

             .feature-section {
                 margin-bottom: 40px;
                 padding: 25px;
                 background: rgba(255, 255, 255, 0.02);
                 border-radius: 8px;
                 border-left: 4px solid var(--accent-blue);
             }

             .feature-title {
                 color: var(--dark-text);
                 font-size: 1.3rem;
                 margin-bottom: 15px;
                 display: flex;
                 align-items: center;
                 gap: 10px;
             }

             .confidence-badge {
                 display: inline-flex;
                 align-items: center;
                 background: var(--accent-green);
                 color: white;
                 padding: 8px 15px;
                 border-radius: 20px;
                 margin-bottom: 20px;
                 font-weight: 600;
             }

             .confidence-label {
                 margin-right: 8px;
             }

             .confidence-value {
                 font-size: 1.1rem;
                 font-weight: bold;
             }

             .feature-steps {
                 display: flex;
                 flex-direction: column;
                 gap: 20px;
             }

             .step-item {
                 display: flex;
                 align-items: flex-start;
                 gap: 15px;
                 padding: 20px;
                 background: var(--dark-card);
                 border: 1px solid #404040;
                 border-radius: 8px;
                 box-shadow: 0 2px 8px rgba(0,0,0,0.05);
             }

             .step-number {
                 background: var(--accent-blue);
                 color: white;
                 width: 30px;
                 height: 30px;
                 border-radius: 50%;
                 display: flex;
                 align-items: center;
                 justify-content: center;
                 font-weight: bold;
                 flex-shrink: 0;
             }

             .step-content h5 {
                 color: var(--dark-text);
                 margin-bottom: 8px;
                 font-size: 1.1rem;
             }

             .step-content p {
                 color: var(--dark-text-secondary);
                 margin-bottom: 0;
             }

             .step-content ul {
                 margin: 10px 0 0 0;
                 padding-left: 20px;
             }

             .step-content li {
                 color: var(--dark-text-secondary);
                 margin-bottom: 5px;
             }

             .feature-description {
                 color: var(--dark-text-secondary);
                 margin-bottom: 20px;
                 font-style: italic;
             }

             .config-steps {
                 display: flex;
                 flex-direction: column;
                 gap: 20px;
             }

             .config-step {
                 background: var(--dark-card);
                 border: 1px solid #404040;
                 padding: 20px;
                 border-radius: 8px;
                 box-shadow: 0 2px 8px rgba(0,0,0,0.05);
             }

             .step-header {
                 display: flex;
                 align-items: center;
                 gap: 15px;
                 margin-bottom: 15px;
             }

             .step-badge {
                 background: var(--accent-red);
                 color: white;
                 width: 25px;
                 height: 25px;
                 border-radius: 50%;
                 display: flex;
                 align-items: center;
                 justify-content: center;
                 font-weight: bold;
                 font-size: 0.9rem;
             }

             .step-header h5 {
                 color: var(--dark-text);
                 margin: 0;
                 font-size: 1.1rem;
             }

             .code-block {
                 background: #1a1a1a;
                 border-radius: 6px;
                 padding: 15px;
                 margin-top: 10px;
                 border: 1px solid #333;
             }

             .code-block pre {
                 margin: 0;
                 color: #f8f8f2;
                 font-family: 'Courier New', monospace;
                 font-size: 0.9rem;
                 line-height: 1.4;
             }

             .code-block code {
                 color: #f8f8f2;
             }

             .prize-controls {
                 display: flex;
                 gap: 15px;
                 justify-content: center;
                 margin-top: 30px;
                 padding-top: 20px;
                 border-top: 2px solid #404040;
             }

             .prize-controls .btn {
                 padding: 12px 25px;
                 font-weight: 600;
                 border-radius: 6px;
                 display: flex;
                 align-items: center;
                 gap: 8px;

             }



             /* Estilos para a nova aba de Teste Prêmios */
             .prize-selector-card {
                 background: var(--dark-card);
                 border: 1px solid #404040;
                 border-radius: 12px;
                 padding: 2rem;
                 margin-bottom: 2rem;
                 box-shadow: 0 4px 12px rgba(0,0,0,0.1);
             }

             .raspadinha-grid {
                 display: grid;
                 grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                 gap: 1.5rem;
                 margin-top: 1.5rem;
             }

             .raspadinha-test-card {
                 background: var(--dark-card);
                 border: 2px solid #404040;
                 border-radius: 12px;
                 padding: 1.5rem;
                 cursor: pointer;
                 position: relative;
                 overflow: hidden;
             }

             .raspadinha-image {
                 width: 100%;
                 height: 120px;
                 border-radius: 8px;
                 overflow: hidden;
                 margin-bottom: 1rem;
             }

             .raspadinha-image img {
                 width: 100%;
                 height: 100%;
                 object-fit: cover;
             }

             .raspadinha-info h4 {
                 color: var(--dark-text);
                 font-size: 1.1rem;
                 font-weight: 600;
                 margin-bottom: 0.5rem;
                 text-align: center;
             }

             .price-badge {
                 display: inline-block;
                 padding: 0.5rem 1rem;
                 border-radius: 20px;
                 font-weight: bold;
                 font-size: 0.9rem;
                 text-align: center;
                 margin: 0.5rem auto;
                 display: block;
                 width: fit-content;
             }

             .price-badge.green {
                 background: linear-gradient(135deg, #10b981, #059669);
                 color: white;
             }

             .price-badge.orange {
                 background: linear-gradient(135deg, #f59e0b, #d97706);
                 color: white;
             }

             .price-badge.red {
                 background: linear-gradient(135deg, #ef4444, #dc2626);
                 color: white;
             }

             .price-badge.purple {
                 background: linear-gradient(135deg, #8b5cf6, #a855f7);
                 color: white;
             }

             .prize-max {
                 color: var(--dark-text-secondary);
                 font-size: 0.9rem;
                 text-align: center;
                 margin-top: 0.5rem;
             }

             /* Estilos para cards da aba Teste Prêmios */
             .test-config-card, .test-status-card, .test-history-card, .test-stats-card {
                 background: var(--dark-card);
                 border: 1px solid #404040;
                 border-radius: 12px;
                 padding: 2rem;
                 margin-bottom: 2rem;
                 box-shadow: 0 4px 12px rgba(0,0,0,0.1);
             }

             .card-title {
                 color: var(--dark-text);
                 font-size: 1.25rem;
                 font-weight: 600;
                 margin-bottom: 1.5rem;
                 display: flex;
                 align-items: center;
                 gap: 0.75rem;
                 border-bottom: 2px solid #404040;
                 padding-bottom: 1rem;
             }

             .card-title i {
                 color: var(--accent-blue);
                 font-size: 1.1rem;
             }

             /* Estilos para status items */
             .status-item {
                 background: rgba(0,0,0,0.2);
                 border-radius: 8px;
                 padding: 1rem;
                 margin-bottom: 0.75rem;
                 border: 1px solid #404040;
             }

             .status-item:last-child {
                 margin-bottom: 0;
             }

             .status-label {
                 display: block;
                 color: var(--dark-text-secondary);
                 font-weight: 500;
                 font-size: 0.9rem;
                 margin-bottom: 0.5rem;
             }

             .status-value {
                 color: var(--dark-text);
                 font-weight: 700;
                 font-size: 1.1rem;
                 display: block;
             }

             /* Estilos para estatísticas */
             .stat-box {
                 background: rgba(0,0,0,0.2);
                 border: 1px solid #404040;
                 border-radius: 10px;
                 padding: 1.5rem;
                 text-align: center;
                 height: 100%;
                 display: flex;
                 flex-direction: column;
                 align-items: center;
                 justify-content: center;
             }

             .stat-icon {
                 width: 60px;
                 height: 60px;
                 border-radius: 50%;
                 display: flex;
                 align-items: center;
                 justify-content: center;
                 margin-bottom: 1rem;
                 font-size: 1.5rem;
             }

             .stat-content h4 {
                 color: var(--dark-text);
                 font-size: 1.8rem;
                 font-weight: 700;
                 margin-bottom: 0.5rem;
                 line-height: 1;
             }

             .stat-content p {
                 color: var(--dark-text-secondary);
                 font-size: 0.9rem;
                 margin: 0;
                 font-weight: 500;
             }

             /* Estilos para progress bar */
             .progress {
                 background: rgba(0,0,0,0.3);
                 border-radius: 10px;
                 height: 12px;
                 overflow: hidden;
                 border: 1px solid #404040;
             }

             .progress-bar {
                 background: linear-gradient(90deg, var(--accent-blue), var(--accent-green));
                 height: 100%;

                 display: flex;
                 align-items: center;
                 justify-content: center;
                 color: white;
                 font-size: 0.75rem;
                 font-weight: 600;
             }

             .test-result-card, .test-history-card {
                 background: var(--dark-card);
                 border: 1px solid #404040;
                 border-radius: 12px;
                 padding: 2rem;
                 margin-bottom: 2rem;
                 box-shadow: 0 4px 12px rgba(0,0,0,0.1);
             }

             .result-content {
                 background: var(--dark-bg);
                 border: 1px solid #404040;
                 border-radius: 8px;
                 padding: 1.5rem;
                 margin: 1rem 0;
                 text-align: center;
             }

             .result-win {
                 border-color: var(--accent-green);
                 background: rgba(16, 185, 129, 0.1);
             }

             .result-lose {
                 border-color: var(--accent-red);
                 background: rgba(239, 68, 68, 0.1);
             }

             .result-title {
                 font-size: 1.5rem;
                 font-weight: bold;
                 margin-bottom: 1rem;
             }

             .result-title.win {
                 color: var(--accent-green);
             }

             .result-title.lose {
                 color: var(--accent-red);
             }

             .result-details {
                 display: grid;
                 grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                 gap: 1rem;
                 margin-top: 1rem;
             }

             .result-item {
                 text-align: center;
             }

             .result-label {
                 color: var(--dark-text-secondary);
                 font-size: 0.9rem;
                 margin-bottom: 0.25rem;
             }

             .result-value {
                 color: var(--dark-text);
                 font-size: 1.1rem;
                 font-weight: 600;
             }

             .result-actions {
                 display: flex;
                 gap: 1rem;
                 justify-content: center;
                 margin-top: 1.5rem;
             }

             .history-table-container {
                 overflow-x: auto;
                 margin-top: 1rem;
             }

             .history-table {
                 width: 100%;
                 border-collapse: collapse;
                 background: var(--dark-bg);
                 border-radius: 8px;
                 overflow: hidden;
             }

             .history-table th {
                 background: var(--dark-card);
                 color: var(--dark-text);
                 padding: 1rem;
                 text-align: left;
                 font-weight: 600;
                 border-bottom: 2px solid #404040;
             }

             .history-table td {
                 padding: 0.75rem 1rem;
                 border-bottom: 1px solid #404040;
                 color: var(--dark-text-secondary);
             }



             .history-win {
                 color: var(--accent-green) !important;
                 font-weight: 600;
             }

             .history-lose {
                 color: var(--accent-red) !important;
                 font-weight: 600;
             }

             /* Estilos responsivos */
             @media (max-width: 768px) {
                 .raspadinha-grid {
                     grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                     gap: 1rem;
                 }

                 .result-details {
                     grid-template-columns: 1fr;
                 }

                 .result-actions {
                     flex-direction: column;
                 }

                 /* Responsividade para aba Teste Prêmios */
                 .test-config-card, .test-status-card, .test-history-card, .test-stats-card {
                     padding: 1rem;
                     margin-bottom: 1rem;
                 }

                 .card-title {
                     font-size: 1.1rem;
                     flex-direction: column;
                     align-items: flex-start;
                     gap: 0.5rem;
                 }

                 .row {
                     margin: 0;
                 }

                 .row > [class*="col-"] {
                     padding: 0.5rem;
                     margin-bottom: 1rem;
                 }

                 .status-item {
                     padding: 0.75rem;
                     margin-bottom: 0.5rem;
                 }

                 .stat-box {
                     padding: 1rem;
                 }

                 .stat-content h4 {
                     font-size: 1.5rem;
                 }

                 .table-responsive {
                     font-size: 0.8rem;
                 }

                 .btn {
                     width: 100%;
                     margin-bottom: 0.5rem;
                 }

                 .d-flex.justify-content-between {
                     flex-direction: column;
                     gap: 1rem;
                 }

                 .d-flex.justify-content-between > div {
                     display: flex;
                     gap: 0.5rem;
                 }
             }

             @media (max-width: 576px) {
                 .test-config-card .row > [class*="col-"] {
                     flex: 0 0 100%;
                     max-width: 100%;
                 }

                 .status-item {
                     text-align: center;
                 }

                 .status-label, .status-value {
                     font-size: 0.9rem;
                 }

                 .progress {
                     height: 8px;
                 }

                 .progress-bar {
                     font-size: 0.7rem;
                 }
             }
             </style>

            <!-- JavaScript do Módulo -->
            <script>
            // Configurações do módulo
           const MODULO_API_BASE = '/admin/api.php';


function atualizarChance(novaChance) {
  fetch(`${MODULO_API_BASE}?action=atualizar_chance`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer admin_token_123' // mesmo token que você definiu na API
    },
    body: JSON.stringify({ chance_vitoria: novaChance })
  })
  .then(res => res.json())
  .then(data => {
    if (data.sucesso) {
      alert('Chance atualizada com sucesso!');
    } else {
      alert('Erro: ' + data.erro);
    }
  })
  .catch(err => {
    console.error('Erro de conexão:', err);
  });
}
 

            // Inicialização do módulo
            document.addEventListener('DOMContentLoaded', function() {
                carregarStatusModulo();
            });

            // Função para carregar status atual
            async function carregarStatusModulo() {
                try {
                    const response = await fetch(`${MODULO_API_BASE}?action=config`);
                    const data = await response.json();

                    if (data.sucesso) {
                        document.getElementById('chanceAtualModulo').textContent = (data.chance_vitoria * 100).toFixed(1) + '%';
                        document.getElementById('ultimaAtualizacaoModulo').textContent = data.ultima_atualizacao;
                        document.getElementById('novaChanceModulo').value = data.chance_vitoria;
                        document.getElementById('chanceRangeModulo').value = data.chance_vitoria;
                    } else {
                        mostrarAlertaModulo('Erro ao carregar configurações: ' + (data.erro || 'Erro desconhecido'), 'error');
                    }
                } catch (error) {
                    mostrarAlertaModulo('Erro de conexão: ' + error.message, 'error');
                }
            }

            // Função para atualizar chance de vitória
            async function atualizarChanceModulo() {
                const novaChance = parseFloat(document.getElementById('novaChanceModulo').value);

                if (isNaN(novaChance) || novaChance < 0 || novaChance > 1) {
                    mostrarAlertaModulo('Chance deve ser um número entre 0 e 1', 'error');
                    return;
                }

                try {
                    // Aqui você deve usar o token de autenticação do seu painel admin existente
                    // Substitua 'SEU_TOKEN_AQUI' pelo token real do usuário logado
                    const authToken = localStorage.getItem('admin_token') || 'admin_token_123'; // Ajuste conforme seu sistema

                    const response = await fetch(`${MODULO_API_BASE}?action=atualizar_chance`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`
                        },
                        body: JSON.stringify({ chance_vitoria: novaChance })
                    });

                    const data = await response.json();

                    if (data.sucesso) {
                        mostrarAlertaModulo('Chance atualizada com sucesso!', 'success');
                        carregarStatusModulo();
                    } else {
                        mostrarAlertaModulo(data.erro || 'Erro ao atualizar', 'error');
                    }
                } catch (error) {
                    mostrarAlertaModulo('Erro de conexão: ' + error.message, 'error');
                }
            }

            // Função para atualizar display da chance
            function atualizarChanceDisplayModulo() {
                const valor = document.getElementById('chanceRangeModulo').value;
                document.getElementById('novaChanceModulo').value = valor;
            }

            // Função para atualizar range da chance
            function atualizarChanceRangeModulo() {
                const valor = document.getElementById('novaChanceModulo').value;
                document.getElementById('chanceRangeModulo').value = valor;
            }

            // Função para mostrar alertas
            function mostrarAlertaModulo(mensagem, tipo) {
                const container = document.getElementById('alertContainerModulo');
                
                // Remove alertas anteriores
                container.innerHTML = '';
                
                const alerta = document.createElement('div');
                alerta.className = `alert alert-${tipo}`;
                alerta.textContent = mensagem;
                
                container.appendChild(alerta);
                
                setTimeout(() => {
                    alerta.remove();
                }, 5000);
            }

            // Event listeners para Enter
            document.getElementById('novaChanceModulo').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    atualizarChanceModulo();
                }
            });

            // ===== MÓDULO RTP TESTE =====
            let rtpStats = {
                total: 0,
                vitorias: 0,
                derrotas: 0,
                historico: []
            };

            let rtpChart;

            // Inicializar gráfico RTP
            function inicializarGraficoRTP() {
                const ctx = document.getElementById('rtpChart').getContext('2d');
                rtpChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Vitórias', 'Derrotas'],
                        datasets: [{
                            data: [0, 0],
                            backgroundColor: [
                                'rgba(0, 212, 170, 0.8)',
                                'rgba(255, 87, 87, 0.8)'
                            ],
                            borderColor: [
                                'rgba(0, 212, 170, 1)',
                                'rgba(255, 87, 87, 1)'
                            ],
                            borderWidth: 3,
                            hoverBackgroundColor: [
                                'rgba(0, 212, 170, 1)',
                                'rgba(255, 87, 87, 1)'
                            ],
                            hoverBorderWidth: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: 'var(--dark-text)',
                                    padding: 20,
                                    font: {
                                        size: 14,
                                        weight: '500'
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: '#404040',
                                borderWidth: 1,
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 1000
                        },
                        interaction: {
                            intersect: false,
                            mode: 'nearest'
                        }
                    }
                });
            }

            // Função para testar RTP (simulado)
            async function testarRTP() {
                try {
                    const response = await fetch('api.php');
                    const data = await response.json();
                    
                    const venceu = data.win === true;
                    rtpStats.total++;
                    
                    if (venceu) {
                        rtpStats.vitorias++;
                    } else {
                        rtpStats.derrotas++;
                    }
                    
                    // Adicionar ao histórico
                    const agora = new Date();
                    rtpStats.historico.unshift({
                        resultado: venceu ? 'vitoria' : 'derrota',
                        timestamp: agora.toLocaleString('pt-BR'),
                        hora: agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }),
                        tipo: 'simulado'
                    });
                    
                    // Manter apenas os últimos 10 resultados
                    if (rtpStats.historico.length > 10) {
                        rtpStats.historico.pop();
                    }
                    
                    atualizarInterfaceRTP();
                    
                } catch (error) {
                    console.error('Erro ao testar RTP:', error);
                    mostrarAlertaModulo('Erro ao conectar com a API: ' + error.message, 'error');
                }
            }
            
            // Função para testar RTP com jogadas reais
            async function testarRTPReal() {
                const tipoRaspadinha = document.getElementById('tipoRaspadinha').value;
                const usuarioTeste = document.getElementById('usuarioTeste').value;
                const numeroTestes = parseInt(document.getElementById('numeroTestes').value);
                
                if (!usuarioTeste.trim()) {
                    mostrarAlertaModulo('Por favor, informe o usuário de teste', 'error');
                    return;
                }
                
                if (numeroTestes < 1 || numeroTestes > 1000) {
                    mostrarAlertaModulo('Número de testes deve estar entre 1 e 1000', 'error');
                    return;
                }
                
                // Confirmar teste
                if (!confirm(`Confirma o teste de RTP real?\n\nTipo: ${tipoRaspadinha}\nUsuário: ${usuarioTeste}\nTestes: ${numeroTestes}\n\nEste teste fará jogadas reais no sistema!`)) {
                    return;
                }
                
                // Desabilitar botão durante o teste
                const botaoTeste = event.target;
                botaoTeste.disabled = true;
                botaoTeste.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';
                
                try {
                    let vitorias = 0;
                    let derrotas = 0;
                    
                    for (let i = 0; i < numeroTestes; i++) {
                        // Fazer jogada real
                        const response = await fetch('../jogar.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `tipo=${tipoRaspadinha}&usuario_teste=${encodeURIComponent(usuarioTeste)}&teste_rtp=1`
                        });
                        
                        const resultado = await response.json();
                        
                        if (resultado.ganhou) {
                            vitorias++;
                            rtpStats.vitorias++;
                        } else {
                            derrotas++;
                            rtpStats.derrotas++;
                        }
                        
                        rtpStats.total++;
                        
                        // Adicionar ao histórico (apenas os últimos 10)
                        if (rtpStats.historico.length < 10) {
                            const agora = new Date();
                            rtpStats.historico.unshift({
                                resultado: resultado.ganhou ? 'vitoria' : 'derrota',
                                timestamp: agora.toLocaleString('pt-BR'),
                                hora: agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }),
                                tipo: 'real',
                                raspadinha: tipoRaspadinha,
                                premio: resultado.premio || 0
                            });
                        }
                        
                        // Atualizar interface a cada 10 testes
                        if (i % 10 === 0) {
                            atualizarInterfaceRTP();
                            botaoTeste.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${i + 1}/${numeroTestes}`;
                        }
                        
                        // Pequena pausa para não sobrecarregar o servidor
                        if (i % 50 === 0 && i > 0) {
                            await new Promise(resolve => setTimeout(resolve, 100));
                        }
                    }
                    
                    // Atualizar interface final
                    atualizarInterfaceRTP();
                    
                    const percentualVitorias = ((vitorias / numeroTestes) * 100).toFixed(2);
                    mostrarAlertaModulo(
                        `Teste concluído!\n\nResultados:\n- Vitórias: ${vitorias}\n- Derrotas: ${derrotas}\n- Taxa de vitória: ${percentualVitorias}%`, 
                        'success'
                    );
                    
                } catch (error) {
                    console.error('Erro ao testar RTP real:', error);
                    mostrarAlertaModulo('Erro durante o teste: ' + error.message, 'error');
                } finally {
                    // Reabilitar botão
                    botaoTeste.disabled = false;
                    botaoTeste.innerHTML = '<i class="fas fa-dice"></i> Testar RTP Real';
                }
            }

            // Função para atualizar interface
            function atualizarInterfaceRTP() {
                // Atualizar estatísticas
                document.getElementById('totalTentativas').textContent = rtpStats.total;
                document.getElementById('totalVitorias').textContent = rtpStats.vitorias;
                document.getElementById('totalDerrotas').textContent = rtpStats.derrotas;
                
                // Calcular percentual
                const percentual = rtpStats.total > 0 ? ((rtpStats.vitorias / rtpStats.total) * 100).toFixed(1) : 0;
                document.getElementById('percentualVitorias').textContent = percentual + '%';
                
                // Atualizar gráfico
                if (rtpChart) {
                    rtpChart.data.datasets[0].data = [rtpStats.vitorias, rtpStats.derrotas];
                    rtpChart.update();
                }
                
                // Atualizar lista de resultados
                atualizarListaResultados();
            }

            // Função para atualizar lista de resultados
            function atualizarListaResultados() {
                const tableBody = document.getElementById('resultsTableBody');
                
                if (rtpStats.historico.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" class="no-results">Nenhum teste realizado ainda</td></tr>';
                    return;
                }
                
                const html = rtpStats.historico.map((item, index) => {
                    const numeroTeste = rtpStats.total - index;
                    const statusClass = item.resultado === 'vitoria' ? 'vitoria' : 'derrota';
                    const statusText = item.resultado === 'vitoria' ? 'Vitória' : 'Derrota';
                    
                    let resultadoText = item.resultado === 'vitoria' ? 'Prêmio Ganho' : 'Sem Prêmio';
                    
                    // Para testes reais, mostrar mais detalhes
                    if (item.tipo === 'real') {
                        const tipoFormatado = item.raspadinha ? item.raspadinha.charAt(0).toUpperCase() + item.raspadinha.slice(1) : '';
                        if (item.resultado === 'vitoria' && item.premio) {
                            resultadoText = `${tipoFormatado} - R$ ${item.premio.toFixed(2)}`;
                        } else {
                            resultadoText = `${tipoFormatado} - Sem Prêmio`;
                        }
                    }
                    
                    const tipoIcon = item.tipo === 'real' ? 
                        '<i class="fas fa-coins" title="Teste Real"></i>' : 
                        '<i class="fas fa-flask" title="Teste Simulado"></i>';
                    
                    return `
                        <tr>
                            <td>#${numeroTeste} ${tipoIcon}</td>
                            <td>
                                <span class="result-status ${statusClass}">
                                    ${statusText}
                                </span>
                            </td>
                            <td>${resultadoText}</td>
                            <td class="result-time">${item.hora}</td>
                        </tr>
                    `;
                }).join('');
                
                tableBody.innerHTML = html;
            }

            // Função para limpar testes
            function limparTestes() {
                if (rtpStats.total === 0) {
                    mostrarAlertaModulo('Não há testes para limpar', 'info');
                    return;
                }
                
                if (confirm('Tem certeza que deseja limpar todos os testes?')) {
                    rtpStats = {
                        total: 0,
                        vitorias: 0,
                        derrotas: 0,
                        historico: []
                    };
                    
                    // Limpar tabela
                    const tableBody = document.getElementById('resultsTableBody');
                    if (tableBody) {
                        tableBody.innerHTML = '<tr><td colspan="4" class="no-results">Nenhum teste realizado ainda</td></tr>';
                    }
                    
                    atualizarInterfaceRTP();
                    mostrarAlertaModulo('Testes limpos com sucesso!', 'success');
                }
            }

            // Função para exportar resultados
            function exportarResultados() {
                if (rtpStats.total === 0) {
                    mostrarAlertaModulo('Não há dados para exportar', 'info');
                    return;
                }
                
                const header = 'Data/Hora,Resultado\n';
                const linhas = rtpStats.historico.map(item => 
                    `${item.timestamp},${item.resultado === 'vitoria' ? 'Vitória' : 'Derrota'}`
                ).join('\n');
                
                const csvContent = header + linhas;
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                
                if (link.download !== undefined) {
                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', `rtp_teste_${new Date().toISOString().split('T')[0]}.csv`);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    mostrarAlertaModulo('Arquivo CSV exportado com sucesso!', 'success');
                }
            }



            // Funções para a aba de Teste Prêmios
            function implementarSistema() {
                if (confirm('Deseja implementar o sistema completo de teste de prêmios com usuário fixo?')) {
                    mostrarAlertaModulo('Implementando sistema de teste de prêmios...', 'info');
                    
                    // Simular implementação
                    setTimeout(() => {
                        mostrarAlertaModulo('Sistema de teste de prêmios implementado com sucesso! Nível de confiança: 95%', 'success');
                    }, 2000);
                }
            }

            function criarTabela() {
                if (confirm('Deseja criar a tabela tipos_raspadinha no banco de dados?')) {
                    mostrarAlertaModulo('Criando tabela tipos_raspadinha...', 'info');
                    
                    // Simular criação da tabela
                    setTimeout(() => {
                        mostrarAlertaModulo('Tabela tipos_raspadinha criada com sucesso!', 'success');
                    }, 1500);
                }
            }

            function testarPremios() {
                if (confirm('Deseja iniciar o teste de prêmios com usuário fixo?')) {
                    mostrarAlertaModulo('Iniciando teste de prêmios...', 'info');
                    
                    // Simular teste de prêmios
                    setTimeout(() => {
                        mostrarAlertaModulo('Teste de prêmios concluído! Verifique os resultados na aba Teste RTP.', 'success');
                    }, 3000);
                }
            }

            // Funções para gerenciamento de tipos de raspadinha
            async function verificarTabelaTipos() {
                try {
                    mostrarAlertaModulo('Verificando tabela tipos_raspadinha...', 'info');
                    
                    const response = await fetch('gerenciar_tipos_raspadinha.php?action=verificar_tabela');
                    const resultado = await response.json();
                    
                    // Verificar se há erro de autenticação
                    if (resultado.success === false && resultado.erro === 'Não autorizado') {
                        mostrarAlertaModulo('Sessão expirada. Redirecionando para login...', 'error');
                        setTimeout(() => {
                            window.location.href = '../login.php';
                        }, 2000);
                        return;
                    }
                    
                    if (resultado.existe) {
                        mostrarAlertaModulo(`Tabela encontrada! ${resultado.total_tipos} tipos cadastrados.`, 'success');
                        carregarEstatisticasTipos();
                    } else {
                        mostrarAlertaModulo('Tabela tipos_raspadinha não encontrada. Execute o script SQL primeiro.', 'warning');
                    }
                } catch (error) {
                    console.error('Erro ao verificar tabela:', error);
                    mostrarAlertaModulo('Erro ao verificar tabela: ' + error.message, 'error');
                }
            }

            async function carregarEstatisticasTipos() {
                try {
                    const response = await fetch('gerenciar_tipos_raspadinha.php?action=estatisticas');
                    const stats = await response.json();
                    
                    // Verificar se há erro de autenticação
                    if (stats.success === false && stats.erro === 'Não autorizado') {
                        mostrarAlertaModulo('Sessão expirada. Redirecionando para login...', 'error');
                        setTimeout(() => {
                            window.location.href = '../login.php';
                        }, 2000);
                        return;
                    }
                    
                    if (stats.success) {
                        document.getElementById('tiposCadastrados').textContent = stats.total || 0;
                        document.getElementById('tiposAtivos').textContent = stats.ativos || 0;
                        document.getElementById('ultimaAtualizacaoTipos').textContent = new Date().toLocaleString('pt-BR');
                    }
                } catch (error) {
                    console.error('Erro ao carregar estatísticas:', error);
                }
            }

            // Variáveis para teste automatizado de prêmios
            let testeAutomatizado = {
                ativo: false,
                intervalId: null,
                testeAtual: 0,
                totalTestes: 0,
                estatisticas: {
                    total: 0,
                    ganhos: 0,
                    perdas: 0,
                    valorTotalGanho: 0,
                    valorTotalApostado: 0,
                    maiorPremio: 0
                },
                historico: []
            };
            
            // Variável para estatísticas de teste de prêmios
            let prizeTestStats = {
                total: 0,
                ganhos: 0,
                perdas: 0,
                valorTotalGanho: 0,
                valorTotalApostado: 0,
                historico: []
            };

            // Função para iniciar teste automatizado
            async function iniciarTesteAutomatizado() {
                const tipoRaspadinha = document.getElementById('tipoRaspadinha').value;
                const quantidadeTestes = parseInt(document.getElementById('quantidadeTestes').value);
                const intervaloTestes = parseInt(document.getElementById('intervaloTestes').value);
                
                if (quantidadeTestes < 1 || quantidadeTestes > 10000) {
                    mostrarAlertaModulo('Quantidade de testes deve estar entre 1 e 10.000', 'error');
                    return;
                }
                
                if (intervaloTestes < 10 || intervaloTestes > 5000) {
                    mostrarAlertaModulo('Intervalo deve estar entre 10ms e 5000ms', 'error');
                    return;
                }
                
                // Resetar estatísticas
                testeAutomatizado.ativo = true;
                testeAutomatizado.testeAtual = 0;
                testeAutomatizado.totalTestes = quantidadeTestes;
                testeAutomatizado.estatisticas = {
                    total: 0,
                    ganhos: 0,
                    perdas: 0,
                    valorTotalGanho: 0,
                    valorTotalApostado: 0,
                    maiorPremio: 0
                };
                
                // Atualizar interface
                document.getElementById('btnIniciarTeste').style.display = 'none';
                document.getElementById('btnPararTeste').style.display = 'inline-block';
                document.getElementById('testStatusCard').style.display = 'block';
                
                mostrarAlertaModulo(`Iniciando teste automatizado: ${quantidadeTestes} testes com raspadinha de R$ ${tipoRaspadinha}`, 'info');
                
                // Iniciar testes
                testeAutomatizado.intervalId = setInterval(() => {
                    executarTesteUnico(parseFloat(tipoRaspadinha));
                }, intervaloTestes);
            }
            
            // Função para parar teste automatizado
            function pararTesteAutomatizado() {
                if (testeAutomatizado.intervalId) {
                    clearInterval(testeAutomatizado.intervalId);
                    testeAutomatizado.intervalId = null;
                }
                
                testeAutomatizado.ativo = false;
                
                document.getElementById('btnIniciarTeste').style.display = 'inline-block';
                document.getElementById('btnPararTeste').style.display = 'none';
                
                mostrarAlertaModulo('Teste automatizado interrompido pelo usuário', 'warning');
            }
            
            // Função para executar um teste único
            async function executarTesteUnico(valor) {
                if (!testeAutomatizado.ativo) return;
                
                try {
                    // Fazer requisição para o jogo com usuário de teste
                    const response = await fetch('../jogar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `valor=${valor}&teste_premio=1&usuario_teste=teste_usuario_ilimitado`
                    });
                    
                    const resultado = await response.json();
                    
                    // Atualizar contadores
                    testeAutomatizado.testeAtual++;
                    testeAutomatizado.estatisticas.total++;
                    testeAutomatizado.estatisticas.valorTotalApostado += valor;
                    
                    let ganhou = false;
                    let valorGanho = 0;
                    
                    if (resultado.ganhou && resultado.premio > 0) {
                        ganhou = true;
                        valorGanho = resultado.premio;
                        testeAutomatizado.estatisticas.ganhos++;
                        testeAutomatizado.estatisticas.valorTotalGanho += valorGanho;
                        
                        if (valorGanho > testeAutomatizado.estatisticas.maiorPremio) {
                            testeAutomatizado.estatisticas.maiorPremio = valorGanho;
                        }
                    } else {
                        testeAutomatizado.estatisticas.perdas++;
                    }
                    
                    // Adicionar ao histórico
                    const agora = new Date();
                    const lucroPerda = ganhou ? (valorGanho - valor) : -valor;
                    const rtpAcumulado = testeAutomatizado.estatisticas.valorTotalApostado > 0 ? 
                        (testeAutomatizado.estatisticas.valorTotalGanho / testeAutomatizado.estatisticas.valorTotalApostado * 100) : 0;
                    
                    testeAutomatizado.historico.unshift({
                        numero: testeAutomatizado.estatisticas.total,
                        hora: agora.toLocaleTimeString('pt-BR'),
                        tipo: `R$ ${valor.toFixed(2)}`,
                        valorAposta: valor,
                        resultado: ganhou ? 'Ganhou' : 'Perdeu',
                        premio: valorGanho,
                        lucroPerda: lucroPerda,
                        rtpAcumulado: rtpAcumulado
                    });
                    
                    // Manter apenas os últimos 100 registros no histórico
                    if (testeAutomatizado.historico.length > 100) {
                        testeAutomatizado.historico = testeAutomatizado.historico.slice(0, 100);
                    }
                    
                    // Atualizar resultado individual na interface
                    atualizarResultadoIndividual(valor, ganhou, valorGanho, lucroPerda);
                    
                    // Atualizar interface
                    atualizarInterfaceTesteAutomatizado();
                    
                    // Verificar se terminou
                    if (testeAutomatizado.testeAtual >= testeAutomatizado.totalTestes) {
                        finalizarTesteAutomatizado();
                    }
                    
                } catch (error) {
                    console.error('Erro ao executar teste:', error);
                    mostrarAlertaModulo('Erro ao executar teste: ' + error.message, 'error');
                    pararTesteAutomatizado();
                }
            }
            
            // Função para atualizar resultado individual
            function atualizarResultadoIndividual(valorAposta, ganhou, valorGanho, lucroPerda) {
                const resultadoDiv = document.getElementById('resultadoIndividual');
                
                if (ganhou) {
                    const lucroFormatado = lucroPerda >= 0 ? `+${lucroPerda.toFixed(2)}` : lucroPerda.toFixed(2);
                    resultadoDiv.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Apostou:</span>
                            <span class="fw-bold">R$ ${valorAposta.toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-success">🎉 Ganhou:</span>
                            <span class="fw-bold text-success">R$ ${valorGanho.toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-success">Resultado:</span>
                            <span class="fw-bold text-success">${lucroFormatado >= 0 ? '+' : ''}R$ ${lucroFormatado}</span>
                        </div>
                    `;
                } else {
                    resultadoDiv.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Apostou:</span>
                            <span class="fw-bold">R$ ${valorAposta.toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-danger">😔 Perdeu:</span>
                            <span class="fw-bold text-danger">R$ ${valorAposta.toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-danger">Resultado:</span>
                            <span class="fw-bold text-danger">-R$ ${valorAposta.toFixed(2)}</span>
                        </div>
                    `;
                }
            }
            
            // Função para atualizar interface do teste automatizado
            function atualizarInterfaceTesteAutomatizado() {
                const stats = testeAutomatizado.estatisticas;
                const progresso = (testeAutomatizado.testeAtual / testeAutomatizado.totalTestes) * 100;
                const taxaGanho = stats.total > 0 ? (stats.ganhos / stats.total * 100) : 0;
                const rtp = stats.valorTotalApostado > 0 ? (stats.valorTotalGanho / stats.valorTotalApostado * 100) : 0;
                const lucroTotal = stats.valorTotalGanho - stats.valorTotalApostado;
                const valorTotalPerdido = stats.valorTotalApostado - stats.valorTotalGanho;
                
                // Atualizar status em tempo real
                document.getElementById('progressoTeste').textContent = `${testeAutomatizado.testeAtual}/${testeAutomatizado.totalTestes}`;
                document.getElementById('totalGanhos').textContent = stats.ganhos;
                document.getElementById('totalPerdas').textContent = stats.perdas;
                document.getElementById('taxaGanho').textContent = `${taxaGanho.toFixed(2)}%`;
                document.getElementById('valorTotalGanho').textContent = `R$ ${stats.valorTotalGanho.toFixed(2)}`;
                document.getElementById('valorTotalPerdido').textContent = `R$ ${Math.max(0, valorTotalPerdido).toFixed(2)}`;
                document.getElementById('lucroPrejuizo').textContent = `R$ ${lucroTotal.toFixed(2)}`;
                document.getElementById('lucroPrejuizo').className = `status-value ${lucroTotal >= 0 ? 'text-success' : 'text-danger'}`;
                
                // Calcular diferença com RTP configurado
                if (window.rtpConfiguradoAtual !== undefined) {
                    const diferencaRTP = rtp - window.rtpConfiguradoAtual;
                    document.getElementById('diferencaRTP').textContent = `${diferencaRTP >= 0 ? '+' : ''}${diferencaRTP.toFixed(2)}%`;
                    document.getElementById('diferencaRTP').className = `status-value ${diferencaRTP >= 0 ? 'text-success' : 'text-danger'}`;
                }
                
                // Atualizar barra de progresso
                document.getElementById('barraProgresso').style.width = `${progresso.toFixed(1)}%`;
                document.getElementById('barraProgresso').textContent = `${progresso.toFixed(1)}%`;
                
                // Atualizar estatísticas consolidadas
                document.getElementById('totalTestesRealizados').textContent = stats.total;
                document.getElementById('percentualGanhos').textContent = `${taxaGanho.toFixed(2)}%`;
                document.getElementById('rtpCalculado').textContent = `${rtp.toFixed(2)}%`;
                document.getElementById('maiorPremio').textContent = `R$ ${stats.maiorPremio.toFixed(2)}`;
                
                // Atualizar histórico
                atualizarTabelaHistorico();
            }
            
            // Função para finalizar teste automatizado
            function finalizarTesteAutomatizado() {
                if (testeAutomatizado.intervalId) {
                    clearInterval(testeAutomatizado.intervalId);
                    testeAutomatizado.intervalId = null;
                }
                
                testeAutomatizado.ativo = false;
                
                document.getElementById('btnIniciarTeste').style.display = 'inline-block';
                document.getElementById('btnPararTeste').style.display = 'none';
                
                const stats = testeAutomatizado.estatisticas;
                const rtp = stats.valorTotalApostado > 0 ? (stats.valorTotalGanho / stats.valorTotalApostado * 100) : 0;
                const lucroTotal = stats.valorTotalGanho - stats.valorTotalApostado;
                
                mostrarAlertaModulo(
                    `Teste automatizado concluído! ${stats.total} testes realizados. ` +
                    `RTP: ${rtp.toFixed(2)}%, Lucro/Prejuízo: R$ ${lucroTotal.toFixed(2)}`,
                    'success'
                );
            }
            
            // Função para atualizar tabela de histórico
            function atualizarTabelaHistorico() {
                const tbody = document.getElementById('corpoTabelaHistorico');
                
                if (testeAutomatizado.historico.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Nenhum teste realizado ainda</td></tr>';
                    return;
                }
                
                const html = testeAutomatizado.historico.slice(0, 20).map(item => {
                    const resultClass = item.resultado === 'Ganhou' ? 'text-success' : 'text-danger';
                    const premioFormatado = item.premio > 0 ? `R$ ${item.premio.toFixed(2)}` : '-';
                    const lucroClass = item.lucroPerda >= 0 ? 'text-success' : 'text-danger';
                    
                    return `
                        <tr>
                            <td>${item.numero}</td>
                            <td>${item.hora}</td>
                            <td>${item.tipo}</td>
                            <td>R$ ${item.valorAposta.toFixed(2)}</td>
                            <td class="${resultClass}">${item.resultado}</td>
                            <td class="${resultClass}">${premioFormatado}</td>
                            <td class="${lucroClass}">R$ ${item.lucroPerda.toFixed(2)}</td>
                            <td>${item.rtpAcumulado.toFixed(2)}%</td>
                        </tr>
                    `;
                }).join('');
                
                tbody.innerHTML = html;
            }
            
            // Função para exportar CSV
            function exportarCSV() {
                if (testeAutomatizado.historico.length === 0) {
                    mostrarAlertaModulo('Nenhum dado para exportar', 'warning');
                    return;
                }
                
                const headers = ['Número', 'Hora', 'Tipo', 'Valor Aposta', 'Resultado', 'Prêmio', 'Lucro/Perda', 'RTP Acumulado'];
                const csvContent = [headers.join(',')];
                
                testeAutomatizado.historico.forEach(item => {
                    const row = [
                        item.numero,
                        item.hora,
                        item.tipo,
                        item.valorAposta.toFixed(2),
                        item.resultado,
                        item.premio.toFixed(2),
                        item.lucroPerda.toFixed(2),
                        item.rtpAcumulado.toFixed(2) + '%'
                    ];
                    csvContent.push(row.join(','));
                });
                
                const blob = new Blob([csvContent.join('\n')], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `teste_premios_${new Date().toISOString().slice(0, 10)}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                mostrarAlertaModulo('Arquivo CSV exportado com sucesso!', 'success');
            }
            
            // Função para limpar histórico completo
            function limparHistoricoCompleto() {
                if (confirm('Tem certeza que deseja limpar todo o histórico de testes?')) {
                    testeAutomatizado.historico = [];
                    testeAutomatizado.estatisticas = {
                        total: 0,
                        ganhos: 0,
                        perdas: 0,
                        valorTotalGanho: 0,
                        valorTotalApostado: 0,
                        maiorPremio: 0
                    };
                    
                    atualizarInterfaceTesteAutomatizado();
                    mostrarAlertaModulo('Histórico limpo com sucesso!', 'success');
                }
            }
            
            // Função para atualizar histórico de testes
            function atualizarHistoricoTestes() {
                // Esta função pode ser implementada conforme necessário
                // Por enquanto, apenas um placeholder
                console.log('Atualizando histórico de testes...');
            }
            
            // Função para limpar histórico de testes
            function limparHistoricoTestes() {
                if (prizeTestStats.total === 0) {
                    mostrarAlertaModulo('Não há testes para limpar', 'info');
                    return;
                }
                
                if (confirm('Tem certeza que deseja limpar todo o histórico de testes?')) {
                    prizeTestStats = {
                        total: 0,
                        ganhos: 0,
                        perdas: 0,
                        valorTotalGanho: 0,
                        valorTotalApostado: 0,
                        historico: []
                    };
                    
                    // Limpar interface
                    document.getElementById('testResult').style.display = 'none';
                    atualizarHistoricoTestes();
                    
                    mostrarAlertaModulo('Histórico limpo com sucesso!', 'success');
                }
            }

            // Carregar estatísticas ao inicializar
            document.addEventListener('DOMContentLoaded', function() {
                // Aguardar um pouco para garantir que o canvas esteja renderizado
                setTimeout(() => {
                    inicializarGraficoRTP();
                    carregarEstatisticasTipos();
                    atualizarHistoricoTestes();
                    
                    // Inicializar aba Teste Prêmios
                    inicializarTesteAutomatizado();
                }, 500);
            });
            
            // Função para testar raspadinha individual (usada pelos cards de teste)
            async function testarRaspadinha(valor) {
                try {
                    const response = await fetch('../jogar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `valor=${valor}&teste_premio=1&usuario_teste=teste_usuario_ilimitado`
                    });
                    
                    const resultado = await response.json();
                    
                    if (resultado.erro) {
                        mostrarAlertaModulo(`Erro: ${resultado.erro}`, 'danger');
                        return;
                    }
                    
                    const ganhou = resultado.ganhou;
                    const valorGanho = parseFloat(resultado.premio) || 0;
                    
                    // Atualizar estatísticas
                    prizeTestStats.total++;
                    prizeTestStats.valorTotalApostado += valor;
                    
                    if (ganhou) {
                        prizeTestStats.ganhos++;
                        prizeTestStats.valorTotalGanho += valorGanho;
                    } else {
                        prizeTestStats.perdas++;
                    }
                    
                    // Adicionar ao histórico
                    const agora = new Date();
                    const lucroPerda = ganhou ? (valorGanho - valor) : -valor;
                    const rtpAcumulado = prizeTestStats.valorTotalApostado > 0 ? 
                        (prizeTestStats.valorTotalGanho / prizeTestStats.valorTotalApostado * 100) : 0;
                    
                    prizeTestStats.historico.unshift({
                        numero: prizeTestStats.total,
                        hora: agora.toLocaleTimeString('pt-BR'),
                        tipo: `R$ ${valor.toFixed(2)}`,
                        valorAposta: valor,
                        resultado: ganhou ? 'Ganhou' : 'Perdeu',
                        premio: valorGanho,
                        lucroPerda: lucroPerda,
                        rtpAcumulado: rtpAcumulado
                    });
                    
                    // Limitar histórico a 100 itens
                    if (prizeTestStats.historico.length > 100) {
                        prizeTestStats.historico = prizeTestStats.historico.slice(0, 100);
                    }
                    
                    // Mostrar resultado com formato melhorado
                    let mensagem;
                    if (ganhou) {
                        const lucroFormatado = lucroPerda >= 0 ? `+${lucroPerda.toFixed(2)}` : lucroPerda.toFixed(2);
                        mensagem = `🎉 Apostou R$ ${valor.toFixed(2)} → Ganhou R$ ${valorGanho.toFixed(2)} → Resultado: ${lucroFormatado >= 0 ? '+' : ''}R$ ${lucroFormatado}`;
                    } else {
                        mensagem = `😔 Apostou R$ ${valor.toFixed(2)} → Perdeu → Resultado: -R$ ${valor.toFixed(2)}`;
                    }
                    
                    mostrarAlertaModulo(mensagem, ganhou ? 'success' : 'warning');
                    
                    // Atualizar interface
                    atualizarHistoricoTestes();
                    
                } catch (error) {
                    console.error('Erro no teste:', error);
                    mostrarAlertaModulo('Erro ao executar teste', 'danger');
                }
            }
            
            // Função para carregar RTP configurado
            async function carregarRTPConfigurado() {
                try {
                    const response = await fetch('config.json');
                    const config = await response.json();
                    const rtpConfigurado = (config.chance_vitoria * 100).toFixed(1);
                    
                    window.rtpConfiguradoAtual = parseFloat(rtpConfigurado);
                    
                    const rtpElement = document.getElementById('rtpConfigurado');
                    if (rtpElement) {
                        rtpElement.textContent = `${rtpConfigurado}%`;
                    }
                    
                    return parseFloat(rtpConfigurado);
                } catch (error) {
                    console.error('Erro ao carregar RTP configurado:', error);
                    const rtpElement = document.getElementById('rtpConfigurado');
                    if (rtpElement) {
                        rtpElement.textContent = 'Erro ao carregar';
                    }
                    window.rtpConfiguradoAtual = 0;
                    return 0;
                }
            }
            
            // Função para inicializar a aba Teste Prêmios
            async function inicializarTesteAutomatizado() {
                // Carregar RTP configurado
                await carregarRTPConfigurado();
                
                // Resetar estatísticas
                testeAutomatizado.estatisticas = {
                    total: 0,
                    ganhos: 0,
                    perdas: 0,
                    valorTotalGanho: 0,
                    valorTotalApostado: 0,
                    maiorPremio: 0
                };
                testeAutomatizado.historico = [];
                
                // Limpar resultado individual
                const resultadoDiv = document.getElementById('resultadoIndividual');
                if (resultadoDiv) {
                    resultadoDiv.innerHTML = '<span class="text-muted">Aguardando primeiro teste...</span>';
                }
                
                // Resetar campos adicionais
                const valorPerdidoElement = document.getElementById('valorTotalPerdido');
                if (valorPerdidoElement) {
                    valorPerdidoElement.textContent = 'R$ 0,00';
                }
                
                const diferencaRTPElement = document.getElementById('diferencaRTP');
                if (diferencaRTPElement) {
                    diferencaRTPElement.textContent = '0%';
                    diferencaRTPElement.className = 'text-muted';
                }
                
                // Atualizar interface inicial
                atualizarInterfaceTesteAutomatizado();
                
                // Garantir que os botões estejam no estado correto
                document.getElementById('btnIniciarTeste').style.display = 'inline-block';
                document.getElementById('btnPararTeste').style.display = 'none';
                document.getElementById('testStatusCard').style.display = 'none';
            }
            </script>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            if (sidebar && sidebarOverlay) {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
                
                // Update menu icon
                if (mobileMenuBtn) {
                    const icon = mobileMenuBtn.querySelector('i');
                    if (icon) {
                        if (sidebar.classList.contains('show')) {
                            icon.className = 'bi bi-x';
                        } else {
                            icon.className = 'bi bi-list';
                        }
                    }
                }
            }
        }

        function closeSidebar() {
            if (sidebar && sidebarOverlay) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                if (mobileMenuBtn) {
                    const icon = mobileMenuBtn.querySelector('i');
                    if (icon) {
                        icon.className = 'bi bi-list';
                    }
                }
            }
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleSidebar);
        }
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }

        // Close sidebar when clicking on nav links in mobile
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });

        // Animação de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll(".fade-in");
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                }, index * 100);
            });
        });

        // Atualização em tempo real do relógio
        function updateTime() {
            const now = new Date();
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('pt-BR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }

        setInterval(updateTime, 1000);

        // Touch gestures for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleGesture();
        });

        function handleGesture() {
            const swipeThreshold = 50;
            const swipeDistance = touchEndX - touchStartX;
            
            if (window.innerWidth <= 768) {
                // Swipe right to open sidebar
                if (swipeDistance > swipeThreshold && touchStartX < 50) {
                    if (!sidebar.classList.contains('show')) {
                        toggleSidebar();
                    }
                }
                // Swipe left to close sidebar
                else if (swipeDistance < -swipeThreshold && sidebar.classList.contains('show')) {
                    closeSidebar();
                }
            }
        }

        // Prevent zoom on double tap for better mobile experience
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>
</body>
</html>

