<?php
require '../includes/db.php';

echo "<h2>Verificando tabela 'jogadas_raspadinha':</h2>";

// Verificar se a tabela existe
$result = $conn->query("SHOW TABLES LIKE 'jogadas_raspadinha'");
if ($result->num_rows > 0) {
    echo "<p>✅ Tabela 'jogadas_raspadinha' encontrada!</p>";
    
    // Mostrar estrutura
    echo "<h3>Estrutura da tabela:</h3>";
    $result = $conn->query("DESCRIBE jogadas_raspadinha");
    echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td><td>{$row['Extra']}</td></tr>";
    }
    echo "</table>";
    
    // Contar registros
    $result = $conn->query("SELECT COUNT(*) as total FROM jogadas_raspadinha");
    $total = $result->fetch_assoc()['total'];
    echo "<p><strong>Total de registros:</strong> {$total}</p>";
    
    // Mostrar primeiros 5 registros
    if ($total > 0) {
        echo "<h3>Primeiros 5 registros:</h3>";
        $result = $conn->query("SELECT * FROM jogadas_raspadinha ORDER BY id DESC LIMIT 5");
        if ($result->num_rows > 0) {
            echo "<table border='1'><tr>";
            // Cabeçalhos
            $fields = $result->fetch_fields();
            foreach ($fields as $field) {
                echo "<th>{$field->name}</th>";
            }
            echo "</tr>";
            
            // Reset result pointer
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }
} else {
    echo "<p>❌ Tabela 'jogadas_raspadinha' NÃO encontrada!</p>";
    
    // Listar todas as tabelas disponíveis
    echo "<h3>Tabelas disponíveis no banco:</h3>";
    $result = $conn->query("SHOW TABLES");
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>{$row[0]}</li>";
    }
    echo "</ul>";
}
?>