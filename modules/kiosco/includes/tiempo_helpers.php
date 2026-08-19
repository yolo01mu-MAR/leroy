<?php

function get_faltas_tiempo($usuario_id){
    global $db;
    $usuario_id = (int)$usuario_id;

    $sql = "SELECT
                a.ID,
                a.id_empleado,
                a.fecha,
                ta.tipo,
                a.observaciones
            FROM asistencia a
                INNER JOIN tipo_asistencia ta ON ta.ID = a.asistencia	
                LEFT JOIN tiempo_solicitud ts ON ts.asistencia_id = a.ID
            WHERE ta.tipo = 'FALTA INJUSTIFICADA' AND a.id_empleado = {$usuario_id}
            AND ts.id IS NULL
            ORDER BY a.fecha DESC";
    return find_by_sql($sql);
}
function get_solicitudes_TXT_usuario($usuario_id){
    global $db;
    $usuario_id = (int)$usuario_id;

    $sql = "SELECT 
                ts.id,
                ts.fecha_solicitud,
                COALESCE(a.fecha, ts.fecha_falta) AS fecha_falta,
                ts.origen,
                ts.fecha_programada,
                ts.estatus
            FROM tiempo_solicitud ts
                INNER JOIN users u ON u.id = ts.usuario_id
                LEFT JOIN asistencia a ON a.ID = ts.asistencia_id
            WHERE ts.usuario_id = {$usuario_id  }
            ORDER BY ts.fecha_solicitud DESC";

    return find_by_sql($sql);
}
function get_calendario_txt($anio, $mes){

    $calendario = [];

    $diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

    $hoy = date('Y-m-d');

    for($d = 1; $d <= $diasMes; $d++){

        $fecha = sprintf(
            "%04d-%02d-%02d",
            $anio,
            $mes,
            $d
        );

        $estado = "disponible";
        $seleccionable = true;

        // Solo bloquear días anteriores
        if($fecha < $hoy){

            $estado = "pasado";
            $seleccionable = false;

        }

        $calendario[$fecha] = [

            "estado" => $estado,
            "seleccionable" => $seleccionable

        ];

    }

    return $calendario;

}