<?php
require_once 'includes/db.php';

// Nova senha para o admin
$nova_senha = 'admin123';
$senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

// Atualizar senha do admin
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'admin@admin.com'");
$stmt->bind_param("s", $senha_hash);

if ($stmt->execute()) {
    echo "<h3 style='color: green;'>Senha do administrador atualizada com sucesso!</h3>";
    echo "<p><strong>Email:</strong> admin@admin.com</p>";
    echo "<p><strong>Nova senha:</strong> " . $nova_senha . "</p>";
    echo "<p><a href='admin/login.php'>Fazer login agora</a></p>";
} else {
    echo "<h3 style='color: red;'>Erro ao atualizar senha: " . $conn->error . "</h3>";
}

$stmt->close();
$conn->close();
?>