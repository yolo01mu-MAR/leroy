<?php

    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once('includes/validar_kiosco.php');
    require_once('includes/tiempo_helpers.php');

    $id = $_SESSION['kiosco']['empleado_id'];

    $nueva = 'user_tiempo';
    $pagina = basename($nueva, '.php');

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];

    // Año y mes actuales
    $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
    $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');

    // Primer y último día
    $primerDia = date('Y-m-01', mktime(0,0,0,$mes,1,$anio));
    $ultimoDia = date('Y-m-t', mktime(0,0,0,$mes,1,$anio));

    // Calendario
    $disponibilidad = get_calendario_txt($anio,$mes);

    // Mes anterior
    $mesAnterior  = $mes - 1;
    $anioAnterior = $anio;

    if($mesAnterior < 1){
        $mesAnterior = 12;
        $anioAnterior--;
    }

    // ¿Se puede regresar al mes anterior?
    $permitirAnterior = true;

    $fechaMesActual = date('Y-m');

    $fechaMesAnterior = sprintf(
        '%04d-%02d',
        $anioAnterior,
        $mesAnterior
    );

    if($fechaMesAnterior < $fechaMesActual){
        $permitirAnterior = false;
    }

    // Mes siguiente
    $mesSiguiente  = $mes + 1;
    $anioSiguiente = $anio;

    if($mesSiguiente > 12){
        $mesSiguiente = 1;
        $anioSiguiente++;
    }

    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];
    $nombre_mes = ucfirst($meses[$mes]) . ' ' . $anio . ' - ' . $empleado['lugar'];;
    $primer_dia_semana = ((int) date('N', mktime(0, 0, 0, $mes, 1, $anio))) - 1;
    $dias_en_mes = (int) date('t', mktime(0, 0, 0, $mes, 1, $anio));
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
        <link rel="stylesheet" href="libs/css/calendario.css">
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
                <div class="col-lg-3 mb-4">
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

                <!-- CONTENIDO Calendario-->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h4 class="mb-0">
                                <i class="bi bi-calendar-plus text-primary"></i>
                                Necesito faltar
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Calendario -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <?php if($permitirAnterior){ ?>
                                            <a class="btn btn-light shadow-sm"
                                            href="?mes=<?php echo $mesAnterior; ?>&anio=<?php echo $anioAnterior; ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        <?php }else{ ?>
                                            <button class="btn btn-light shadow-sm" disabled>
                                                <i class="bi bi-chevron-left"></i>
                                            </button>
                                        <?php } ?>
                                        <div class="text-center">
                                            <h4 class="mb-1">
                                                <?php echo $nombre_mes; ?>
                                            </h4>
                                            <small class="text-muted">
                                                Selecciona el día que necesitas faltar.
                                            </small>
                                        </div>
                                        <a class="btn btn-light shadow-sm"
                                        href="?mes=<?php echo $mesSiguiente; ?>&anio=<?php echo $anioSiguiente; ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </div>
                                    <div class="calendario-grid mb-2">
                                        <?php foreach (['L','M','X','J','V','S','D'] as $dia_letra): ?>
                                            <div class="dia-letra"><?php echo $dia_letra; ?></div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="calendario-grid" id="calendarioTxt">
                                        <?php for ($i = 0; $i < $primer_dia_semana; $i++): ?>
                                            <div class="celda-dia vacia"></div>
                                        <?php endfor; ?>
                                        <?php for($d=1;$d<=$dias_en_mes;$d++):

                                        $fecha = sprintf(
                                            "%04d-%02d-%02d",
                                            $anio,
                                            $mes,
                                            $d
                                        );

                                        $info = $disponibilidad[$fecha] ?? [
                                            'estado'        => 'no_laborable',
                                            'seleccionable' => 'false',
                                        ];
                                        $estado = $info["estado"];
                                        $seleccionable = $info["seleccionable"];

                                        ?>
                                        <div
                                            class="celda-dia <?= $estado ?>"
                                            data-fecha="<?= $fecha ?>"
                                            data-estado="<?= $estado ?>"
                                            data-seleccionable="<?= $seleccionable ?>">
                                            <div class="dia-numero">
                                                <?= $d ?>
                                            </div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="card-footer bg-white">
                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <small class="d-flex align-items-center">
                                            <span class="leyenda disponible me-2"></span>
                                            Disponible
                                        </small>
                                        <small class="d-flex align-items-center">
                                            <span class="leyenda pasado me-2"></span>
                                            Día pasado
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        Haz clic en el día que necesitas faltar. Puedes cambiarlo
                                        las veces que quieras antes de enviar la solicitud.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTENIDO Firma-->
                <div class="col-lg-3">
                    <form id="formTxt" method="POST" action="guardar_txt_futura.php">
                        <input
                            type="hidden"
                            id="usuario"
                            value="<?= (int)$empleado['id'] ?>">
                        <input
                            type="hidden"
                            id="fecha"
                            name="fecha">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-clipboard-check text-warning"></i>
                                    Tu solicitud
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <i class="bi bi-calendar-event display-5 text-warning"></i>
                                    <h2 id="lblFecha">
                                        —
                                    </h2>
                                    <small class="text-muted">
                                        Fecha seleccionada
                                    </small>
                                </div>
                                <div class="form-floating mb-3">
                                    <textarea
                                        id="motivo"
                                        name="motivo"
                                        class="form-control"
                                        style="height:120px"
                                        maxlength="300"
                                        placeholder="Motivo"
                                        required></textarea>
                                    <label>
                                        Describe el motivo
                                    </label>
                                </div>
                                <div class="text-end mb-3">
                                    <small
                                        id="contadorMotivo"
                                        class="text-muted">
                                        0 / 300
                                    </small>
                                </div>
                                <div class="d-grid gap-2">
                                    <button
                                        id="btnLimpiar"
                                        type="button"
                                        class="btn btn-outline-secondary">
                                        <i class="bi bi-eraser"></i>
                                        Limpiar
                                    </button>
                                    <button
                                        id="btnEnviar"
                                        type="submit"
                                        class="btn btn-roy"
                                        disabled>
                                        <i class="bi bi-send"></i>
                                        Enviar solicitud
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
        <!-- MODAL VALIDAR CONTRASEÑA TXT FUTURA -->
        <div class="modal fade"
            id="modalPasswordTxtFutura"
            tabindex="-1"
            aria-hidden="true">
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
                            id="passwordTxtFutura"
                            class="form-control form-control-lg"
                            autocomplete="off"
                            placeholder="Ingresa tu contraseña">
                        <div
                            id="errorPasswordTxtFutura"
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
                            id="btnValidarPasswordTxtFutura">
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
        <script src="libs/js/calendario.js"></script>

        <script src="<?= BASE_URL ?>/firmas/firmas/assets/js/signature_pad.min.js"></script>
        <script src="<?= BASE_URL ?>/firmas/firmas/assets/js/firmas.js"></script>

        <script>
    let kioscoAutenticado =
        <?= !empty($_SESSION['kiosco']['autenticado']) ? 'true' : 'false'; ?>;
</script>


        <script src="libs/js/txt_futura.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Kiosco.iniciar();
            });
        </script>
    </body>
</html>