<?php
    $page_title = 'Lista de encargados';
    require_once __DIR__ . '/../../app/bootstrap.php';

    // Checkin What level user has permission to view this page
    page_require_level(5);
    require_permiso('personal.jefes_planilla');

    $scripts = [
        'buscar_usuario_saldo'
    ];

    $estatusMap = [
        'VIGENTE'    => ['texto' => 'VIGENTE',  'class' => 'label-success'],
        'FINALIZADO' => ['texto' => 'INACTIVO', 'class' => 'label-default'],
    ];

    $empleadosSaldos = get_saldo_vacaciones();

?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
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
                    SALDO DE VACACIONES
                </strong>
                <!-- Buscador Generico -->
                <div class="pull-right buscador-panel">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-search"></span>
                        </span>
                        <input
                            type="text"
                            id="buscador"
                            class="form-control"
                            placeholder="Buscar por nómina o nombre">
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <a href="" class="btn btn-roy pull-right">Actualizar</a>
                    <br>
                    <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">Nomina</th>
                        <th class="text-center">Colaborador</th>
                        <th class="text-center" style="width: 15%;">Grupo</th>
                        <th class="text-center" style="width: 10%;">Fecha de Ingreso</th>
                        <th class="text-center" style="width: 10%;">Otorgados</th>
                        <th class="text-center" style="width: 100px;">Disfrutados</th>
                        <th class="text-center" style="width: 100px;">Pendientes</th>
                        <th class="text-center" style="width: 100px;">Disponibles</th>
                    </tr>
                    </thead>
                    <tbody id="tabla-resultados">
                        <?php foreach($empleadosSaldos as $emp): ?>
                        <tr>
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['nomina'])); ?></td>
                            <!-- Nombre -->
                             <td class="text-center">
                                <?php echo remove_junk(ucwords($emp['nombre'])); ?>
                                <br>
                                <?php echo remove_junk(ucwords($emp['puesto'])); ?>
                            </td>
                            <td class="text-center">
                                <?php echo remove_junk(ucwords($emp['grupos'])); ?>
                                <br>
                                <?php echo remove_junk(ucwords($emp['departamento'])); ?>
                            </td>
                            <!-- Ingreso -->
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['ingreso'])); ?></td>
                            <!-- Periodo -->
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['otorgados'])); ?></td>
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['disfrutados'])); ?></td>
                            <!-- Actualizaciones -->
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['pendientes'])); ?></td>
                            <td class="text-center"><?php echo remove_junk(ucwords($emp['disponibles'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>