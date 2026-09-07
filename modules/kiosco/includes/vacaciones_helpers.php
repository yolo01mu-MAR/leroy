<?php

require_once __DIR__ . '/../../../firmas/firmas/includes/firmas.php';
require_once __DIR__ . '/../../notificaciones/includes/notificaciones_helper.php';
require_once __DIR__ . '/../../../modules/correo/includes/correo_helper.php';

// Cupo máximo de personas simultáneas por departamento
define('CUPO_MAXIMO_VACACIONES', 2);

// Días mínimos de anticipación para solicitar vacaciones
define('DIAS_ANTICIPACION', 8);

/**
 * Convierte los rangos (fecha_inicio, fecha_fin) en un arreglo
 * ['YYYY-MM-DD' => 'estado'] para pintar el calendario.
 */
function crear_solicitud_vacaciones(
    $usuario_id,
    $departamento_id,
    $fechaInicio,
    $fechaFin,
    $firma
){

    /*
    |--------------------------------------------------------------------------
    | VALIDAR FECHAS
    |--------------------------------------------------------------------------
    */

    if(empty($fechaInicio) || empty($fechaFin)){

        return [
            'ok' => false,
            'mensaje' => 'Debe seleccionar un rango de fechas.'
        ];

    }


    if($fechaInicio > $fechaFin){

        return [
            'ok' => false,
            'mensaje' => 'La fecha inicial no puede ser mayor a la fecha final.'
        ];

    }


    global $db;


    /*
    |--------------------------------------------------------------------------
    | OBTENER SALDO
    |--------------------------------------------------------------------------
    */

    $resultado = obtener_saldo_vigente(
        $usuario_id
    );

    if(!$resultado['ok']){
        return $resultado;
    }

    $saldo = $resultado['saldo'];


    /*
    |--------------------------------------------------------------------------
    | CALCULAR DÍAS
    |--------------------------------------------------------------------------
    */

    $dias = calcular_dias_vacaciones(
        $fechaInicio,
        $fechaFin
    );


    /*
    |--------------------------------------------------------------------------
    | FECHA DE REGRESO
    |--------------------------------------------------------------------------
    */

    $fechaRegreso = calcular_fecha_regreso(
        $fechaFin
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDAR SALDO
    |--------------------------------------------------------------------------
    */

    $resultado = validar_saldo(
        $saldo,
        $dias
    );

    if(!$resultado['ok']){
        return $resultado;
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDAR TRASLAPE
    |--------------------------------------------------------------------------
    */

    $validacion = validar_traslape(
        $usuario_id,
        $fechaInicio,
        $fechaFin
    );

    if(!$validacion['ok']){
        return $validacion;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAR CUPO
    |--------------------------------------------------------------------------
    */

    $resultado = validar_cupo(
        $departamento_id,
        $fechaInicio,
        $fechaFin
    );

    if(!$resultado['ok']){
        return $resultado;
    }

    /*
    |--------------------------------------------------------------------------
    | OBTENER JEFE
    |--------------------------------------------------------------------------
    */

    $jefe = obtener_jefe(
        $usuario_id
    );

    if(empty($jefe)){

        return [
            'ok' => false,
            'mensaje' => 'El colaborador no tiene un jefe asignado.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | DATOS DEL COLABORADOR
    |--------------------------------------------------------------------------
    */

    $colaborador = obtener_datos_usuario(
        $usuario_id
    );

    $nombre_colaborador =
        $colaborador['name']
        ?? 'Un colaborador';

    /*
    |--------------------------------------------------------------------------
    | DATOS DEL JEFE
    |--------------------------------------------------------------------------
    */

    $datos_jefe = obtener_datos_usuario(
        $jefe
    );

    $nombre_jefe =
        $datos_jefe['name']
        ?? 'Jefe';

    $correo_jefe =
        !empty($datos_jefe['email'])
        ? trim($datos_jefe['email'])
        : null;

    /*
    |--------------------------------------------------------------------------
    | GUARDAR FECHAS ORIGINALES
    |--------------------------------------------------------------------------
    |
    | Las conservamos para mostrar correctamente
    | la información en el correo.
    |
    */

    $fechaInicioOriginal = $fechaInicio;
    $fechaFinOriginal = $fechaFin;

    /*
    |--------------------------------------------------------------------------
    | ESCAPAR FECHAS PARA SQL
    |--------------------------------------------------------------------------
    */

    $fechaInicio = $db->escape(
        $fechaInicio
    );

    $fechaFin = $db->escape(
        $fechaFin
    );

    /*
    |--------------------------------------------------------------------------
    | INICIAR TRANSACCIÓN
    |--------------------------------------------------------------------------
    */

    $db->query(
        "START TRANSACTION"
    );

    /*
    |--------------------------------------------------------------------------
    | CREAR SOLICITUD
    |--------------------------------------------------------------------------
    */

    $sql = "INSERT INTO vacaciones(

                usuario_id,
                saldo_id,
                fecha_inicio,
                fecha_fin,
                fecha_regreso,
                dias,
                estatus,
                jefe_id

            ) VALUES(

                '{$usuario_id}',
                '{$saldo['id']}',
                '{$fechaInicio}',
                '{$fechaFin}',
                '{$fechaRegreso}',
                '{$dias}',
                'PENDIENTE_JEFE',
                '{$jefe}'

            )";

    if(!$db->query($sql)){

        $db->query(
            "ROLLBACK"
        );

        return [
            'ok' => false,
            'mensaje' => 'No fue posible guardar la solicitud.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | ID DE LA SOLICITUD
    |--------------------------------------------------------------------------
    */

    $vacacionId =
        $db->insert_id();


    /*
    |--------------------------------------------------------------------------
    | GUARDAR FIRMA
    |--------------------------------------------------------------------------
    */

    $datosFirma = [

        'modulo' => 'VACACIONES',

        'registro_id' =>
            $vacacionId,

        'tipo' =>
            'EMPLEADO',

        'usuario_id' =>
            $usuario_id,

        'firma' =>
            $firma

    ];

    $resultadoFirma = guardar_firma($datosFirma);

    if(!$resultadoFirma['ok']){

        $db->query(
            "ROLLBACK"
        );

        return [
            'ok' => false,
            'mensaje' =>
                $resultadoFirma['mensaje']
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR SALDO
    |--------------------------------------------------------------------------
    */

    $sql = "UPDATE vacaciones_saldo
            SET dias_pendientes =
                dias_pendientes + {$dias}
            WHERE id = {$saldo['id']}";


    if(!$db->query($sql)){

        $db->query(
            "ROLLBACK"
        );

        return [
            'ok' => false,
            'mensaje' =>
                'No fue posible actualizar el saldo.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | CREAR NOTIFICACIÓN INTERNA
    |--------------------------------------------------------------------------
    */

    $notificacion_id =
        crear_notificacion(
            $jefe,
            $vacacionId,
            'VACACIONES_PENDIENTE',
            'Nueva solicitud de vacaciones',
            $nombre_colaborador .
            ' solicita vacaciones del ' .
            date(
                'd/m/Y',
                strtotime($fechaInicioOriginal)
            ) .
            ' al ' .
            date(
                'd/m/Y',
                strtotime($fechaFinOriginal)
            ) .
            '.'
        );

    /*
    |--------------------------------------------------------------------------
    | LA NOTIFICACIÓN INTERNA SÍ ES CRÍTICA
    |--------------------------------------------------------------------------
    */

    if(!$notificacion_id){

        $db->query(
            "ROLLBACK"
        );

        return [
            'ok' => false,
            'mensaje' =>
                'No fue posible generar la notificación al jefe.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | CORREO AL JEFE
    |--------------------------------------------------------------------------
    |
    | El correo NO es crítico.
    | Si falla, la solicitud continúa.
    |
    */

    if(!empty($correo_jefe)){

        try {

            /*
            |--------------------------------------------------------------------------
            | URL DE LA SOLICITUD
            |--------------------------------------------------------------------------
            */

            $url_solicitud =
                BASE_URL .
                '/modules/solicitudes/solicitudes_vacaciones.php' .
                '?id=' .
                $vacacionId;


            /*
            |--------------------------------------------------------------------------
            | GENERAR CORREO CON PLANTILLA
            |--------------------------------------------------------------------------
            */

            $contenido_correo =
                generar_correo_vacaciones([
                    'titulo' => 'Nueva solicitud de vacaciones',
                    'nombre_colaborador' => $nombre_colaborador,
                    'mensaje' => 'El colaborador ha generado una nueva solicitud de vacaciones y está pendiente de tu revisión.',
                    'fecha_inicio' => date('d/m/Y', strtotime($fechaInicioOriginal)),
                    'fecha_fin' => date('d/m/Y', strtotime($fechaFinOriginal)),
                    'dias' => $dias,
                    'estado' => 'Pendiente de revisión',
                    'estado_color' => 'azul',
                    'url' => $url_solicitud,
                    'texto_boton' => 'Ver solicitud'
                ]);


            /*
            |--------------------------------------------------------------------------
            | ENVIAR CORREO
            |--------------------------------------------------------------------------
            */

            $resultadoCorreo =
                enviar_correo_a_usuarios(

                    [
                        [
                            'id' => (int)$jefe,
                            'nombre' => $nombre_jefe,
                            'email' => $correo_jefe
                        ]
                    ],
                    'Nueva solicitud de vacaciones',
                    $contenido_correo
                );


            /*
            |--------------------------------------------------------------------------
            | REGISTRAR ERROR
            |--------------------------------------------------------------------------
            */

            if(!$resultadoCorreo){

                error_log(
                    'Correo de solicitud de vacaciones no enviado. ' .
                    'Solicitud: ' .
                    $vacacionId
                );

            }

        } catch(Throwable $e){

            error_log(
                'Excepción enviando correo de vacaciones. ' .
                'Solicitud: ' .
                $vacacionId .
                '. Error: ' .
                $e->getMessage()
            );

        }

    } else {

        error_log(
            'El jefe no tiene correo registrado. ' .
            'Solicitud: ' .
            $vacacionId
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR TRANSACCIÓN
    |--------------------------------------------------------------------------
    */

    if(!$db->query(
        "COMMIT"
    )){

        return [
            'ok' => false,
            'mensaje' =>
                'No fue posible confirmar la transacción.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | ÉXITO
    |--------------------------------------------------------------------------
    */

    return [
        'ok' => true,
        'mensaje' =>
            'La solicitud fue registrada correctamente.'
    ];

}
// function crear_solicitud_vacaciones($usuario_id, $departamento_id, $fechaInicio, $fechaFin, $firma){

//     /*
//     |--------------------------------------------------------------------------
//     | VALIDAR FECHAS
//     |--------------------------------------------------------------------------
//     */

//     if(empty($fechaInicio) || empty($fechaFin)){

//         return [
//             'ok' => false,
//             'mensaje' => 'Debe seleccionar un rango de fechas.'
//         ];

//     }


//     if($fechaInicio > $fechaFin){

//         return [
//             'ok' => false,
//             'mensaje' => 'La fecha inicial no puede ser mayor a la fecha final.'
//         ];

//     }


//     global $db;


//     /*
//     |--------------------------------------------------------------------------
//     | OBTENER SALDO
//     |--------------------------------------------------------------------------
//     */

//     $resultado = obtener_saldo_vigente(
//         $usuario_id
//     );

//     if(!$resultado['ok']){
//         return $resultado;
//     }

//     $saldo = $resultado['saldo'];


//     /*
//     |--------------------------------------------------------------------------
//     | CALCULAR DÍAS
//     |--------------------------------------------------------------------------
//     */

//     $dias = calcular_dias_vacaciones(
//         $fechaInicio,
//         $fechaFin
//     );


//     /*
//     |--------------------------------------------------------------------------
//     | FECHA DE REGRESO
//     |--------------------------------------------------------------------------
//     */

//     $fechaRegreso = calcular_fecha_regreso(
//         $fechaFin
//     );


//     /*
//     |--------------------------------------------------------------------------
//     | VALIDAR SALDO
//     |--------------------------------------------------------------------------
//     */

//     $resultado = validar_saldo(
//         $saldo,
//         $dias
//     );

//     if(!$resultado['ok']){
//         return $resultado;
//     }


//     /*
//     |--------------------------------------------------------------------------
//     | VALIDAR TRASLAPE
//     |--------------------------------------------------------------------------
//     */

//     $validacion = validar_traslape(
//         $usuario_id,
//         $fechaInicio,
//         $fechaFin
//     );

//     if(!$validacion['ok']){
//         return $validacion;
//     }


//     /*
//     |--------------------------------------------------------------------------
//     | VALIDAR CUPO
//     |--------------------------------------------------------------------------
//     */

//     $resultado = validar_cupo(
//         $departamento_id,
//         $fechaInicio,
//         $fechaFin
//     );

//     if(!$resultado['ok']){
//         return $resultado;
//     }


//     /*
//     |--------------------------------------------------------------------------
//     | OBTENER JEFE
//     |--------------------------------------------------------------------------
//     */

//     $jefe = obtener_jefe(
//         $usuario_id
//     );

//     if(empty($jefe)){

//         return [
//             'ok' => false,
//             'mensaje' => 'El colaborador no tiene un jefe asignado.'
//         ];

//     }


//     /*
//     |--------------------------------------------------------------------------
//     | DATOS DEL COLABORADOR
//     |--------------------------------------------------------------------------
//     */

//     $colaborador = obtener_datos_usuario(
//         $usuario_id
//     );

//     $nombre_colaborador =
//         $colaborador['name']
//         ?? 'Un colaborador';


//     /*
//     |--------------------------------------------------------------------------
//     | DATOS DEL JEFE
//     |--------------------------------------------------------------------------
//     */

//     $datos_jefe = obtener_datos_usuario(
//         $jefe
//     );

//     $nombre_jefe =
//         $datos_jefe['name']
//         ?? 'Jefe';

//     $correo_jefe =
//         !empty($datos_jefe['email'])
//         ? trim($datos_jefe['email'])
//         : null;


//     /*
//     |--------------------------------------------------------------------------
//     | ESCAPAR FECHAS
//     |--------------------------------------------------------------------------
//     */

//     $fechaInicio = $db->escape(
//         $fechaInicio
//     );

//     $fechaFin = $db->escape(
//         $fechaFin
//     );


//     /*
//     |--------------------------------------------------------------------------
//     | INICIAR TRANSACCIÓN
//     |--------------------------------------------------------------------------
//     */

//     $db->query(
//         "START TRANSACTION"
//     );


//     /*
//     |--------------------------------------------------------------------------
//     | CREAR SOLICITUD
//     |--------------------------------------------------------------------------
//     */

//     $sql = "INSERT INTO vacaciones(

//                 usuario_id,
//                 saldo_id,
//                 fecha_inicio,
//                 fecha_fin,
//                 fecha_regreso,
//                 dias,
//                 estatus,
//                 jefe_id

//             ) VALUES(

//                 '{$usuario_id}',
//                 '{$saldo['id']}',
//                 '{$fechaInicio}',
//                 '{$fechaFin}',
//                 '{$fechaRegreso}',
//                 '{$dias}',
//                 'PENDIENTE_JEFE',
//                 '{$jefe}'

//             )";


//     if(!$db->query($sql)){

//         $db->query(
//             "ROLLBACK"
//         );

//         return [
//             'ok' => false,
//             'mensaje' => 'No fue posible guardar la solicitud.'
//         ];

//     }


//     /*
//     |--------------------------------------------------------------------------
//     | ID DE LA SOLICITUD
//     |--------------------------------------------------------------------------
//     */

//     $vacacionId =
//         $db->insert_id();


//     /*
//     |--------------------------------------------------------------------------
//     | GUARDAR FIRMA
//     |--------------------------------------------------------------------------
//     */

//     $datosFirma = [
//         'modulo' => 'VACACIONES',
//         'registro_id' => $vacacionId,
//         'tipo' => 'EMPLEADO',
//         'usuario_id' => $usuario_id,
//         'firma' => $firma
//     ];


//     $resultadoFirma =
//         guardar_firma(
//             $datosFirma
//         );


//     if(!$resultadoFirma['ok']){

//         $db->query(
//             "ROLLBACK"
//         );

//         return [
//             'ok' => false,
//             'mensaje' =>
//                 $resultadoFirma['mensaje']
//         ];

//     }


//     /*
//     |--------------------------------------------------------------------------
//     | ACTUALIZAR SALDO
//     |--------------------------------------------------------------------------
//     */

//     $sql = "UPDATE vacaciones_saldo
//             SET dias_pendientes =
//                 dias_pendientes + {$dias}
//             WHERE id = {$saldo['id']}";


//     if(!$db->query($sql)){

//         $db->query(
//             "ROLLBACK"
//         );

//         return [
//             'ok' => false,
//             'mensaje' =>
//                 'No fue posible actualizar el saldo.'
//         ];

//     }


//     /*
//     |--------------------------------------------------------------------------
//     | CREAR NOTIFICACIÓN INTERNA
//     |--------------------------------------------------------------------------
//     */

//     $notificacion_id =
//         crear_notificacion(
//             $jefe,
//             $vacacionId,
//             'VACACIONES_PENDIENTE',
//             'Nueva solicitud de vacaciones',
//             $nombre_colaborador .
//             ' solicita vacaciones del ' .
//             date(
//                 'd/m/Y',
//                 strtotime($fechaInicio)
//             ) .
//             ' al ' .
//             date(
//                 'd/m/Y',
//                 strtotime($fechaFin)
//             ) .
//             '.'

//         );


//     /*
//     |--------------------------------------------------------------------------
//     | LA NOTIFICACIÓN INTERNA SÍ ES CRÍTICA
//     |--------------------------------------------------------------------------
//     */

//     if(!$notificacion_id){

//         $db->query(
//             "ROLLBACK"
//         );

//         return [
//             'ok' => false,
//             'mensaje' =>
//                 'No fue posible generar la notificación al jefe.'
//         ];

//     }


//     /*
//     |--------------------------------------------------------------------------
//     | CORREO AL JEFE
//     |--------------------------------------------------------------------------
//     |
//     | El correo es secundario.
//     | Si falla, NO afecta la solicitud.
//     |
//     */

//     if (!empty($correo_jefe)) {

//         try {

//             /*
//             |--------------------------------------------------------------------------
//             | URL DE LA SOLICITUD
//             |--------------------------------------------------------------------------
//             */

//             $url_solicitud =
//                 BASE_URL .
//                 '/modules/solicitudes/solicitudes_vacaciones.php' .
//                 '?id=' .
//                 $vacacionId;


//             /*
//             |--------------------------------------------------------------------------
//             | GENERAR CORREO CON PLANTILLA
//             |--------------------------------------------------------------------------
//             */

//             $contenido_correo =
//                 generar_correo_vacaciones([

//                     'titulo' =>
//                         'Nueva solicitud de vacaciones',

//                     'nombre_colaborador' =>
//                         $nombre_colaborador,

//                     'mensaje' =>
//                         'El colaborador ha generado una nueva solicitud de vacaciones y está pendiente de tu revisión.',

//                     'fecha_inicio' =>
//                         date(
//                             'd/m/Y',
//                             strtotime($fechaInicio)
//                         ),

//                     'fecha_fin' =>
//                         date(
//                             'd/m/Y',
//                             strtotime($fechaFin)
//                         ),

//                     'dias' =>
//                         $dias,

//                     'estado' =>
//                         'Pendiente de revisión',

//                     'estado_color' =>
//                         'azul',

//                     'url' =>
//                         $url_solicitud,

//                     'texto_boton' =>
//                         'Ver solicitud'

//                 ]);


//             /*
//             |--------------------------------------------------------------------------
//             | ENVIAR
//             |--------------------------------------------------------------------------
//             */

//             $resultadoCorreo =
//                 enviar_correo_a_usuarios(

//                     [
//                         [
//                             'id' =>
//                                 (int)$jefe,

//                             'nombre' =>
//                                 $nombre_jefe,

//                             'email' =>
//                                 $correo_jefe
//                         ]
//                     ],

//                     'Nueva solicitud de vacaciones',

//                     $contenido_correo
//                 );


//             /*
//             |--------------------------------------------------------------------------
//             | REGISTRAR ERROR SIN AFECTAR SOLICITUD
//             |--------------------------------------------------------------------------
//             */

//             if (!$resultadoCorreo) {

//                 error_log(
//                     'Correo de solicitud de vacaciones no enviado. ' .
//                     'Solicitud: ' .
//                     $vacacionId
//                 );

//             }

//         } catch (Throwable $e) {

//             error_log(
//                 'Excepción enviando correo de vacaciones. ' .
//                 'Solicitud: ' .
//                 $vacacionId .
//                 '. Error: ' .
//                 $e->getMessage()
//             );

//         }

//     } else {

//         error_log(
//             'El jefe no tiene correo registrado. ' .
//             'Solicitud: ' .
//             $vacacionId
//         );

//     }

//     /*
//     |--------------------------------------------------------------------------
//     | CONFIRMAR TRANSACCIÓN
//     |--------------------------------------------------------------------------
//     */

//     if(!$db->query(
//         "COMMIT"
//     )){

//         return [
//             'ok' => false,
//             'mensaje' =>
//                 'No fue posible confirmar la transacción.'
//         ];

//     }


//     /*
//     |--------------------------------------------------------------------------
//     | ÉXITO
//     |--------------------------------------------------------------------------
//     */

//     return [
//         'ok' => true,
//         'mensaje' =>
//             'La solicitud fue registrada correctamente.'
//     ];

// }
function validar_traslape($usuario_id, $fecha_inicio, $fecha_fin){

    global $db;

    $usuario_id = (int)$usuario_id;

    $fecha_inicio = $db->escape($fecha_inicio);
    $fecha_fin    = $db->escape($fecha_fin);

    $sql = "SELECT id
            FROM vacaciones
            WHERE usuario_id = {$usuario_id}
              AND estatus IN (
                    'PENDIENTE_JEFE',
                    'PENDIENTE_RH',
                    'APROBADA'
              )
              AND fecha_inicio <= '{$fecha_fin}'
              AND fecha_fin >= '{$fecha_inicio}'
            LIMIT 1";

    $resultado = find_by_sql($sql);

    if(!empty($resultado)){
        return [
            'ok' => false,
            'mensaje' => 'Ya existe una solicitud de vacaciones que se cruza con las fechas seleccionadas.'
        ];
    }

    return ['ok' => true];
}
function obtener_saldo_vigente($usuario_id){

    global $db;

    $usuario_id = (int)$usuario_id;

    $sql = "SELECT *
            FROM vacaciones_saldo
            WHERE usuario_id = {$usuario_id}
              AND estatus = 'VIGENTE'
            LIMIT 1";

    $saldo = $db->fetch_assoc($db->query($sql));

    if(!$saldo){
        return [
            'ok' => false,
            'mensaje' => 'No existe un saldo de vacaciones vigente.'
        ];
    }

    $saldo['dias_disponibles'] =
        $saldo['dias_otorgados']
        - $saldo['dias_disfrutados']
        - $saldo['dias_pendientes'];

    return [
        'ok' => true,
        'saldo' => $saldo
    ];
}
function calcular_dias_vacaciones($fechaInicio, $fechaFin){

    $inicio = new DateTime($fechaInicio);
    $fin    = new DateTime($fechaFin);

    return $inicio->diff($fin)->days + 1;
}
function calcular_fecha_regreso($fechaFin){

    $fecha = new DateTime($fechaFin);
    $fecha->modify('+1 day');

    return $fecha->format('Y-m-d');
}
function obtener_jefe($usuario_id){

    global $db;

    $usuario_id = (int)$usuario_id;

    $sql = "SELECT jefe
            FROM vw_vacaciones_usuario
            WHERE id = {$usuario_id}
            LIMIT 1";

    $resultado = $db->fetch_assoc($db->query($sql));

    return $resultado ? (int)$resultado['jefe'] : null;
}
function obtener_datos_usuario($usuario_id){

    global $db;

    $usuario_id = (int)$usuario_id;

    $sql = "SELECT
                id,
                name,
                email
            FROM users
            WHERE id = {$usuario_id}
            LIMIT 1";

    $resultado = $db->fetch_assoc(
        $db->query($sql)
    );

    return $resultado ?: null;
}
function validar_saldo($saldo, $dias){

    if($dias > $saldo['dias_disponibles']){

        return [
            'ok' => false,
            'mensaje' => 'No cuentas con saldo suficiente para realizar la solicitud.'
        ];

    }

    return [
        'ok' => true
    ];

}
function validar_cupo($departamento, $fechaInicio, $fechaFin){

    $vacaciones = get_disponibilidad_vacaciones(
        $departamento,
        $fechaInicio,
        $fechaFin
    );

    $conteo = obtener_conteo_vacaciones($vacaciones);

    $inicio = new DateTime($fechaInicio);

    $fin = new DateTime($fechaFin);

    $fin->modify('+1 day');

    $periodo = new DatePeriod(
        $inicio,
        new DateInterval('P1D'),
        $fin
    );

    foreach($periodo as $fecha){

        $dia = $fecha->format('Y-m-d');

        $ocupados = $conteo[$dia] ?? 0;

        if($ocupados >= CUPO_MAXIMO_VACACIONES){

            return [
                'ok' => false,
                'mensaje' => "El día {$dia} ya alcanzó el cupo máximo de vacaciones."
            ];

        }

    }

    return [
        'ok' => true
    ];

}
function construir_disponibilidad_calendario($ocupacion, $anio, $mes, $cupo_maximo){

    $dias_en_mes = (int)date('t', mktime(0, 0, 0, $mes, 1, $anio));

    // Inicializar contador
    $conteo = [];

    for($d = 1; $d <= $dias_en_mes; $d++){

        $conteo[sprintf('%04d-%02d-%02d', $anio, $mes, $d)] = 0;

    }

    // Contar personas por día
    foreach($ocupacion as $rango){

        $inicio = new DateTime($rango['fecha_inicio']);
        $fin    = new DateTime($rango['fecha_fin']);
        $fin->modify('+1 day');

        $periodo = new DatePeriod(
            $inicio,
            new DateInterval('P1D'),
            $fin
        );

        foreach($periodo as $fecha){

            $clave = $fecha->format('Y-m-d');

            if(isset($conteo[$clave])){
                $conteo[$clave]++;
            }

        }

    }

    $hoy = date('Y-m-d');

    $fechaMinima = date(
        'Y-m-d',
        strtotime('+' . DIAS_ANTICIPACION . ' days')
    );

    $disponibilidad = [];

    foreach($conteo as $fecha => $ocupados){

        if($fecha < $hoy){
            $estado = 'pasado';
        }
        elseif($fecha < $fechaMinima){
            $estado = 'bloqueado';
        }
        elseif($ocupados >= $cupo_maximo){
            $estado = 'lleno';
        }
        elseif($ocupados == ($cupo_maximo - 1)){
            $estado = 'uno';
        }
        else{
            $estado = 'disponible';
        }
        $disponibilidad[$fecha] = [
            'estado'   => $estado,
            'ocupados' => $ocupados,
            'libres'   => max(0, $cupo_maximo - $ocupados)
        ];

    }

    return $disponibilidad;

}
function obtener_conteo_vacaciones($vacaciones){

    $conteo = [];

    foreach($vacaciones as $vacacion){

        $inicio = new DateTime($vacacion['fecha_inicio']);

        $fin = new DateTime($vacacion['fecha_fin']);

        $fin->modify('+1 day');

        $periodo = new DatePeriod(
            $inicio,
            new DateInterval('P1D'),
            $fin
        );

        foreach($periodo as $fecha){

            $dia = $fecha->format('Y-m-d');

            if(!isset($conteo[$dia])){

                $conteo[$dia] = 0;

            }

            $conteo[$dia]++;

        }

    }

    return $conteo;

}