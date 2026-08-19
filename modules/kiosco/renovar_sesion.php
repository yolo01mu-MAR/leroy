<?php

require_once __DIR__ . '/../../app/bootstrap.php';
header('Content-Type: application/json');

if(empty($_SESSION['kiosco']['empleado_id'])){

    http_response_code(401);

    echo json_encode([
        "ok" => false
    ]);

    exit;

}

$_SESSION['kiosco']['ultimo_movimiento'] = time();

echo json_encode([
    "ok" => true
]);