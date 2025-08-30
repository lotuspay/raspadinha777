<?php
echo "Testando apenas db.php...<br>";
require '../includes/db.php';
echo "DB incluído com sucesso!<br>";
echo "Método HTTP: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "Content-Type: text/html<br>";
?>