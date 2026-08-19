<?php 
    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once __DIR__ . '/includes/vacaciones_helpers.php';
    require_once __DIR__ . '/includes/validar_kiosco.php';

    $id = $_SESSION['kiosco']['empleado_id'];

    $nueva = 'user_vacaciones';
    $pagina = basename($nueva, '.php');

    $empleado = get_datos_kiosco($id);
    $empleado   = $empleado[0];
    $depUsuario = $empleado['lugar_id'];

    // Fecha mínima permitida
    $fechaMinima = strtotime('+' . DIAS_ANTICIPACION . ' days');

    $anioMinimo = (int)date('Y', $fechaMinima);
    $mesMinimo  = (int)date('n', $fechaMinima);

    // Si el usuario no eligió un mes, abrir directamente el primer mes válido
    $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : $anioMinimo;
    $mes  = isset($_GET['mes']) ? (int)$_GET['mes'] : $mesMinimo;

    // Mes anterior
    $mesAnterior  = $mes - 1;
    $anioAnterior = $anio;

    if($mesAnterior < 1){
        $mesAnterior = 12;
        $anioAnterior--;
    }

    // ¿Se puede regresar al mes anterior?
    $permitirAnterior = true;

    if (
        $anioAnterior < $anioMinimo ||
        ($anioAnterior == $anioMinimo && $mesAnterior < $mesMinimo)
    ) {
        $permitirAnterior = false;
    }

    // Mes siguiente
    $mesSiguiente  = $mes + 1;
    $anioSiguiente = $anio;

    if($mesSiguiente > 12){
        $mesSiguiente = 1;
        $anioSiguiente++;
    }

    $primerDia = date('Y-m-01', mktime(0,0,0,$mes,1,$anio));
    $ultimoDia = date('Y-m-t', mktime(0,0,0,$mes,1,$anio));

    $ocupacion = get_disponibilidad_vacaciones(
        $empleado['lugar_id'],
        $primerDia,
        $ultimoDia
    );

    $disponibilidad = construir_disponibilidad_calendario($ocupacion, $anio, $mes, CUPO_MAXIMO_VACACIONES);

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
                                    <b>Departamento:</b><br>
                                    <?php echo remove_junk($empleado['departamento'] . ' - ' . $empleado['lugar']); ?>
                                </p>
                            </div>
                            <hr>
                            <h1 class="text-success mb-0">
                                <?php echo remove_junk($empleado['dias_disponibles']); ?>
                            </h1>
                            <p class="text-muted mb-0">
                                días disponibles
                            </p>
                        </div>
                    </div>
                    <a href="user_vacaciones.php" class="btn btn-outline-secondary w-100 mt-3">
                        <i class="bi bi-arrow-left"></i>
                        Volver a mis vacaciones
                    </a>
                </div>

                <!-- CONTENIDO -->
                <div class="col-lg-8">
                    <h2 class="mb-4">
                        <i class="bi bi-calendar2-plus"></i>
                        Solicitud de vacaciones
                    </h2>
                    <div id="mensajeVacaciones"></div>
                    <form id="formVacaciones" action="guardar_vacaciones.php" method="post" data-saldo="<?= (int)$empleado['dias_disponibles'] ?>" data-usuario="<?= $id ?>">
                        <input type="hidden" name="inicio" id="inputInicio">
                        <input type="hidden" name="fin" id="inputFin">
                        <!-- CALENDARIO -->
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
                                            Selecciona un periodo de vacaciones
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

                                <div class="calendario-grid">
                                    <?php for ($i = 0; $i < $primer_dia_semana; $i++): ?>
                                        <div class="celda-dia vacia"></div>
                                    <?php endfor; ?>

                                    <?php for ($d = 1; $d <= $dias_en_mes; $d++):
                                        $fecha  = sprintf('%04d-%02d-%02d', $anio, $mes, $d);
                                        $info = $disponibilidad[$fecha] ?? [
                                            'estado'   => 'no_laborable',
                                            'ocupados' => 0,
                                            'libres'   => CUPO_MAXIMO_VACACIONES
                                        ];

                                        $estado = $info['estado'];
                                        $ocupados = $info['ocupados'];

                                        $seleccionable = in_array($estado, ['disponible', 'uno']);
                                        ?>
                                        <?php
                                        switch($estado){
                                            case 'disponible':
                                                $textoEstado = 'Disponible';
                                                break;
                                            case 'uno':
                                                $textoEstado = '1 lugar';
                                                break;
                                            case 'lleno':
                                                $textoEstado = 'Completo';
                                                break;
                                            default:
                                                $textoEstado = '';
                                        }
                                        ?>
                                        <div class="celda-dia <?= $estado ?>"
                                            data-fecha="<?= $fecha ?>"
                                            data-estado="<?= $estado ?>"
                                            data-seleccionable="<?= $seleccionable ?>">
                                            <div class="dia-numero">
                                                <?= $d ?>
                                            </div>

                                            <?php if($estado != 'no_laborable'){ ?>
                                                <small><?= $ocupados ?>/<?= CUPO_MAXIMO_VACACIONES ?></small>
                                            <?php } ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex flex-wrap gap-3 mb-3">
                                    <small class="d-flex align-items-center">
                                        <span class="leyenda disponible me-2"></span>
                                        Libre
                                    </small>
                                    <small class="d-flex align-items-center">
                                        <span class="leyenda uno me-2"></span>
                                        Cupo disponible
                                    </small>
                                    <small class="d-flex align-items-center">
                                        <span class="leyenda lleno me-2"></span>
                                        Lleno
                                    </small>
                                    <small class="d-flex align-items-center">
                                        <span class="leyenda bloqueado me-2"></span>
                                        No disponible
                                    </small>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Recordatorio: solo se pueden ir dos personas del mismo departamento el mismo día.
                                    <br>
                                    Da <b>doble clic</b> en un día para elegir el inicio y el fin del periodo.
                                </small>
                            </div>
                        </div>
                        <!-- PERIODO SELECCIONADO -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body d-flex flex-wrap gap-4">
                                <div>
                                    <div class="text-muted small">Inicio</div>
                                    <div class="fw-semibold" id="outInicio">—</div>
                                </div>
                                <div>
                                    <div class="text-muted small">Fin</div>
                                    <div class="fw-semibold" id="outFin">—</div>
                                </div>
                                <div>
                                    <div class="text-muted small">Días solicitados</div>
                                    <div class="fw-semibold" id="outDias">0</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-auto" id="btnBorrarFechas">
                                    <i class="bi bi-x-circle"></i>
                                    Borrar fechas
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="user_vacaciones.php" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-warning" id="btnEnviar" disabled>
                                <i class="bi bi-send"></i>
                                Enviar solicitud
                            </button>
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
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="libs/js/kiosco.js"></script>
        <script src="libs/js/vacaciones_nueva.js"></script>
        <script src="libs/js/calendario.js"></script>
        <script src="<?= BASE_URL ?>/firmas/firmas/assets/js/signature_pad.min.js"></script>
        <script src="<?= BASE_URL ?>/firmas/firmas/assets/js/firmas.js"></script>
        <script src="<?= BASE_URL ?>/firmas/vacaciones/assets/js/vacaciones.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Kiosco.iniciar();
            });
        </script>
    </body>
</html>