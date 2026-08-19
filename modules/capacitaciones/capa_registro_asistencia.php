<?php
  $page_title = 'Registro Capacitacion';

  $libraries = [
    'leer_codigo_barras'
  ];
  $scripts = [
    'capa_registro'
  ];

  require_once __DIR__ . '/../../app/bootstrap.php';

  $id_capacitacion = (int)$_GET['id'];
  $capacitacion = find_by_id('historico_capacitaciones', $id_capacitacion);

  // CONSULTA FINAL
  $lista_capa = find_asistencia_capacitacion($id_capacitacion);

  // Checkin What level user has permission to view this page
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
                    <span>Asistencia de Capacitacion:</span>
                </strong>
            </div>
            <div class="panel-body">
                <div class="text-center">
                    <h2><?php echo remove_junk($capacitacion['nombre']); ?></h2>
                    <p>Instructor: <strong><?php echo remove_junk($capacitacion['instructor']); ?></strong></p>
                    <p>Fecha: <strong><?php echo $capacitacion['fecha']; ?></strong></p>
                    <p>Lugar: <strong><?php echo remove_junk($capacitacion['ubicacion']); ?></strong></p>
                    <button
                        type="button"
                        id="btnLeer"
                        class="btn btn-success btn-lg btn-block">
                        Leer código
                    </button>
                    <div id="reader" style="width:100%; max-width:400px; margin:15px auto;"></div>
                </div>
                <br>
                <div class="table-responsive">
                    <input
                        type="hidden"
                        id="capacitacion_id"
                        value="<?php echo $id_capacitacion; ?>">

                    <div id="resultado"></div>
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th class="text-center" style="width: 50px;">Nomina</th>
                            <th class="text-center" >Nombre</th>
                            <th class="text-center">Puesto</th>
                            <th class="text-center">Departamento al que pertenece</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($lista_capa as $lista): ?>
                        <tr>
                            <td class="text-center"><?php echo count_id();?></td>
                            <td><?php echo remove_junk(ucwords($lista['nomina']))?></td>
                            <td class="text-center"><?php echo remove_junk(ucwords($lista['name']))?></td>
                            <td class="text-center"><?php echo remove_junk(ucwords($lista['puesto']))?></td>
                            <td class="text-center"><?php echo remove_junk(ucwords($lista['zona']))?></td>
                        </tr>
                        <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>