<?php
$page_title = 'Reportes de Tickets';
require_once __DIR__ . '/../../app/bootstrap.php';
page_require_level(5);

// ---------------------------------------------------------
// Filtros (fecha desde / hasta / categoría)
// ---------------------------------------------------------
$fechaDesde   = isset($_GET['desde']) && $_GET['desde'] != '' ? $_GET['desde'] : date('Y-m-d', strtotime('-30 days'));
$fechaHasta   = isset($_GET['hasta']) && $_GET['hasta'] != '' ? $_GET['hasta'] : date('Y-m-d');
$categoriaSel = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;

$whereFecha = "t.fecha_creacion BETWEEN '{$fechaDesde} 00:00:00' AND '{$fechaHasta} 23:59:59'";
$whereCategoria = $categoriaSel > 0 ? " AND t.categoria_id = {$categoriaSel}" : "";

// ---------------------------------------------------------
// KPIs generales
// ---------------------------------------------------------
$sqlKpis = "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN t.estatus_id IN (5,6) THEN 1 ELSE 0 END) AS resueltos,
                SUM(CASE WHEN t.estatus_id NOT IN (5,6) THEN 1 ELSE 0 END) AS abiertos,
                AVG(
                    CASE WHEN t.estatus_id IN (5,6)
                    THEN TIMESTAMPDIFF(HOUR, t.fecha_creacion, t.fecha_cierre)
                    END
                ) AS horas_promedio
            FROM ticket t
            WHERE {$whereFecha} {$whereCategoria}";

$kpis = find_by_sql($sqlKpis);
$kpis = $kpis[0];

$horasPromedio = $kpis['horas_promedio'] !== null ? round($kpis['horas_promedio'], 1) : null;

// ---------------------------------------------------------
// Top usuarios con más tickets
// ---------------------------------------------------------
$sqlTopUsuarios = "SELECT
                        u.id,
                        u.name,
                        COUNT(*) AS total_tickets,
                        SUM(CASE WHEN t.estatus_id NOT IN (5,6) THEN 1 ELSE 0 END) AS abiertos
                    FROM ticket t
                        INNER JOIN users u ON u.id = t.usuario_id
                    WHERE {$whereFecha} {$whereCategoria}
                    GROUP BY u.id, u.name
                    ORDER BY total_tickets DESC
                    LIMIT 8";

$topUsuarios = find_by_sql($sqlTopUsuarios);

// ---------------------------------------------------------
// Top equipos/impresoras/zebras con más fallas
// ---------------------------------------------------------
$sqlTopEquipos = "SELECT
                        e.id,
                        e.codigo_equipo,
                        e.marca,
                        e.modelo,
                        et.nombre AS tipo,
                        COUNT(*) AS total_tickets,
                        MAX(t.fecha_creacion) AS ultimo_reporte
                    FROM ticket t
                        INNER JOIN equipo e ON e.id = t.equipo_id
                        LEFT JOIN equipo_tipo et ON et.id = e.tipo_equipo
                    WHERE {$whereFecha} {$whereCategoria}
                        AND t.equipo_id IS NOT NULL
                    GROUP BY e.id, e.codigo_equipo, e.marca, e.modelo, et.nombre
                    ORDER BY total_tickets DESC
                    LIMIT 8";

$topEquipos = find_by_sql($sqlTopEquipos);

// ---------------------------------------------------------
// Tickets por categoría (para la gráfica)
// ---------------------------------------------------------
$sqlPorCategoria = "SELECT
                        tc.nombre_categoria,
                        COUNT(*) AS total
                    FROM ticket t
                        INNER JOIN ticket_categoria tc ON tc.id = t.categoria_id
                    WHERE {$whereFecha} {$whereCategoria}
                    GROUP BY tc.nombre_categoria
                    ORDER BY total DESC";

$porCategoria = find_by_sql($sqlPorCategoria);

$labelsCategoria = array_map(function ($r) { return $r['nombre_categoria']; }, $porCategoria);
$dataCategoria   = array_map(function ($r) { return (int)$r['total']; }, $porCategoria);

// ---------------------------------------------------------
// Catálogo de categorías (para el filtro)
// ---------------------------------------------------------
$categorias = find_by_sql("SELECT id, nombre_categoria FROM ticket_categoria ORDER BY id ASC");

?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/ticket.css"/>
<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>

        <div class="tk-card" style="margin-bottom:18px;">
            <div class="tk-card-heading">
                <span class="glyphicon glyphicon-stats"></span> Reportes de Tickets
            </div>
            <div class="tk-card-body" style="padding-bottom:12px;">

                <form method="get" class="rp-filtros">
                    <div>
                        <label>Desde</label>
                        <input type="date" name="desde" class="form-control" value="<?php echo $fechaDesde; ?>">
                    </div>
                    <div>
                        <label>Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="<?php echo $fechaHasta; ?>">
                    </div>
                    <div>
                        <label>Categoría</label>
                        <select name="categoria_id" class="form-control">
                            <option value="0">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo (int)$cat['id']; ?>" <?php echo $categoriaSel === (int)$cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo remove_junk($cat['nombre_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <span class="glyphicon glyphicon-filter"></span> Filtrar
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- KPIs -->
        <div class="rp-kpi-row">
            <div class="rp-kpi-card total">
                <div class="rp-kpi-label">Total de tickets</div>
                <div class="rp-kpi-value"><?php echo (int)$kpis['total']; ?></div>
            </div>
            <div class="rp-kpi-card abiertos">
                <div class="rp-kpi-label">Abiertos / en proceso</div>
                <div class="rp-kpi-value"><?php echo (int)$kpis['abiertos']; ?></div>
            </div>
            <div class="rp-kpi-card resueltos">
                <div class="rp-kpi-label">Resueltos / cerrados</div>
                <div class="rp-kpi-value"><?php echo (int)$kpis['resueltos']; ?></div>
            </div>
            <div class="rp-kpi-card tiempo">
                <div class="rp-kpi-label">Tiempo prom. de solución</div>
                <div class="rp-kpi-value">
                    <?php echo $horasPromedio !== null ? $horasPromedio . ' h' : '—'; ?>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- Top usuarios -->
            <div class="col-md-6">
                <div class="tk-card">
                    <div class="tk-card-heading">
                        <span class="glyphicon glyphicon-user"></span> Usuarios con más tickets
                    </div>
                    <div class="tk-card-body">
                        <?php if (!empty($topUsuarios)): ?>
                            <table class="rp-rank-table">
                                <thead>
                                    <tr>
                                        <th>Empleado</th>
                                        <th class="text-right">Tickets</th>
                                        <th class="text-right">Abiertos</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topUsuarios as $i => $u): ?>
                                        <tr>
                                            <td>
                                                <span class="rp-rank-badge <?php echo $i === 0 ? 'top1' : ($i === 1 ? 'top2' : ($i === 2 ? 'top3' : '')); ?>">
                                                    <?php echo $i + 1; ?>
                                                </span>
                                                <?php echo remove_junk($u['name']); ?>
                                            </td>
                                            <td class="text-right">
                                                <span class="rp-count-pill"><?php echo (int)$u['total_tickets']; ?></span>
                                            </td>
                                            <td class="text-right">
                                                <?php echo (int)$u['abiertos']; ?>
                                            </td>
                                            <td class="text-right">
                                                <a href="ticket_histo_admin.php?usuario_id=<?php echo (int)$u['id']; ?>" title="Ver tickets">
                                                    <span class="glyphicon glyphicon-eye-open"></span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="rp-empty">Sin datos en este rango de fechas.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Top equipos / impresoras / zebras -->
            <div class="col-md-6">
                <div class="tk-card">
                    <div class="tk-card-heading">
                        Equipos con más fallas
                    </div>
                    <div class="tk-card-body">
                        <?php if (!empty($topEquipos)): ?>
                            <table class="rp-rank-table">
                                <thead>
                                    <tr>
                                        <th>Equipo</th>
                                        <th>Tipo</th>
                                        <th class="text-right">Fallas</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topEquipos as $i => $eq): ?>
                                        <tr>
                                            <td>
                                                <span class="rp-rank-badge <?php echo $i === 0 ? 'top1' : ($i === 1 ? 'top2' : ($i === 2 ? 'top3' : '')); ?>">
                                                    <?php echo $i + 1; ?>
                                                </span>
                                                <?php echo remove_junk($eq['marca'] . ' ' . $eq['modelo']); ?>
                                                <br>
                                                <small class="text-muted" style="margin-left:30px;">
                                                    <?php echo remove_junk($eq['codigo_equipo']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo remove_junk($eq['tipo']); ?></td>
                                            <td class="text-right">
                                                <span class="rp-count-pill <?php echo (int)$eq['total_tickets'] >= 5 ? 'alerta' : ''; ?>">
                                                    <?php echo (int)$eq['total_tickets']; ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <a href="ticket_equipo_historial.php?equipo_id=<?php echo (int)$eq['id']; ?>" title="Ver historial de reparación">
                                                    <span class="glyphicon glyphicon-time"></span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="rp-empty">Sin datos en este rango de fechas.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Gráfica por categoría -->
        <div class="tk-card">
            <div class="tk-card-heading">
                <span class="glyphicon glyphicon-tag"></span> Tickets por categoría
            </div>
            <div class="tk-card-body">
                <canvas id="chartCategorias" height="90"></canvas>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    var ctx = document.getElementById('chartCategorias').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labelsCategoria); ?>,
            datasets: [{
                label: 'Tickets',
                data: <?php echo json_encode($dataCategoria); ?>,
                backgroundColor: '#337ab7',
                borderRadius: 5,
                maxBarThickness: 44
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
</script>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
