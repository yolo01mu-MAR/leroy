<?php
  $page_title = 'Registro incidencia';

  $libraries = [
    'leer_codigo_barras'
  ];
  $scripts = [
    'incidencia_registro'
  ];

  require_once __DIR__ . '/../../app/bootstrap.php';
  page_require_level(5);
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
                    <span>Registro de insidencia</span>
                </strong>
            </div>
            <div class="panel-body">
                <div class="text-center">
                    <button
                        type="button"
                        id="btnLeer"
                        class="btn btn-success btn-lg btn-block">
                        Leer credencial
                    </button>
                    <div id="reader" style="width:100%; max-width:400px; margin:15px auto;"></div>
                </div>
            </div>
            <br>
            <div id="formIncidencia">
            <!-- <div id="formIncidencia" style="display:none;">     -->
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <strong>Datos del empleado</strong>
                    </div>

                    <div class="panel-body">

                        <table class="table table-bordered table-condensed">

                            <tr>
                                <th width="25%">Nómina</th>
                                <td id="nomina"></td>
                            </tr>

                            <tr>
                                <th>Nombre</th>
                                <td id="nombre"></td>
                            </tr>

                            <tr>
                                <th>Puesto</th>
                                <td id="puesto"></td>
                            </tr>

                            <tr>
                                <th>Departamento</th>
                                <td id="departamento"></td>
                            </tr>

                            <tr>
                                <th>Área</th>
                                <td id="area"></td>
                            </tr>

                            <tr>
                                <th>Grupo</th>
                                <td id="grupo"></td>
                            </tr>

                            <tr>
                                <th>Encargado</th>
                                <td id="encargado"></td>
                            </tr>

                        </table>

                    </div>
                </div>
                <div class="panel panel-warning">

                    <div class="panel-heading">
                        <strong>Información de la incidencia</strong>
                    </div>

                    <div class="panel-body">

                        <div class="form-group">

                            <label>Tipo de incidencia</label>

                            <select class="form-control" name="tipo">

                                <option value="">Seleccione...</option>

                                <option>Asistencia</option>

                                <option>Conducta</option>

                                <option>Seguridad</option>

                                <option>Equipo</option>

                                <option>Producción</option>

                                <option>Calidad</option>

                                <option>Otro</option>

                            </select>

                        </div>

                        <div class="form-group">

                            <label>Motivo</label>

                            <input
                                type="text"
                                class="form-control"
                                name="motivo">

                        </div>

                        <div class="form-group">

                            <label>Descripción</label>

                            <textarea
                                rows="5"
                                class="form-control"
                                name="descripcion"></textarea>

                        </div>

                    </div>

                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">

                        <strong>Evidencias</strong>

                    </div>

                    <div class="panel-body">

                        <input
                            type="file"
                            class="form-control"
                            accept="image/*"
                            capture="environment"
                            multiple>

                    </div>

                </div>
            </div>
        </div>
    </div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>