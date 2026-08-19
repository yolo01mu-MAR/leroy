<?php

    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once('includes/validar_kiosco.php');
    require_once('includes/tiempo_helpers.php');

    $id = $_SESSION['kiosco']['empleado_id'];

    $nueva = 'user_tiempo';
    $pagina = basename($nueva, '.php');

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];
/**
 * Devuelve los compañeros de la misma cuadrilla que podrían cubrir el
 * turno de $idEmpleado, excluyéndolo a él mismo.
 *
 * OJO: ajusta el nombre de columnas reales. Aquí asumo que `users` tiene
 * `cuadrilla_id` (ya lo vimos en tu esquema) y que basta con estar en la
 * misma cuadrilla y activo para poder cubrir. Si además necesitas
 * filtrar por mismo turno/grupo específico, agrega esa condición.
 */
function get_companeros_turno($idEmpleado) {
    global $db;

    $idEmpleado  = (int) $idEmpleado;

    $sql = "SELECT
                u.id,
                u.name,
                u.puesto,
                dp.nombre AS lugar
            FROM users u
                LEFT JOIN cuadrilla c ON c.ID = u.cuadrilla_id
                LEFT JOIN departamento_plantilla dp ON dp.id = c.depPlantilla_id
            WHERE u.cuadrilla_id = (SELECT cuadrilla_id FROM users WHERE id = {$idEmpleado})
                AND u.id != {$idEmpleado}
                AND u.statusLaboral_id = 1
            ORDER BY u.name";

    $resultado  = $db->query($sql);
    $companeros = [];

    while ($fila = mysqli_fetch_assoc($resultado)) { // ajusta si tu $db usa otro método de fetch
        $companeros[] = $fila;
    }

    return $companeros;
}
    // Compañeros de la misma cuadrilla que podrían cubrir el turno,
    // sin incluirte a ti mismo. AJUSTA esta función a tu esquema real:
    // no tengo la tabla/columnas exactas de "cuadrilla" ni el criterio
    // real para decidir quién puede cubrir a quién (mismo turno, mismo
    // puesto, etc.) — ver la función sugerida al final de la respuesta.
    

    $companeros = get_companeros_turno($id);

    // echo "<pre>";
    // print_r($companeros);
    // echo "</pre>";
    // exit();

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
        <link rel="shortcut icon" href="../libs/images/logo.ico" type="image/x-icon">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="libs/css/kiosco.css">
        <link rel="stylesheet" href="libs/css/calendario.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="libs/css/tiempo_nuevo.css">
        <style>
            .boleto-solicitud {
    overflow: hidden;
}

.boleto-cabecera {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.25rem;
}

.boleto-fecha-caja {
    flex-shrink: 0;
    background: #fff3cd;
    border-radius: 8px;
    padding: 8px 12px;
    min-width: 56px;
    text-align: center;
}

.boleto-mes {
    display: block;
    font-size: 11px;
    color: #997404;
    text-transform: lowercase;
    line-height: 1;
}

.boleto-dia {
    display: block;
    font-size: 22px;
    font-weight: 600;
    color: #997404;
    line-height: 1.2;
}

.boleto-info small {
    display: block;
}

.boleto-perforacion {
    border-top: 1px dashed #dee2e6;
    margin: 0 1.25rem;
}

.lista-companeros {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 260px;
    overflow-y: auto;
}

.companero-opcion {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 8px 10px;
    margin: 0;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease;
}

.companero-opcion:hover {
    border-color: #adb5bd;
}

.companero-opcion input[type="radio"] {
    margin: 0;
}

.companero-opcion.seleccionado,
.companero-opcion:has(input:checked) {
    border-color: #337ab7;
    background: #eaf2fa;
}

.companero-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e9ecef;
    color: #495057;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}

.companero-datos {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.companero-nombre {
    font-size: 14px;
    font-weight: 500;
}

.companero-detalle {
    font-size: 12px;
    color: #6c757d;
}

.companero-opcion.oculto {
    display: none;
}
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg bg-white">
            <div class="container">
                <img src="../libs/images/leroy.png" alt="Logo LE ROY" height="45">
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
                                        ? BASE_URL . '/uploads/users/'.$empleado['image']
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
                                <i class="bi bi-people-fill text-success"></i>
                                Cubrir turno
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

                <!-- CONTENIDO: Tu solicitud, con selección de compañero -->
                <div class="col-lg-3">
                    <form id="formTxt" method="POST" action="guardar_txt_futura.php">
                        <input type="hidden" id="usuario" name="usuario" value="<?= (int)$empleado['id'] ?>">
                        <input type="hidden" id="fecha" name="fecha">
                        <input type="hidden" id="cubre_id" name="cubre_id">
                        <input type="hidden" id="firma" name="firma">

                        <div class="boleto-solicitud card border-0 shadow-sm mb-3">

                            <div class="boleto-cabecera">
                                <div class="boleto-fecha-caja">
                                    <span class="boleto-mes" id="boletoMes">—</span>
                                    <span class="boleto-dia" id="boletoDia">—</span>
                                </div>
                                <div class="boleto-info">
                                    <p class="mb-0 fw-semibold">Turno a cubrir</p>
                                    <small class="text-muted" id="boletoDiaSemana">Elige un día en el calendario</small>
                                </div>
                            </div>

                            <div class="boleto-perforacion"></div>

                            <div class="card-body">

                                <p class="text-muted small mb-2">Quién cubrirá tu turno</p>
                                <input type="text" class="form-control mb-2" id="buscarCompanero"
                                       placeholder="Buscar compañero por nombre">

                                <div id="listaCompaneros" class="lista-companeros mb-3">
                                    <?php foreach ($companeros as $c): ?>
                                        <?php
                                            $iniciales = '';
                                            $partesNombre = explode(' ', trim($c['name']));
                                            $iniciales .= mb_substr($partesNombre[0] ?? '', 0, 1);
                                            $iniciales .= mb_substr($partesNombre[1] ?? '', 0, 1);
                                        ?>
                                        <label class="companero-opcion" data-nombre="<?= remove_junk(strtolower($c['name'])) ?>">
                                            <input type="radio" name="cubre_id_radio" value="<?= (int)$c['id'] ?>">
                                            <span class="companero-avatar"><?= remove_junk(strtoupper($iniciales)) ?></span>
                                            <span class="companero-datos">
                                                <span class="companero-nombre"><?= remove_junk(ucwords(strtolower($c['name']))) ?></span>
                                                <span class="companero-detalle"><?= remove_junk($c['lugar'] ?? $c['puesto']) ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                    <?php if (empty($companeros)): ?>
                                        <p class="text-muted small mb-0">No hay compañeros disponibles para cubrir tu turno.</p>
                                    <?php endif; ?>
                                </div>

                                <div class="form-floating mb-2">
                                    <textarea
                                        id="motivo"
                                        name="motivo"
                                        class="form-control"
                                        style="height:100px"
                                        maxlength="300"
                                        placeholder="Motivo"
                                        required></textarea>
                                    <label>Describe el motivo</label>
                                </div>
                                <div class="text-end mb-3">
                                    <small id="contadorMotivo" class="text-muted">0 / 300</small>
                                </div>
                                <div class="d-grid gap-2">
                                    <button id="btnLimpiar" type="button" class="btn btn-outline-secondary">
                                        <i class="bi bi-eraser"></i>
                                        Limpiar
                                    </button>
                                    <button id="btnEnviar" type="submit" class="btn btn-roy" disabled>
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
        <script>
            $(function () {

    var meses = [
        'ene', 'feb', 'mar', 'abr', 'may', 'jun',
        'jul', 'ago', 'sep', 'oct', 'nov', 'dic'
    ];
    var diasSemana = [
        'domingo', 'lunes', 'martes', 'miércoles',
        'jueves', 'viernes', 'sábado'
    ];

    var $inputFecha     = $('#fecha');
    var $inputCubreId   = $('#cubre_id');
    var $boletoDia       = $('#boletoDia');
    var $boletoMes        = $('#boletoMes');
    var $boletoDiaSemana   = $('#boletoDiaSemana');
    var $motivo          = $('#motivo');
    var $contadorMotivo  = $('#contadorMotivo');
    var $btnEnviar       = $('#btnEnviar');
    var $btnLimpiar      = $('#btnLimpiar');
    var $buscarCompanero = $('#buscarCompanero');

    function pintarBoleto(fechaISO) {
        if (!fechaISO) {
            $boletoDia.text('—');
            $boletoMes.text('—');
            $boletoDiaSemana.text('Elige un día en el calendario');
            return;
        }
        var partes = fechaISO.split('-').map(Number);
        var fechaObj = new Date(partes[0], partes[1] - 1, partes[2]);
        var nombreDia = diasSemana[fechaObj.getDay()];

        $boletoDia.text(partes[2]);
        $boletoMes.text(meses[partes[1] - 1]);
        $boletoDiaSemana.text(nombreDia.charAt(0).toUpperCase() + nombreDia.slice(1));
    }

    function actualizarBoton() {
        var hayFecha     = $inputFecha.val() !== '';
        var hayCompanero = $inputCubreId.val() !== '';
        var hayMotivo    = $motivo.val().trim() !== '';
        $btnEnviar.prop('disabled', !(hayFecha && hayCompanero && hayMotivo));
    }

    // Selección de día. Si libs/js/calendario.js ya hace esto mismo en
    // esta página, esta parte es redundante pero inofensiva.
    $('#calendarioTxt').on('click', '.celda-dia[data-seleccionable="true"]', function () {
        var fecha = $(this).data('fecha');

        $('#calendarioTxt .celda-dia').removeClass('seleccionado');
        $(this).addClass('seleccionado');

        $inputFecha.val(fecha);
        pintarBoleto(fecha);
        actualizarBoton();
    });

    // Selección de compañero
    $('#listaCompaneros').on('change', 'input[type="radio"]', function () {
        $('.companero-opcion').removeClass('seleccionado');
        $(this).closest('.companero-opcion').addClass('seleccionado');
        $inputCubreId.val($(this).val());
        actualizarBoton();
    });

    // Buscador de compañeros (filtro en el cliente, sin recargar)
    $buscarCompanero.on('input', function () {
        var texto = $(this).val().trim().toLowerCase();
        $('.companero-opcion').each(function () {
            var coincide = $(this).data('nombre').indexOf(texto) !== -1;
            $(this).toggleClass('oculto', !coincide);
        });
    });

    $motivo.on('input', function () {
        var largo = $motivo.val().length;
        $contadorMotivo.text(largo + ' / 300');
        actualizarBoton();
    });

    $btnLimpiar.on('click', function () {
        $motivo.val('');
        $contadorMotivo.text('0 / 300');
        actualizarBoton();
    });

    $('#formTxt').on('submit', function (e) {
        if (!$inputFecha.val()) {
            e.preventDefault();
            alert('Selecciona el día que necesitas faltar.');
            return;
        }
        if (!$inputCubreId.val()) {
            e.preventDefault();
            alert('Elige quién cubrirá tu turno.');
            return;
        }
        if (!$motivo.val().trim()) {
            e.preventDefault();
            alert('Describe el motivo de tu solicitud.');
            return;
        }
        // Aquí es donde engancharías la firma capturada con
        // signature_pad/firmas.js antes de dejar continuar el submit.
    });

    pintarBoleto(null);
    actualizarBoton();
});
        </script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script src="libs/js/kiosco.js"></script>
        <script src="libs/js/calendario.js"></script>

        <!-- FIRMA: primero sus dependencias -->
        <script src="../modulos/firmas/assets/js/signature_pad.min.js"></script>
        <script src="../modulos/firmas/assets/js/firmas.js"></script>

        <!-- Después nuestro módulo -->
        <script src="libs/js/txt_futura.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Kiosco.iniciar();
            });
        </script>
    </body>
</html>