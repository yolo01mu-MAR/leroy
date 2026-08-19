<?php
  $page_title = 'Lista de empleados';
  require_once __DIR__ . '/../../app/bootstrap.php';

  // Checkin What level user has permission to view this page
  page_require_level(5);
  require_permiso('personal.empleados');

  $scripts = [
    'buscar_usuario'
  ];

  $limite = 10;

  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $pagina = ($pagina < 1) ? 1 : $pagina;

  $total_registros = count_view_empleados(); // COUNT(*)
  $total_paginas   = ceil($total_registros / $limite);

  if ($pagina > $total_paginas && $total_paginas > 0) {
    redirect('?page='.$total_paginas);
  }

  $estatusMap = [
    1 => ['texto' => 'ACTIVO',       'class' => 'label-success'],
    2 => ['texto' => 'INACTIVO',     'class' => 'label-default'],
    3 => ['texto' => 'BAJA',         'class' => 'label-danger'],
    4 => ['texto' => 'INCAPACIDAD',  'class' => 'label-warning'],
    5 => ['texto' => 'VACACIONES',   'class' => 'label-info'],
  ];

  $offset = ($pagina - 1) * $limite;

  // CONSULTA FINAL
  $empleados = find_view_empleados_paginated($limite, $offset);

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
        <div class="pull-left" style="padding-top:6px;">
              <strong>
                  <span class="glyphicon glyphicon-th"></span>
                  <span>Empleados</span>
              </strong>
          </div>
          <div class="pull-right" style="width:300px;">
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
      <div class="panel-body">
        <div class="tabla-scroll">
          <table class="table table-bordered table-striped tabla-fija">
            <thead>
              <tr>
                <th class="text-center">Nomina</th>
                <th class="text-center" style="width: 30%;">Nombre </th>
                <th class="text-center" style="width: 15%;">Puesto</th>
                <th class="text-center" style="width: 15%;">Departamento</th>
                <th class="text-center" style="width: 15%;">Areas</th>
                <th class="text-center" style="width: 10%;">Grupos</th>
                <th class="text-center" style="width: 20%;">Estatus</th>
                <th class="text-center" style="width: 100px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-resultados">
            <?php foreach ($empleados as $emp): ?>
            <tr>
              <td class="text-center"><?php echo (int)$emp['id'];?></td>
              <td>
                <a href="detalle_empleado.php?id=<?php echo (int)$emp['id']; ?>&return=allUsers.php">
                  <?php echo remove_junk(ucwords($emp['nombre']));?>
                </a>
              </td>
              <td class="text-center">
                <?php echo !empty($emp['puesto']) ? remove_junk(ucwords($emp['puesto'])) : '-'; ?>
              </td>
              <td class="text-center"><?php echo remove_junk(ucwords($emp['departamentos']));?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($emp['dep_cuadrilla']));?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($emp['grupos'])); ?></td>
              <!-- ESTATUS -->
              <?php
                $estatusId = (int)$emp['statusLaboral_id'];
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
              <!-- ACCIONES -->
              <td class="text-center">
                <div class="btn-group">
                  <a href="edit_user.php?id=<?= (int)$emp['id']; ?>&return=allUsers.php"
                    class="btn btn-xs btn-warning"
                    title="Editar Usuario">
                    <i class="glyphicon glyphicon-pencil"></i>
                  </a>
                  <a href="delete_user.php?id=<?php echo (int)$emp['id']; ?>&return=allUsers.php" class="btn btn-xs btn-danger"
                    class="btn btn-xs btn-danger"
                    data-toggle="tooltip"
                    title="Eliminar">
                    <i class="glyphicon glyphicon-remove"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          </table>
          <div class="table-footer clearfix">
            <div class="pull-left text-muted" style="font-size:12px; line-height:30px;">
              Mostrando <?php echo count($empleados); ?> de <?php echo $total_registros; ?> registros
            </div>
            <div class="pull-right">
              <div id="paginacion" class="pull-right">
                <ul class="pagination pagination-sm no-margin">
                  <li class="<?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                    <a href="?page=<?php echo $pagina - 1; ?>">&laquo;</a>
                  </li>
                  <?php
                    $vecinos = 2; // páginas a cada lado

                    $inicio = max(1, $pagina - $vecinos);
                    $fin    = min($total_paginas, $pagina + $vecinos);

                    // Siempre mostrar la primera
                    if ($inicio > 1):
                  ?>
                    <li>
                        <a href="?page=1">1</a>
                    </li>
                    <?php if ($inicio > 2): ?>
                    <li class="disabled"><span>...</span></li>
                    <?php endif; ?>
                    <?php
                    endif;
                    // Páginas alrededor de la actual
                    for ($i = $inicio; $i <= $fin; $i++):
                    ?>
                    <li class="<?= ($i == $pagina) ? 'active' : ''; ?>">
                        <a href="?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <?php
                    // Siempre mostrar la última
                      if ($fin < $total_paginas):
                          if ($fin < $total_paginas - 1):
                      ?>
                            <li class="disabled"><span>...</span></li>
                      <?php endif; ?>
                      <li>
                          <a href="?page=<?= $total_paginas; ?>">
                              <?= $total_paginas; ?>
                          </a>
                      </li>
                    <?php endif; ?>
                    <li class="<?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                      <a href="?page=<?php echo $pagina + 1; ?>">&raquo;</a>
                    </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
