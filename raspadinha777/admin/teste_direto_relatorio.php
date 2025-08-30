<?php
// Teste direto do relatório sem sessão HTTP
require '../includes/db.php';

// Simular dados de sessão diretamente
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
    echo "Usuário criado.<br>";
} else {
    // Tornar admin se já existir
    $stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    echo "Usuário configurado como admin.<br>";
}

// Verificar se é admin
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !$user['is_admin']) {
    echo "❌ Usuário não é admin";
    exit;
}

echo "✅ Usuário é admin.<br>";

// Testar query da tabela jogadas_raspadinha
$query = "
  SELECT j.*, u.name 
  FROM jogadas_raspadinha j 
  JOIN users u ON j.usuario_id = u.id 
  ORDER BY j.data_jogada DESC
  LIMIT 5
";

$result = $conn->query($query);

if ($result) {
    echo "✅ Query executada com sucesso. Registros encontrados: " . $result->num_rows . "<br>";
    
    if ($result->num_rows > 0) {
        echo "<h3>Primeiros 5 registros:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Usuário</th><th>Aposta</th><th>Resultado</th><th>Prêmio</th><th>Data</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>R$ " . number_format($row['valor_aposta'], 2, ',', '.') . "</td>";
            echo "<td>" . htmlspecialchars($row['resultado']) . "</td>";
            echo "<td>R$ " . number_format($row['premio'], 2, ',', '.') . "</td>";
            echo "<td>" . date('d/m/Y H:i:s', strtotime($row['data_jogada'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Nenhum registro encontrado.";
    }
} else {
    echo "❌ Erro na query: " . $conn->error;
}

// Testar estatísticas
echo "<h3>Testando estatísticas:</h3>";
$stats_query = "
  SELECT 
    COUNT(*) as total_jogadas,
    SUM(j.valor_aposta) as total_apostado,
    SUM(j.premio) as total_premios,
    SUM(CASE WHEN j.resultado = 'ganhou' THEN 1 ELSE 0 END) as total_vitorias
  FROM jogadas_raspadinha j 
  JOIN users u ON j.usuario_id = u.id
";

$stats_result = $conn->query($stats_query);
if ($stats_result) {
    $stats = $stats_result->fetch_assoc();
    echo "Total de jogadas: " . $stats['total_jogadas'] . "<br>";
    echo "Total apostado: R$ " . number_format($stats['total_apostado'], 2, ',', '.') . "<br>";
    echo "Total em prêmios: R$ " . number_format($stats['total_premios'], 2, ',', '.') . "<br>";
    echo "Total de vitórias: " . $stats['total_vitorias'] . "<br>";
    
    $lucro_casa = $stats['total_apostado'] - $stats['total_premios'];
    $taxa_vitoria = $stats['total_jogadas'] > 0 ? ($stats['total_vitorias'] / $stats['total_jogadas']) * 100 : 0;
    
    echo "Lucro da casa: R$ " . number_format($lucro_casa, 2, ',', '.') . "<br>";
    echo "Taxa de vitória: " . number_format($taxa_vitoria, 1) . "%<br>";
    
    echo "✅ Todas as queries funcionaram corretamente!";
} else {
    echo "❌ Erro na query de estatísticas: " . $conn->error;
}

echo "<br><br><a href='relatorio.php'>🔗 Ir para o relatório completo</a>";
?>