<?php 
    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once('includes/validar_kiosco.php');
    require_once('includes/funtions.php');

    $id = $_SESSION['kiosco']['empleado_id'];

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];

    if(empty($empleado)){
        redirect('identificar_colaborador.php');
    }

    $solicitudes = get_solicitudes_vacaciones_usuario($empleado['id']);

    $pagina = basename($_SERVER['PHP_SELF'], '.php');

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
    </head>
    <body>
        <!-- NAVBAR -->
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
        <div class="container py-5">
            <div class="row">
                <!-- PERFIL -->
                <div class="col-lg-4 mb-4">
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
                <div class="col-lg-8">
                    <h2 class="mb-4">
                        <i class="bi bi-calendar2-check"></i>
                        Mis vacaciones
                    </h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 text-center h-100">
                                <div class="card-body">
                                    <h1 class="text-primary">
                                        <?php echo remove_junk($empleado['dias_otorgados']); ?>
                                    </h1>
                                    <p class="text-muted mb-0">
                                        Días otorgados
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 text-center h-100">
                                <div class="card-body">
                                    <h1 class="text-danger">
                                        <?php echo remove_junk($empleado['dias_disfrutados']); ?>
                                    </h1>
                                    <p class="text-muted mb-0">
                                        Disfrutados
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 text-center h-100">
                                <div class="card-body">
                                    <h1 class="text-success">
                                       <?php echo remove_junk($empleado['dias_disponibles']); ?>
                                    </h1>
                                    <p class="text-muted mb-0">
                                        Disponibles
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h5 class="mb-3">
                                Periodo vigente
                            </h5>
                            <p class="mb-2">
                                <b>Inicio:</b>
                                <?php echo remove_junk($empleado['inicio']); ?>
                                &nbsp;&nbsp;
                                <b>Fin:</b>
                                <?php echo remove_junk($empleado['fin']); ?>
                            </p>
                            <?php
                                $porcentaje = ($empleado['dias_otorgados'] > 0)
                                    ? ($empleado['dias_disponibles'] * 100) / $empleado['dias_otorgados']
                                    : 0;
                            ?>
                            <div class="progress" style="height:18px;">
                                <div class="progress-bar bg-success"
                                    role="progressbar"
                                    style="width: <?php echo $porcentaje; ?>%;">
                                    <?php echo $empleado['dias_disponibles']; ?>
                                    disponibles
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        $autenticado = !empty($_SESSION['kiosco']['autenticado']);
                    ?>
                    <div class="d-grid mt-4">
                        <?php if ($autenticado): ?>
                            <!-- YA ESTÁ AUTENTICADO -->
                            <a
                                href="vacaciones_nueva.php"
                                class="btn btn-primary">
                                <i class="bi bi-calendar-plus"></i>
                                Solicitar vacaciones
                            </a>
                        <?php else: ?>
                            <!-- ENTRÓ POR CREDENCIAL -->
                            <button
                                type="button"
                                class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalPassword">
                                <i class="bi bi-calendar-plus"></i>
                                Solicitar vacaciones
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white">
                            <b>Últimas solicitudes</b>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Fecha Solicitud</th>
                                        <th>Días</th>
                                        <th>Fechas de los dias</th>
                                        <th>Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($solicitudes as $soli): ?>
                                    <tr>
                                        <td class="text-center"><?php echo remove_junk($soli['fecha_solicitud']); ?></td>
                                        <td class="text-center"><?php echo remove_junk($soli['dias']); ?></td>
                                        <td class="text-center"><?php echo remove_junk($soli['fecha_inicio'] . " - " . $soli['fecha_fin']);?></td>
                                        <td class="text-center"><?= badge_estatus($soli['estatus']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
        <!-- MODAL VALIDAR CONTRASEÑA -->
        <div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Confirmar identidad
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">
                            Para realizar una solicitud de vacaciones,
                            confirma tu identidad.
                        </p>
                        <label class="form-label">
                            Contraseña
                        </label>
                        <input
                            type="password"
                            id="passwordKiosco"
                            class="form-control form-control-lg"
                            autocomplete="off"
                            placeholder="Ingresa tu contraseña">
                        <div
                            id="errorPassword"
                            class="alert alert-danger mt-3 d-none">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            id="btnValidarPassword"
                            onclick="Kiosco.validarPassword()">
                            Continuar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="libs/js/kiosco.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Kiosco.iniciar();
            });
        </script>
    </body>
</html>