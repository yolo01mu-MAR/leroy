<?php

require_once(__DIR__.'/../../../includes/load.php');
require_once(__DIR__.'/../includes/firmas.php');

header('Content-Type: application/json; charset=utf-8');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    $respuesta = guardar_firma($_POST);

    echo json_encode($respuesta);

} catch (Exception $e) {

    echo json_encode([
        'ok'      => false,
        'mensaje' => $e->getMessage()
    ]);

}