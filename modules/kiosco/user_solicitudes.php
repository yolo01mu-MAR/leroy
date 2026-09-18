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
<div class="row">
    <!-- PERFIL -->
    <div class="col-lg-3 mb-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <?php 
                    $foto = !empty($empleado['image'])
                        ? BASE_URL . '/uploads/users/' . $empleado['image']
                        : BASE_URL . '/uploads/users/no_image.jpg';
                ?>
                <img src="<?php echo $foto; ?>"
                    class="rounded-circle border border-3 border-warning mb-3"
                    width="130"
                    height="130">
                <h4 class="mb-0">
                    <?php echo remove_junk($empleado['nombre']); ?>
                </h4>
                <small class="text-muted">
                    <?php echo remove_junk($empleado['puesto']); ?>
                </small>
                <hr>
                <div class="text-start">
                    <p class="mb-2">
                        <b>Nómina:</b>
                        <?php echo remove_junk($empleado['id']); ?>
                    </p>
                    <p class="mb-2">
                        <b>Departamento:</b><br>
                        <?php echo remove_junk($empleado['departamento'] ." - ". $empleado['lugar']); ?>
                    </p>
                    <p class="mb-2">
                        <b>Grupo:</b>
                        <?php echo remove_junk($empleado['grupos']); ?>
                    </p>
                    <p class="mb-2">
                        <b>Fecha ingreso:</b>
                        <?php echo remove_junk($empleado['fecha_ingreso']); ?>
                    </p>
                </div>
                <a class="btn btn-sm btn-outline-danger w-100 mt-3" onclick="Kiosco.cerrarSesion()">
                    <i class="bi bi-arrow-left"></i>
                    Cerrar sesion
                </a>
            </div>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="col-lg-9">
        <!-- ENCABEZADO -->
        <div
            class="d-flex
                justify-content-between
                align-items-center
                mb-3">

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
                    <div
                        class="d-flex
                            justify-content-between
                            align-items-center">

                        <div>
                            <div class="text-muted small">
                                Todas
                            </div>
                            <div
                                class="stat-number"
                                id="totalSolicitudes">
                                0
                            </div>
                        </div>
                        <div
                            class="stat-icon bg-light">
                            <i class="bi bi-files"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PENDIENTES -->
            <div class="col-6 col-md-3">
                <div class="stat-card p-3">
                    <div
                        class="d-flex
                            justify-content-between
                            align-items-center">
                        <div>
                            <div class="text-muted small">
                                Pendientes
                            </div>
                            <div
                                class="stat-number"
                                id="totalPendientes">
                                0
                            </div>
                        </div>
                        <div
                            class="stat-icon bg-warning-subtle">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- APROBADAS -->
            <div class="col-6 col-md-3">
                <div class="stat-card p-3">
                    <div
                        class="d-flex
                            justify-content-between
                            align-items-center">
                        <div>
                            <div class="text-muted small">
                                Aprobadas
                            </div>
                            <div
                                class="stat-number"
                                id="totalAprobadas">
                                0
                            </div>
                        </div>
                        <div
                            class="stat-icon bg-success-subtle">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RECHAZADAS -->
            <div class="col-6 col-md-3">
                <div class="stat-card p-3">
                    <div
                        class="d-flex
                            justify-content-between
                            align-items-center">

                        <div>
                            <div class="text-muted small">
                                Rechazadas
                            </div>
                            <div
                                class="stat-number"
                                id="totalRechazadas">
                                0
                            </div>
                        </div>
                        <div
                            class="stat-icon bg-danger-subtle">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- FILTROS -->
        <div class="mb-3">
            <div
                class="filtro-solicitudes
                    d-flex
                    gap-1">
                <button
                    type="button"
                    class="btn active"
                    data-filtro="TODAS">
                    Todas
                </button>
                <button
                    type="button"
                    class="btn"
                    data-filtro="VACACIONES">
                    <i class="bi bi-calendar-heart"></i>
                    Vacaciones
                </button>
                <button
                    type="button"
                    class="btn"
                    data-filtro="TXT">
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
                <h5 class="mt-3">
                    No hay solicitudes
                </h5>
                <p class="text-muted mb-0">
                    Aquí aparecerán tus solicitudes recientes.
                </p>
            </div>

        </div>
    </div>
</div>
<?php include_once 'layouts/footer.php'; ?>