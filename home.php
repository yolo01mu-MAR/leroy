<?php
  $page_title = 'Home';
  require_once __DIR__ . '/app/bootstrap.php';
  if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
    exit;
  }
?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<!-- MENSAJES -->
<div class="row home-message-wrapper">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<?php if(!empty($rotacionHoy)) : ?>
  <div class="alert alert-warning text-center">
    Hoy se realizó una rotación de turno automáticamente.
  </div>
<?php endif; ?>

<!-- CONTENIDO -->
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="jumbotron text-center" style="margin-bottom:0;">
        <img src="libs/images/logo.png" alt="Logo LE ROY" style="max-height:120px;">
        <!-- <h2>Registro de asistencia</h2> -->
        <h2>Sistema Integral Le Roy</h2>
      </div>
    </div>
  </div>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
