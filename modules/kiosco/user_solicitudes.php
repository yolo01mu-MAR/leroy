<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once("includes/validar_kiosco.php");

$id = (int)$_SESSION['kiosco']['empleado_id'];

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];

    $nueva = 'user_solicitudes';
    $pagina = basename($nueva, '.php');

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>LE ROY</title>
        <link rel="shortcut icon" href="<?= BASE_URL ?>/libs/images/logo.ico" type="image/x-icon">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="libs/css/kiosco.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="libs/css/tiempo_nuevo.css">
        <link rel="stylesheet" href="libs/css/user_solicitudes.css">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg bg-white">
            <div class="container">
                <img src="<?= BASE_URL ?>/libs/images/leroy.png" alt="Logo LE ROY" height="45">
                <button class="navbar-toggler"
                    data-bs-toggle="collapse"
                    data-bs-target="#menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="menu">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= $pagina == 'user_vacaciones' ? 'active' : '' ?>" href="user_vacaciones.php">
                                Vacaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $pagina == 'user_tiempo' ? 'active' : '' ?>" href="user_tiempo.php">
                                Tiempo por Tiempo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $pagina == 'user_solicitudes' ? 'active' : '' ?>" href="user_solicitudes.php">
                                Mis Solicitudes
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container py-4">
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
        </div>
        <!-- PIE DE PAGINA -->
        <footer class="py-4">
            <div class="container text-center">
                <b>LE ROY</b><br>
                Portal de Recursos Humanos
            </div>
        </footer>
        <div id="relojSesion">
            <i class="bi bi-clock-history"></i>
            <span id="tiempoSesion">05:00</span>
        </div>
        <div class="modal fade"
            id="modalSesion"
            data-bs-backdrop="static"
            data-bs-keyboard="false"
            tabindex="-1">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            Sesión por finalizar
                        </h5>
                    </div>
                    <div class="modal-body text-center">
                        <h2 id="contadorSesion">60</h2>
                        <p>Tu sesión finalizará por inactividad.</p>
                        <p>¿Deseas continuar?</p>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-success"
                            onclick="Kiosco.renovarSesion()">
                            Continuar sesión
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger"
                            onclick="Kiosco.cerrarSesion()">
                            Cerrar sesión
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="libs/js/kiosco.js"></script>
        <script src="libs/js/user_solicitudes.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Kiosco.iniciar();
            });
        </script>
    </body>
</html>