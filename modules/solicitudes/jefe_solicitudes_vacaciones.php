<?php

$page_title = 'Solicitudes de vacaciones';

require_once __DIR__ . '/../../app/bootstrap.php';

  $scripts = [
    'jefe_solicitudes_vacaciones'
  ];

page_require_level(2);

$jefe_id = (int)$_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| ESTATUS
|--------------------------------------------------------------------------
*/

$estatus_meta = [

    'PENDIENTE_JEFE' => [
        'label' => 'Pendiente',
        'clase' => 'estatus-pendiente',
        'icono' => 'glyphicon-time'
    ],

    'PENDIENTE_RH' => [
        'label' => 'Aprobada',
        'clase' => 'estatus-rh',
        'icono' => 'glyphicon-transfer'
    ],

    'RECHAZADA_JEFE' => [
        'label' => 'Rechazada',
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
| FILTRO
|--------------------------------------------------------------------------
*/

$estatus_filtro = $_GET['estatus'] ?? 'PENDIENTE_JEFE';

if (
    $estatus_filtro !== 'TODAS' &&
    !array_key_exists($estatus_filtro, $estatus_meta)
) {
    $estatus_filtro = 'PENDIENTE_JEFE';
}


/*
|--------------------------------------------------------------------------
| CONDICIÓN
|--------------------------------------------------------------------------
*/

$condicion_estatus = '';

if ($estatus_filtro !== 'TODAS') {
    $estatus_seguro = remove_junk($estatus_filtro);
    $condicion_estatus = "
        AND v.estatus = '{$estatus_seguro}'
    ";
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
            INNER JOIN vw_usuarios_completos vu ON v.usuario_id = vu.id
        WHERE v.jefe_id = {$jefe_id}
        {$condicion_estatus}
        ORDER BY v.fecha_solicitud ASC
";

$solicitudes = find_by_sql($sql);

/*
|--------------------------------------------------------------------------
| PENDIENTES
|--------------------------------------------------------------------------
*/

$sql_pendientes = "
    SELECT COUNT(*) AS total
    FROM vacaciones
    WHERE jefe_id = {$jefe_id}
      AND estatus = 'PENDIENTE_JEFE'
";

$resultado_pendientes = find_by_sql($sql_pendientes);

$total_pendientes = (int)(
    $resultado_pendientes[0]['total'] ?? 0
);


/*
|--------------------------------------------------------------------------
| FUNCIONES
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

?>

<?php include_once __DIR__ . '/../../layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/solicitudes.css">
<div class="row">
   <div class="col-md-12">
     <?php echo display_msg($msg); ?>
   </div>
</div>
<div class="row">
   <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <div class="vacaciones-header">
                    <h1>Solicitudes de vacaciones</h1>
                    <p>Revisión de solicitudes de colaboradores</p>
                </div>
            </div>
            <div class="panel-body">
                <!-- FILTROS -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="vacaciones-tabs">

                            <!-- TODAS -->
                            <a href="#" data-estatus="TODAS" class="vacaciones-tab <?= $estatus_filtro === 'TODAS' ? 'activo' : ''; ?>">
                                Todas
                            </a>

                            <!-- ESTATUS -->
                            <?php foreach ($estatus_meta as $codigo => $meta_tab): ?>
                                <a
                                    href="#"
                                    data-estatus="<?= $codigo; ?>"
                                    class="vacaciones-tab <?= $estatus_filtro === $codigo ? 'activo' : ''; ?>"
                                >
                                    <?= $meta_tab['label']; ?>
                                    <?php if (
                                        $codigo === 'PENDIENTE_JEFE' &&
                                        $total_pendientes > 0
                                    ): ?>
                                        <span class="vacaciones-contador">
                                            <?= $total_pendientes; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <!-- BANDEJA -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="vacaciones-inbox">
                            <!-- LISTA -->
                            <div class="vacaciones-lista">

                                <!-- BUSCADOR -->
                                <div class="vacaciones-buscador">
                                    <span class="glyphicon glyphicon-search"></span>
                                    <input
                                        type="text"
                                        id="buscarColaborador"
                                        placeholder="Buscar colaborador..."
                                    >
                                </div>

                                <!-- SOLICITUDES -->
                                <div id="listaSolicitudes">

                                    <?php if (empty($solicitudes)): ?>

                                        <div class="vacaciones-vacio">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                            <p>No hay solicitudes con este filtro.</p>
                                        </div>

                                    <?php else: ?>

                                        <?php foreach ($solicitudes as $indice => $solicitud): ?>

                                            <?php
                                                $meta = $estatus_meta[$solicitud['estatus']] ?? [
                                                    'label' => $solicitud['estatus'],
                                                    'clase' => 'estatus-default',
                                                    'icono' => 'glyphicon-question-sign'
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

                                                <div class="vacaciones-avatar">
                                                    <?= iniciales_colaborador($solicitud['nombre']); ?>
                                                </div>

                                                <div class="vacaciones-item-info">

                                                    <div class="vacaciones-item-nombre">
                                                        <?= remove_junk($solicitud['nombre']); ?>
                                                    </div>

                                                    <div class="vacaciones-item-sub">
                                                        <?= (int)$solicitud['dias']; ?>
                                                        día<?= (int)$solicitud['dias'] === 1 ? '' : 's'; ?>
                                                        · desde
                                                        <?= date(
                                                            'd/m/Y',
                                                            strtotime($solicitud['fecha_inicio'])
                                                        ); ?>
                                                    </div>

                                                </div>

                                                <span class="vacaciones-status <?= $meta['clase']; ?>">
                                                    <span class="glyphicon <?= $meta['icono']; ?>"></span>
                                                    <?= $meta['label']; ?>
                                                </span>

                                            </div>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                </div>
                            </div>

                            <!-- DETALLE -->
                            <div class="vacaciones-detalle" id="detalleSolicitud">
                                <div class="vacaciones-detalle-vacio">
                                    <span class="glyphicon glyphicon-hand-left"></span>
                                    <p>Selecciona una solicitud para ver el detalle.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   </div>
</div>
<div class="container-fluid vacaciones-jefe">
<script>
    window.usuarioJefeId = <?= (int)$_SESSION['user_id']; ?>;
</script>
<script src="<?= BASE_URL ?>/firmas/firmas/assets/js/signature_pad.min.js"></script>
<script src="<?= BASE_URL ?>/firmas/firmas/assets/js/firmas.js"></script>
<script src="<?= BASE_URL ?>/firmas/vacaciones/assets/js/firma_vacaciones_jefe.js"></script>
<?php include_once __DIR__ . '/../../layouts/footer.php'; ?>