<?php
  $page_title = 'Lista de grupos';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $limite = 30;

  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $pagina = ($pagina < 1) ? 1 : $pagina;

  $total_registros = count_view_departamento(); // COUNT(*)
  $total_paginas   = ceil($total_registros / $limite);

  if ($pagina > $total_paginas && $total_paginas > 0) {
    redirect('?page='.$total_paginas);
  }

  $offset = ($pagina - 1) * $limite;

  // CONSULTA FINAL
  $all_zonas = find_view_zona_paginated($limite, $offset);

  // Checkin What level user has permission to view this page
   page_require_level(1);

?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<div class="row">
   <div class="col-md-12">
     <?php echo display_msg($msg); ?>
   </div>
</div>
<div class="row" style="margin-top:-20px;">
  <div class="col-md-12">
    <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <strong>
        <span class="glyphicon glyphicon-th"></span>
        <span>Zonas de trabajo</span>
     </strong>
       <a href="add_group.php" class="btn btn-roy pull-right btn-sm">Agregar Zona de Trabajo</a>
    </div>
     <div class="panel-body">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center" style="width: 10%;">#</th>
            <th class="text-center" style="width: 10%;">NAVE</th>
            <th class="text-center">NOMBRE DEL AREA</th>
            <th class="text-center">ZONA DE TRABAJO</th>
            <th class="text-center" style="width: 20%;">ACCIONES</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($all_zonas as $a_zonas): ?>
          <tr>
           <td class="text-center"><?php echo count_id();?></td>
           <td class="text-center"><?php echo remove_junk(ucwords($a_zonas['nave']))?></td>
           <td class="text-center"><?php echo remove_junk(ucwords($a_zonas['zona']))?></td>
           <td class="text-center"><?php echo remove_junk(ucwords($a_zonas['nombre']))?></td>
           <td class="text-center">
             <div class="btn-group">
                <a href="edit_area.php?id=<?php echo (int)$a_zonas['ID'];?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Editar">
                  <i class="glyphicon glyphicon-pencil"></i>
               </a>
                <a href="delete_area.php?id=<?php echo (int)$a_zonas['ID'];?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Eliminar">
                  <i class="glyphicon glyphicon-remove"></i>
                </a>
                </div>
           </td>
          </tr>
        <?php endforeach;?>
       </tbody>
     </table>
      <div class="table-footer clearfix">
        <div class="pull-left text-muted" style="font-size:12px; line-height:30px;">
          Mostrando <?php echo count($all_zonas); ?> de <?php echo $total_registros; ?> registros
        </div>
        <div class="pull-right">
          <div class="pull-right">
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
</div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
