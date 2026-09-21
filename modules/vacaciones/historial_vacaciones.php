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
<link rel="stylesheet" href="assets/css/historial_vacaciones.css">
<div class="row">
   <div class="col-md-12">
        <?php echo display_msg($msg); ?>
   </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    Historial de Vacaciones
                </strong>
                <div class="pull-right filtro-historial">
                    <!-- BUSCADOR -->
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-search"></span>
                        </span>
                        <input
                            type="text"
                            id="buscador"
                            class="form-control"
                            placeholder="Nómina o nombre"
                            autocomplete="off">

                    </div>
                    <!-- SEMANA -->
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">
                            Semana
                        </span>
                        <input
                            type="number"
                            id="filtro_semana"
                            class="form-control no-spinners"
                            value="<?php echo $semana_actual; ?>"
                            min="1"
                            max="53"
                            step="1"
                            title="Número de semana">
                    </div>
                    <!-- AÑO -->
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">
                            Año
                        </span>
                        <input
                            type="number"
                            id="filtro_anio"
                            class="form-control no-spinners"
                            value="<?php echo $anio_actual; ?>"
                            min="2020"
                            max="2100"
                            step="1"
                            title="Año">
                    </div>
                </div>
                <!-- LIMPIAR -->
                <button
                    type="button"
                    id="btn_limpiar_filtros"
                    class="btn btn-default btn-sm"
                    title="Limpiar filtros">

                    <span class="glyphicon glyphicon-refresh"></span>

                </button>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">Nomina</th>
                        <th class="text-center">Nombre</th>
                        <th class="text-center">Fecha de solicitud</th>
                        <th class="text-center">Periodo Inicio</th>
                        <th class="text-center">Periodo Fin</th>
                        <th class="text-center" style="width: 100px;">Dias</th>
                        <th class="text-center" style="width: 100px;">Estatus</th>
                    </tr>
                    </thead>
                    <tbody id="tabla-resultados">
                        <?php foreach($empleadosSaldos as $emp): ?>
                        <tr>
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['nomina'])); ?></td>
                            <!-- Nombre -->
                             <td class="text-center"><?php echo remove_junk(ucwords($emp['nombre'])); ?></td>
                            <!-- Ingreso -->
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['fecha_solicitud'])); ?></td>
                            <!-- Periodo -->
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['inicio'])); ?></td>
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['fin'])); ?></td>
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['dias'])); ?></td>
                            <!-- Estado -->
                            <?php
                                $estatus = remove_junk(ucwords($emp['estatus']));
                                if (isset($estatusMap[$estatus])) {
                                    $e = $estatusMap[$estatus];
                                    echo "<td class='text-center'>
                                            <span class='label {$e['class']}'>{$e['texto']}</span>
                                        </td>";
                                } else {
                                    echo "<td class='text-center'>
                                            <span class='label label-default'>DESCONOCIDO</span>
                                        </td>";
                                }
                            ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- BOTON FLOTANTE -->
 <button 
    id="btn_generar_reporte" 
    class="btn btn-primary btn-flotante" 
    onclick="scrollToTop()">
    Generar reporte

</button>

<!-- MODAL DE DETALLE -->
 
<div class="modal fade" id="modalDetalleEncargado" tabindex="-1" role="dialog" aria-labelledby="modalDetalleEncargadoLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalDetalleEncargadoLabel">
                    <span class="glyphicon glyphicon-user"></span>
                    <span id="detalle-nombre-titulo">Detalle del encargado</span>
                </h4>
            </div>
            <div class="modal-body">
                <table class="table table-condensed">
                    <tr>
                        <td style="width:40%;"><strong>Nómina</strong></td>
                        <td id="detalle-nomina"></td>
                    </tr>
                    <tr>
                        <td><strong>Nombre</strong></td>
                        <td id="detalle-nombre"></td>
                    </tr>
                    <tr>
                        <td><strong>Ingreso</strong></td>
                        <td id="detalle-ingreso"></td>
                    </tr>
                    <tr>
                        <td><strong>Periodo</strong></td>
                        <td>
                            <span id="detalle-inicio"></span>
                            &nbsp;–&nbsp;
                            <span id="detalle-fin"></span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Estado</strong></td>
                        <td>
                            <span id="detalle-estado" class="label"></span>
                        </td>
                    </tr>
                </table>
                <hr>
                <!-- DESGLOSE DE DÍAS -->
                <table class="table table-bordered text-center" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th class="text-center">Otorgados</th>
                            <th class="text-center">Disfrutados</th>
                            <th class="text-center">Pendientes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td id="detalle-otorgados" style="font-size:18px; font-weight:bold;"></td>
                            <td id="detalle-disfrutados" style="font-size:18px; font-weight:bold;"></td>
                            <td id="detalle-pendientes" style="font-size:18px; font-weight:bold; color:#d97706;"></td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-condensed" style="margin-top:15px; margin-bottom:0;">
                    <tr>
                        <td style="width:40%;"><strong>Se generó</strong></td>
                        <td id="detalle-creo"></td>
                    </tr>
                    <tr>
                        <td><strong>Se actualizó</strong></td>
                        <td id="detalle-actualizo"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
    const semanaActual = <?php echo $semana_actual; ?>;
    const anioActual = <?php echo $anio_actual; ?>;
</script>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>