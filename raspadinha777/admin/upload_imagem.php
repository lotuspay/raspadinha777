<?php
require_once '../includes/db.php';
// require_once '../includes/auth.php'; // Temporariamente comentado para teste

header('Content-Type: application/json');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se um arquivo foi enviado
if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo foi enviado ou ocorreu um erro']);
    exit;
}

$arquivo = $_FILES['imagem'];
$tipo = $_POST['tipo'] ?? ''; // 'banner' ou 'card'

// Validar tipo
if (!in_array($tipo, ['banner', 'card'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de imagem inválido']);
    exit;
}

// Validar extensão do arquivo
$extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

if (!in_array($extensao, $extensoesPermitidas)) {
    echo json_encode(['success' => false, 'message' => 'Extensão não permitida. Use: ' . implode(', ', $extensoesPermitidas)]);
    exit;
}

// Validar tamanho do arquivo (máximo 5MB)
if ($arquivo['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 5MB']);
    exit;
}

// Validar se é realmente uma imagem
$infoImagem = getimagesize($arquivo['tmp_name']);
if ($infoImagem === false) {
    echo json_encode(['success' => false, 'message' => 'Arquivo não é uma imagem válida']);
    exit;
}

// Criar diretórios se não existirem
$diretorioBase = '../uploads/raspadinhas';
$diretorioTipo = $diretorioBase . '/' . $tipo . 's';

if (!is_dir($diretorioBase)) {
    mkdir($diretorioBase, 0755, true);
}

if (!is_dir($diretorioTipo)) {
    mkdir($diretorioTipo, 0755, true);
}

// Gerar nome único para o arquivo
$nomeArquivo = uniqid($tipo . '_') . '.' . $extensao;
$caminhoCompleto = $diretorioTipo . '/' . $nomeArquivo;
$caminhoRelativo = 'uploads/raspadinhas/' . $tipo . 's/' . $nomeArquivo;

// Mover arquivo para o destino
if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
    // Otimizar imagem se for JPEG ou PNG
    otimizarImagem($caminhoCompleto, $infoImagem[2]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Imagem enviada com sucesso',
        'caminho' => $caminhoRelativo,
        'nome_arquivo' => $nomeArquivo
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o arquivo']);
}

/**
 * Otimiza a imagem reduzindo a qualidade para economizar espaço
 */
function otimizarImagem($caminho, $tipoImagem) {
    $qualidade = 85; // Qualidade para JPEG
    
    switch ($tipoImagem) {
        case IMAGETYPE_JPEG:
            $imagem = imagecreatefromjpeg($caminho);
            if ($imagem) {
                imagejpeg($imagem, $caminho, $qualidade);
                imagedestroy($imagem);
            }
            break;
            
        case IMAGETYPE_PNG:
            $imagem = imagecreatefrompng($caminho);
            if ($imagem) {
                // Para PNG, usar compressão nível 6 (0-9, onde 9 é máxima compressão)
                imagepng($imagem, $caminho, 6);
                imagedestroy($imagem);
            }
            break;
    }
}
?>