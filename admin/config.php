<?php
ini_set("display_errors", 0);
ini_set("html_errors", 0);
error_reporting(E_ALL);

$host = 'localhost';
$db = 'raspadinha777';
$user = 'root';
$pass = '';

// Conexão MySQLi
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Conexão PDO para compatibilidade
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão PDO: " . $e->getMessage());
}
?>