<?php

$user = current_user();

$nombreCompleto = remove_junk(ucfirst($user['name']));
$nombrePartes = explode(' ', $nombreCompleto);
$nombreCorto = $nombrePartes[0];

if(isset($nombrePartes[1])){
    $nombreCorto .= ' ' . strtoupper(substr($nombrePartes[1],0,1)) . '.';
}
?>
<!DOCTYPE html>
  <html lang="es">
    <head>
    <meta charset="UTF-8">
    <title><?php if (!empty($page_title))
           echo remove_junk($page_title);
            elseif(!empty($user))
           echo ucfirst($user['name']);
            else echo "Asistencia LE ROY";?>
    </title>
       
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	  <link rel="shortcut icon" href="<?= BASE_URL ?>/libs/images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/libs/images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/libs/css/main.css"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>/libs/css/detalleAsistencia.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/modules/notificaciones/assets/css/notificaciones.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  </head>
  <body>
  <?php  if ($session->isUserLoggedIn(true)): ?>
    <header id="header">  
      <div class="logo pull-left">
        <img src="<?= BASE_URL ?>/libs/images/logo.png" alt="LE ROY" style="max-height:40px;">
      </div>
      <button id="openSidebar" class="mobile-menu-btn">
          <i class="bi bi-list"></i>
      </button>
      <div class="header-content">
        <div class="header-date pull-left">
            <strong>
                <span id="reloj"></span>
            </strong>
        </div>
        <div class="pull-right clearfix">
          <ul class="info-menu list-inline list-unstyled">

            <!-- NOTIFICACIONES -->
            <li class="notificaciones-wrapper">
              <button
                  type="button"
                  id="notificacionesBoton"
                  class="notificaciones-boton"
                  aria-label="Notificaciones"
                  title="Notificaciones"
              >

                <i class="bi bi-bell"></i>
                <span
                    id="notificacionesContador"
                    class="notificaciones-contador"
                ></span>
              </button>
            </li>

            <!-- Perfil -->
            <li class="profile">
              <a href="#" data-toggle="dropdown" class="toggle" aria-expanded="false">
                <img
                    src="<?= BASE_URL ?>/uploads/users/<?php echo $user['image'];?>"
                    alt="user-image"
                    class="img-circle img-inline"
                >
                <span class="desktop-user">
                    <?php echo $nombreCompleto; ?>
                    <i class="caret"></i>
                </span>
                <span class="mobile-user"></span>
              </a>
              <ul class="dropdown-menu">
                <li>
                  <a href="profile.php?id=<?php echo (int)$user['id'];?>">
                    <i class="glyphicon glyphicon-user"></i>
                    Perfil
                  </a>
                </li>
                <li>
                  <a
                      href="edit_account.php"
                      title="edit account"
                  >
                    <i class="glyphicon glyphicon-cog"></i>
                    Configuración
                  </a>
                </li>
                <li class="last">
                  <a href="<?= BASE_URL ?>/logout.php">
                    <i class="glyphicon glyphicon-off"></i>
                    Salir
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
      <script>
        window.BASE_URL = <?= json_encode(BASE_URL) ?>;
      </script>
      <script src="<?= BASE_URL ?>/modules/notificaciones/assets/js/notificaciones.js"></script>
    </header>
    <!-- panel de notificaciones -->
    <div id="notificacionesOverlay" aria-hidden="true"></div>
    <div id="notificacionesPanel" class="notificaciones-panel" aria-hidden="true">
      <div class="notificaciones-panel-header">
        <h3 class="notificaciones-panel-titulo">
          Notificaciones
        </h3>
        <div class="notificaciones-panel-acciones">
          <button
              type="button"
              id="notificacionesCerrar"
              class="notificaciones-cerrar"
              aria-label="Cerrar notificaciones"
              title="Cerrar"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>
      <div id="notificacionesLista" class="notificaciones-lista"></div>
      <div class="notificaciones-panel-footer">
        <a href="#" class="notificaciones-ver-todas">
          Ver todas
        </a>
      </div>
    </div>
    <div class="sidebar">
        <div class="mobile-sidebar-header">
            <img src="<?= BASE_URL ?>/libs/images/logo.png" alt="Le Roy">
            <button id="closeSidebar">&times;</button>
        </div>
        <div class="mobile-user-box">
            <div class="mobile-date">
              <i class="bi bi-calendar3"></i>
              <?php echo date('d/m/Y'); ?>
            </div>
            <h4><?php echo $nombreCompleto; ?></h4>
        </div>
        <?php include_once('menu_dinamico.php'); ?> 
    </div>
<?php endif;?>
<div class="page">
  <div class="container-fluid">
