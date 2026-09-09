<?php

require_once __DIR__ . '/../../../app/bootstrap.php';

page_require_level(5);

header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| INICIALES
|--------------------------------------------------------------------------
*/

function iniciales_colaborador($nombre)
{
    $partes = preg_split('/\s+/', trim($nombre));

    $iniciales = '';

    foreach (array_slice($partes, 0, 2) as $parte) {

        $iniciales .= mb_strtoupper(
            mb_substr($parte, 0, 1)
        );

    }

    return $iniciales ?: '?';
}

/*
|--------------------------------------------------------------------------
| ESTATUS
|--------------------------------------------------------------------------
*/

$estatus = $_POST['estatus'] ?? 'PENDIENTE_RH';

/*
|--------------------------------------------------------------------------
| VALIDAR ESTATUS
|--------------------------------------------------------------------------
*/

$estatus_validos = [
    'TODAS',
    'PENDIENTE_RH',
    'APROBADA',
    'RECHAZADA_RH'
];

if (!in_array($estatus, $estatus_validos, true)) {

    echo json_encode([
        'success' => false,
        'message' => 'Estatus no válido.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| CONDICIÓN
|--------------------------------------------------------------------------
*/

$condicion = '';

switch ($estatus) {

    case 'PENDIENTE_RH':
        $where = "WHERE v.estatus = 'PENDIENTE_RH'";
        break;

    case 'APROBADA':
        $where = "WHERE v.estatus = 'APROBADA'";
        break;

    case 'RECHAZADA_RH':
        $where = "WHERE v.estatus = 'RECHAZADA_RH'";
        break;

    case 'TODAS':
        $where = "WHERE v.estatus IN (
            'PENDIENTE_RH',
            'APROBADA',
            'RECHAZADA_RH'
        )";
        break;

    default:
        $where = "WHERE v.estatus = 'PENDIENTE_RH'";
        break;
}

/*
|--------------------------------------------------------------------------
| SOLICITUDES
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            v.id,
            v.usuario_id,
            v.fecha_solicitud,
            v.fecha_inicio,
            v.dias,
            v.estatus,
            vu.nombre,
            vu.puesto,
            vu.departamentos,
            vu.dep_cuadrilla,
            vu.grupos
        FROM vacaciones v
        INNER JOIN vw_usuarios_completos vu
            ON v.usuario_id = vu.id
        {$where}
        ORDER BY v.fecha_solicitud ASC";

$solicitudes = find_by_sql($sql);

/*
|--------------------------------------------------------------------------
| CONTADOR DE PENDIENTES
|--------------------------------------------------------------------------
*/

$sql_pendientes = "
    SELECT COUNT(*) AS total
    FROM vacaciones
    WHERE estatus = 'PENDIENTE_RH'
";

$resultado_pendientes = find_by_sql($sql_pendientes);

$total_pendientes = (int)($resultado_pendientes[0]['total'] ?? 0);

/*
|--------------------------------------------------------------------------
| CATÁLOGO DE ESTATUS
|--------------------------------------------------------------------------
*/

$estatus_meta = [

    'PENDIENTE_RH' => [
        'label' => 'Pendientes',
        'clase' => 'estatus-rh',
        'icono' => 'glyphicon-transfer'
    ],

    'APROBADA' => [
        'label' => 'Aprobada',
        'clase' => 'estatus-aprobada',
        'icono' => 'glyphicon-ok'
    ],

    'RECHAZADA_RH' => [
        'label' => 'Rechazada',
        'clase' => 'estatus-rechazada',
        'icono' => 'glyphicon-remove'
    ]

];

/*
|--------------------------------------------------------------------------
| CONSTRUIR HTML
|--------------------------------------------------------------------------
*/

$html = '';

/*
|--------------------------------------------------------------------------
| SIN RESULTADOS
|--------------------------------------------------------------------------
*/

if (empty($solicitudes)) {

    $html = '
        <div class="vacaciones-vacio">
            <span class="glyphicon glyphicon-calendar"></span>
            <p>No hay solicitudes con este filtro.</p>
        </div>
    ';

} else {

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR SOLICITUDES
    |--------------------------------------------------------------------------
    */

    foreach ($solicitudes as $solicitud) {

        $meta =
            $estatus_meta[$solicitud['estatus']]
            ??
            [
                'label' => $solicitud['estatus'],
                'clase' => 'estatus-default',
                'icono' => 'glyphicon-question-sign'
            ];

        $nombre = htmlspecialchars(
            remove_junk($solicitud['nombre']),
            ENT_QUOTES,
            'UTF-8'
        );

        $puesto = htmlspecialchars(
            remove_junk($solicitud['puesto']),
            ENT_QUOTES,
            'UTF-8'
        );

        $departamento = htmlspecialchars(
            remove_junk($solicitud['departamentos']),
            ENT_QUOTES,
            'UTF-8'
        );

        $cuadrilla = htmlspecialchars(
            remove_junk($solicitud['dep_cuadrilla']),
            ENT_QUOTES,
            'UTF-8'
        );

        $grupo = htmlspecialchars(
            remove_junk($solicitud['grupos']),
            ENT_QUOTES,
            'UTF-8'
        );

        $fecha_solicitud = date(
            'd/m/Y',
            strtotime($solicitud['fecha_solicitud'])
        );

        $fecha_inicio = date(
            'd/m/Y',
            strtotime($solicitud['fecha_inicio'])
        );

        $dias = (int)$solicitud['dias'];

        $label = htmlspecialchars(
            $meta['label'],
            ENT_QUOTES,
            'UTF-8'
        );

        $clase = htmlspecialchars(
            $meta['clase'],
            ENT_QUOTES,
            'UTF-8'
        );

        $icono = htmlspecialchars(
            $meta['icono'],
            ENT_QUOTES,
            'UTF-8'
        );

        $iniciales = iniciales_colaborador(
            $solicitud['nombre']
        );

        $html .= '

        <div
            class="vacaciones-item"
            data-id="' . (int)$solicitud['id'] . '"
            data-nombre="' . $nombre . '"
            data-puesto="' . $puesto . '"
            data-departamento="' . $departamento . '"
            data-cuadrilla="' . $cuadrilla . '"
            data-grupo="' . $grupo . '"
            data-fecha-solicitud="' . $fecha_solicitud . '"
            data-fecha-inicio="' . $fecha_inicio . '"
            data-dias="' . $dias . '"
            data-estatus="' . $label . '"
            data-estatus-clase="' . $clase . '"
            data-estatus-icono="' . $icono . '"
        >

            <!-- AVATAR -->
            <div class="vacaciones-avatar">
                ' . htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') . '
            </div>
            <!-- INFORMACIÓN -->
            <div class="vacaciones-item-info">
                <div class="vacaciones-item-nombre">
                    ' . $nombre . '
                </div>
                <div class="vacaciones-item-sub">
                    ' . $dias . '
                    día' . ($dias === 1 ? '' : 's') . '
                    · desde
                    ' . $fecha_inicio . '
                </div>
            </div>
            <!-- ESTATUS -->
            <span class="vacaciones-status ' . $clase . '">
                <span class="glyphicon ' . $icono . '"></span>
                ' . $label . '
            </span>
        </div>
        ';
    }

}

/*
|--------------------------------------------------------------------------
| RESPUESTA JSON
|--------------------------------------------------------------------------
*/

echo json_encode([
    'success' => true,
    'html' => $html,
    'total_pendientes' => $total_pendientes
], JSON_UNESCAPED_UNICODE);

exit;