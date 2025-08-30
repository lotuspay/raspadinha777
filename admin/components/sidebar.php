<?php
// Componente de Sidebar para Dashboard Administrativo
// Detecta a página atual para marcar o item ativo
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand">
            <i class="bi bi-speedometer2"></i>
            Dashboard Admin
        </a>
    </div>
    
    <div class="sidebar-nav">
        <div class="nav-item">
            <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-house-door"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="usuarios.php" class="nav-link <?php echo ($current_page == 'usuarios.php') ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span>Gerenciar Usuários</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="payout_management.php" class="nav-link <?php echo ($current_page == 'payout_management.php') ? 'active' : ''; ?>">
                <i class="bi bi-credit-card"></i>
                <span>Gestão de Pagamentos</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="global_settings.php" class="nav-link <?php echo ($current_page == 'global_settings.php') ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i>
                <span>Configurações Globais</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="configuracoes_seguras.php" class="nav-link <?php echo ($current_page == 'configuracoes_seguras.php') ? 'active' : ''; ?>">
                <i class="bi bi-key"></i>
                <span>Credenciais</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="affiliates.php" class="nav-link <?php echo ($current_page == 'affiliates.php') ? 'active' : ''; ?>">
                <i class="bi bi-diagram-3"></i>
                <span>Gestão de Afiliados</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="affiliate_levels.php" class="nav-link <?php echo ($current_page == 'affiliate_levels.php') ? 'active' : ''; ?>">
                <i class="bi bi-layers"></i>
                <span>Níveis de Afiliados</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="influencer_management.php" class="nav-link <?php echo ($current_page == 'influencer_management.php') ? 'active' : ''; ?>">
                <i class="bi bi-star"></i>
                <span>Gerenciar Influenciadores</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="depositos.php" class="nav-link <?php echo ($current_page == 'depositos.php') ? 'active' : ''; ?>">
                <i class="bi bi-arrow-down-circle"></i>
                <span>Ver Depósitos</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="controle_raspadinha.php" class="nav-link <?php echo ($current_page == 'controle_raspadinha.php') ? 'active' : ''; ?>">
                <i class="bi bi-dice-6"></i>
                <span>Controle de Raspadinha</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="forcar_premio.php" class="nav-link <?php echo ($current_page == 'forcar_premio.php') ? 'active' : ''; ?>">
                <i class="bi bi-trophy-fill"></i>
                <span>Configuração de Prêmios</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="saques_pix.php" class="nav-link <?php echo ($current_page == 'saques_pix.php') ? 'active' : ''; ?>">
                <i class="bi bi-arrow-up-circle"></i>
                <span>Saques</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="relatorio.php" class="nav-link <?php echo ($current_page == 'relatorio.php') ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i>
                <span>Relatórios</span>
            </a>
        </div>
    </div>
</nav>