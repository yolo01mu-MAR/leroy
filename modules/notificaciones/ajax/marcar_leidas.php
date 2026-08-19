<?php

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../includes/notificaciones_helper.php';

header('Content-Type: application/json; charset=utf-8');

$usuario_id = (int)($_SESSION['user_id'] ?? 0);
$notificacion_id = (int)($_POST['id'] ?? 0);

if ($usuario_id <= 0) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Usuario no autenticado'
    ]);

    exit;
}

if ($notificacion_id <= 0) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Notificación inválida'
    ]);

    exit;
}

$resultado = marcar_notificacion_leida(
    $notificacion_id,
    $usuario_id
);

echo json_encode([
    'ok' => $resultado
], JSON_UNESCAPED_UNICODE);