<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

echo "<h1>Teste de Debug</h1>";
echo "<p>Sessão ativa: " . (isset($_SESSION['usuario_id']) ? 'SIM' : 'NÃO') . "</p>";
echo "<p>Usuário ID: " . ($_SESSION['usuario_id'] ?? 'N/A') . "</p>";
echo "<p>Método HTTP: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>Content-Type: text/html</p>";

if (isset($_SESSION['usuario_id'])) {
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo "<p>É admin: " . ($user && $user['is_admin'] ? 'SIM' : 'NÃO') . "</p>";
}

echo "<p>Teste concluído com sucesso!</p>";
?>