<?php
  $page_title = 'Reportes de produccion';
  require_once __DIR__ . '/../../app/bootstrap.php';

  // CONSULTA FINAL
  $reportes = obtener_reportes();

  // Checkin What level user has permission to view this page
  page_require_level(4);
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
      <div class="panel-heading">
        <!-- FILA 1: Título + botón -->
        <div class="clearfix">
          <strong class="pull-left">
            <span class="glyphicon glyphicon-th"></span>
            <span>Reportes de producción</span>
          </strong>
        </div>
      </div>
    <div class="panel-body">
    <div class="tabla-scroll">
      <table class="table table-bordered table-striped tabla-fija">
        <thead>
          <tr>
            <th class="text-center">Fecha</th>
            <th class="text-center">Turno</th>
            <th class="text-center">Maquina</th>
            <th class="text-center">Supervisor</th>
            <th class="text-center">Operador</th>
            <th class="text-center">Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reportes as $r): ?>
            <tr>
          <td class="text-center"><?php echo remove_junk(ucwords($r['fecha']));?></td>
          <td class="text-center"><?php echo remove_junk(ucwords($r['grupo']));?></td>
          <td class="text-center"><?php echo remove_junk(ucwords($r['maquina']));?></td>
          <td class="text-center"><?php echo remove_junk(ucwords($r['supervisor']));?></td>
          <!-- <td class="text-center"><?php echo remove_junk(ucwords($r['operador']));?></td> -->
           <td class="text-center"></td>
          <td class="text-center"><?php echo remove_junk(ucwords($r['estado']));?></td>
          <?php $activo = ($r['estado'] == 'ACTIVO'); ?>
          <td class="text-center">
            <!-- VER -->
            <?php if($activo): ?>
              <button class="btn btn-info btn-sm btn-accion" disabled>
                <i class="glyphicon glyphicon-eye-open"></i> Ver
              </button>
            <?php else: ?>
              <a href="view_reporte_produccion.php?id=<?= (int)$r['id']; ?>"
                class="btn btn-info btn-sm btn-accion">
                <i class="glyphicon glyphicon-eye-open"></i> Ver
              </a>
            <?php endif; ?>
            <!-- EDITAR (siempre activo) -->
            <a href="edit_reporte_produccion.php?id=<?= (int)$r['id']; ?>"
              class="btn btn-warning btn-sm btn-accion">
              <i class="glyphicon glyphicon-pencil"></i> Editar
            </a>
            <!-- PDF -->
            <?php if($activo): ?>
              <button class="btn btn-danger btn-sm btn-accion" disabled>
                <i class="glyphicon glyphicon-file"></i> PDF
              </button>
            <?php else: ?>
              <a href="imprimir_reporte_produccion.php?id=<?= (int)$r['id']; ?>"
                class="btn btn-danger btn-sm btn-accion"
                target="_blank">
                <i class="glyphicon glyphicon-file"></i> PDF
              </a>
            <?php endif; ?>
          </td>
          </td>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
