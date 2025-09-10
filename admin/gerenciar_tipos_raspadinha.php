<?php
require_once '../includes/db.php';
// require_once '../includes/auth.php'; // Temporariamente comentado para teste

// Verificar se é uma requisição GET para API
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    
    switch ($action) {
        case 'verificar_tabela':
            try {
                $result = $conn->query("SELECT COUNT(*) as total FROM tipos_raspadinha");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo json_encode([
                        'existe' => true,
                        'total_tipos' => $row['total']
                    ]);
                } else {
                    echo json_encode(['existe' => false]);
                }
            } catch (Exception $e) {
                echo json_encode(['existe' => false, 'erro' => $e->getMessage()]);
            }
            exit;
            
        case 'estatisticas':
            try {
                $total_result = $conn->query("SELECT COUNT(*) as total FROM tipos_raspadinha");
                $ativos_result = $conn->query("SELECT COUNT(*) as ativos FROM tipos_raspadinha WHERE ativo = 1");
                
                $total = $total_result ? $total_result->fetch_assoc()['total'] : 0;
                $ativos = $ativos_result ? $ativos_result->fetch_assoc()['ativos'] : 0;
                
                echo json_encode([
                    'success' => true,
                    'total' => $total,
                    'ativos' => $ativos
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'erro' => $e->getMessage()]);
            }
            exit;
    }
}

// Verificar se é uma requisição AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $nome = $_POST['nome'] ?? '';
            $premio_maximo = floatval($_POST['premio_maximo'] ?? 0);
            $chance_base = floatval($_POST['chance_base'] ?? 0);
            $valor_aposta_padrao = floatval($_POST['valor_aposta_padrao'] ?? 0);
            $banner_personalizado = !empty($_POST['banner_personalizado']) ? $_POST['banner_personalizado'] : null;
            $cor_badge = (!empty($_POST['cor_badge']) && $_POST['cor_badge'] !== '#000000') ? $_POST['cor_badge'] : null;
            $imagem_card = !empty($_POST['imagem_card']) ? $_POST['imagem_card'] : null;
            $descricao_curta = $_POST['descricao_curta'] ?? null;
            $ordem_exibicao = intval($_POST['ordem_exibicao'] ?? 0);
            $exibir_carrossel = isset($_POST['exibir_carrossel']) ? 1 : 0;
            
            if (empty($nome) || $premio_maximo <= 0 || $chance_base <= 0 || $valor_aposta_padrao <= 0) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser válidos']);
                exit;
            }
            
            $stmt = $conn->prepare("INSERT INTO tipos_raspadinha (nome, premio_maximo, chance_base, valor_aposta_padrao, banner_personalizado, cor_badge, imagem_card, descricao_curta, ordem_exibicao, exibir_carrossel) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdddssssii", $nome, $premio_maximo, $chance_base, $valor_aposta_padrao, $banner_personalizado, $cor_badge, $imagem_card, $descricao_curta, $ordem_exibicao, $exibir_carrossel);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Tipo de raspadinha criado com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao criar tipo de raspadinha']);
            }
            break;
            
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $nome = $_POST['nome'] ?? '';
            $premio_maximo = floatval($_POST['premio_maximo'] ?? 0);
            $chance_base = floatval($_POST['chance_base'] ?? 0);
            $valor_aposta_padrao = floatval($_POST['valor_aposta_padrao'] ?? 0);
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            $banner_personalizado = !empty($_POST['banner_personalizado']) ? $_POST['banner_personalizado'] : null;
            $cor_badge = (!empty($_POST['cor_badge']) && $_POST['cor_badge'] !== '#000000') ? $_POST['cor_badge'] : null;
            $imagem_card = !empty($_POST['imagem_card']) ? $_POST['imagem_card'] : null;
            $descricao_curta = $_POST['descricao_curta'] ?? null;
            $ordem_exibicao = intval($_POST['ordem_exibicao'] ?? 0);
            $exibir_carrossel = isset($_POST['exibir_carrossel']) ? 1 : 0;
            
            if ($id <= 0 || empty($nome) || $premio_maximo <= 0 || $chance_base <= 0 || $valor_aposta_padrao <= 0) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                exit;
            }
            
            $stmt = $conn->prepare("UPDATE tipos_raspadinha SET nome = ?, premio_maximo = ?, chance_base = ?, valor_aposta_padrao = ?, ativo = ?, banner_personalizado = ?, cor_badge = ?, imagem_card = ?, descricao_curta = ?, ordem_exibicao = ?, exibir_carrossel = ? WHERE id = ?");
            $stmt->bind_param("sdddisissiii", $nome, $premio_maximo, $chance_base, $valor_aposta_padrao, $ativo, $banner_personalizado, $cor_badge, $imagem_card, $descricao_curta, $ordem_exibicao, $exibir_carrossel, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Tipo de raspadinha atualizado com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar tipo de raspadinha']);
            }
            break;
            
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }
            
            $stmt = $conn->prepare("DELETE FROM tipos_raspadinha WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Tipo de raspadinha excluído com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao excluir tipo de raspadinha']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
    exit;
}

// Buscar todos os tipos
$result = $conn->query("SELECT * FROM tipos_raspadinha ORDER BY nome");
$tipos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Tipos de Raspadinha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .upload-container {
            position: relative;
        }
        
        .image-preview {
            padding: 10px;
            border: 1px dashed #dee2e6;
            border-radius: 0.375rem;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .image-preview img {
            border-radius: 0.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .upload-container input[type="file"] {
            transition: border-color 0.15s ease-in-out;
        }
        
        .upload-container input[type="file"]:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge {
            font-size: 0.75em;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-cogs"></i> Gerenciar Tipos de Raspadinha</h2>
                    <a href="controle_raspadinha.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Formulário para criar novo tipo -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> Criar Novo Tipo</h5>
                    </div>
                    <div class="card-body">
                        <form id="createForm">
                            <input type="hidden" name="action" value="create">
                            
                            <!-- Campos Básicos -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" name="nome" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Prêmio Máximo (R$) *</label>
                                    <input type="number" name="premio_maximo" class="form-control" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Chance Base (0-1) *</label>
                                    <input type="number" name="chance_base" class="form-control" step="0.0001" min="0" max="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Valor Padrão (R$) *</label>
                                    <input type="number" name="valor_aposta_padrao" class="form-control" step="0.01" required>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Ordem</label>
                                    <input type="number" name="ordem_exibicao" class="form-control" min="0" placeholder="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input type="checkbox" name="ativo" class="form-check-input" checked>
                                        <label class="form-check-label">Ativo</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="exibir_carrossel" class="form-check-input" checked>
                                        <label class="form-check-label">Carrossel</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Campos de Personalização -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Banner Personalizado</label>
                                    <div class="upload-container">
                                        <input type="file" id="bannerUpload" class="form-control" accept="image/*" onchange="uploadImagem(this, 'banner', 'bannerPreview', 'banner_personalizado')">
                                        <input type="hidden" name="banner_personalizado" id="banner_personalizado">
                                        <div id="bannerPreview" class="image-preview mt-2" style="display: none;">
                                            <img src="" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 60px;">
                                            <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removerImagem('bannerUpload', 'bannerPreview', 'banner_personalizado')">×</button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Formatos: JPG, PNG, WEBP (máx. 5MB)</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Cor do Badge</label>
                                    <input type="color" name="cor_badge" class="form-control form-control-color">
                                    <small class="text-muted">Cor em hexadecimal</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Imagem do Card</label>
                                    <div class="upload-container">
                                        <input type="file" id="cardUpload" class="form-control" accept="image/*" onchange="uploadImagem(this, 'card', 'cardPreview', 'imagem_card')">
                                        <input type="hidden" name="imagem_card" id="imagem_card">
                                        <div id="cardPreview" class="image-preview mt-2" style="display: none;">
                                            <img src="" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 60px;">
                                            <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removerImagem('cardUpload', 'cardPreview', 'imagem_card')">×</button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Imagem para o card da raspadinha</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Descrição Curta</label>
                                    <textarea name="descricao_curta" class="form-control" rows="2" placeholder="Descrição promocional..."></textarea>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Criar Tipo de Raspadinha
                                    </button>
                                    <small class="text-muted ms-3">* Campos obrigatórios</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de tipos existentes -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Tipos Existentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Prêmio Máximo</th>
                                        <th>Chance Base</th>
                                        <th>Valor Padrão</th>
                                        <th>Banner</th>
                                        <th>Card</th>
                                        <th>Cor</th>
                                        <th>Ordem</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tipos as $tipo): ?>
                                        <tr>
                                            <td><?php echo $tipo['id']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($tipo['nome']); ?>
                                                <?php if (!empty($tipo['descricao_curta'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($tipo['descricao_curta']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>R$ <?php echo @number_format($tipo['premio_maximo'], 2, ',', '.'); ?></td>
                                            <td><?php echo @number_format($tipo['chance_base'] * 100, 2, ',', '.'); ?>%</td>
                                            <td>R$ <?php echo @number_format($tipo['valor_aposta_padrao'], 2, ',', '.'); ?></td>
                                            <td>
                                <?php if (!empty($tipo['banner_personalizado'])): ?>
                                    <span class="badge bg-info">Personalizado</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Padrão</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($tipo['imagem_card'])): ?>
                                    <span class="badge bg-success">Personalizado</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Padrão</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($tipo['cor_badge'])): ?>
                                    <span class="badge" style="background-color: <?php echo htmlspecialchars($tipo['cor_badge']); ?>; color: white;">
                                        <?php echo htmlspecialchars($tipo['cor_badge']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">Padrão</span>
                                <?php endif; ?>
                            </td>
                                            <td><?php echo $tipo['ordem_exibicao'] ?: 'Auto'; ?></td>
                                            <td>
                                                <?php if ($tipo['ativo']): ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inativo</span>
                                                <?php endif; ?>
                                                <?php if ($tipo['exibir_carrossel']): ?>
                                                    <span class="badge bg-primary ms-1">Carrossel</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="editarTipo(<?php echo htmlspecialchars(json_encode($tipo)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="excluirTipo(<?php echo $tipo['id']; ?>, '<?php echo htmlspecialchars($tipo['nome']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para editar tipo -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Tipo de Raspadinha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        
                        <!-- Campos Básicos -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome *</label>
                                <input type="text" name="nome" id="editNome" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prêmio Máximo (R$) *</label>
                                <input type="number" name="premio_maximo" id="editPremioMaximo" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Chance Base (0-1) *</label>
                                <input type="number" name="chance_base" id="editChanceBase" class="form-control" step="0.0001" min="0" max="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Valor Padrão (R$) *</label>
                                <input type="number" name="valor_aposta_padrao" id="editValorPadrao" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        
                        <!-- Campos de Personalização -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Banner Personalizado</label>
                                <div class="upload-container">
                                    <input type="file" id="editBannerUpload" class="form-control" accept="image/*" onchange="uploadImagem(this, 'banner', 'editBannerPreview', 'editBannerPersonalizado')">
                                    <input type="hidden" name="banner_personalizado" id="editBannerPersonalizado">
                                    <div id="editBannerPreview" class="image-preview mt-2" style="display: none;">
                                        <img src="" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 60px;">
                                        <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removerImagem('editBannerUpload', 'editBannerPreview', 'editBannerPersonalizado')">×</button>
                                    </div>
                                </div>
                                <small class="text-muted">Formatos: JPG, PNG, WEBP (máx. 5MB)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Imagem do Card</label>
                                <div class="upload-container">
                                    <input type="file" id="editCardUpload" class="form-control" accept="image/*" onchange="uploadImagem(this, 'card', 'editCardPreview', 'editImagemCard')">
                                    <input type="hidden" name="imagem_card" id="editImagemCard">
                                    <div id="editCardPreview" class="image-preview mt-2" style="display: none;">
                                        <img src="" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 60px;">
                                        <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removerImagem('editCardUpload', 'editCardPreview', 'editImagemCard')">×</button>
                                    </div>
                                </div>
                                <small class="text-muted">Imagem para o card da raspadinha</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Cor do Badge</label>
                                <input type="color" name="cor_badge" id="editCorBadge" class="form-control form-control-color">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ordem de Exibição</label>
                                <input type="number" name="ordem_exibicao" id="editOrdemExibicao" class="form-control" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Configurações</label>
                                <div class="form-check">
                                    <input type="checkbox" name="ativo" id="editAtivo" class="form-check-input">
                                    <label class="form-check-label">Ativo</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="exibir_carrossel" id="editExibirCarrossel" class="form-check-input">
                                    <label class="form-check-label">Exibir no Carrossel</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descrição Curta</label>
                            <textarea name="descricao_curta" id="editDescricaoCurta" class="form-control" rows="2" placeholder="Descrição promocional..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Form para excluir -->
    <form method="POST" id="deleteForm" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarTipo(tipo) {
            // Campos básicos
            document.getElementById('editId').value = tipo.id;
            document.getElementById('editNome').value = tipo.nome;
            document.getElementById('editPremioMaximo').value = tipo.premio_maximo;
            document.getElementById('editChanceBase').value = tipo.chance_base;
            document.getElementById('editValorPadrao').value = tipo.valor_aposta_padrao;
            document.getElementById('editAtivo').checked = tipo.ativo == 1;
            
            // Campos de personalização
            document.getElementById('editBannerPersonalizado').value = tipo.banner_personalizado || '';
            document.getElementById('editCorBadge').value = tipo.cor_badge || '#000000';
            document.getElementById('editImagemCard').value = tipo.imagem_card || '';
            document.getElementById('editDescricaoCurta').value = tipo.descricao_curta || '';
            document.getElementById('editOrdemExibicao').value = tipo.ordem_exibicao || 0;
            document.getElementById('editExibirCarrossel').checked = tipo.exibir_carrossel == 1;
            
            // Mostrar previews das imagens existentes
            if (tipo.banner_personalizado) {
                mostrarPreviewExistente('editBannerPreview', tipo.banner_personalizado);
            }
            if (tipo.imagem_card) {
                mostrarPreviewExistente('editCardPreview', tipo.imagem_card);
            }
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        function excluirTipo(id, nome) {
            if (confirm(`Tem certeza que deseja excluir o tipo "${nome}"?\n\nEsta ação não pode ser desfeita!`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
        
        function uploadImagem(input, tipo, previewId, hiddenInputId) {
            const arquivo = input.files[0];
            if (!arquivo) return;
            
            // Validar tamanho (5MB)
            if (arquivo.size > 5 * 1024 * 1024) {
                alert('Arquivo muito grande. Máximo 5MB.');
                input.value = '';
                return;
            }
            
            // Validar tipo
            const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
            if (!tiposPermitidos.includes(arquivo.type)) {
                alert('Tipo de arquivo não permitido. Use: JPG, PNG, WEBP ou GIF.');
                input.value = '';
                return;
            }
            
            // Mostrar loading
            const previewDiv = document.getElementById(previewId);
            previewDiv.style.display = 'block';
            previewDiv.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Enviando...';
            
            // Criar FormData
            const formData = new FormData();
            formData.append('imagem', arquivo);
            formData.append('tipo', tipo);
            
            // Enviar arquivo
            fetch('upload_imagem.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar campo hidden
                    document.getElementById(hiddenInputId).value = data.caminho;
                    
                    // Mostrar preview
                    previewDiv.innerHTML = `
                        <img src="../${data.caminho}" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 60px;">
                        <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removerImagem('${input.id}', '${previewId}', '${hiddenInputId}')">×</button>
                    `;
                } else {
                    alert('Erro ao enviar imagem: ' + data.message);
                    previewDiv.style.display = 'none';
                    input.value = '';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar imagem.');
                previewDiv.style.display = 'none';
                input.value = '';
            });
        }
        
        function removerImagem(inputId, previewId, hiddenInputId) {
            document.getElementById(inputId).value = '';
            document.getElementById(previewId).style.display = 'none';
            document.getElementById(hiddenInputId).value = '';
        }
        
        function mostrarPreviewExistente(previewId, caminho) {
            const previewDiv = document.getElementById(previewId);
            previewDiv.style.display = 'block';
            previewDiv.innerHTML = `
                <img src="../${caminho}" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 60px;">
                <span class="badge bg-info ms-2">Atual</span>
            `;
        }
        
        // Processar formulário de criação
        document.getElementById('createForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Tipo de raspadinha criado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao criar tipo de raspadinha.');
            });
        });
        
        // Processar formulário de edição
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Tipo de raspadinha atualizado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar tipo de raspadinha.');
            });
        });
    </script>
</body>
</html>