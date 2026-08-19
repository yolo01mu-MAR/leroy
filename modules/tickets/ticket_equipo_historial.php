<?php
$page_title = 'Historial de Equipo';
require_once __DIR__ . '/../../app/bootstrap.php';

$equipo_id = (int)$_GET['equipo_id'];

$sqlEquipo = "SELECT
                e.id, 
			    e.codigo_equipo, 
				e.marca, 
				e.modelo,
                et.nombre AS tipo,
                d.zona AS departamento
              FROM equipo e
                    LEFT JOIN equipo_tipo et ON et.id = e.tipo_equipo
                    LEFT JOIN equipo_ubicacion eu ON eu.id_equipo = e.id
                    LEFT JOIN departamento d ON d.ID = eu.id_departamento
                WHERE e.id = {$equipo_id}
                LIMIT 1";

$equipo = find_by_sql($sqlEquipo);

// if (empty($equipo)) {
//     redirect('ticket_reportes.php');
// }

$equipo = $equipo[0];

$sqlTickets = "SELECT
                    t.id, t.folio, t.asunto, t.descripcion,
                    t.fecha_creacion, t.fecha_cierre,
                    te.nombre_estatus, tp.nombre_prioridad,
                    u.name AS empleado,
                    asig.name AS responsable
                FROM ticket t
                    INNER JOIN ticket_estatus te ON te.id = t.estatus_id
                    LEFT JOIN ticket_prioridad tp ON tp.id = t.prioridad_id
                    INNER JOIN users u ON u.id = t.usuario_id
                    LEFT JOIN users asig ON asig.id = t.asignado_a
                WHERE t.equipo_id = {$equipo_id}
                ORDER BY t.fecha_creacion DESC";

$tickets = find_by_sql($sqlTickets);

$totalTickets = count($tickets);
$resueltos = 0;
$horas = [];

foreach ($tickets as $tk) {
    if (!empty($tk['fecha_cierre']) && in_array(strtoupper($tk['nombre_estatus']), ['RESUELTO', 'CERRADO'])) {
        $resueltos++;
        $horas[] = (strtotime($tk['fecha_cierre']) - strtotime($tk['fecha_creacion'])) / 3600;
    }
}

$promedioHoras = !empty($horas) ? round(array_sum($horas) / count($horas), 1) : null;

?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/ticket.css"/>
<div class="row">
    <div class="col-md-10 col-md-offset-1">

        <a href="ticket_reportes.php" class="btn btn-default btn-xs" style="margin-bottom:14px;">
            <span class="glyphicon glyphicon-arrow-left"></span> Regresar a reportes
        </a>

        <div class="tk-card">
            <div class="tk-card-heading">
                <span class="glyphicon glyphicon-print"></span>
                <?php if($equipo['tipo'] == 'IMPRESORA'){?>
                    <?php echo remove_junk($equipo['departamento']. ' - ' .$equipo['marca'] . ' ' . $equipo['modelo']); ?>
                <?php } else { ?>
                    <?php echo remove_junk($equipo['marca'] . ' ' . $equipo['modelo']); ?>
                <?php } ?>
                <small class="text-muted">— <?php echo remove_junk($equipo['codigo_equipo']); ?></small>
            </div>
            <div class="tk-card-body">
                <div class="row text-center">
                    <div class="col-xs-4">
                        <div style="font-size:22px;font-weight:700;color:#337ab7;"><?php echo $totalTickets; ?></div>
                        <small class="text-muted">Tickets totales</small>
                    </div>
                    <div class="col-xs-4">
                        <div style="font-size:22px;font-weight:700;color:#5cb85c;"><?php echo $resueltos; ?></div>
                        <small class="text-muted">Resueltos</small>
                    </div>
                    <div class="col-xs-4">
                        <div style="font-size:22px;font-weight:700;color:#f0ad4e;">
                            <?php echo $promedioHoras !== null ? $promedioHoras . 'h' : '—'; ?>
                        </div>
                        <small class="text-muted">Tiempo prom. solución</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="tk-card">
            <div class="tk-card-heading">
                <span class="glyphicon glyphicon-time"></span> Historial de reportes
            </div>
            <div class="tk-card-body">

                <?php if (empty($tickets)): ?>
                    <p class="text-muted text-center">Este equipo no tiene tickets registrados.</p>
                <?php else: ?>

                    <ul class="tk-timeline">
                        <?php foreach ($tickets as $tk): ?>
                            <li class="tk-timeline-item">
                                <div>
                                    <a href="ticket_detalle_admin.php?id=<?php echo (int)$tk['id']; ?>">
                                        <strong><?php echo remove_junk($tk['folio']); ?></strong>
                                    </a>
                                    — <?php echo remove_junk($tk['asunto']); ?>
                                </div>
                                <div style="margin:4px 0;">
                                    <span class="tk-status <?php echo strtolower($tk['nombre_estatus']); ?>">
                                        <?php echo strtoupper(remove_junk($tk['nombre_estatus'])); ?>
                                    </span>
                                    <?php if (!empty($tk['nombre_prioridad'])): ?>
                                        <span class="tk-priority <?php echo strtolower($tk['nombre_prioridad']); ?>">
                                            <?php echo remove_junk($tk['nombre_prioridad']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    Reportado por <?php echo remove_junk($tk['empleado']); ?>
                                    · <?php echo date('d/m/Y H:i', strtotime($tk['fecha_creacion'])); ?>
                                    <?php if (!empty($tk['responsable'])): ?>
                                        · Atendido por <?php echo remove_junk($tk['responsable']); ?>
                                    <?php endif; ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
