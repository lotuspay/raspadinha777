<?php
// Verificar usuários administradores
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db = 'raspadinha';
$user = 'raspadinha';
$pass = 'raspadinha';

echo "<h1>Verificação de Usuários Administradores</h1>";
echo "<style>body{font-family:Arial;margin:20px;background:#1a1a1a;color:#fff;} .error{color:#ff5757;} .success{color:#00d4aa;} table{width:100%;border-collapse:collapse;background:#2d2d2d;} th,td{border:1px solid #404040;padding:8px;text-align:left;} th{background:#404040;}</style>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Todos os Usuários com Status de Admin</h2>";
    $stmt = $pdo->query("SELECT id, name, email, balance, is_admin, password FROM users ORDER BY is_admin DESC, id ASC");
    $users = $stmt->fetchAll();
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Saldo</th><th>É Admin</th><th>Senha (hash)</th></tr>";
    
    $adminCount = 0;
    foreach($users as $user) {
        $isAdmin = $user['is_admin'] ? 'SIM' : 'Não';
        $rowStyle = $user['is_admin'] ? 'background:#004d00;' : '';
        if($user['is_admin']) $adminCount++;
        
        echo "<tr style='$rowStyle'>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>R$ " . @number_format($user['balance'], 2, ',', '.') . "</td>";
        echo "<td><strong>$isAdmin</strong></td>";
        echo "<td>" . substr($user['password'], 0, 20) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Resumo</h2>";
    echo "<p class='success'>Total de usuários: " . count($users) . "</p>";
    echo "<p class='success'>Total de administradores: $adminCount</p>";
    
    if($adminCount == 0) {
        echo "<p class='error'>⚠️ PROBLEMA ENCONTRADO: Não há usuários administradores no sistema!</p>";
        echo "<p>Para resolver, você pode:</p>";
        echo "<ol>";
        echo "<li>Promover um usuário existente a admin</li>";
        echo "<li>Criar um novo usuário admin</li>";
        echo "</ol>";
        
        echo "<h3>Promover usuário 'admin' para administrador:</h3>";
        echo "<form method='post'>";
        echo "<button type='submit' name='promote_admin' style='background:#00d4aa;color:#000;padding:10px;border:none;cursor:pointer;'>Promover usuário 'admin' para administrador</button>";
        echo "</form>";
    }
    
    // Processar promoção
    if(isset($_POST['promote_admin'])) {
        $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE name = 'admin'");
        if($stmt->execute()) {
            echo "<p class='success'>✓ Usuário 'admin' promovido para administrador com sucesso!</p>";
            echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
        } else {
            echo "<p class='error'>✗ Erro ao promover usuário</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "<p class='error'>Erro: " . $e->getMessage() . "</p>";
}
?>