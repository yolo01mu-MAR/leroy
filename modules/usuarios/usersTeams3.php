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
        <div class="asistencia-header">
          <div class="header-info">
            <h2>Pase de asistencia</h2>
            <div class="header-jefe">
              <?php echo remove_junk($jefe['encargado']); ?>
            </div>
            <div class="header-badges">
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
          <div class="header-right">
            <div class="fecha">
              <?php echo date('d/m/Y'); ?>
            </div>
            <input
              id="buscador"
              type="text"
              class="form-control"
              placeholder="Buscar empleado...">
          </div>
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
              <button type="button" class="btn btn-success btn-xs btn-todos">
                <span class="glyphicon glyphicon-ok"></span>
                Todos A
              </button>
            </div>
            <div class="grupo-box">
              <div class="table-responsive">
                <table class="table tabla-asistencia tabla-grupo">
                  <thead>
                    <tr>
                      <th width="80">Nómina</th>
                      <th>Empleado</th>
                      <th width="180">Puesto</th>
                      <th class="text-center" width="60">
                        <span class="text-success">A</span>
                      </th>
                      <th class="text-center" width="60">
                        <span class="text-danger">FI</span>
                      </th>
                      <th class="text-center" width="60">
                        <span style="color:#f39c12;">TPT</span>
                      </th>
                      <th class="text-center" width="60">
                        <span class="text-info">INC</span>
                      </th>
                      <th class="text-center" width="60">
                        <span class="text-primary">VAC</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($empleados as $usuario): ?>
                      <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td>
                          <strong>
                            <?php echo remove_junk(ucwords($usuario['name'])); ?>
                          </strong>
                        </td>
                        <td><?php echo remove_junk($usuario['puesto']); ?></td>
                        <td class="text-center">
                          <label class="radio-color verde">
                              <input
                                  type="radio"
                                  name="estado[<?php echo $usuario['id'];?>]"
                                  value="A"
                                  checked>
                              <span></span>
                          </label>
                        </td>
                        <td class="text-center">
                          <label class="radio-color rojo">
                              <input
                                  type="radio"
                                  name="estado[<?php echo $usuario['id'];?>]"
                                  value="FI">
                              <span></span>
                          </label>
                        </td>
                        <td class="text-center">
                          <label class="radio-color amarillo">
                              <input
                                  type="radio"
                                  name="estado[<?php echo $usuario['id'];?>]"
                                  value="TPT">
                              <span></span>
                          </label>
                        </td>
                        <td class="text-center">
                          <label class="radio-color azul">
                              <input
                                  type="radio"
                                  name="estado[<?php echo $usuario['id'];?>]"
                                  value="INC">
                              <span></span>
                          </label>
                        </td>
                        <td class="text-center">
                          <label class="radio-color morado">
                              <input
                                  type="radio"
                                  name="estado[<?php echo $usuario['id'];?>]"
                                  value="VAC">
                              <span></span>
                          </label>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php
        $i++;
        endforeach;
      ?>
    </div>
  </div>
</div>
<style>
  .grupo-card{

    background:#fff;

    border-radius:10px;

    margin-bottom:25px;

    border:1px solid #ddd;

    overflow:hidden;
  }
  .tabla-asistencia{

      margin:0;

  }

  .tabla-asistencia thead{

      background:#34495e;

      color:white;

  }

  .tabla-asistencia th{

      text-align:center;

      vertical-align:middle;

  }

  .tabla-asistencia td{

      vertical-align:middle;

  }

  .tabla-asistencia tbody tr:hover{

      background:#f8f9fa;

  }
  .radio-color{

      display:flex;

      justify-content:center;

      cursor:pointer;

      margin:0;

  }

  .radio-color input{

      display:none;

  }

  .radio-color span{

      width:22px;

      height:22px;

      border-radius:50%;

      border:2px solid #bbb;

      background:white;

      transition:.2s;

  }
  .verde input:checked + span{

      background:#5cb85c;

      border-color:#5cb85c;

  }

  .rojo input:checked + span{

      background:#d9534f;

      border-color:#d9534f;

  }

  .amarillo input:checked + span{

      background:#f0ad4e;

      border-color:#f0ad4e;

  }

  .azul input:checked + span{

      background:#5bc0de;

      border-color:#5bc0de;

  }

  .morado input:checked + span{

      background:#7f5af0;

      border-color:#7f5af0;

  }
  .btn-todos{

      border-radius:20px;

      padding:4px 15px;

      font-weight:600;

  }
</style>
<script>
  document.querySelectorAll(".btn-todos").forEach(function(boton){

      boton.addEventListener("click",function(){

          // Buscar únicamente la tabla del grupo
          const grupo = this.closest(".grupo-box");

          // Buscar todos los radios de Asistencia
          grupo.querySelectorAll("input[value='A']").forEach(function(radio){

              radio.checked = true;

          });

      });

  });
</script>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>