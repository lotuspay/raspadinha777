<?php
require_once 'includes/db.php';

echo "<h3>Estrutura da tabela 'users':</h3>";

// Verificar estrutura da tabela
$result = $conn->query("DESCRIBE users");

if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
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

echo "<hr>";
echo "<h3>Primeiros 5 registros da tabela 'users':</h3>";

// Mostrar alguns registros para ver os dados disponíveis
$result = $conn->query("SELECT * FROM users LIMIT 5");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    
    // Cabeçalho
    $first_row = $result->fetch_assoc();
    echo "<tr>";
    foreach ($first_row as $key => $value) {
        echo "<th>" . $key . "</th>";
    }
    echo "</tr>";
    
    // Primeira linha
    echo "<tr>";
    foreach ($first_row as $key => $value) {
        echo "<td>" . $value . "</td>";
    }
    echo "</tr>";
    
    // Outras linhas
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . $value . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Nenhum registro encontrado ou erro: " . $conn->error;
}
?>