<?php
session_start();
require 'includes/db.php';

// Verifica se é um teste RTP ou teste de prêmios
$isTesteRTP = (isset($_POST['teste_rtp']) && $_POST['teste_rtp'] == '1') || (isset($_POST['teste_premio']) && $_POST['teste_premio'] == '1');
$usuarioTeste = $_POST['usuario_teste'] ?? null;

if ($isTesteRTP && $usuarioTeste) {
    // Para testes RTP, busca ou cria o usuário de teste
    $stmt = $conn->prepare("SELECT id, balance FROM users WHERE email = ?");
    $stmt->bind_param("s", $usuarioTeste);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
    } else {
        // Cria usuário de teste se não existir
        $stmt = $conn->prepare("INSERT INTO users (email, password, balance, created_at) VALUES (?, ?, 1000.00, NOW())");
        $hashedPassword = password_hash('teste123', PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $usuarioTeste, $hashedPassword);
        $stmt->execute();
        $userId = $conn->insert_id;
    }
} else {
    // Verifica se está logado normalmente
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['erro' => 'Faça login novamente.']);
        exit;
    }
    $userId = $_SESSION['usuario_id'];
}

// Identifica tipo da raspadinha (GET para jogo normal, POST para teste RTP/Prêmios)
$tipo = $_POST['tipo'] ?? $_GET['raspadinha'] ?? 'esperanca';

// Pega o valor da URL ou POST se fornecido, senão usa o padrão do tipo
$valorAposta = isset($_POST['valor']) ? floatval($_POST['valor']) : (isset($_GET['valor']) ? floatval($_GET['valor']) : 1.00);

// CORREÇÃO: Para Teste RTP, o valor da aposta deve ser baseado no tipo de raspadinha
// Para Teste Prêmios, o valor já vem definido no parâmetro 'valor'
if ($isTesteRTP && isset($_POST['tipo']) && !isset($_POST['valor'])) {
    // Se é teste RTP e não tem valor específico, usa o valor padrão do tipo
    switch ($tipo) {
        case 'alegria':
            $valorAposta = 2.00;
            break;
        case 'emocao':
            $valorAposta = 20.00;
            break;
        default: // esperanca
            $valorAposta = 1.00;
            break;
    }
}

// INTEGRAÇÃO COM CONFIGURAÇÕES DO ADMIN
// Verifica se existe configuração de chance personalizada no config.json do admin
$adminConfigFile = __DIR__ . '/admin/config.json';
$adminChance = null;

if (file_exists($adminConfigFile)) {
    $adminConfig = json_decode(file_get_contents($adminConfigFile), true);
    if ($adminConfig && isset($adminConfig['chance_vitoria'])) {
        $adminChance = floatval($adminConfig['chance_vitoria']);
    }
}

// Busca configurações da tabela tipos_raspadinha baseado no valor da aposta
$stmt = $conn->prepare("SELECT nome, premio_maximo, chance_base, valor_aposta_padrao FROM tipos_raspadinha WHERE valor_aposta_padrao = ? AND ativo = 1 LIMIT 1");
$stmt->bind_param("d", $valorAposta);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Usa configurações da tabela tipos_raspadinha
    $tipoConfig = $result->fetch_assoc();
    $premioMaximo = floatval($tipoConfig['premio_maximo']);
    $chance = floatval($tipoConfig['chance_base']);
    
    // SOBRESCREVE com configuração do admin se existir (incluindo 0)
    if ($adminChance !== null) {
        $chance = $adminChance;
    }
} else {
    // Fallback para configurações hardcoded se não encontrar na tabela
    switch ($tipo) {
      case 'alegria':
        if (!isset($_POST['valor']) && !isset($_GET['valor'])) $valorAposta = 2.00;
        $premioMaximo = 70.00;
        $chance = 0.002;
        break;
      case 'emocao':
        if (!isset($_POST['valor']) && !isset($_GET['valor'])) $valorAposta = 20.00;
        $premioMaximo = 150.00;
        $chance = 0.001;
        break;
      default:
        if (!isset($_POST['valor']) && !isset($_GET['valor'])) $valorAposta = 1.00;
        $premioMaximo = 20.00;
        $chance = 0.003;
        break;
    }
    
    // SOBRESCREVE com configuração do admin se existir (incluindo 0)
    if ($adminChance !== null) {
        $chance = $adminChance;
    }
}

// Pega saldo atual
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$saldoAtual = floatval($user['balance']);

// Verifica saldo suficiente
if ($saldoAtual < $valorAposta) {
  echo json_encode(['erro' => 'Saldo insuficiente.']);
  exit;
}

// Desconta aposta imediatamente
$novoSaldo = $saldoAtual - $valorAposta;
$stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
$stmt->bind_param("di", $novoSaldo, $userId);
$stmt->execute();

// Verifica config do prêmio forçado
$config = $conn->query("SELECT * FROM raspadinha_config LIMIT 1")->fetch_assoc();

$premioForcadoAtivo = $config && intval($config['ativo']) === 1;
$maxPremios = intval($config['max_premios']);
$premiosPagos = intval($config['premios_pagos']);
$valorPremio = floatval($config['valor_premio']);

$allSimbolos = ['maça.png', 'banana.png', 'uva.png', 'laranja.png', 'abacaxi.png', 'morango.png'];
shuffle($allSimbolos);
$simbolosSorteados = array_slice($allSimbolos, 0, 6);


// Decide se ganha ou não
$ganhou = false;
$mensagem = 'Que pena, tente de novo!';
$valorGanho = 0;

// Se manipulado pelo admin e ainda não atingiu o limite de prêmios
if ($premioForcadoAtivo && $premiosPagos < $maxPremios) {
  $ganhou = true;
  $valorGanho = $valorPremio;
  $mensagem = "🎉 Parabéns! Você ganhou R$ " . @number_format($valorPremio, 2, ',', '.');

  // Atualiza saldo com prêmio
  $novoSaldo += $valorPremio;
  $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
  $stmt->bind_param("di", $novoSaldo, $userId);
  $stmt->execute();

  // Incrementa contador de prêmios pagos
  $conn->query("UPDATE raspadinha_config SET premios_pagos = premios_pagos + 1");
} else {
  // Sorteio aleatório com base na chance configurada
  if (mt_rand(0, 99999) / 100000 < $chance) {
    $ganhou = true;
    $valorGanho = $premioMaximo;
    $mensagem = "🎉 Parabéns! Você ganhou R$ " . @number_format($valorGanho, 2, ',', '.');

    $novoSaldo += $valorGanho;
    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->bind_param("di", $novoSaldo, $userId);
    $stmt->execute();
  }
}

// Resposta JSON para o frontend
echo json_encode([
  'simbolos' => $simbolosSorteados,
  'ganhou' => $ganhou,
  'premio' => $valorGanho,
  'mensagem' => $mensagem,
  'saldo' => @number_format($novoSaldo, 2, ',', '.')
]);

exit;?>