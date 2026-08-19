<?php
  $page_title = 'Lista de encargados';
  require_once __DIR__ . '/../../app/bootstrap.php';

  // Checkin What level user has permission to view this page
  page_require_level(5);
  require_permiso('personal.jefes_planilla');

  $limite = 10;

  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $pagina = ($pagina < 1) ? 1 : $pagina;

  $total_registros = count_view_user_cuadrilla(); // COUNT(*)
  $total_paginas   = ceil($total_registros / $limite);

  if ($pagina > $total_paginas && $total_paginas > 0) {
    redirect('?page='.$total_paginas);
  }

  $estatusMap = [
    1 => ['texto' => 'ACTIVO',       'class' => 'label-success'],
    2 => ['texto' => 'INACTIVO',     'class' => 'label-default'],
    3 => ['texto' => 'BAJA',         'class' => 'label-danger'],
  ];

  $offset = ($pagina - 1) * $limite;

  // CONSULTA FINAL
  $all_jefes = find_view_user_cuadrilla_paginated($limite, $offset);

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
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Encargados</span>
       </strong>
        <a href="add_user.php?tipo=jefe" class="btn btn-roy pull-right">Agregar usuario</a>
      </div>
     <div class="panel-body">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th class="text-center" style="width: 50px;">Nomina</th>
            <th class="text-center">Nombre </th>
            <th class="text-center" style="width: 15%;">Puesto</th>
            <th class="text-center" style="width: 15%;">Rol de usuario</th>
            <th class="text-center" style="width: 10%;">Estado</th>
            <th class="text-center" style="width: 100px;">Acciones</th>
            <th class="text-center" style="width: 100px;">Equipo</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($all_jefes as $a_user): ?>
          <tr>

            <td class="text-center"><?php echo remove_junk(ucwords($a_user['id'])); ?></td>
            <!-- Nombre -->
            <td>
              <a href="detalle_empleado.php?id=<?php echo (int)$a_user['id']; ?>&return=users.php">
                <?php echo remove_junk(ucwords($a_user['name']));?>
              </a>
            </td>
            <!-- Puesto -->
            <td class="text-center"><?php echo remove_junk(ucwords($a_user['puesto'])); ?></td>
            <!-- Rol -->
            <td class="text-center"><?php echo remove_junk(ucwords($a_user['group_name'])); ?></td>
            <!-- Estado -->
            <?php
              $estatusId = (int)$a_user['statusLaboral_id'];
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
            <!-- Acciones -->
            <td class="text-center">
              <div class="btn-group">
                  <a href="edit_user.php?id=<?php echo (int)$a_user['id']; ?>&return=users.php"
                    class="btn btn-xs btn-warning"
                    title="Editar Usuario">
                    <i class="glyphicon glyphicon-pencil"></i>
                  </a>
                  <a href="delete_user.php?id=<?php echo (int)$a_user['id']; ?>&return=users.php" class="btn btn-xs btn-danger"
                    class="btn btn-xs btn-danger"
                    data-toggle="tooltip"
                    title="Eliminar">
                    <i class="glyphicon glyphicon-remove"></i>
                  </a>
              </div>
            </td>
            <!-- Equipo -->
            <td class="text-center">
              <a href="usersTeams.php?id=<?php echo (int)$a_user['id']; ?>"
                class="btn btn-info btn-xs">
                Ver
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
     </table>
      <div class="table-footer clearfix">
        <div class="pull-left text-muted" style="font-size:12px; line-height:30px;">
          Mostrando <?php echo count($all_jefes); ?> de <?php echo $total_registros; ?> registros
        </div>
        <div class="pull-right">
          <div class="pull-right">
            <ul class="pagination pagination-sm no-margin">
              <li class="<?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                <a href="?page=<?php echo $pagina - 1; ?>">&laquo;</a>
              </li>
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="<?php echo ($i == $pagina) ? 'active' : ''; ?>">
                  <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
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
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>