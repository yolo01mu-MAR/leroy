<?php

    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once("includes/validar_kiosco.php");

    $id = (int)$_SESSION['kiosco']['empleado_id'];

    $scripts_kiosco = [
        BASE_URL . '/modules/kiosco/libs/js/user_solicitudes.js'
    ];

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];

    $nueva = 'user_solicitudes';
    $pagina = basename($nueva, '.php');

?>
<?php include_once 'layouts/header.php'; ?>
<link rel="stylesheet" href="libs/css/user_solicitudes.css">

<div class="container-fluid px-0">
    <div class="row g-4">

        <!-- TARJETA PERFIL USUARIO (4 COLUMNAS) -->
        <div class="col-lg-4">
            <?php include_once 'perfil_kiosco.php'; ?>
        </div>

        <!-- CONTENIDO (8 COLUMNAS) -->
        <div class="col-lg-8">

            <!-- ENCABEZADO -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="titulo-pagina mb-1">
                        <i class="bi bi-file-earmark-text text-warning"></i>
                        Mis solicitudes
                    </h3>
                    <div class="subtitulo">
                        Consulta el estado de tus trámites.
                    </div>
                </div>
            </div>

            <!-- ESTADÍSTICAS -->
            <div class="row g-2 mb-3">

                <!-- TODAS -->
                <div class="col-6 col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Todas</div>
                                <div class="stat-number" id="totalSolicitudes">0</div>
                            </div>
                            <div class="stat-icon bg-light">
                                <i class="bi bi-files"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PENDIENTES -->
                <div class="col-6 col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Pendientes</div>
                                <div class="stat-number" id="totalPendientes">0</div>
                            </div>
                            <div class="stat-icon bg-warning-subtle">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- APROBADAS -->
                <div class="col-6 col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Aprobadas</div>
                                <div class="stat-number" id="totalAprobadas">0</div>
                            </div>
                            <div class="stat-icon bg-success-subtle">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RECHAZADAS -->
                <div class="col-6 col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Rechazadas</div>
                                <div class="stat-number" id="totalRechazadas">0</div>
                            </div>
                            <div class="stat-icon bg-danger-subtle">
                                <i class="bi bi-x-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- FILTROS -->
            <div class="mb-3">
                <div class="filtro-solicitudes d-flex gap-1">
                    <button type="button" class="btn active" data-filtro="TODAS">
                        Todas
                    </button>
                    <button type="button" class="btn" data-filtro="VACACIONES">
                        <i class="bi bi-calendar-heart"></i>
                        Vacaciones
                    </button>
                    <button type="button" class="btn" data-filtro="TXT">
                        <i class="bi bi-clock-history"></i>
                        Tiempo x Tiempo
                    </button>
                </div>
            </div>

            <!-- SOLICITUDES -->
            <div id="listaSolicitudes">
                <!-- Las solicitudes serán cargadas por JavaScript -->
                <div class="sin-solicitudes">
                    <i class="bi bi-inbox"></i>
                    <h5 class="mt-3">No hay solicitudes</h5>
                    <p class="text-muted mb-0">
                        Aquí aparecerán tus solicitudes recientes.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include_once 'layouts/footer.php'; ?>