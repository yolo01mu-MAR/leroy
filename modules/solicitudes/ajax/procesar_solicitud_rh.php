<?php

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../../../modules/notificaciones/includes/notificaciones_helper.php';
require_once __DIR__ . '/../../../modules/correo/includes/correo_helper.php';

page_require_level(5);

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| DATOS
|--------------------------------------------------------------------------
*/

$rh_id = (int)$_SESSION['user_id'];

$id = isset($_POST['id'])
    ? (int)$_POST['id']
    : 0;

$accion = $_POST['accion'] ?? '';

$observacion = trim(
    $_POST['observacion'] ?? ''
);


/*
|--------------------------------------------------------------------------
| VALIDAR ID
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'Solicitud no válida.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDAR ACCIÓN
|--------------------------------------------------------------------------
*/

if (!in_array(
    $accion,
    ['aprobar', 'rechazar'],
    true
)) {

    echo json_encode([
        'success' => false,
        'message' => 'Acción no válida.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDAR OBSERVACIÓN
|--------------------------------------------------------------------------
*/

if (
    $accion === 'rechazar' &&
    $observacion === ''
) {

    echo json_encode([
        'success' => false,
        'message' => 'Debe indicar una observación.'
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
        v.estatus,
        v.fecha_inicio,
        v.fecha_fin,
        v.dias,
        u.name AS nombre_colaborador,
        u.email AS email_colaborador

    FROM vacaciones v

    INNER JOIN users u
        ON u.id = v.usuario_id

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
| VALIDAR ESTATUS
|--------------------------------------------------------------------------
*/

if ($solicitud['estatus'] !== 'PENDIENTE_RH') {

    echo json_encode([
        'success' => false,
        'message' =>
            'Esta solicitud ya fue procesada por RH.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| ESCAPAR OBSERVACIÓN
|--------------------------------------------------------------------------
*/

$observacion_sql = $db->escape(
    $observacion
);


/*
|--------------------------------------------------------------------------
| DETERMINAR NUEVO ESTATUS
|--------------------------------------------------------------------------
*/

if ($accion === 'aprobar') {

    $nuevo_estatus = 'APROBADA';

} else {

    $nuevo_estatus = 'RECHAZADA_RH';

}


/*
|--------------------------------------------------------------------------
| ACTUALIZAR SOLICITUD
|--------------------------------------------------------------------------
*/

$sql_update = "
    UPDATE vacaciones
    SET
        estatus = '{$nuevo_estatus}',
        fecha_revision_rh = NOW(),
        observacion_rh = '{$observacion_sql}'
    WHERE id = {$id}
    AND estatus = 'PENDIENTE_RH'
    LIMIT 1
";

$resultado_update =
    $db->query($sql_update);

/*
|--------------------------------------------------------------------------
| VALIDAR ACTUALIZACIÓN
|--------------------------------------------------------------------------
*/

if (!$resultado_update) {

    echo json_encode([
        'success' => false,
        'message' =>
            'No fue posible actualizar la solicitud.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| CONFIRMAR ESTATUS FINAL
|--------------------------------------------------------------------------
*/

$sql_verificar = "
    SELECT estatus
    FROM vacaciones
    WHERE id = {$id}
    LIMIT 1
";

$verificacion = find_by_sql($sql_verificar);

if (
    empty($verificacion) ||
    $verificacion[0]['estatus'] !== $nuevo_estatus
) {

    echo json_encode([
        'success' => false,
        'message' => 'No fue posible confirmar el nuevo estado de la solicitud.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| DATOS DEL COLABORADOR
|--------------------------------------------------------------------------
*/

$nombre_colaborador =
    remove_junk(
        $solicitud['nombre_colaborador']
    );

$fecha_inicio =
    date(
        'd/m/Y',
        strtotime($solicitud['fecha_inicio'])
    );

$fecha_fin =
    date(
        'd/m/Y',
        strtotime($solicitud['fecha_fin'])
    );

$dias =
    (int)$solicitud['dias'];


/*
|--------------------------------------------------------------------------
| NOTIFICACIÓN
|--------------------------------------------------------------------------
*/

if ($nuevo_estatus === 'APROBADA') {

    $titulo_notificacion = 'Vacaciones aprobadas';

    $mensaje_notificacion =
        'Tu solicitud de vacaciones fue aprobada por RH. ' .
        'Periodo: ' .
        $fecha_inicio .
        ' al ' .
        $fecha_fin .
        '.';

} else {

    $titulo_notificacion = 'Vacaciones no aprobadas';
    $mensaje_notificacion = 'Tu solicitud de vacaciones no fue aprobada por RH.';

    if ($observacion !== '') {

        $mensaje_notificacion .=
            ' Motivo: ' .
            $observacion;
    }
}


crear_notificacion(
    (int)$solicitud['usuario_id'],
    $id,
    'VACACIONES_' . $nuevo_estatus,
    $titulo_notificacion,
    $mensaje_notificacion
);


/*
|--------------------------------------------------------------------------
| CORREO
|--------------------------------------------------------------------------
*/

if (
    !empty(
        trim(
            $solicitud['email_colaborador'] ?? ''
        )
    )
) {

    try {

        $url_solicitud =
            BASE_URL .
            '/modules/solicitudes/solicitudes_vacaciones.php' .
            '?id=' .
            $id;


        if ($nuevo_estatus === 'APROBADA') {
            $titulo_correo = 'Solicitud de vacaciones aprobada';
            $mensaje_correo = 'Tu solicitud de vacaciones fue aprobada por RH.';
            $estado_correo = 'Aprobada';
            $color_correo = 'verde';

        } else {

            $titulo_correo = 'Solicitud de vacaciones no aprobada';
            $mensaje_correo = 'Tu solicitud de vacaciones no fue aprobada por RH.';

            if ($observacion !== '') {

                $mensaje_correo .=
                    '<br><br><strong>Motivo:</strong> ' .
                    htmlspecialchars(
                        $observacion,
                        ENT_QUOTES,
                        'UTF-8'
                    );
            }

            $estado_correo = 'No aprobada';
            $color_correo = 'rojo';
        }


        $contenido_correo =
            generar_correo_vacaciones([
                'titulo' => $titulo_correo,
                'nombre_colaborador' => $nombre_colaborador,
                'mensaje' => $mensaje_correo,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'dias' => $dias,
                'estado' => $estado_correo,
                'estado_color' => $color_correo,
                'url' => $url_solicitud,
                'texto_boton' => 'Ver solicitud'
            ]);


        enviar_correo_a_usuarios(

            [
                [
                    'id' => (int)$solicitud['usuario_id'],
                    'nombre' => $nombre_colaborador,
                    'email' => trim($solicitud['email_colaborador'])
                ]
            ],

            $titulo_correo,
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
| NOTIFICAR AL COLABORADOR
|--------------------------------------------------------------------------
*/

$nombre_colaborador =
    remove_junk(
        $solicitud['nombre_colaborador']
    );

$fecha_inicio =
    date(
        'd/m/Y',
        strtotime(
            $solicitud['fecha_inicio']
        )
    );

$fecha_fin =
    date(
        'd/m/Y',
        strtotime(
            $solicitud['fecha_fin']
        )
    );

$dias =
    (int)$solicitud['dias'];

/*
|--------------------------------------------------------------------------
| RESPUESTA
|--------------------------------------------------------------------------
*/

echo json_encode([

    'success' => true,

    'message' =>
        $accion === 'aprobar'
            ? 'Solicitud aprobada correctamente.'
            : 'Solicitud no aprobada correctamente.',

    'estatus' =>
        $nuevo_estatus

]);

exit;