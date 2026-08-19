<?php

require_once __DIR__ . '/../../../firmas/firmas/includes/firmas.php';

// Cupo máximo de personas simultáneas por departamento
define('CUPO_MAXIMO_VACACIONES', 2);

// Días mínimos de anticipación para solicitar vacaciones
define('DIAS_ANTICIPACION', 8);

/**
 * Convierte los rangos (fecha_inicio, fecha_fin) en un arreglo
 * ['YYYY-MM-DD' => 'estado'] para pintar el calendario.
 */
function crear_solicitud_vacaciones($usuario_id, $departamento_id, $fechaInicio, $fechaFin, $firma){

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

    // Obtener saldo
    $resultado = obtener_saldo_vigente($usuario_id);

    if(!$resultado['ok']){
        return $resultado;
    }

    $saldo = $resultado['saldo'];

    // Calcular días
    $dias = calcular_dias_vacaciones($fechaInicio,$fechaFin);

    // Fecha de regreso
    $fechaRegreso = calcular_fecha_regreso($fechaFin);

    // Validar saldo
    $resultado = validar_saldo($saldo,$dias);

    if(!$resultado['ok']){
        return $resultado;
    }

    // Traslape
    $validacion = validar_traslape($usuario_id, $fechaInicio, $fechaFin);

    if(!$validacion['ok']){
        return $validacion;
    }

    // Validar cupo
    $resultado = validar_cupo($departamento_id,$fechaInicio,$fechaFin);

    if(!$resultado['ok']){
        return $resultado;
    }

    // Validar Jefe
    $jefe = obtener_jefe($usuario_id);

    if(empty($jefe)){
        return [
            'ok' => false,
            'mensaje' => 'El colaborador no tiene un jefe asignado.'
        ];
    }

    $fechaInicio = $db->escape($fechaInicio);
    $fechaFin    = $db->escape($fechaFin);

    $db->query("START TRANSACTION");

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

        $db->query("ROLLBACK");

        return [
            'ok'=>false,
            'mensaje'=>'No fue posible guardar la solicitud.'
        ];

    }

    $vacacionId = $db->insert_id();

    $datosFirma = [
        'modulo'      => 'VACACIONES',
        'registro_id' => $vacacionId,
        'tipo'        => 'EMPLEADO',
        'usuario_id'  => $usuario_id,
        'firma'       => $firma
    ];

    $resultadoFirma = guardar_firma($datosFirma);

    if(!$resultadoFirma['ok']){

        $db->query("ROLLBACK");

        return [
            'ok' => false,
            'mensaje' => $resultadoFirma['mensaje']
        ];

    }

    $sql = "UPDATE vacaciones_saldo
            SET dias_pendientes = dias_pendientes + {$dias}
            WHERE id = {$saldo['id']}";

    if(!$db->query($sql)){

        $db->query("ROLLBACK");

        return [
            'ok'=>false,
            'mensaje'=>'No fue posible actualizar el saldo.'
        ];

    }   
    
    if(!$db->query("COMMIT")){

        return [
            'ok'=>false,
            'mensaje'=>'No fue posible confirmar la transacción.'
        ];

    }

    return [
        'ok' => true,
        'mensaje' => 'La solicitud fue registrada correctamente.'
    ];

}
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