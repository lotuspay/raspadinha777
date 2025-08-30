<?php
echo "<h1>Debug de Requisição</h1>";
echo "<pre>";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "HTTP_X_REQUESTED_WITH: " . (isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : 'não definido') . "\n";
echo "\nTodos os cabeçalhos HTTP:\n";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
echo "\n_POST:\n";
var_dump($_POST);
echo "\n_GET:\n";
var_dump($_GET);
echo "\n_SERVER (relevante):\n";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0 || in_array($key, ['REQUEST_METHOD', 'REQUEST_URI', 'QUERY_STRING'])) {
        echo "$key: $value\n";
    }
}
echo "</pre>";
?>