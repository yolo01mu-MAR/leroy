<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once('includes/validar_kiosco.php');
require_once('includes/tiempo_helpers.php');

$id = $_SESSION['kiosco']['empleado_id'];

$nueva = 'user_tiempo';
$pagina = basename($nueva, '.php');

$empleado = get_datos_kiosco($id);
$empleado = $empleado[0];

$faltas = get_faltas_tiempo($id);

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
                            <a href="user_tiempo.php" class="btn btn-outline-secondary w-100 mt-3">
                                <i class="bi bi-arrow-left"></i>
                                Volver
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CONTENIDO -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h4 class="mb-0">
                                <i class="bi bi-calendar-x text-danger"></i>
                                Mis faltas
                            </h4>
                        </div>
                        <div class="card-body">
                            <?php if(empty($faltas)): ?>
                                <div class="alert alert-success mb-0">
                                    No tienes faltas disponibles para solicitar
                                    Tiempo x Tiempo.
                                </div>
                            <?php else: ?>
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Observaciones</th>
                                            <th width="150"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($faltas as $f): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y',strtotime($f['fecha'])); ?></td>
                                            <td><?php echo remove_junk($f['tipo']); ?></td>
                                            <td><?php echo !empty($f['observaciones']) ? remove_junk($f['observaciones']) : '-';?></td>
                                            <td>
                                                <button
                                                    class="btn btn-warning btn-sm btnSolicitar"
                                                    data-id="<?php echo $f['ID']; ?>"
                                                    data-fecha="<?php echo $f['fecha']; ?>">
                                                    Solicitar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
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
        <!-- MODAL VALIDAR CONTRASEÑA TXT -->
<div class="modal fade" id="modalPasswordTxt" tabindex="-1" aria-hidden="true">
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
                    Para solicitar Tiempo por Tiempo,
                    confirma tu identidad.
                </p>
                <label class="form-label">
                    Contraseña
                </label>
                <input
                    type="password"
                    id="passwordTxt"
                    class="form-control form-control-lg"
                    autocomplete="off"
                    placeholder="Ingresa tu contraseña">
                <div
                    id="errorPasswordTxt"
                    class="alert alert-danger mt-3 d-none">
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnValidarPasswordTxt">
                    Continuar
                </button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="libs/js/kiosco.js"></script>
<script src="<?= BASE_URL ?>/firmas/firmas/assets/js/signature_pad.min.js"></script>
<script src="<?= BASE_URL ?>/firmas/firmas/assets/js/firmas.js"></script>
<script>
    let kioscoAutenticado =
        <?= !empty($_SESSION['kiosco']['autenticado']) ? 'true' : 'false'; ?>;
</script>
<script src="<?= BASE_URL ?>/firmas/txt/assets/js/txt_falta.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Kiosco.iniciar();
    });
</script>
<script>

    $(document).on("click",".btnSolicitar",function(){

        confirmarSolicitudTxt(
            $(this).data("id"),
            $(this).data("fecha")
        );

    });

</script>
</body>
</html>