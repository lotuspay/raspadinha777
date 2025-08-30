<?php
require_once 'includes/db.php';

// Verificar senha do admin
$stmt = $conn->prepare("SELECT id, email, password, is_admin FROM users WHERE email = 'admin@admin.com'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<h3>Dados do Admin:</h3>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Password Hash: " . $user['password'] . "<br>";
    echo "Is Admin: " . $user['is_admin'] . "<br><br>";
    
    // Testar senhas comuns
    $senhas_teste = ['admin', '123456', 'password', 'admin123', '12345678'];
    
    echo "<h3>Teste de Senhas:</h3>";
    foreach ($senhas_teste as $senha) {
        if (password_verify($senha, $user['password'])) {
            echo "<strong style='color: green;'>SENHA ENCONTRADA: " . $senha . "</strong><br>";
        } else {
            echo "Senha '" . $senha . "' não confere<br>";
        }
    }
    
    // Verificar se a senha está em texto plano
    if ($user['password'] === 'admin' || $user['password'] === '123456') {
        echo "<br><strong style='color: orange;'>ATENÇÃO: Senha em texto plano detectada!</strong><br>";
    }
} else {
    echo "Usuário admin@admin.com não encontrado!";
}
?>