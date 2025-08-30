<?php
session_start();
require '../includes/db.php';

// Configurar sessão de administrador
$_SESSION['usuario_id'] = 1;

// Verificar se o usuário existe e torná-lo admin
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Criar usuário se não existir
    $stmt = $conn->prepare("INSERT INTO users (id, name, email, password, is_admin) VALUES (?, 'Admin Test', 'admin@test.com', 'test123', 1)");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
} else {
    // Tornar admin se já existir
    $stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
}

echo "Sessão configurada. <a href='relatorio.php'>Acessar Relatório</a>";
?>