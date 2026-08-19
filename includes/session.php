<?php

  ini_set('session.cookie_lifetime', 0); // cookie muere al cerrar navegador
  ini_set('session.gc_maxlifetime', 3600); // 1 hora en el servidor (opcional)

  session_start();

  class Session {

  private $timeout = 3600; // 1 hora (en segundos)

  public $msg;
  private $user_is_logged_in = false;

  function __construct(){
    $this->flash_msg();
    $this->userLoginSetup();
  }

  public function isUserLoggedIn(){
    return $this->user_is_logged_in;
  }
  public function login($user){

    $_SESSION['user_id'] = $user['id'];
  
    // ID REAL DEL GRUPO
    $_SESSION['group_id'] = $user['user_level'];
  
    // NIVEL JERÁRQUICO
    $_SESSION['group_level'] = $user['group_level'];
  
    // NOMBRE DEL GRUPO
    $_SESSION['group_name'] = $user['group_name'];
  
    $_SESSION['last_activity'] = time();
  }
  private function userLoginSetup(){
    if (isset($_SESSION['user_id'])) {

      if (isset($_SESSION['last_activity'])) {
        if ((time() - $_SESSION['last_activity']) > $this->timeout) {
          $this->logout();
          $this->user_is_logged_in = false;
          return;
        }
      }

      // actualizar actividad
      $_SESSION['last_activity'] = time();
      $this->user_is_logged_in = true;

    } else {
      $this->user_is_logged_in = false;
    }
  }
public function logout(){
  unset($_SESSION['user_id']);
  unset($_SESSION['last_activity']);
  session_destroy();
}
  public function msg($type ='', $msg =''){
    if(!empty($msg)){
       if(strlen(trim($type)) == 1){
         $type = str_replace( array('d', 'i', 'w','s'), array('danger', 'info', 'warning','success'), $type );
       }
       $_SESSION['msg'][$type] = $msg;
    } else {
      return $this->msg;
    }
  }
  private function flash_msg(){

    if(isset($_SESSION['msg'])) {
      $this->msg = $_SESSION['msg'];
      unset($_SESSION['msg']);
    } else {
      $this->msg;
    }
  }
}

$session = new Session();
$msg = $session->msg();

?>
