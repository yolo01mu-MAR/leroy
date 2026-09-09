<?php

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../../../firmas/firmas/includes/firmas.php';
require_once __DIR__ . '/../../../modules/notificaciones/includes/notificaciones_helper.php';
require_once __DIR__ . '/../../../modules/correo/includes/correo_helper.php';

page_require_level(2);

header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| DATOS
|--------------------------------------------------------------------------
*/

$jefe_id = (int)$_SESSION['user_id'];

$id = isset($_POST['id'])
    ? (int)$_POST['id']
    : 0;

$accion = $_POST['accion'] ?? '';
$firma = $_POST['firma'] ?? '';
$observacion = $_POST['observacion'] ?? '';

/*
|--------------------------------------------------------------------------
| VALIDACIONES BÁSICAS
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'Solicitud no válida.'
    ]);

    exit;
}


if (!in_array($accion, ['aprobar', 'rechazar'], true)) {

    echo json_encode([
        'success' => false,
        'message' => 'Acción no válida.'
    ]);

    exit;
}

if ($accion === 'aprobar' && trim($firma) === '') {

    echo json_encode([
        'success' => false,
        'message' => 'Debe capturar una firma.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| BUSCAR SOLICITUD
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        v.id,
        v.usuario_id,
        v.jefe_id,
        v.estatus,
        v.fecha_inicio,
        v.fecha_fin,
        v.dias,
        vu.nombre AS nombre_colaborador,
        jefe.name AS nombre_jefe,
        jefe.email AS email_jefe
    FROM vacaciones v
    INNER JOIN vw_usuarios_completos vu ON vu.id = v.usuario_id
    LEFT JOIN users jefe ON jefe.id = v.jefe_id
    WHERE v.id = {$id}
    LIMIT 1
";

$resultado = find_by_sql($sql);

if (empty($resultado)) {

    echo json_encode([
        'success' => false,
        'message' => 'La solicitud no existe.'
    ]);

    exit;
}

$solicitud = $resultado[0];

/*
|--------------------------------------------------------------------------
| VALIDAR JEFE
|--------------------------------------------------------------------------
*/

if ((int)$solicitud['jefe_id'] !== $jefe_id) {

    echo json_encode([
        'success' => false,
        'message' => 'No tienes autorización para procesar esta solicitud.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDAR ESTATUS
|--------------------------------------------------------------------------
*/

if ($solicitud['estatus'] !== 'PENDIENTE_JEFE') {

    echo json_encode([
        'success' => false,
        'message' => 'Esta solicitud ya fue procesada.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| GUARDAR FIRMA DEL JEFE
| Solo cuando aprueba
|--------------------------------------------------------------------------
*/

if ($accion === 'aprobar') {

    $datos_firma = [

        'modulo' => FirmaModulo::VACACIONES,
        'registro_id' => $id,
        'tipo' => FirmaTipo::JEFE,
        'usuario_id' => $jefe_id,
        'firma' => $firma

    ];

    $resultado_firma = guardar_firma($datos_firma);

    if (
        !isset($resultado_firma['ok']) ||
        !$resultado_firma['ok']
    ) {

        echo json_encode([
            'success' => false,
            'message' => $resultado_firma['mensaje']
                ?? 'No fue posible registrar la firma.'
        ]);

        exit;
    }

}


/*
|--------------------------------------------------------------------------
| DETERMINAR NUEVO ESTATUS
|--------------------------------------------------------------------------
*/

if ($accion === 'aprobar') {
    $nuevo_estatus = 'PENDIENTE_RH';
} else {
    $nuevo_estatus = 'RECHAZADA_JEFE';
}


/*
|--------------------------------------------------------------------------
| ACTUALIZAR
|--------------------------------------------------------------------------
*/
if ($accion === 'rechazar' && trim($observacion) === '') {

    echo json_encode([
        'success' => false,
        'message' => 'Debe indicar el motivo de la no aprobación.'
    ]);

    exit;
}

$observacion_sql = $db->escape($observacion);

$sql_update = " UPDATE vacaciones
                SET
                    estatus = '{$nuevo_estatus}',
                    fecha_revision_jefe = NOW(),
                    observacion_jefe =
                        CASE
                            WHEN '{$accion}' = 'rechazar'
                            THEN '{$observacion_sql}'
                            ELSE observacion_jefe
                        END
                WHERE id = {$id}
                AND jefe_id = {$jefe_id}
                AND estatus = 'PENDIENTE_JEFE'
                LIMIT 1";

global $db;

$resultado_update = $db->query($sql_update);

if (!$resultado_update) {

    echo json_encode([
        'success' => false,
        'message' => 'No fue posible actualizar la solicitud.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| NOTIFICACIONES
|--------------------------------------------------------------------------
*/

if ($accion === 'aprobar') {

    $nombre_colaborador = remove_junk($solicitud['nombre_colaborador']);
    $fecha_inicio = date('d/m/Y', strtotime($solicitud['fecha_inicio']));
    $fecha_fin = date('d/m/Y',  strtotime($solicitud['fecha_fin']));
    $dias = (int)$solicitud['dias'];

    /*
    |--------------------------------------------------------------------------
    | URL DE LA SOLICITUD
    |--------------------------------------------------------------------------
    */

    $url_solicitud =
        BASE_URL .
        '/modules/solicitudes/solicitudes_vacaciones.php' .
        '?id=' .
        $id .
        '&estatus=PENDIENTE_RH';

    /*
    |--------------------------------------------------------------------------
    | NÓMINA - NOTIFICACIÓN
    |--------------------------------------------------------------------------
    */

    $mensaje_nomina =
        $nombre_colaborador .
        ' tiene una solicitud de vacaciones pendiente de revisión por Nómina. ' .
        'Periodo: ' .
        $fecha_inicio .
        ' al ' .
        $fecha_fin .
        '.';

    $destinatarios_nomina =
        notificar_por_responsabilidad(
            'NOMINA',
            $id,
            'VACACIONES_ENVIADA_RH',
            'Nueva solicitud de vacaciones',
            $mensaje_nomina
        );

    /*
    |--------------------------------------------------------------------------
    | JEFA RH - SOLO NOTIFICACIÓN
    |--------------------------------------------------------------------------
    */

    notificar_por_responsabilidad(
        'JEFE_RH',
        $id,
        'VACACIONES_ENVIADA_RH',
        'Solicitud enviada a RH',
        $nombre_colaborador .
        ' tiene una solicitud de vacaciones pendiente de revisión por RH.'
    );


    /*
    |--------------------------------------------------------------------------
    | JEFE - NOTIFICACIÓN
    |--------------------------------------------------------------------------
    */

    crear_notificacion(
        $jefe_id,
        $id,
        'VACACIONES_ENVIADA_RH',
        'Solicitud enviada a RH',
        'La solicitud de vacaciones de ' .
        $nombre_colaborador .
        ' fue aprobada y enviada a RH para revisión.'
    );


    /*
    |--------------------------------------------------------------------------
    | DESTINATARIOS DE CORREO
    |--------------------------------------------------------------------------
    |
    | Nómina + jefe
    |
    */

    $destinatarios_correo = $destinatarios_nomina;

    /*
    |--------------------------------------------------------------------------
    | AGREGAR JEFE AL CORREO
    |--------------------------------------------------------------------------
    */

    if (
        !empty(
            trim(
                $solicitud['email_jefe'] ?? ''
            )
        )
    ) {

        $destinatarios_correo[] = [
            'id' => (int)$solicitud['jefe_id'],
            'nombre' => $solicitud['nombre_jefe'],
            'email' => trim($solicitud['email_jefe'])
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | GENERAR Y ENVIAR CORREO
    |--------------------------------------------------------------------------
    */

    try {

        $contenido_correo =
            generar_correo_vacaciones([
                'titulo' => 'Nueva solicitud de vacaciones',
                'nombre_colaborador' => $nombre_colaborador,
                'mensaje' => 'La solicitud de vacaciones fue aprobada por su jefe y se encuentra pendiente de revisión por RH.',
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'dias' => $dias,
                'estado' => 'Pendiente de revisión',
                'estado_color' => 'azul',
                'url' => $url_solicitud,
                'texto_boton' => 'Ver solicitud'
            ]);

        enviar_correo_a_usuarios(
            $destinatarios_correo,
            'Nueva solicitud de vacaciones',
            $contenido_correo
        );

    } catch (Throwable $e) {

        error_log(
            'Error enviando correo de vacaciones #' .
            $id .
            ': ' .
            $e->getMessage()
        );

    }

}

/*
|--------------------------------------------------------------------------
| RESPUESTA
|--------------------------------------------------------------------------
*/

echo json_encode([
    'success' => true,
    'message' => $accion === 'aprobar'
        ? 'Solicitud aprobada correctamente.'
        : 'Solicitud rechazada correctamente.',
    'estatus' => $nuevo_estatus
]);

exit;