<?php
session_start();
require '../includes/db.php';

// Configurar sessão de teste
$_SESSION['usuario_id'] = 1;
$stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = 1");
$stmt->execute();

echo "<!DOCTYPE html>";
echo "<html><head><title>Teste Tabela Simples</title></head><body>";
echo "<h1>Teste da Tabela do Relatório</h1>";

// Query simples
$query = "SELECT j.*, u.name FROM jogadas_raspadinha j JOIN users u ON j.usuario_id = u.id ORDER BY j.data_jogada DESC LIMIT 10";
$result = $conn->query($query);

echo "<p>Número de registros encontrados: " . $result->num_rows . "</p>";

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<thead style='background: #f0f0f0;'>";
    echo "<tr>";
    echo "<th style='padding: 10px;'>ID</th>";
    echo "<th style='padding: 10px;'>USUÁRIO</th>";
    echo "<th style='padding: 10px;'>APOSTA</th>";
    echo "<th style='padding: 10px;'>RESULTADO</th>";
    echo "<th style='padding: 10px;'>PRÊMIO</th>";
    echo "<th style='padding: 10px;'>DATA/HORA</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td style='padding: 10px;'>R$ " . number_format($row['valor_aposta'], 2, ',', '.') . "</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['resultado']) . "</td>";
        echo "<td style='padding: 10px;'>R$ " . number_format($row['premio'], 2, ',', '.') . "</td>";
        echo "<td style='padding: 10px;'>" . date('d/m/Y H:i:s', strtotime($row['data_jogada'])) . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<p style='color: red; font-weight: bold;'>NENHUM REGISTRO ENCONTRADO!</p>";
}

echo "</body></html>";
?>