<?php
require_once '../includes/db.php';

echo "<h1>Debug Simples - Verificação de Usuários</h1>";
echo "<style>body{background:#1a1a1a;color:#fff;font-family:Arial;}</style>";

// 1. Verificar conexão
if (!$conn) {
    die("❌ Erro na conexão: " . mysqli_connect_error());
}
echo "✅ Conexão OK<br><br>";

// 2. Contar usuários total
$total = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
echo "📊 Total de usuários na tabela: <strong>$total</strong><br><br>";

if ($total == 0) {
    echo "❌ Tabela users está vazia!<br>";
    exit();
}

// 3. Testar consulta exata do usuarios.php
$page = 1;
$limit = 10;
$offset = 0;
$search = '';
$whereClause = '';
$params = [];
$types = '';

$sql = "SELECT id, name, email, balance, is_admin FROM users $whereClause ORDER BY id ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

echo "🔍 Testando consulta:<br>";
echo "<strong>SQL:</strong> $sql<br>";
echo "<strong>Tipos:</strong> $types<br>";
echo "<strong>Parâmetros:</strong> " . implode(', ', $params) . "<br><br>";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("❌ Erro no prepare: " . $conn->error);
}

$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    die("❌ Erro no execute: " . $stmt->error);
}

$result = $stmt->get_result();
echo "📋 Resultados encontrados: <strong>" . $result->num_rows . "</strong><br><br>";

if ($result->num_rows > 0) {
    echo "✅ Usuários encontrados:<br>";
    echo "<table border='1' style='border-collapse:collapse;margin-top:10px;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Saldo</th><th>Admin</th></tr>";
    
    while ($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>R$ " . number_format($user['balance'], 2, ',', '.') . "</td>";
        echo "<td>" . ($user['is_admin'] ? 'Sim' : 'Não') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Nenhum usuário retornado pela consulta!<br>";
    echo "🔧 Possíveis causas:<br>";
    echo "- Problema nos parâmetros de paginação<br>";
    echo "- Erro na consulta SQL<br>";
    echo "- Problema no bind_param<br>";
}

echo "<br><br><a href='usuarios.php' style='color:#4a9eff;'>← Voltar para usuarios.php</a>";
?>