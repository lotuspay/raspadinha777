<?php
session_start();
require '../includes/db.php';

// Configurar sessão de teste
$_SESSION['usuario_id'] = 1;
$stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = 1");
$stmt->execute();

echo "<h2>Debug do Relatório</h2>";

// 1. Verificar estrutura da tabela
echo "<h3>1. Estrutura da tabela jogadas_raspadinha:</h3>";
$result = $conn->query("DESCRIBE jogadas_raspadinha");
echo "<table border='1'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// 2. Contar registros
echo "<h3>2. Total de registros:</h3>";
$count = $conn->query("SELECT COUNT(*) as total FROM jogadas_raspadinha")->fetch_assoc();
echo "Total: {$count['total']}<br>";

// 3. Verificar se há usuários
echo "<h3>3. Verificar tabela users:</h3>";
$users_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc();
echo "Total de usuários: {$users_count['total']}<br>";

// 4. Testar query com JOIN
echo "<h3>4. Teste da query com JOIN:</h3>";
$query = "SELECT j.*, u.name FROM jogadas_raspadinha j JOIN users u ON j.usuario_id = u.id LIMIT 5";
$result = $conn->query($query);

if ($result) {
    echo "Query executada com sucesso!<br>";
    echo "Número de linhas retornadas: " . $result->num_rows . "<br><br>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Usuario ID</th><th>Nome</th><th>Resultado</th><th>Premio</th><th>Valor Aposta</th><th>Data</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['usuario_id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['resultado']}</td>";
            echo "<td>{$row['premio']}</td>";
            echo "<td>{$row['valor_aposta']}</td>";
            echo "<td>{$row['data_jogada']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Nenhum registro encontrado no JOIN!";
    }
} else {
    echo "Erro na query: " . $conn->error;
}

// 5. Verificar se há registros órfãos
echo "<h3>5. Verificar registros órfãos:</h3>";
$orphans = $conn->query("SELECT COUNT(*) as total FROM jogadas_raspadinha j LEFT JOIN users u ON j.usuario_id = u.id WHERE u.id IS NULL")->fetch_assoc();
echo "Registros órfãos (sem usuário correspondente): {$orphans['total']}<br>";

// 6. Mostrar alguns registros da tabela jogadas_raspadinha
echo "<h3>6. Primeiros 5 registros da tabela jogadas_raspadinha:</h3>";
$result = $conn->query("SELECT * FROM jogadas_raspadinha LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Usuario ID</th><th>Resultado</th><th>Premio</th><th>Valor Aposta</th><th>Data</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['usuario_id']}</td>";
        echo "<td>{$row['resultado']}</td>";
        echo "<td>{$row['premio']}</td>";
        echo "<td>{$row['valor_aposta']}</td>";
        echo "<td>{$row['data_jogada']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Nenhum registro encontrado na tabela jogadas_raspadinha!";
}

// 7. Verificar alguns usuários
echo "<h3>7. Primeiros 5 usuários:</h3>";
$result = $conn->query("SELECT id, name FROM users LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nome</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Nenhum usuário encontrado!";
}
?>