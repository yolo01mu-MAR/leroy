<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once('includes/validar_kiosco.php');
require_once('includes/vacaciones_helpers.php');

header('Content-Type: application/json');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    if (empty($_SESSION['kiosco']['autenticado'])) {
        throw new Exception('Debes validar tu identidad antes de enviar la solicitud.');
    }

    $usuario_id      = $_SESSION['kiosco']['empleado_id'];
    $departamento_id = $_SESSION['kiosco']['departamento_id'];

    $inicio = trim($_POST['inicio'] ?? '');
    $fin    = trim($_POST['fin'] ?? '');
    $firma  = trim($_POST['firma'] ?? '');

    if (
        empty($inicio) ||
        empty($fin) ||
        empty($firma)
    ) {
        throw new Exception('Información incompleta.');
    }

    $resultado = crear_solicitud_vacaciones(
        $usuario_id,
        $departamento_id,
        $inicio,
        $fin,
        $firma
    );

    if (!$resultado['ok']) {
        throw new Exception($resultado['mensaje']);
    }

    echo json_encode($resultado);

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'ok'      => false,
        'mensaje' => $e->getMessage()
    ]);

}