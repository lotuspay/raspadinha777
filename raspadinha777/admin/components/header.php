<?php
// Verificar se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexão com banco de dados se não estiver incluída
if (!isset($conn)) {
    require_once __DIR__ . '/../includes/db.php';
}

// Verificar se o usuário está logado (usando usuario_id que é o padrão do sistema)
if (!isset($_SESSION['usuario_id'])) {
    // Só redirecionar se não estivermos já processando headers
    if (!headers_sent()) {
        header('Location: login.php');
        exit();
    }
}

// Buscar informações do usuário
$user_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Definir título da página dinamicamente
$page_titles = [
    'index.php' => 'Dashboard',
    'usuarios.php' => 'Gerenciar Usuários',
    'depositos.php' => 'Ver Depósitos',
    'saques_pix.php' => 'Saques PIX',
    'payout_management.php' => 'Gestão de Pagamentos',
    'global_settings.php' => 'Configurações Globais',
    'configuracoes_seguras.php' => 'Credenciais',
    'affiliates.php' => 'Gestão de Afiliados',
    'affiliate_levels.php' => 'Níveis de Afiliados',
    'influencer_management.php' => 'Gerenciar Influenciadores',
    'controle_raspadinha.php' => 'Controle de Raspadinha',
    'painel_config_premios.php' => 'Configuração de Prêmios',
    'relatorio.php' => 'Relatórios',
    'dashboard_dark.php' => 'Dashboard'
];

$current_page = basename($_SERVER['PHP_SELF']);
$page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Painel Admin';
?>

<!-- Header -->
<header class="header">
    <div class="header-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title"><?php echo htmlspecialchars($page_title); ?></h1>
    </div>
    
    <div class="header-center">
        <div class="search-container">
            <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="globalSearch" placeholder="Buscar em tudo..." autocomplete="off">
            </div>
            <div class="search-results" id="searchResults"></div>
        </div>
    </div>
    
    <div class="header-right">
        <div class="user-menu">
            <div class="user-avatar" onclick="toggleUserDropdown()">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%23007bff'/%3E%3Ctext x='16' y='20' text-anchor='middle' fill='white' font-family='Arial' font-size='14' font-weight='bold'%3E<?php echo strtoupper(substr($user['name'], 0, 1)); ?>%3C/text%3E%3C/svg%3E" alt="Avatar">
                <i class="bi bi-chevron-down dropdown-arrow"></i>
            </div>
            
            <div class="dropdown-menu" id="userDropdown">
                <div class="dropdown-header">
                    <div class="user-info">
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                        <small><?php echo htmlspecialchars($user['email']); ?></small>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="setup_2fa.php" class="dropdown-item">
                    <i class="bi bi-shield-lock"></i>
                    <span>Autenticação 2FA</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sair</span>
                </a>
            </div>
        </div>
    </div>
</header>

<script>
// Função para alternar sidebar
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const header = document.querySelector('.header');
    
    sidebar.classList.toggle('collapsed');
    
    if (mainContent) {
        mainContent.classList.toggle('sidebar-collapsed');
    }
    
    if (header) {
        header.classList.toggle('sidebar-collapsed');
    }
    
    // Salvar estado no localStorage
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
}

// Restaurar estado da sidebar
function restoreSidebarState() {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const header = document.querySelector('.header');
        
        if (sidebar) sidebar.classList.add('collapsed');
        if (mainContent) mainContent.classList.add('sidebar-collapsed');
        if (header) header.classList.add('sidebar-collapsed');
    }
}

// Função para alternar dropdown do usuário
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Fechar dropdown ao clicar fora
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-menu');
    const dropdown = document.getElementById('userDropdown');
    
    if (!userMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Busca global
let searchTimeout;
const searchInput = document.getElementById('globalSearch');
const searchResults = document.getElementById('searchResults');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length < 2) {
        searchResults.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        performGlobalSearch(query);
    }, 300);
});

function performGlobalSearch(query) {
    fetch('ajax/global_search.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'query=' + encodeURIComponent(query)
    })
    .then(response => response.json())
    .then(data => {
        displaySearchResults(data);
    })
    .catch(error => {
        console.error('Erro na busca:', error);
    });
}

function displaySearchResults(data) {
    const results = data.results || data;
    if (!results || results.length === 0) {
        searchResults.style.display = 'none';
        return;
    }
    
    let html = '';
    results.forEach(result => {
        const icon = result.icon || 'bi-search';
        html += `
            <div class="search-result-item" onclick="window.location.href='${result.url}'">
                <div class="result-icon">
                    <i class="bi ${icon}"></i>
                </div>
                <div class="result-content">
                    <div class="result-title">${result.title}</div>
                    <div class="result-description">${result.description}</div>
                </div>
            </div>
        `;
    });
    
    searchResults.innerHTML = html;
    searchResults.style.display = 'block';
}

// Fechar resultados de busca ao clicar fora
document.addEventListener('click', function(event) {
    const searchContainer = document.querySelector('.search-container');
    if (!searchContainer.contains(event.target)) {
        searchResults.style.display = 'none';
    }
});

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    restoreSidebarState();
});
</script>