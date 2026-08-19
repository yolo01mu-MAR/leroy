<?php
  $page_title = 'Registro Capacitacion';
  require_once __DIR__ . '/../../app/bootstrap.php';

  // Checkin What level user has permission to view this page
  page_require_level(5);
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
                    <span>Capacitación</span>
                </strong>
            </div>
            <div class="panel-body">
                <div class="text-center">
                    <h3 class="titulo-capacitacion">
                        <span class="glyphicon glyphicon-education"></span>
                        Nueva capacitación
                    </h3>
                    <form action="add_capacitacion.php" method="POST">
                        <div class="form-group">
                            <div class="form-group text-left">
                                <small>Nombre de la capacitación</small>
                                <input
                                    type="text"
                                    name="nombre"
                                    class="form-control"
                                    required>
                                <small>Nombre del instructor</small>
                                <input
                                    type="text"
                                    name="instructor"
                                    class="form-control"
                                    required>
                                <small>Lugar de la capacitación</small>
                                <input
                                    type="text"
                                    name="ubicacion"
                                    class="form-control">
                                <small>Fecha de capacitación</small>
                                <input
                                    type="date"
                                    id="fecha"
                                    name="fecha"
                                    class="form-control">
                            </div>
                        <button
                            type="submit"
                            class="btn btn-success btn-lg btn-block"
                            name="guardar">
                            <span class="glyphicon glyphicon-play"></span>
                            Iniciar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
