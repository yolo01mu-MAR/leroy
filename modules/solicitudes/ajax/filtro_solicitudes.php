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
| JEFE ACTUAL
|--------------------------------------------------------------------------
*/

$jefe_id = (int)$_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| ESTATUS SOLICITADO
|--------------------------------------------------------------------------
*/

$estatus = $_POST['estatus'] ?? 'PENDIENTE_JEFE';


/*
|--------------------------------------------------------------------------
| ESTATUS PERMITIDOS
|--------------------------------------------------------------------------
*/

$estatus_permitidos = [
    'TODAS',
    'PENDIENTE_JEFE',
    'PENDIENTE_RH',
    'APROBADA',
    'RECHAZADA_JEFE',
    'RECHAZADA_RH'
];


if (!in_array($estatus, $estatus_permitidos, true)) {

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

    case 'PENDIENTE_JEFE':
        $condicion = "
            AND v.estatus = 'PENDIENTE_JEFE'
        ";
        break;

    case 'PENDIENTE_RH':
        $condicion = "
            AND v.estatus = 'PENDIENTE_RH'
        ";
        break;

    case 'APROBADA':
        $condicion = "
            AND v.estatus = 'APROBADA'
        ";
        break;

    case 'RECHAZADA_JEFE':
        $condicion = "
            AND v.estatus = 'RECHAZADA_JEFE'
        ";
        break;

    case 'RECHAZADA_RH':
        $condicion = "
            AND v.estatus = 'RECHAZADA_RH'
        ";
        break;

    case 'TODAS':
        $condicion = "
            AND v.estatus IN (
                'PENDIENTE_JEFE',
                'PENDIENTE_RH',
                'APROBADA',
                'RECHAZADA_JEFE',
                'RECHAZADA_RH'
            )
        ";
        break;

    default:
        $condicion = "
            AND v.estatus = 'PENDIENTE_JEFE'
        ";
        break;
}


/*
|--------------------------------------------------------------------------
| CONSULTA
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
            INNER JOIN vw_usuarios_completos vu ON v.usuario_id = vu.id
        WHERE v.jefe_id = {$jefe_id}
            {$condicion}
        ORDER BY v.fecha_solicitud ASC";


$solicitudes = find_by_sql($sql);


/*
|--------------------------------------------------------------------------
| CATÁLOGO DE ESTATUS REALES
|--------------------------------------------------------------------------
*/

$estatus_meta = [

    'PENDIENTE_JEFE' => [
        'label' => 'Pendientes',
        'clase' => 'estatus-pendiente',
        'icono' => 'glyphicon-time'
    ],

    'PENDIENTE_RH' => [
        'label' => 'En RH',
        'clase' => 'estatus-rh',
        'icono' => 'glyphicon-transfer'
    ],

    'APROBADA' => [
        'label' => 'Aprobada',
        'clase' => 'estatus-aprobada',
        'icono' => 'glyphicon-ok'
    ],

    'RECHAZADA_JEFE' => [
        'label' => 'Rechazada por jefe',
        'clase' => 'estatus-rechazada',
        'icono' => 'glyphicon-remove'
    ],

    'RECHAZADA_RH' => [
        'label' => 'Rechazada por RH',
        'clase' => 'estatus-rechazada',
        'icono' => 'glyphicon-remove'
    ]

];


/*
|--------------------------------------------------------------------------
| SIN RESULTADOS
|--------------------------------------------------------------------------
*/

if (empty($solicitudes)) {

    echo '

        <div class="vacaciones-vacio">

            <span class="glyphicon glyphicon-calendar"></span>

            <p>
                No hay solicitudes con este filtro.
            </p>

        </div>

    ';

    exit;

}


/*
|--------------------------------------------------------------------------
| MOSTRAR SOLICITUDES
|--------------------------------------------------------------------------
*/

foreach ($solicitudes as $solicitud):


    $meta =
        $estatus_meta[$solicitud['estatus']]
        ??
        [

            'label' =>
                $solicitud['estatus'],

            'clase' =>
                'estatus-default',

            'icono' =>
                'glyphicon-question-sign'

        ];

?>

    <div
        class="vacaciones-item"

        data-id="<?= (int)$solicitud['id']; ?>"

        data-nombre="<?= htmlspecialchars(
            remove_junk($solicitud['nombre']),
            ENT_QUOTES
        ); ?>"

        data-puesto="<?= htmlspecialchars(
            remove_junk($solicitud['puesto']),
            ENT_QUOTES
        ); ?>"

        data-departamento="<?= htmlspecialchars(
            remove_junk($solicitud['departamentos']),
            ENT_QUOTES
        ); ?>"

        data-cuadrilla="<?= htmlspecialchars(
            remove_junk($solicitud['dep_cuadrilla']),
            ENT_QUOTES
        ); ?>"

        data-grupo="<?= htmlspecialchars(
            remove_junk($solicitud['grupos']),
            ENT_QUOTES
        ); ?>"

        data-fecha-solicitud="<?= date(
            'd/m/Y',
            strtotime($solicitud['fecha_solicitud'])
        ); ?>"

        data-fecha-inicio="<?= date(
            'd/m/Y',
            strtotime($solicitud['fecha_inicio'])
        ); ?>"

        data-dias="<?= (int)$solicitud['dias']; ?>"

        data-estatus="<?= htmlspecialchars(
            $solicitud['estatus'],
            ENT_QUOTES
        ); ?>"

        data-estatus-clase="<?= htmlspecialchars(
            $meta['clase'],
            ENT_QUOTES
        ); ?>"

        data-estatus-icono="<?= htmlspecialchars(
            $meta['icono'],
            ENT_QUOTES
        ); ?>"
    >
        <!-- AVATAR -->
        <div class="vacaciones-avatar">
            <?= iniciales_colaborador($solicitud['nombre']); ?>
        </div>
        <!-- INFORMACIÓN -->
        <div class="vacaciones-item-info">
            <div class="vacaciones-item-nombre">
                <?= remove_junk($solicitud['nombre']); ?>
            </div>
            <div class="vacaciones-item-sub">
                <?= (int)$solicitud['dias']; ?>
                día<?= (int)$solicitud['dias'] === 1
                    ? ''
                    : 's'; ?>
                · desde
                <?= date('d/m/Y', strtotime($solicitud['fecha_inicio'])); ?>
            </div>
        </div>
        <!-- ESTATUS -->
        <span class="vacaciones-status <?= $meta['clase']; ?>">
            <span class="glyphicon <?= $meta['icono']; ?>"></span>
            <?= $meta['label']; ?>
        </span>
    </div>

<?php endforeach; ?>