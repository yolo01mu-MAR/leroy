<?php
  require_once __DIR__ . '/../../app/bootstrap.php';
  // Checkin What level user has permission to view this page
   page_require_level(5);

     $return = $_GET['return'] ?? 'allUsers.php';

?>
<?php
  $delete_id = baja_by_id('users',(int)$_GET['id']);
  if($delete_id){
      $session->msg("s","Usuario eliminado");
      redirect($return);

  } else {
      $session->msg("d","Se ha producido un error en la eliminación del usuario");
      redirect($return);

  }
?>
