<?php
  $page_title = 'Lista de administradores';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $limite = 10;

  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $pagina = ($pagina < 1) ? 1 : $pagina;

  $total_registros = count_view_user_admins(); // COUNT(*)
  $total_paginas   = ceil($total_registros / $limite);

  if ($pagina > $total_paginas && $total_paginas > 0) {
    redirect('?page='.$total_paginas);
  }

  $offset = ($pagina - 1) * $limite;

  $estatusMap = [
    1 => ['texto' => 'ACTIVO',       'class' => 'label-success'],
    2 => ['texto' => 'INACTIVO',     'class' => 'label-default'],
    3 => ['texto' => 'BAJA',         'class' => 'label-danger'],
  ];

  // CONSULTA FINAL
  $all_users = find_view_user_admins_paginated($limite, $offset);  

  // Checkin What level user has permission to view this page
  page_require_level(1);
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
          <span>Administradores</span>
       </strong>
         <a href="add_user.php?tipo=admin" class="btn btn-roy pull-right">Agregar administrador</a>
      </div>
      <div class="panel-body">
        <div class="desktop-table">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">Nomina</th>
                <th class="text-center">Nombre </th>
                <th class="text-center">Usuario</th>
                <th class="text-center" style="width: 15%;">Rol de usuario</th>
                <th class="text-center" style="width: 10%;">Estado</th>
                <th style="width: 20%;">Último login</th>
                <th class="text-center" style="width: 100px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($all_users as $a_user): ?>
              <tr>
                <td class="text-center"><?php echo count_id();?></td>
                <td><?php echo remove_junk(ucwords($a_user['name']))?></td>
                <td class="text-center"><?php echo remove_junk(ucwords($a_user['username']))?></td>
                <td class="text-center"><?php echo remove_junk(ucwords($a_user['group_name']))?></td>
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
                <td><?php echo read_date($a_user['last_login'])?></td>
                <td class="text-center">
                  <div class="btn-group">
                      <a href="edit_user.php?id=<?= (int)$a_user['id']; ?>&return=adminUsers.php"
                        class="btn btn-xs btn-warning"
                        data-toggle="tooltip"
                        title="Editar">
                        <i class="glyphicon glyphicon-pencil"></i>
                      </a>
                      <a href="delete_user.php?id=<?php echo (int)$a_user['id'];?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Eliminar">
                        <i class="glyphicon glyphicon-remove"></i>
                      </a>
                      </div>
                </td>
              </tr>
              <?php endforeach;?>
            </tbody>
          </table>
        </div>
        <div class="mobile-cards">
          <?php foreach($all_users as $a_user): ?>
          <div class="mobile-card">
            <div class="mobile-card-header">
              <?php echo remove_junk(ucwords($a_user['name']))?>
            </div>
            <div class="mobile-card-body">
              <p>
                <strong>Usuario:</strong>
                <?php echo remove_junk(ucwords($a_user['username']))?>
              </p>
              <p>
                <strong>Rol:</strong>
                <?php echo remove_junk(ucwords($a_user['group_name']))?>
              </p>
              <p>
                <strong>Estado:</strong>
                <?php
                  $estatusId = (int)$a_user['statusLaboral_id'];
                  if(isset($estatusMap[$estatusId])){
                    $e = $estatusMap[$estatusId];
                    echo "<span class='label {$e['class']}'>{$e['texto']}</span>";
                  }else{
                    echo "<span class='label label-default'>DESCONOCIDO</span>";
                  }
                ?>
              </p>
              <p>
                <strong>Último login:</strong><br>
                <?php echo read_date($a_user['last_login'])?>
              </p>
            </div>
            <div class="mobile-card-actions">
              <a href="edit_user.php?id=<?= (int)$a_user['id']; ?>&return=adminUsers.php"
                class="btn btn-warning btn-sm">
                <i class="glyphicon glyphicon-pencil"></i>
                  Editar
              </a>
              <a href="delete_user.php?id=<?php echo (int)$a_user['id'];?>"
                class="btn btn-danger btn-sm">
                <i class="glyphicon glyphicon-remove"></i>
                  Eliminar
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="table-footer clearfix">
          <div class="pull-left text-muted" style="font-size:12px; line-height:30px;">
            Mostrando <?php echo count($all_users); ?> de <?php echo $total_registros; ?> registros
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
