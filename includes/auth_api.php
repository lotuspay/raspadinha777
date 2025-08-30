<?php
// Inicia a sessão apenas se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    // Para requisições AJAX/API, retorna JSON em vez de redirecionar
    if (isset($_GET['action']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'erro' => 'Não autorizado']);
        exit();
    } else {
        // Para requisições normais, redireciona para login
        header("Location: ../login.php");
        exit();
    }
}
?>