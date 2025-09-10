<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

$host = 'localhost';
$db = 'raspadinha777';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>

