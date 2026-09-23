<?php
    $page_title = 'Historial de Vacaciones';
    require_once __DIR__ . '/../../app/bootstrap.php';

    // Checkin What level user has permission to view this page
    // page_require_level(5);
    // require_permiso('personal.jefes_planilla');

    $scripts = [
        'buscador_historial_vacaciones'
    ];

    $estatusMap = [
        'APROBADA'    => ['texto' => 'APROBADA',  'class' => 'label-warning'],
        'FINALIZADA'  => ['texto' => 'FINALIZADA', 'class' => 'label-success'],
        'CANCELADA'   => ['texto' => 'CANCELADA', 'class' => 'label-danger'],
        'DESCONOCIDO' => ['texto' => 'DESCONOCIDO', 'class' => 'label-secondary'],
    ];

    $semana_actual = (int)date('W');
    $anio_actual   = (int)date('o');

    $empleadosSaldos = find_all_solicitudes_aprobadas($semana_actual, $anio_actual
    );


?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<!-- Bootstrap 5 & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/historial_vacaciones.css">

<div class="container-fluid px-3 px-md-4 py-4">

    <!-- MENSAJES DEL SISTEMA -->
    <?php if(!empty($msg)): ?>
    <div class="row mb-3">
        <div class="col-12">
            <?php echo display_msg($msg); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- CONTENEDOR ÚNICO PRINCIPAL -->
    <div class="card border-0 rounded-3 shadow-sm">
        <div class="card-body p-4 p-md-5">

            <!-- 1. ENCABEZADO Y TÍTULO -->
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3  text-primary rounded-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-calendar3 fs-2"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-0">Historial de Vacaciones</h1>
                    </div>
                </div>
            </div>

            <!-- 2. SECCIÓN DE FILTROS -->
            <div class="mb-4 pb-2">
                <div class="row g-3 align-items-end">
                    
                    <!-- Filtro Semana -->
                    <div class="col-6 col-sm-4 col-lg-2">
                        <label for="filtro_semana" class="form-label mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-week text-primary fs-5"></i>
                            <span>Semana</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">#</span>
                            <input
                                type="number"
                                id="filtro_semana"
                                class="form-control text-center no-spinners fw-semibold"
                                value="<?php echo $semana_actual; ?>"
                                min="1"
                                max="53"
                                step="1">
                        </div>
                    </div>

                    <!-- Filtro Año -->
                    <div class="col-6 col-sm-4 col-lg-2">
                        <label for="filtro_anio" class="form-label mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-event text-primary fs-5"></i>
                            <span>Año</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-calendar-fill"></i>
                            </span>
                            <input
                                type="number"
                                id="filtro_anio"
                                class="form-control text-center no-spinners fw-semibold"
                                value="<?php echo $anio_actual; ?>"
                                min="2020"
                                max="2100"
                                step="1">
                        </div>
                    </div>

                    <!-- Buscador Empleado -->
                    <div class="col-12 col-sm-8 col-lg-6">
                        <label for="buscador" class="form-label mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-search text-primary fs-5"></i>
                            <span>Buscar empleado</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input
                                type="text"
                                id="buscador"
                                class="form-control"
                                placeholder="Buscar por nómina o nombre..."
                                autocomplete="off">
                        </div>
                    </div>

                    <!-- Botón Reiniciar / Limpiar -->
                    <div class="col-12 col-sm-4 col-lg-2 d-flex">
                        <button
                            type="button"
                            id="btn_limpiar_filtros"
                            class="btn w-100 rounded-3 d-flex align-items-center justify-content-center gap-2 py-2"
                            title="Limpiar filtros">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                            <span>Limpiar</span>
                        </button>
                    </div>

                </div>
            </div>

            <!-- 3. TABLA DE RESULTADOS (Con margen interno/ancho ajustado) -->
            <div class="tabla-contenedor-wrapper px-2 px-md-3 py-2 rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-uppercase">
                                <th class="text-center py-3" style="width: 110px;">Nómina</th>
                                <th class="py-3">Nombre</th>
                                <th class="text-center py-3">Fecha de Solicitud</th>
                                <th class="text-center py-3">Periodo Inicio</th>
                                <th class="text-center py-3">Periodo Fin</th>
                                <th class="text-center py-3" style="width: 90px;">Días</th>
                                <th class="text-center py-3" style="width: 140px;">Estatus</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-resultados">
                            <?php foreach($empleadosSaldos as $emp): ?>
                            <tr>
                                <td class="text-center fw-bold py-3">
                                    <?php echo remove_junk(ucwords($emp['nomina'])); ?>
                                </td>
                                <td class="fw-semibold py-3">
                                    <?php echo remove_junk(ucwords($emp['nombre'])); ?>
                                </td>
                                <td class="text-center text-muted py-3">
                                    <?php echo remove_junk(ucwords($emp['fecha_solicitud'])); ?>
                                </td>
                                <td class="text-center text-muted py-3">
                                    <?php echo remove_junk(ucwords($emp['inicio'])); ?>
                                </td>
                                <td class="text-center text-muted py-3">
                                    <?php echo remove_junk(ucwords($emp['fin'])); ?>
                                </td>
                                <td class="text-center fw-bold py-3">
                                    <?php echo remove_junk(ucwords($emp['dias'])); ?>
                                </td>
                                <td class="text-center py-3">
                                    <?php
                                        $estatus = remove_junk(ucwords($emp['estatus']));
                                        if (isset($estatusMap[$estatus])) {
                                            $e = $estatusMap[$estatus];
                                            echo "<span class='badge {$e['class']} rounded-pill px-3 py-2'>{$e['texto']}</span>";
                                        } else {
                                            echo "<span class='badge bg-secondary rounded-pill px-3 py-2'>DESCONOCIDO</span>";
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- BOTÓN FLOTANTE GENERAR REPORTE -->
<button 
    type="button"
    id="btn_generar_reporte" 
    class="btn btn-flotante d-flex align-items-center gap-2">
    <i class="bi bi-file-earmark-bar-graph-fill fs-5"></i>
    <span>Generar reporte</span>
</button>

<!-- MODAL DE DETALLE -->
<div class="modal fade" id="modalDetalleEncargado" tabindex="-1" aria-labelledby="modalDetalleEncargadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalDetalleEncargadoLabel">
                    <i class="bi bi-person-circle text-primary fs-5"></i>
                    <span id="detalle-nombre-titulo">Detalle del encargado</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <table class="table table-borderless mb-3 small">
                    <tbody>
                        <tr>
                            <td class="fw-bold text-muted" style="width:40%;">Nómina</td>
                            <td id="detalle-nomina" class="fw-semibold"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Nombre</td>
                            <td id="detalle-nombre" class="fw-semibold"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Ingreso</td>
                            <td id="detalle-ingreso"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Periodo</td>
                            <td>
                                <span id="detalle-inicio"></span> &nbsp;–&nbsp; <span id="detalle-fin"></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Estado</td>
                            <td>
                                <span id="detalle-estado" class="badge rounded-pill"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- DESGLOSE DE DÍAS -->
                <div class="card border-0 rounded-3 mb-3" style="background-color: var(--color-surface-secondary);">
                    <div class="card-body p-3">
                        <table class="table table-borderless text-center mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Otorgados</th>
                                    <th>Disfrutados</th>
                                    <th>Pendientes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="fs-5 fw-bold">
                                    <td id="detalle-otorgados"></td>
                                    <td id="detalle-disfrutados"></td>
                                    <td id="detalle-pendientes" style="color: var(--color-primary-hover);"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <table class="table table-borderless mb-0 small text-muted">
                    <tbody>
                        <tr>
                            <td style="width:40%;">Se generó</td>
                            <td id="detalle-creo"></td>
                        </tr>
                        <tr>
                            <td>Se actualizó</td>
                            <td id="detalle-actualizo"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('btn_generar_reporte').addEventListener('click', function () {

    const semana = document.getElementById('filtro_semana').value;
    const anio = document.getElementById('filtro_anio').value;
    const buscador = document.getElementById('buscador').value.trim();

    const url = new URL('generar_reporte_vacaciones.php', window.location.href);

    url.searchParams.set('semana', semana);
    url.searchParams.set('anio', anio);

    if (buscador !== '') {
        url.searchParams.set('buscar', buscador);
    }

    window.open(url.toString(), '_blank');
});
</script>

<script>
    const semanaActual = <?php echo $semana_actual; ?>;
    const anioActual = <?php echo $anio_actual; ?>;
</script>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>