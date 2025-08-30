<?php
session_start();
require '../includes/db.php';

// Configurar sessão de teste
$_SESSION['usuario_id'] = 1;
$stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = 1");
$stmt->execute();

echo "<h2>Debug da Variável \$result</h2>";

// Reproduzir exatamente o mesmo código do relatorio.php
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$resultado_filter = isset($_GET['resultado']) ? $_GET['resultado'] : '';

$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "u.name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(j.data_jogada) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(j.data_jogada) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if ($resultado_filter !== '') {
    $where_conditions[] = "j.resultado = ?";
    $params[] = $resultado_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query principal
$query = "
  SELECT j.*, u.name 
  FROM jogadas_raspadinha j 
  JOIN users u ON j.usuario_id = u.id 
  $where_clause
  ORDER BY j.data_jogada DESC
  LIMIT ? OFFSET ?
";

echo "<h3>1. Preparando query...</h3>";
echo "Query: <pre>$query</pre>";

$stmt = $conn->prepare($query);
echo "Stmt preparado: " . ($stmt ? "✅ OK" : "❌ ERRO") . "<br>";

if (!empty($params)) {
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    echo "Binding params: types='$types', params=" . print_r($params, true) . "<br>";
    $stmt->bind_param($types, ...$params);
} else {
    echo "Binding apenas limit e offset<br>";
    $stmt->bind_param('ii', $limit, $offset);
}

echo "<h3>2. Executando query...</h3>";
$execute_result = $stmt->execute();
echo "Execute result: " . ($execute_result ? "✅ OK" : "❌ ERRO") . "<br>";

if (!$execute_result) {
    echo "Erro na execução: " . $stmt->error . "<br>";
}

echo "<h3>3. Obtendo resultado...</h3>";
$result = $stmt->get_result();
echo "\$result type: " . gettype($result) . "<br>";
echo "\$result class: " . get_class($result) . "<br>";
echo "\$result is object: " . (is_object($result) ? "✅ SIM" : "❌ NÃO") . "<br>";
echo "\$result is null: " . (is_null($result) ? "❌ SIM" : "✅ NÃO") . "<br>";
echo "\$result is false: " . ($result === false ? "❌ SIM" : "✅ NÃO") . "<br>";

if ($result) {
    echo "\$result->num_rows: " . $result->num_rows . "<br>";
    echo "Condição (\$result && \$result->num_rows > 0): " . (($result && $result->num_rows > 0) ? "✅ TRUE" : "❌ FALSE") . "<br>";
    
    if ($result->num_rows > 0) {
        echo "<h3>4. Primeiros registros:</h3>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 3) {
            echo "Registro " . ($count + 1) . ": ID=" . $row['id'] . ", Nome=" . $row['name'] . "<br>";
            $count++;
        }
    } else {
        echo "<h3>4. Nenhum registro encontrado</h3>";
    }
} else {
    echo "<h3>4. \$result é null ou false!</h3>";
    echo "Erro MySQL: " . $conn->error . "<br>";
}

?>