<?php

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../../../firmas/firmas/includes/firmas.php';
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

$firma = $_POST['firma'] ?? '';

$observacion =
    trim(
        $_POST['observacion'] ?? ''
    );


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


if (
    !in_array(
        $accion,
        ['aprobar', 'rechazar'],
        true
    )
) {

    echo json_encode([
        'success' => false,
        'message' => 'Acción no válida.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDAR FIRMA
|--------------------------------------------------------------------------
*/

if (
    $accion === 'aprobar' &&
    trim($firma) === ''
) {

    echo json_encode([
        'success' => false,
        'message' => 'Debe capturar una firma.'
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
        'message' => 'Debe indicar el motivo de la no aprobación.'
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

        u.name AS nombre_colaborador,
        u.email AS email_colaborador,

        jefe.name AS nombre_jefe,
        jefe.email AS email_jefe

    FROM vacaciones v

    INNER JOIN users u
        ON u.id = v.usuario_id

    LEFT JOIN users jefe
        ON jefe.id = v.jefe_id

    WHERE v.id = {$id}

    LIMIT 1
";


$resultado =
    find_by_sql($sql);


if (empty($resultado)) {

    echo json_encode([
        'success' => false,
        'message' => 'La solicitud no existe.'
    ]);

    exit;
}


$solicitud =
    $resultado[0];


/*
|--------------------------------------------------------------------------
| VALIDAR ESTATUS
|--------------------------------------------------------------------------
|
| RH solamente puede procesar solicitudes
| que ya fueron aprobadas por el jefe.
|
*/

if (
    $solicitud['estatus'] !== 'PENDIENTE_RH'
) {

    echo json_encode([
        'success' => false,
        'message' =>
            'Esta solicitud ya fue procesada o no está pendiente de RH.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| GUARDAR FIRMA DE RH
|--------------------------------------------------------------------------
|
| La firma solamente se guarda cuando RH aprueba.
|
*/

if ($accion === 'aprobar') {

    $datos_firma = [

        'modulo' =>
            FirmaModulo::VACACIONES,

        'registro_id' =>
            $id,

        'tipo' =>
            FirmaTipo::RH,

        'usuario_id' =>
            $rh_id,

        'firma' =>
            $firma

    ];


    $resultado_firma =
        guardar_firma(
            $datos_firma
        );


    if (
        !isset($resultado_firma['ok']) ||
        !$resultado_firma['ok']
    ) {

        echo json_encode([

            'success' => false,

            'message' =>
                $resultado_firma['mensaje']
                ??
                'No fue posible registrar la firma.'

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

    $nuevo_estatus =
        'APROBADA';

} else {

    $nuevo_estatus =
        'RECHAZADA_RH';

}


/*
|--------------------------------------------------------------------------
| ESCAPAR OBSERVACIÓN
|--------------------------------------------------------------------------
*/

$observacion_sql =
    $db->escape(
        $observacion
    );


/*
|--------------------------------------------------------------------------
| ACTUALIZAR SOLICITUD
|--------------------------------------------------------------------------
|
| IMPORTANTE:
| La condición correcta es PENDIENTE_RH.
|
*/

$sql_update = "
    UPDATE vacaciones

    SET
        estatus = '{$nuevo_estatus}',

        rh_id = {$rh_id},

        fecha_revision_rh = NOW(),

        observacion_rh =
            CASE

                WHEN '{$accion}' = 'rechazar'
                THEN '{$observacion_sql}'

                ELSE observacion_rh

            END

    WHERE id = {$id}

    AND estatus = 'PENDIENTE_RH'

    LIMIT 1
";


$resultado_update =
    $db->query(
        $sql_update
    );


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
    SELECT
        estatus,
        rh_id

    FROM vacaciones

    WHERE id = {$id}

    LIMIT 1
";


$verificacion =
    find_by_sql(
        $sql_verificar
    );


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
| NOTIFICACIÓN AL JEFE DE CUADRILLA
|--------------------------------------------------------------------------
*/

if ($nuevo_estatus === 'APROBADA') {

    $titulo_notificacion =
        'Vacaciones aprobadas';

    $mensaje_notificacion =
        'Las vacaciones de ' .
        $nombre_colaborador .
        ' fueron aprobadas por RH. ' .
        'Periodo: ' .
        $fecha_inicio .
        ' al ' .
        $fecha_fin .
        '.';

} else {

    $titulo_notificacion =
        'Vacaciones no aprobadas';

    $mensaje_notificacion =
        'Tu solicitud de vacaciones no fue aprobada por RH.';

    if ($observacion !== '') {

        $mensaje_notificacion .=
            ' Motivo: ' .
            $observacion;

    }

}

// Notificacion al usuario cuando este su menu
// crear_notificacion(
//     (int)$solicitud['usuario_id'],
//     $id,
//     'VACACIONES_' . $nuevo_estatus,
//     $titulo_notificacion,
//     $mensaje_notificacion
// );


/*
|--------------------------------------------------------------------------
| NOTIFICAR AL JEFE
|--------------------------------------------------------------------------
|
| RH ya tomó una decisión.
|
*/

if (!empty($solicitud['jefe_id'])) {

    crear_notificacion(
        (int)$solicitud['jefe_id'],
        $id,
        'VACACIONES_' . $nuevo_estatus,
        $titulo_notificacion,
        $mensaje_notificacion
    );

}


/*
|--------------------------------------------------------------------------
| CORREO AL COLABORADOR
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


        /*
        |--------------------------------------------------------------------------
        | APROBADA
        |--------------------------------------------------------------------------
        */

        if ($nuevo_estatus === 'APROBADA') {

            $titulo_correo =
                'Solicitud de vacaciones aprobada';

            $mensaje_correo =
                'Tu solicitud de vacaciones fue aprobada por RH.';

            $estado_correo =
                'Aprobada';

            $color_correo =
                'verde';

        }


        /*
        |--------------------------------------------------------------------------
        | RECHAZADA
        |--------------------------------------------------------------------------
        */

        else {

            $titulo_correo =
                'Solicitud de vacaciones no aprobada';

            $mensaje_correo =
                'Tu solicitud de vacaciones no fue aprobada por RH.';


            if ($observacion !== '') {

                $mensaje_correo .=
                    '<br><br><strong>Motivo:</strong> ' .
                    htmlspecialchars(
                        $observacion,
                        ENT_QUOTES,
                        'UTF-8'
                    );

            }


            $estado_correo =
                'No aprobada';

            $color_correo =
                'rojo';

        }


        /*
        |--------------------------------------------------------------------------
        | GENERAR CORREO
        |--------------------------------------------------------------------------
        */

        $contenido_correo =
            generar_correo_vacaciones([

                'titulo' =>
                    $titulo_correo,

                'nombre_colaborador' =>
                    $nombre_colaborador,

                'mensaje' =>
                    $mensaje_correo,

                'fecha_inicio' =>
                    $fecha_inicio,

                'fecha_fin' =>
                    $fecha_fin,

                'dias' =>
                    $dias,

                'estado' =>
                    $estado_correo,

                'estado_color' =>
                    $color_correo,

                'url' =>
                    $url_solicitud,

                'texto_boton' =>
                    'Ver solicitud'

            ]);


        /*
        |--------------------------------------------------------------------------
        | ENVIAR
        |--------------------------------------------------------------------------
        */

        enviar_correo_a_usuarios(

            [

                [

                    'id' =>
                        (int)$solicitud['usuario_id'],

                    'nombre' =>
                        $nombre_colaborador,

                    'email' =>
                        trim(
                            $solicitud['email_colaborador']
                        )

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
| RESPUESTA
|--------------------------------------------------------------------------
*/

echo json_encode([

    'success' =>
        true,

    'message' =>
        $accion === 'aprobar'

            ? 'Solicitud aprobada correctamente.'

            : 'Solicitud no aprobada correctamente.',

    'estatus' =>
        $nuevo_estatus

]);

exit;