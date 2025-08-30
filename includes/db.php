<?php
ini_set("display_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);

$host = '186.194.55.166';
$db = 'raspadinha777';
$user = 'raspadinha777';
$pass = 'Nxf7xzdcEiGBFttA';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>

