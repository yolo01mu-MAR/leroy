<?php

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../includes/validar_kiosco.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $usuarioId = (int)$_SESSION['kiosco']['empleado_id'];

    $sql = "
        SELECT
            id,
            usuario_id,
            tipo,
            tipo_solicitud,
            fecha_solicitud,
            fecha_inicio,
            fecha_fin,
            dias,
            estatus
        FROM vw_solicitudes
        WHERE usuario_id = {$usuarioId}
        ORDER BY fecha_solicitud DESC
    ";

    $resultado = $db->query($sql);

    if (!$resultado) {
        throw new Exception("No fue posible consultar las solicitudes.");
    }

    $solicitudes = [];

    while ($fila = $db->fetch_assoc($resultado)) {

        $solicitudes[] = $fila;

    }

    echo json_encode([
        'ok' => true,
        'solicitudes' => $solicitudes
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);

}