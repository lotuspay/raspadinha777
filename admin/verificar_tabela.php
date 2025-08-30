<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'raspadinha';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

echo "<h2>Estrutura da tabela 'jogadas':</h2>";

// Verificar estrutura da tabela jogadas
$result = $conn->query("DESCRIBE jogadas");

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Erro ao verificar estrutura: " . $conn->error;
}

echo "<br><br><h2>Primeiros 5 registros da tabela 'jogadas':</h2>";

// Mostrar alguns registros para entender a estrutura
$result = $conn->query("SELECT * FROM jogadas LIMIT 5");

if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    
    // Cabeçalho
    $first_row = $result->fetch_assoc();
    echo "<tr>";
    foreach ($first_row as $key => $value) {
        echo "<th>" . $key . "</th>";
    }
    echo "</tr>";
    
    // Primeira linha
    echo "<tr>";
    foreach ($first_row as $value) {
        echo "<td>" . htmlspecialchars($value) . "</td>";
    }
    echo "</tr>";
    
    // Outras linhas
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Nenhum registro encontrado ou erro: " . $conn->error;
}

$conn->close();
?>