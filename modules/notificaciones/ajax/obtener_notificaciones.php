<?php

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../includes/notificaciones_helper.php';

header('Content-Type: application/json; charset=utf-8');

$usuario_id = (int)($_SESSION['user_id'] ?? 0);

if ($usuario_id <= 0) {

    echo json_encode([
        'ok' => false,
        'mensaje' => 'Usuario no autenticado'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$total = contar_notificaciones_no_leidas(
    $usuario_id
);

$notificaciones =
    obtener_notificaciones_usuario(
        $usuario_id,
        10
    );

echo json_encode([
    'ok' => true,
    'total_no_leidas' => $total,
    'notificaciones' => $notificaciones
], JSON_UNESCAPED_UNICODE);