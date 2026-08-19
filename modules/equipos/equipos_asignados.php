<?php
  $page_title = 'Equipos asignados';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $limite = 10;

  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $pagina = ($pagina < 1) ? 1 : $pagina;

  $total_registros = count_view_equipos_asignados(); // COUNT(*)
  $total_paginas   = ceil($total_registros / $limite);

  if ($pagina > $total_paginas && $total_paginas > 0) {
    redirect('?page='.$total_paginas);
  }

  $offset = ($pagina - 1) * $limite;

  $estatusMap = [
    1 => ['texto' => 'ACTIVO',       'class' => 'label-success'],
    2 => ['texto' => 'FINALIZADO',   'class' => 'label-danger'],
  ];

  // // CONSULTA FINAL
  $equipos_asignados = find_view_equipos_asignados($limite, $offset);  

  // Checkin What level user has permission to view this page
  page_require_level(1);
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
        <div class="row">
          <!-- LADO IZQUIERDO -->
          <div class="col-md-6">
            <h4 style="margin-top: 5px;">
              <span class="glyphicon glyphicon-th"></span>
              Equipos Asignados
            </h4>
          </div>
          <!-- LADO DERECHO -->
          <div class="col-md-6 text-right">
            <form style="display:inline-block; margin-right:10px;">
              <input type="text" class="form-control" placeholder="Buscar equipo por nomina..." style="width:200px; display:inline-block;">
            </form>
            <a href="add_formulario_equipo.php" class="btn btn-roy">
              + Asignar equipo
            </a>
          </div>
        </div>
      </div>
     <div class="panel-body">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th class="text-center" style="width: 50px;">ID</th>
            <th class="text-center">Equipo</th>
            <th class="text-center">Tipo</th>
            <th class="text-center">Modelo</th>
            <th class="text-center">Nomina</th>
            <th class="text-center">Nombre</th>
            <th class="text-center">Estado</th>
            <th class="text-center" style="width: 300px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($equipos_asignados as $equipos): ?>
            <tr>
              <td class="text-center"><?php echo count_id();?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($equipos['codigo_equipo']))?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($equipos['tipo']))?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($equipos['modelo']))?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($equipos['nomina']))?></td>
              <td class="text-center"><?php echo remove_junk(ucwords($equipos['usuario']))?></td>
              <?php
                if ($equipos['estado'] == "ACTIVO") {
                  $estatus = 1;
                } else {
                  $estatus = 2;
                }
                if (isset($estatusMap[$estatus])) {
                  $e = $estatusMap[$estatus];
                  echo "<td class='text-center'>
                          <span class='label {$e['class']}'>{$e['texto']}</span>
                        </td>";
                } else {
                  echo "<td class='text-center'>
                          <span class='label label-default'>DESCONOCIDO</span>
                        </td>";
                }
              ?>
              <td class="text-center">
                <div class="btn-group">
                  <!-- PDF -->
                  <?php
                    $tipo = strtolower($equipos['tipo'] ?? '');
                    $estado = strtoupper($equipos['estado'] ?? '');

                    $pdf = '';
                    $texto = 'RESPONSIVA';
                    $clase = 'btn-danger';
                    $icono = 'glyphicon-file';

                    if($estado == 'ACTIVO'){
                      if($tipo == 'laptop'){
                        $pdf = 'pdf_responsiva_laptop_entrega.php';
                      }
                      elseif($tipo == 'celular'){
                        $pdf = 'pdf_responsiva_celular_entrega.php';
                      }
                    }elseif($estado == 'FINALIZADO'){

                      if($tipo == 'laptop'){
                        $pdf = 'pdf_responsiva_laptop_entrega.php';
                      }
                      elseif($tipo == 'celular'){
                        $pdf = 'pdf_responsiva_celular_recepcion.php';
                      }
                    }

                    if($pdf != ''){
                    ?>
                      <a href="<?= $pdf ?>?id=<?= (int)$equipos['nomina'] ?>"
                        class="btn <?= $clase ?> btn-sm btn-accion"
                        target="_blank"
                        title="<?= $texto ?>">
                        <i class="glyphicon <?= $icono ?>"></i> <?= $texto ?>
                      </a>
                    <?php } 
                  ?>
                  <!-- ENTREGAR (solo si está ACTIVO) -->
                  <?php if($equipos['estado'] == "ACTIVO"): ?>
                    <button class="btn btn-success btn-sm btn-accion abrir-modal-entrega"
                            data-id="<?= (int)$equipos['idEntrega'] ?>"
                            data-equipo="<?= (int)$equipos['equipo_id']; ?>">
                        <i class="glyphicon glyphicon-ok"></i> ENTREGAR
                    </button>
                  <?php else: ?>
                    <button class="btn btn-default btn-sm btn-accion" disabled>
                      <i class="glyphicon"></i> ENTREGADO
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach;?>
        </tbody>
      </table>
      <div class="table-footer clearfix">
        <div class="pull-left text-muted" style="font-size:12px; line-height:30px;">
          Mostrando <?php echo count($equipos_asignados); ?> de <?php echo $total_registros; ?> registros
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
     <div id="modalEntrega" class="modal fade">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="entregar_equipo.php">

              <div class="modal-header">
                <h4 class="modal-title">Entrega de equipo</h4>
              </div>

              <div class="modal-body">

                <input type="hidden" name="id" id="entrega_id">
                <input type="hidden" name="id_equipo" id="equipo_id">

                <div class="form-group">
                  <label>Condiciones de entrega</label>
                  <textarea name="condiciones" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                  <label>Observaciones</label>
                  <textarea name="observaciones" class="form-control"></textarea>
                </div>

              </div>

              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Confirmar entrega</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
