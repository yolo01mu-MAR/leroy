<?php

header('Content-Type: text/plain');

$inicio = microtime(true);

echo "Respuesta enviada.\n";

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

sleep(10);

file_put_contents(
    __DIR__ . '/prueba_background.txt',
    "Proceso terminado: " . date('Y-m-d H:i:s') . PHP_EOL,
    FILE_APPEND
);