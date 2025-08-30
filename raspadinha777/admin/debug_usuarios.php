<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug - Tabela de Usuários</h2>";

// Incluir configuração
include 'config.php';

echo "<h3>1. Teste de Conexão:</h3>";
if ($conn->connect_error) {
    echo "<p style='color: red;'>Erro na conexão: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✓ Conexão OK!</p>";
}

echo "<h3>2. Teste de Consulta:</h3>";
$result = $conn->query("SELECT id, name, email, balance, is_admin FROM users ORDER BY id ASC LIMIT 5");

if (!$result) {
    echo "<p style='color: red;'>Erro na consulta: " . $conn->error . "</p>";
    exit;
}

echo "<p style='color: green;'>✓ Consulta executada com sucesso!</p>";
echo "<p>Número de registros encontrados: " . $result->num_rows . "</p>";

echo "<h3>3. Primeiros 5 usuários:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Saldo</th><th>Admin</th></tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>R$ " . number_format($row['balance'], 2, ',', '.') . "</td>";
    echo "<td>" . ($row['is_admin'] ? 'Sim' : 'Não') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>4. Estatísticas:</h3>";
$stats = $conn->query("SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN is_admin = 1 THEN 1 END) as total_admins,
    SUM(balance) as total_balance,
    AVG(balance) as avg_balance
    FROM users")->fetch_assoc();

echo "<ul>";
echo "<li>Total de usuários: " . $stats['total_users'] . "</li>";
echo "<li>Total de admins: " . $stats['total_admins'] . "</li>";
echo "<li>Saldo total: R$ " . number_format($stats['total_balance'], 2, ',', '.') . "</li>";
echo "<li>Saldo médio: R$ " . number_format($stats['avg_balance'], 2, ',', '.') . "</li>";
echo "</ul>";

echo "<h3>5. Teste de Sessão:</h3>";
session_start();
echo "<p>Status da sessão: " . (session_status() == PHP_SESSION_ACTIVE ? 'Ativa' : 'Inativa') . "</p>";
echo "<p>usuario_id na sessão: " . (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'Não definido') . "</p>";
echo "<p>is_admin na sessão: " . (isset($_SESSION['is_admin']) ? ($_SESSION['is_admin'] ? 'Sim' : 'Não') : 'Não definido') . "</p>";

echo "<hr>";
echo "<p><a href='usuarios.php'>← Voltar para usuarios.php</a></p>";
?>