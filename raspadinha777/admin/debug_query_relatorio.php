<?php
session_start();
require '../includes/db.php';

// Configurar sessão de teste
$_SESSION['usuario_id'] = 1;
$stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = 1");
$stmt->execute();

echo "<h2>Debug da Query do Relatório</h2>";

// Simular os mesmos parâmetros do relatório
$page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = '';
$date_from = '';
$date_to = '';
$resultado_filter = '';

// Construir query com filtros (igual ao relatório)
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

echo "<h3>1. Parâmetros:</h3>";
echo "Page: $page<br>";
echo "Limit: $limit<br>";
echo "Offset: $offset<br>";
echo "Where clause: '$where_clause'<br>";
echo "Types: '$types'<br>";
echo "Params: " . print_r($params, true) . "<br><br>";

// Query principal (igual ao relatório)
$query = "
  SELECT j.*, u.name 
  FROM jogadas_raspadinha j 
  JOIN users u ON j.usuario_id = u.id 
  $where_clause
  ORDER BY j.data_jogada DESC
  LIMIT ? OFFSET ?
";

echo "<h3>2. Query:</h3>";
echo "<pre>$query</pre>";

echo "<h3>3. Executando query...</h3>";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    echo "Binding params com types: '$types'<br>";
    echo "Params finais: " . print_r($params, true) . "<br>";
    $stmt->bind_param($types, ...$params);
} else {
    echo "Binding apenas limit e offset<br>";
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

echo "<h3>4. Resultado:</h3>";
echo "Número de linhas: " . $result->num_rows . "<br><br>";

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Usuário</th>";
    echo "<th style='padding: 8px;'>Aposta</th>";
    echo "<th style='padding: 8px;'>Resultado</th>";
    echo "<th style='padding: 8px;'>Prêmio</th>";
    echo "<th style='padding: 8px;'>Data</th>";
    echo "</tr>";
    
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $count++;
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$row['id']}</td>";
        echo "<td style='padding: 8px;'>{$row['name']}</td>";
        echo "<td style='padding: 8px;'>R$ " . number_format($row['valor_aposta'], 2, ',', '.') . "</td>";
        echo "<td style='padding: 8px;'>{$row['resultado']}</td>";
        echo "<td style='padding: 8px;'>R$ " . number_format($row['premio'], 2, ',', '.') . "</td>";
        echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($row['data_jogada'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<br>Total de registros exibidos: $count";
} else {
    echo "<strong style='color: red;'>NENHUM REGISTRO ENCONTRADO!</strong>";
}

// Testar contagem
echo "<h3>5. Teste de contagem:</h3>";
$count_query = "
  SELECT COUNT(*) as total
  FROM jogadas_raspadinha j 
  JOIN users u ON j.usuario_id = u.id 
  $where_clause
";

echo "Query de contagem: <pre>$count_query</pre>";

if (!empty($where_conditions)) {
    $count_stmt = $conn->prepare($count_query);
    $count_types = substr($types, 0, -2); // Remove os últimos 'ii' do limit/offset
    $count_params = array_slice($params, 0, -2); // Remove limit/offset
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_records = $conn->query($count_query)->fetch_assoc()['total'];
}

echo "Total de registros: $total_records<br>";

// Verificar se há erro no MySQL
if ($conn->error) {
    echo "<h3 style='color: red;'>6. Erro MySQL:</h3>";
    echo $conn->error;
}

?>