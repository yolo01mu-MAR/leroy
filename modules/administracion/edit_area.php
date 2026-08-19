<?php
  $page_title = 'Editar Zona de Trabajo';
  require_once __DIR__ . '/../../app/bootstrap.php';
  // Checkin What level user has permission to view this page
   page_require_level(1);
?>
<?php
  $e_zonaTrabajo = find_by_id('zona_trabajo',(int)$_GET['id']);
  if(!$e_zonaTrabajo){
    $session->msg("d","Missing Group id.");
    redirect('areas.php');
  }
  $naves = find_all('naves');
?>
<?php
  if(isset($_POST['update'])){
    $req_fields = array('zona-name','zona-nave');
    validate_fields($req_fields);
    if(empty($errors)){
      $zonaName = remove_junk($db->escape($_POST['zona-name']));
      $naveId = remove_junk($db->escape($_POST['zona-nave']));

      $query  = "UPDATE zona_trabajo SET ";
      $query .= "nave_id='{$naveId}',zona='{$zonaName}'";
      $query .= "WHERE ID='{$db->escape($e_zonaTrabajo['ID'])}'";
      $result = $db->query($query);
    
      if($result && $db->affected_rows() === 1){
        //sucess
        $session->msg('s',"La zona de trabajo se ha actualizado! ");
        redirect('areas.php');
      } else {
        //failed
        $session->msg('d','Lamentablemente no se ha actualizado La zona de trabajo!');
        redirect('edit_area.php?id='.(int)$e_zonaTrabajo['ID'], false);
      }
    }else {
      $session->msg("d", $errors);
      redirect('edit_area.php?id='.(int)$e_zonaTrabajo ['ID'], false);
    }
  }
?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<div class="row">
  <div class="col-md-6 col-md-offset-3">
    <div class="panel panel-default shadow-panel">
      
      <!-- HEADER -->
      <div class="panel-heading clearfix">
        <h4 class="panel-title pull-left" style="margin-top:8px;">
          <span class="glyphicon glyphicon-edit"></span>
          Editar Zona de Trabajo
        </h4>
        <a href="areas.php" class="btn btn-default btn-sm pull-right">
          <i class="glyphicon glyphicon-arrow-left"></i> Volver
        </a>
      </div>
      <div class="panel-body">
        <?php echo display_msg($msg); ?>
        <form method="post" action="edit_area.php?id=<?php echo (int)$e_zonaTrabajo['ID'];?>">
          <!--   Nombre -->
          <div class="form-group">
            <label>Nombre de la zona</label>
            <input type="text" class="form-control" name="zona-name" 
                    value="<?php echo remove_junk(ucwords($e_zonaTrabajo['zona'])); ?>" required>
          </div>
          <!-- Nave -->
          <div class="form-group">
            <label>Nave</label>
            <select class="form-control" name="zona-nave" required>
              <option value="">-- Seleccione una nave --</option>
              <?php foreach($naves as $nave): ?>
                <option value="<?php echo (int)$nave['ID']; ?>"
                  <?php if($nave['ID'] == $e_zonaTrabajo['nave_id']) echo 'selected'; ?>>
                  <?php echo remove_junk(ucwords($nave['nombre'])); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <hr>
          <!-- Botón -->
          <div class="text-right">
            <button type="submit" name="update" class="btn btn-roy">
              <i class="glyphicon glyphicon-ok"></i> Actualizar Zona
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
