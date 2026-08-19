<?php
ob_start();
require_once('includes/load.php');

$req_fields = array('username','password');
validate_fields($req_fields);

$username = remove_junk($_POST['username']);
$password = remove_junk($_POST['password']);

if(empty($errors)){

  $user = authenticate($username, $password);

  if($user){
  
    $session->login($user);
  
    $_SESSION['last_activity'] = time();
  
    updateLastLogIn($user['id']);
  
    switch((int)$user['group_level']){
  
      case 1:
        $mensaje = "Bienvenido Administrador.";
        break;
      
      case 2:
        $mensaje = "Bienvenido a Asistencia LE ROY.";
        echo "Bienvenido a Asistencia LE ROY.";
        break;

      case 4:
      case 5:
        $mensaje = "Bienvenido al Sistema de Producción.";
        break;
  
      default:
        $mensaje = "Bienvenido al sistema.";
        break;
    }
  
    $session->msg("s", $mensaje);
  
    redirect('home.php', false);
  } else {
    $session->msg("d", "Nombre de usuario y/o contraseña incorrecto.");
    redirect('login.php', false);
  }

} else {
  $session->msg("d", $errors);
  redirect('login.php', false);
}
