<?php
  $page_title = 'Detalle empleado';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $return = $_GET['return'] ?? 'allUsers.php';

  page_require_level(5);

  $id = (int)$_GET['id'];

  // EMPLEADO
  $empleado = find_by_id('users', $id);

  if(!$empleado){
    $session->msg("d","Empleado no encontrado.");
    redirect('allUsers.php');
  }

  // CONSULTA DE LOS DETALLES
  $empleado = view_detalle_empleado($id);

  if(empty($empleado)){
    $session->msg("d","Empleado no encontrado.");
    redirect('allUsers.php');
  }

  $empleado = $empleado[0];

  // ESTATUS
  $estatusMap = [
    1 => ['texto' => 'ACTIVO',       'class' => 'label-success'],
    2 => ['texto' => 'INACTIVO',     'class' => 'label-default'],
    3 => ['texto' => 'BAJA',         'class' => 'label-danger'],
    4 => ['texto' => 'INCAPACIDAD',  'class' => 'label-warning'],
    5 => ['texto' => 'VACACIONES',   'class' => 'label-info'],
  ];

  $estatusId = (int)$empleado['statusLaboral_id'];

  $estatusTexto = 'DESCONOCIDO';
  $estatusClass = 'label-default';

  if(isset($estatusMap[$estatusId])){
    $estatusTexto = $estatusMap[$estatusId]['texto'];
    $estatusClass = $estatusMap[$estatusId]['class'];
  }

?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>

<div class="row detalle-empleado">
  <!-- SIDEBAR -->
  <div class="col-md-4">
    <div class="panel panel-default panel-perfil">
      <div class="panel-body text-center">
        <?php
          $foto = !empty($empleado['image'])
              ? BASE_URL . '/uploads/users/' . $empleado['image']
              : BASE_URL . '/uploads/users/no_image.jpg';
        ?>
        <img src="<?php echo $foto; ?>"
             class="img-responsive center-block"
             width="125">
        <h3 style="margin-top:15px;">
          <?php echo remove_junk($empleado['nombre']); ?>
        </h3>
        <p class="text-muted">
          <?php echo remove_junk($empleado['puesto']); ?>
        </p>
        <span class="label <?php echo $estatusClass; ?>">
          <?php echo $estatusTexto; ?>
        </span>
        <hr>
        <!-- BOTONES -->
        <a href="credencial.php?id=<?php echo (int)$empleado['id']; ?>"
           class="btn btn-primary btn-block">
          <i class="glyphicon glyphicon-print"></i>
          Imprimir credencial
        </a>
        <a href="edit_userAll.php?id=<?php echo (int)$empleado['id']; ?>"
           class="btn btn-warning btn-block"
           style="margin-top:10px;">
          <i class="glyphicon glyphicon-pencil"></i>
          Editar empleado
        </a>
        <a href="<?= htmlspecialchars($return); ?>"
          class="btn btn-default btn-block"
          style="margin-top:10px;">
          <i class="glyphicon glyphicon-arrow-left"></i>
          Regresar
        </a>
      </div>
    </div>
  </div>
  <!-- INFORMACION -->
  <div class="col-md-8 panel-info">
    <!-- INFORMACION GENERAL -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="bi bi-person-circle"></span>
          Información general
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered">
          <tr>
            <th width="30%">Nómina</th>
            <td><?php echo (int)$empleado['id']; ?></td>
          </tr>
          <tr>
            <th>Nombre</th>
            <td><?php echo remove_junk($empleado['nombre']); ?></td>
          </tr>
          <tr>
            <th>Puesto</th>
            <td><?php echo remove_junk($empleado['puesto']); ?></td>
          </tr>
          <tr>
            <th>Departamento</th>
            <td><?php echo remove_junk($empleado['departamento']); ?></td>
          </tr>
          <tr>
            <th>Área</th>
            <td><?php echo remove_junk($empleado['zonaTrabajo']); ?></td>
          </tr>
          <tr>
            <th>Grupo</th>
            <td><?php echo remove_junk($empleado['grupo']); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <!-- DETALLES DEL SEGURO -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="bi bi-hospital"></span>
          Datos del Seguro
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-striped table-bordered">
          <tr>
            <th>CURP</th>
            <td><?php echo remove_junk($empleado['CURP']); ?></td>
          </tr>
          <tr>
            <th>RFC</th>
            <td><?php echo remove_junk($empleado['RFC']); ?></td>
          </tr>
          <tr>
            <th>NSS</th>
            <td><?php echo remove_junk($empleado['NSS']); ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>