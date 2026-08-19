<?php
  $page_title = 'Lista de grupos';
  require_once __DIR__ . '/../../app/bootstrap.php';
  // Checkin What level user has permission to view this page
   page_require_level(1);
  $all_groups = find_all('grupos');
  $all_naves  = find_all('naves');

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
        <span>grupos y Naves</span>
     </strong>
    </div>
<div class="panel-body">
  <div class="row">
    <div class="col-md-6">
      <h4 class="text-center">Grupos</h4>
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th class="text-center" style="width: 60px;">ID</th>
            <th>Nombre del Grupo</th>
            <th>Tipo de Semana</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($all_groups as $group): ?>
          <tr>
            <td class="text-center">
              <?php echo (int)$group['id']; ?>
            </td>
            <td>
              <?php echo remove_junk(ucwords($group['nombre'])); ?>
            </td>
            <td>
              <?php echo remove_junk(ucwords($group['tipo_semana'])); ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="col-md-6">
      <h4 class="text-center">Naves</h4>
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th class="text-center" style="width: 60px;">ID</th>
            <th>Nombre de la Nave</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($all_naves as $nave): ?>
          <tr>
            <td class="text-center">
              <?php echo (int)$nave['ID']; ?>
            </td>
            <td>
              <?php echo remove_junk(ucwords($nave['nombre'])); ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

    </div>
  </div>
</div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
