<?php
session_start();
require '../includes/db.php';

// Configurar sessão de teste
$_SESSION['usuario_id'] = 1;
$stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = 1");
$stmt->execute();

echo "<h2>Debug do Fetch Issue</h2>";

// Query simples para testar
$query = "SELECT j.*, u.name FROM jogadas_raspadinha j JOIN users u ON j.usuario_id = u.id ORDER BY j.data_jogada DESC LIMIT 5";

echo "<h3>1. Query simples:</h3>";
echo "<pre>$query</pre>";

$result = $conn->query($query);
echo "Resultado: " . ($result ? "✅ OK" : "❌ ERRO") . "<br>";
echo "Num rows: " . $result->num_rows . "<br><br>";

echo "<h3>2. Testando fetch_assoc():</h3>";
if ($result->num_rows > 0) {
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $count++;
        echo "<strong>Registro $count:</strong><br>";
        echo "- ID: '" . $row['id'] . "'<br>";
        echo "- Nome: '" . $row['name'] . "'<br>";
        echo "- Valor Aposta: '" . $row['valor_aposta'] . "'<br>";
        echo "- Resultado: '" . $row['resultado'] . "'<br>";
        echo "- Prêmio: '" . $row['premio'] . "'<br>";
        echo "- Data: '" . $row['data_jogada'] . "'<br>";
        echo "- Array completo: " . print_r($row, true) . "<br><br>";
        
        if ($count >= 3) break;
    }
} else {
    echo "Nenhum registro encontrado!<br>";
}

// Testar se o problema é com prepared statements
echo "<h3>3. Testando com prepared statement:</h3>";
$stmt = $conn->prepare("SELECT j.*, u.name FROM jogadas_raspadinha j JOIN users u ON j.usuario_id = u.id ORDER BY j.data_jogada DESC LIMIT ?");
$limit = 3;
$stmt->bind_param('i', $limit);
$stmt->execute();
$result2 = $stmt->get_result();

echo "Resultado prepared: " . ($result2 ? "✅ OK" : "❌ ERRO") . "<br>";
echo "Num rows prepared: " . $result2->num_rows . "<br><br>";

if ($result2->num_rows > 0) {
    $count = 0;
    while ($row = $result2->fetch_assoc()) {
        $count++;
        echo "<strong>Prepared Registro $count:</strong><br>";
        echo "- ID: '" . $row['id'] . "'<br>";
        echo "- Nome: '" . $row['name'] . "'<br>";
        echo "- Valor Aposta: '" . $row['valor_aposta'] . "'<br><br>";
    }
}

// Verificar se há algum problema com o resultado sendo consumido
echo "<h3>4. Verificando se o resultado está sendo consumido:</h3>";
$stmt3 = $conn->prepare("SELECT j.*, u.name FROM jogadas_raspadinha j JOIN users u ON j.usuario_id = u.id ORDER BY j.data_jogada DESC LIMIT ?");
$limit = 2;
$stmt3->bind_param('i', $limit);
$stmt3->execute();
$result3 = $stmt3->get_result();

echo "Antes do primeiro fetch - num_rows: " . $result3->num_rows . "<br>";
$first_row = $result3->fetch_assoc();
echo "Após primeiro fetch - num_rows: " . $result3->num_rows . "<br>";
echo "Primeiro registro: " . print_r($first_row, true) . "<br>";

$second_row = $result3->fetch_assoc();
echo "Após segundo fetch - num_rows: " . $result3->num_rows . "<br>";
echo "Segundo registro: " . print_r($second_row, true) . "<br>";

$third_row = $result3->fetch_assoc();
echo "Após terceiro fetch - num_rows: " . $result3->num_rows . "<br>";
echo "Terceiro registro (deve ser null): " . print_r($third_row, true) . "<br>";

?>