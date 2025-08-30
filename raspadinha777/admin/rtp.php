
<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

// Verificar se é admin
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !$user['is_admin']) {
    header("Location: ../perfil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configurações RTP - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="components/sidebar.css" rel="stylesheet">
  <!-- Header CSS -->
    <link href="components/header.css" rel="stylesheet">
    <!-- Mobile Navigation CSS -->
    <link href="components/mobile_nav.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      padding: 30px;
    }
    #painel {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      width: 480px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
    }
    h1, h2 {
      color: #333;
    }
    .stat {
      font-size: 18px;
      margin: 10px 0;
    }
    .resultado {
      font-size: 16px;
      background: #eee;
      padding: 5px;
      border-radius: 4px;
      margin: 2px 0;
    }
    #grafico-container {
      margin-top: 20px;
    }
    button {
      margin: 10px 5px 0 0;
      padding: 10px 15px;
      font-size: 15px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .testar { background: #4caf50; color: white; }
    .exportar { background: #2196f3; color: white; }
  </style>
</head>
<body>
     <?php include 'components/header.php'; ?>
     <?php include 'components/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Configurações RTP</h1>
            <p class="page-subtitle">Estatísticas e controle da taxa de retorno ao jogador</p>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card" id="painel">
  <h1>📊 Estatísticas da Raspadinha</h1>
  <div class="stat">Total de tentativas: <span id="total">0</span></div>
  <div class="stat">Vitórias: <span id="vitorias">0</span></div>
  <div class="stat">Derrotas: <span id="derrotas">0</span></div>

  <div id="grafico-container">
    <canvas id="graficoPizza" width="400" height="400"></canvas>
  </div>

  <h2>Últimos resultados</h2>
  <div id="resultados"></div>

  <button class="testar" onclick="testarRodada()">🎰 Testar Jogada</button>
  <button class="exportar" onclick="exportarCSV()">⬇️ Exportar CSV</button>
</div>

<script>
  let total = 0;
  let vitorias = 0;
  let derrotas = 0;
  let ultimos = [];
  let historicoCSV = [];

  const ctx = document.getElementById('graficoPizza').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Vitórias', 'Derrotas'],
      datasets: [{
        label: 'Resultados',
        data: [0, 0],
        backgroundColor: ['#4caf50', '#f44336']
      }]
    },
    options: {
      responsive: true
    }
  });

  function testarRodada() {
    fetch('api.php')
      .then(res => res.json())
      .then(data => {
        const venceu = data.win === true;
        total++;
        if (venceu) vitorias++; else derrotas++;

        ultimos.unshift(venceu ? '✅ Vitória' : '❌ Derrota');
        if (ultimos.length > 10) ultimos.pop();

        historicoCSV.push([new Date().toLocaleString(), venceu ? 'Vitória' : 'Derrota']);

        atualizarEstatisticas();
      })
      .catch(err => {
        alert("Erro ao consultar API");
        console.error(err);
      });
  }

  function atualizarEstatisticas() {
    document.getElementById('total').innerText = total;
    document.getElementById('vitorias').innerText = vitorias;
    document.getElementById('derrotas').innerText = derrotas;
    document.getElementById('resultados').innerHTML =
      ultimos.map(r => `<div class="resultado">${r}</div>`).join('');

    chart.data.datasets[0].data = [vitorias, derrotas];
    chart.update();
  }

  function exportarCSV() {
    const header = 'Data,Resultado\n';
    const linhas = historicoCSV.map(l => l.join(',')).join('\n');
    const blob = new Blob([header + linhas], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'historico_raspadinha.csv';
    link.click();
  }
</script>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
            text-align: center;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0.5rem 0 0 0;
            text-align: center;
        }
        
        #painel {
            padding: 2rem;
        }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
