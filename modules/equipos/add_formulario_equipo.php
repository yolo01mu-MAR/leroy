<?php
    $page_title = 'Agregar Equipo';

    $scripts = [
        'add_formulario_equipo'
    ];

    require_once __DIR__ . '/../../app/bootstrap.php';
    page_require_level(2);

    $tipo = get_equipo_tipo();
    $plan = get_equipo_plan();
    $departamento = get_departamento();

?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-plus"></span>
                    Agregar Equipo  
                </strong>
                <a href="equipos_inventario.php" class="btn btn-danger btn-xs pull-right">
                    <span class="glyphicon glyphicon-remove"></span> Cancelar
                </a>
            </div>
            <div class="panel-body">
                <form method="post" action="add_equipo.php">
                    <!-- ===================== -->
                    <!-- DATOS GENERALES -->
                    <!-- ===================== -->
                    <h4>Datos Generales</h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Tipo</label>
                            <select class="form-control" name="tipo_equipo" id="tipo_equipo" required>
                                <option value="" selected disabled>Selecciona</option>
                                <?php foreach($tipo as $t): ?>
                                    <option value="<?php echo (int)$t['id']; ?>">
                                    <?php echo remove_junk(ucwords($t['nombre'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    	<div class="col-md-3">
                        	<label>Fecha Alta</label>
                        	<input type="date" class="form-control" name="fecha_alta" value="<?php echo date('Y-m-d'); ?>">
                    	</div>
                        <div class="col-md-4">
                            <label>Estado</label>
                            <select class="form-control" name="estado">
                                <option value="" selected disabled>Selecciona</option>
                                <option value="SI">Funciona</option>
                                <option value="NO">No funciona</option>
                                <option value="REPARACION">En reparación</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Marca</label>
                            <input type="text" class="form-control" name="marca">
                        </div>
                        <div class="col-md-4">
                            <label>Modelo</label>
                            <input type="text" class="form-control" name="modelo">
                        </div>
                        <div class="col-md-4">
                            <label>Año</label>
                            <input type="number" class="form-control" name="anio">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Descripción</label>
                            <input type="text" class="form-control" name="descripcion">
                        </div>
                        <div class="col-md-3">
                            <label>Costo</label>
                            <input type="number" class="form-control" name="costo">
                        </div>
                        <div class="col-md-3">
                            <label>Plan</label>
                            <select class="form-control" name="tipo_plan" id="tipo_plan" required>
                                <option value="" selected disabled>Selecciona</option>
                                <?php foreach($plan as $p): ?>
                                    <option value="<?php echo (int)$p['id']; ?>">
                                    <?php echo remove_junk(ucwords($p['nombre'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Observaciones</label>
                            <textarea class="form-control" name="observaciones"></textarea>
                        </div>
                    </div>
                    <!-- ===================== -->
                    <!-- DETALLES TECNICOS -->
                    <!-- ===================== -->
                    <h4 class="mt-4">Detalles Técnicos</h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <label>No. Serie</label>
                            <input type="text" class="form-control" name="serie" id="serie">
                        </div>
                        <div class="col-md-4">
                            <label>IMEI</label>
                            <input type="text" class="form-control" name="imei" id="imei">
                        </div>
                        <div class="col-md-4">
                            <label>Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="telefono">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Tamaño de Pantalla</label>
                            <input type="text" class="form-control" name="pantalla" id="pantalla">
                        </div>
                        <div class="col-md-3">
                            <label>RAM</label>
                            <input type="text" class="form-control" name="ram" id="ram">
                        </div>
                        <div class="col-md-3">
                            <label>Disco Duro</label>
                            <input type="text" class="form-control" name="disco" id="disco">
                        </div>
                        <div class="col-md-3">
                            <label>Procesador</label>
                            <input type="text" class="form-control" name="procesador" id="procesador">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Sistema Operativo</label>
                            <input type="text" class="form-control" name="so" id="so">
                        </div>
                        <div class="col-md-9">
                            <label>Puertos de comunicacion: </label>
                            <input type="text" class="form-control" name="puertos" id="puertos">
                        </div>
                    </div>
                    <!-- ===================== -->
                    <!-- RED -->
                    <!-- ===================== -->
                    <h4 class="mt-4">Red</h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label>IP</label>
                            <input type="text" class="form-control" name="ip" id="ip">
                        </div>
                        <div class="col-md-6">
                            <label>MAC (WIFI)</label>
                            <input type="text" class="form-control" name="mac" id="mac">
                        </div>
                    </div>
                    <!-- ===================== -->
                    <!-- ZONA -->
                    <!-- ===================== -->
                    <div class="row" id="div_departamento" style="display:none;">
                        <hr>
                        <div class="col-md-12">
                            <label>Donde se ubicara?</label>
                            <select class="form-control" name="departamento" id="departamento">
                                <option value="" selected disabled>Selecciona</option>
                                <?php foreach($departamento as $dep): ?>
                                    <option value="<?php echo (int)$dep['id']; ?>">
                                    <?php echo remove_junk(ucwords($dep['zona'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <br><br>
                    <div class="text-right">
                        <button type="submit" name="add_product" class="btn btn-success">
                            <span class="glyphicon glyphicon-ok"></span> Guardar Equipo
                        </button>
                    </div>
                </form>
            </div>
        </div>
     </div>
</div>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>