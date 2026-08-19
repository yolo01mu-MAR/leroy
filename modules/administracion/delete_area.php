<?php
  require_once __DIR__ . '/../../app/bootstrap.php';
  // Checkin What level user has permission to view this page
   page_require_level(1);
?>
<?php
  $delete_id = delete_by_id('zona_trabajo',(int)$_GET['id']);
  if($delete_id){
      $session->msg("s","Zona de Trabajo eliminada");
      redirect('areas.php');
  } else {
      $session->msg("d","La Eliminación falló");
      redirect('areas.php');
  }
?>
