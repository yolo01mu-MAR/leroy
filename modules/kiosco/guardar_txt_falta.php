<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once('includes/validar_kiosco.php');
require_once __DIR__ . '/../../firmas/firmas/includes/firmas.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $usuarioId = (int)$_SESSION['kiosco']['empleado_id'];

    if (empty($_SESSION['kiosco']['autenticado'])) {

        throw new Exception(
            'Debes validar tu identidad antes de enviar la solicitud.'
        );

    }

    $asistenciaId = (int)($_POST['asistencia'] ?? 0);
    $fecha        = trim($_POST['fecha'] ?? '');
    $motivo       = trim($_POST['motivo'] ?? '');
    $firma        = trim($_POST['firma'] ?? '');

    // VALIDACIONES
    if ($asistenciaId <= 0) {

        throw new Exception(
            'La falta no es válida.'
        );

    }

    if (empty($fecha)) {

        throw new Exception(
            'La fecha es obligatoria.'
        );

    }

    if (empty($motivo)) {

        throw new Exception(
            'El motivo es obligatorio.'
        );

    }

    if (empty($firma)) {

        throw new Exception(
            'La firma es obligatoria.'
        );

    }

    $fechaObj = DateTime::createFromFormat(
        'Y-m-d',
        $fecha
    );

    if (
        !$fechaObj ||
        $fechaObj->format('Y-m-d') !== $fecha
    ) {

        throw new Exception(
            'La fecha no es válida.'
        );

    }

    $sql = "
        SELECT
            ID,
            id_empleado,
            fecha
        FROM asistencia
        WHERE ID = '{$asistenciaId}'
        AND id_empleado = '{$usuarioId}'
        LIMIT 1
    ";

    $resultado = $db->query($sql);

    if (
        !$resultado ||
        $db->num_rows($resultado) === 0
    ) {

        throw new Exception(
            'La falta no pertenece al colaborador.'
        );

    }

    $asistencia = $db->fetch_assoc($resultado);

    if ($asistencia['fecha'] !== $fecha) {

        throw new Exception(
            'La fecha no corresponde a la falta seleccionada.'
        );

    }

    $motivoSQL = $db->escape($motivo);
    $fechaSQL  = $db->escape($fecha);

    $sql = "
        INSERT INTO tiempo_solicitud
        (
            usuario_id,
            asistencia_id,
            origen,
            tipo_solicitud,
            fecha_solicitud,
            fecha_falta,
            motivo,
            estatus
        )
        VALUES
        (
            '{$usuarioId}',
            '{$asistenciaId}',
            'ASISTENCIA',
            'FALTA',
            NOW(),
            '{$fechaSQL}',
            '{$motivoSQL}',
            'PENDIENTE_JEFE'
        )
    ";

    if (!$db->query($sql)) {

        throw new Exception(
            'No fue posible registrar la solicitud.'
        );

    }

    // OBTENER ID DE LA SOLICITUD
    $solicitudId = $db->insert_id();

    if (!$solicitudId) {

        throw new Exception(
            'No fue posible obtener el ID de la solicitud.'
        );

    }

    // GUARDAR FIRMA DEL EMPLEADO
    $datosFirma = [

        'modulo'      => FirmaModulo::TXT,
        'registro_id' => $solicitudId,
        'tipo'        => FirmaTipo::EMPLEADO,
        'usuario_id'  => $usuarioId,
        'firma'       => $firma

    ];

    $respuestaFirma = guardar_firma($datosFirma);

    // VERIFICAR FIRMA
    if (
        !isset($respuestaFirma['ok']) ||
        !$respuestaFirma['ok']
    ) {

        // Si la firma falla, eliminamos
        // la solicitud recién creada.
    
        $db->query("
            DELETE FROM tiempo_solicitud
            WHERE id = '{$solicitudId}'
            LIMIT 1
        ");

        throw new Exception(
            $respuestaFirma['mensaje']
            ?? 'No fue posible registrar la firma.'
        );

    }

    echo json_encode([

        'ok' => true,

        'mensaje' =>
            'Solicitud enviada correctamente.',

        'solicitud_id' =>
            $solicitudId,

        'firma' =>
            $respuestaFirma['archivo']

    ]);


} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([

        'ok' => false,

        'mensaje' => $e->getMessage()

    ]);

}