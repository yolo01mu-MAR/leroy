<?php
  $page_title = 'Lista de empleados';
  require_once __DIR__ . '/../../app/bootstrap.php';

  // VALIDAR ACCESO PRIMERO
  page_require_level(5);
  
  if (!isset($_GET['id'])) {
    redirect('users.php');
  }

  $jefe_id = (int)$_GET['id'];

  $estatusMap = [
    1 => ['texto' => 'ACTIVO',       'class' => 'label-success'],
    2 => ['texto' => 'INACTIVO',     'class' => 'label-default'],
    3 => ['texto' => 'BAJA',         'class' => 'label-danger'],
    4 => ['texto' => 'INCAPACIDAD',  'class' => 'label-warning'],
    5 => ['texto' => 'VACACIONES',   'class' => 'label-info'],
  ];

  // CONSULTAS
  $jefe     = find_jefe_planilla($jefe_id);
  $planilla = find_planilla_by_jefe($jefe_id);

  $estructura = [];

  foreach($planilla as $usuario){

    $departamento = $usuario['departamento_plantilla'];
    $grupo        = $usuario['grupo'];

    $estructura[$departamento][$grupo][] = $usuario;
  }
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
      <div class="panel-heading clearfix">
        <!-- IZQUIERDA -->
        <div class="pull-left">

          <!-- BOTON -->
          <a href="<?php
            if ($user['user_level'] == 1) {
              echo 'users.php';
            } elseif ($user['user_level'] == 2) {
              echo 'home.php';
            } elseif ($user['user_level'] == 5) {
              echo 'users.php';
            }
          ?>" class="btn btn-default btn-xs" style="margin-bottom:8px;">
            <span class="glyphicon glyphicon-arrow-left"></span>
            Regresar
          </a>
          <?php if($jefe): ?>
            <!-- TITULO -->
            <div style="margin-top:3px;">
              <div style="
                font-size:18px;
                font-weight:600;
                color:#333;
                margin-bottom:6px;
                ">
                PLANILLA DE:
                <span style="color:#2c3e50;">
                  <?php echo remove_junk($jefe['encargado']); ?>
                </span>
              </div>
              <!-- BADGES -->
              <div>
                <span class="label label-primary">
                  <?php echo remove_junk($jefe['nave']); ?>
                </span>
                <span class="label label-default">
                  <?php echo remove_junk($jefe['departamento']); ?>
                </span>
                <span class="label label-success">
                  <?php echo count($planilla); ?> empleados
                </span>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <!-- DERECHA -->
        <div class="pull-right" style="width:260px; margin-top:15px;">
          <div class="input-group input-group-sm">
            <span class="input-group-addon">
              <span class="glyphicon glyphicon-search"></span>
            </span>
            <input type="text"
                  id="buscador"
                  class="form-control"
                  placeholder="Buscar por nómina o nombre">
          </div>
        </div>
    </div>
  </div>
  <div class="panel-body">
    <!-- TABS -->
    <ul class="nav nav-tabs" role="tablist">
      <?php
        $i = 0;
        foreach($estructura as $departamento => $grupos):
      ?>
        <li role="presentation" class="<?php echo ($i == 0) ? 'active' : ''; ?>">
          <a href="#tab_<?php echo $i; ?>" aria-controls="tab_<?php echo $i; ?>" role="tab" data-toggle="tab">
            <?php echo remove_junk($departamento); ?>
            <span class="badge">
              <?php
                $total = 0;
                foreach($grupos as $emps){
                  $total += count($emps);
                }
                echo $total;
              ?>
            </span>
          </a>
        </li>
      <?php
        $i++;
        endforeach;
      ?>
    </ul>
    <!-- CONTENIDO -->
    <div class="tab-content" style="margin-top:20px;">
      <?php
        $i = 0;
        foreach($estructura as $departamento => $grupos):
      ?>
        <div role="tabpanel"
            class="tab-pane fade <?php echo ($i == 0) ? 'in active' : ''; ?>"
            id="tab_<?php echo $i; ?>">
          <?php foreach($grupos as $grupo => $empleados): ?>
            <!-- HEADER GRUPO -->
            <div style="
                margin-bottom:10px;
                padding:10px;
                background:#f5f5f5;
                border-left:4px solid #337ab7;
            ">
              <strong>
                Grupo <?php echo remove_junk($grupo); ?>
              </strong>
              <span class="badge pull-right">
                <?php echo count($empleados); ?>
              </span>
            </div>
            <!-- TABLA -->
            <div class="table-responsive">
              <table class="table table-bordered table-striped tabla-fija">
                <thead>
                  <tr>
                    <th class="text-center" style="width:80px;">Nomina</th>
                    <th class="text-center">Nombre</th>
                    <th class="text-center">Puesto</th>
                    <th class="text-center">Estatus</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach($empleados as $usuario): ?>
                  <tr>
                    <td class="text-center">
                      <?php echo (int)$usuario['id']; ?>
                    </td>
                    <td>
                      <?php echo remove_junk(ucwords($usuario['name'])); ?>
                    </td>
                    <td class="text-center">
                      <?php echo remove_junk(ucwords($usuario['puesto'])); ?>
                    </td>
                    <?php
                      $estatusId = (int)$usuario['statusLaboral_id'];
                      if (isset($estatusMap[$estatusId])) {
                        $e = $estatusMap[$estatusId];
                        echo "<td class='text-center'>
                                <span class='label {$e['class']}'>
                                  {$e['texto']}
                                </span>
                              </td>";
                      } else {
                        echo "<td class='text-center'>
                                <span class='label label-default'>
                                  DESCONOCIDO
                                </span>
                              </td>";
                      }
                    ?>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endforeach; ?>
        </div>
      <?php
        $i++;
        endforeach;
      ?>
    </div>
      <!-- <div class="panel-body">
        <div class="tabla-scroll">
          <table class="table table-bordered table-striped tabla-fija">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">Nomina</th>
                <th class="text-center">Nombre</th>
                <th class="text-center">Puesto</th>
                <th class="text-center">Zona</th>
                <th class="text-center">Nave</th>
                <th class="text-center">Grupo</th>
                <th class="text-center">Estatus</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($planilla as $usuario): ?>
              <tr>
                <td class="text-center"><?php echo (int)$usuario['id']; ?></td>
                <td><?php echo remove_junk(ucwords($usuario['name'])); ?></td>
                <td class="text-center"><?php echo remove_junk(ucwords($usuario['puesto'])); ?></td>
                <td class="text-center"><?php echo remove_junk(ucwords($usuario['departamento_plantilla'])); ?></td>
                <td class="text-center"><?php echo remove_junk(ucwords($usuario['nave'])); ?></td>
                <td class="text-center"><?php echo remove_junk(ucwords($usuario['grupo'])); ?></td>
                <?php
                  $estatusId = (int)$usuario['statusLaboral_id'];
                  if (isset($estatusMap[$estatusId])) {
                    $e = $estatusMap[$estatusId];
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
      </div> -->
  </div>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>