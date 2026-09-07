<?php

require_once __DIR__ . '/../../../includes/load.php';
require_once __DIR__ . '/../includes/firmas.php';

header('Content-Type: application/json; charset=utf-8');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Método no permitido.');
    }

    $firma = obtener_firma(
        $_GET['modulo'],
        $_GET['registro_id'],
        $_GET['tipo']
    );

    echo json_encode([
        'ok' => true,
        'firma' => $firma
    ]);

} catch (Exception $e) {

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);

}