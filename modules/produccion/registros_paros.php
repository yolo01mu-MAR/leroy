<?php
  $page_title = 'Lista de empleados';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $limite = 10;

  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $pagina = ($pagina < 1) ? 1 : $pagina;

  $total_registros = count_view_registros_paros(); // COUNT(*)
  $total_paginas   = ceil($total_registros / $limite);

  if ($pagina > $total_paginas && $total_paginas > 0) {
    redirect('?page='.$total_paginas);
  }

  $offset = ($pagina - 1) * $limite;

  // CONSULTA FINAL
  $registros = find_view_registros_paros($limite, $offset);

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
            <span>Registros de produccion</span>
          </strong>
        </div>
      </div>
    <div class="panel-body">
    <div class="tabla-scroll">
      <table class="table table-bordered table-striped tabla-fija">
        <thead>
          <tr>
            <th class="text-center">Fecha</th>
            <th class="text-center">Maquina</th>
            <th class="text-center">Paro</th>
            <th class="text-center">Subparo</th>
            <th class="text-center">Minutos</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registros as $r): ?>
            <tr>
              <td class="text-center"><?php echo date('d/m/Y', strtotime($r['fecha']));?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($r['maquina']));?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($r['Paro']));?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($r['Subparo']));?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($r['minutos_totales'])); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="table-footer clearfix">
        <div class="pull-left text-muted" style="font-size:12px; line-height:30px;">
          Mostrando <?php echo count($registros); ?> de <?php echo $total_registros; ?> registros
        </div>
        <div class="pull-right">
          <div id="paginacion" class="pull-right">
            <ul class="pagination pagination-sm no-margin">
              <li class="<?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                <a href="?page=<?php echo $pagina - 1; ?>">&laquo;</a>
              </li>
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="<?php echo ($i == $pagina) ? 'active' : ''; ?>">
                  <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="<?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                  <a href="?page=<?php echo $pagina + 1; ?>">&raquo;</a>
                </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
