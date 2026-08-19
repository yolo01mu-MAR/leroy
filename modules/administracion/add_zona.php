<?php
  $page_title = 'Agregar grupo';
  require_once __DIR__ . '/../../app/bootstrap.php';
  // Checkin What level user has permission to view this page
   page_require_level(1);

   $naves = find_all('naves');
?>
<?php
  if(isset($_POST['add'])){

   $req_fields = array('zona-name','zona-nave');
   validate_fields($req_fields);

    if(find_by_nombreZonaTrabajo($_POST['zona-name']) == false ){
      $session->msg('d','El nombre de la zona ya existe en la Base de datos');
      redirect('add_zona.php', false);
    }
   if(empty($errors)){
      $nameZona = mb_strtoupper(remove_junk($db->escape($_POST['zona-name'])), 'UTF-8');
      $naveZona = remove_junk($db->escape($_POST['zona-nave']));

      $query  = "INSERT INTO zona_trabajo (";
      $query .="nave_id,zona";
      $query .=") VALUES (";
      $query .=" '{$naveZona}', '{$nameZona}'";
      $query .=")";
      if($db->query($query)){
        //sucess
        $session->msg('s',"Zona de trabajo ha sido creada! ");
        redirect('areas.php', false);
      } else {
        //failed
        $session->msg('d','Lamentablemente no se pudo crear la Zona de trabajo!');
        redirect('add_zona.php', false);
      }
   } else {
     $session->msg("d", $errors);
      redirect('add_zona.php',false);
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
          Agregar Zona de Trabajo
        </h4>
        <a href="areas.php" class="btn btn-default btn-sm pull-right">
          <i class="glyphicon glyphicon-arrow-left"></i> Volver
        </a>
      </div>
      <div class="panel-body">
        <?php echo display_msg($msg); ?>
        <form method="post" action="add_zona.php" class="clearfix">
          <!--   Nombre -->
          <div class="form-group">
            <label>Nombre de la zona</label>
            <input type="text" class="form-control" name="zona-name" placeholder="Escriba nombre del area" required>
          </div>
          <!-- Nave -->
          <div class="form-group">
            <label>Nave</label>
            <select class="form-control" name="zona-nave" required>
              <option value="">-- Seleccione una nave --</option>

              <?php foreach($naves as $nave): ?>
                <option value="<?php echo (int)$nave['ID']; ?>">
                  <?php echo remove_junk(ucwords($nave['nombre'])); ?>
                </option>
              <?php endforeach; ?>

            </select>
          </div>
          <hr>
          <!-- Botón -->
          <div class="text-right">
            <button type="submit" name="add" class="btn btn-roy">
              <i class="glyphicon glyphicon-ok"></i>Agregar Zona
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>