<?php
require '../includes/db.php';

echo "<h2>Teste da Tabela 'Últimas 5 Raspadinhas'</h2>";

// Testar a query atualizada
try {
    $stmt = $conn->query("
        SELECT u.name, j.premio, j.resultado, j.tipo_raspadinha, j.data_jogada 
        FROM jogadas_raspadinha j 
        JOIN users u ON j.usuario_id = u.id 
        ORDER BY j.data_jogada DESC 
        LIMIT 5
    ");
    
    echo "<p>✅ Query executada com sucesso!</p>";
    echo "<p><strong>Número de registros encontrados:</strong> " . $stmt->num_rows . "</p>";
    
    if ($stmt->num_rows > 0) {
        echo "<h3>📋 Dados das Últimas 5 Raspadinhas:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Usuário</th><th>Tipo</th><th>Resultado</th><th>Prêmio</th><th>Data</th></tr>";
        
        while ($row = $stmt->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['tipo_raspadinha'] ?? 'Raspadinha') . "</td>";
            echo "<td>" . htmlspecialchars($row['resultado']) . "</td>";
            echo "<td>R$ " . number_format($row['premio'], 2, ',', '.') . "</td>";
            echo "<td>" . date('d/m/Y H:i:s', strtotime($row['data_jogada'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>🎯 Formato para a Tabela do Admin:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Usuário / Jogo</th><th>Prêmio</th></tr>";
        
        $stmt = $conn->query("
            SELECT u.name, j.premio, j.resultado, j.tipo_raspadinha, j.data_jogada 
            FROM jogadas_raspadinha j 
            JOIN users u ON j.usuario_id = u.id 
            ORDER BY j.data_jogada DESC 
            LIMIT 5
        ");
        
        while ($row = $stmt->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['name'] ?? 'N/A') . "<br><small style='color: #888;'>" . htmlspecialchars($row['tipo_raspadinha'] ?? 'Raspadinha') . "</small></td>";
            if ($row['resultado'] === 'ganhou') {
                echo "<td><span style='background: green; color: white; padding: 2px 8px; border-radius: 4px;'>R$ " . number_format($row['premio'], 2, ',', '.') . "</span></td>";
            } else {
                echo "<td><span style='background: red; color: white; padding: 2px 8px; border-radius: 4px;'>R$ 0,00</span></td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhum registro encontrado na tabela jogadas_raspadinha.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao executar query: " . $e->getMessage() . "</p>";
}

echo "<br><br><a href='index.php'>← Voltar para o Dashboard</a>";
?>