<?php
  $page_title = 'Historico de Capacitaciones';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $limite = 10;

  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $pagina = ($pagina < 1) ? 1 : $pagina;

  $total_registros = count_view_capacitaciones(); // COUNT(*)
  $total_paginas   = ceil($total_registros / $limite);

  if ($pagina > $total_paginas && $total_paginas > 0) {
    redirect('?page='.$total_paginas);
  }

  $offset = ($pagina - 1) * $limite;
  $capas_activas = find_capacitacion_activa();

  // CONSULTA FINAL
  $capacitaciones = find_view_capacitaciones_paginated($limite, $offset);

  // Checkin What level user has permission to view this page
  page_require_level(5);
?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
      <?php if(!empty($capas_activas)): ?>
        <div class="row">
          <div class="col-md-12">
            <?php foreach($capas_activas as $capa_activa): ?>
              <div class="capacitacion-activa">
                  <div class="capacitacion-header">
                      🟢 Capacitación en curso
                  </div>
                  <div class="capacitacion-body">
                      <div class="capacitacion-titulo">
                          <?php echo remove_junk($capa_activa['nombre']); ?>
                      </div>
                      <div class="capacitacion-info">
                          <strong>Instructor:</strong>
                          <?php echo remove_junk($capa_activa['instructor']); ?>
                      </div>
                      <div class="capacitacion-info">
                          <strong>Fecha:</strong>
                          <?php echo date('d/m/Y', strtotime($capa_activa['fecha'])); ?>
                      </div>
                      <div class="capacitacion-actions">
                          <a href="capa_registro_asistencia.php?id=<?php echo (int)$capa_activa['id']; ?>"
                            class="btn btn-success">
                              Registrar
                          </a>
                          <a href="finalizar_capacitacion.php?id=<?php echo (int)$capa_activa['id']; ?>"
                            class="btn btn-danger"
                            onclick="return confirm('¿Finalizar capacitación?');">
                              Finalizar
                          </a>
                      </div>
                  </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
  </div>
</div>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading clearfix">
          <!-- SOLO MOVIL -->
          <div class="text-center historial-mobile-btn">
            <button class="btn btn-primary btn-lg"
                    data-toggle="collapse"
                    data-target="#historialCapas">
              <span class="glyphicon glyphicon-book"></span>
              Ver historial
            </button>
          </div>
          <!-- SOLO PC -->
          <div class="pull-left historial-desktop-title" style="padding-top:6px;">
            <strong>
              <span class="glyphicon glyphicon-th"></span>
              <span>Historial de capacitaciones</span>
            </strong>
          </div>
        </div>
        <div id="historialCapas">
          <div class="panel-body historial-body">
            <div class="tabla-scroll">
              <table class="table table-bordered table-striped tabla-fija">
                <thead>
                  <tr>
                    <th class="text-center">#</th>
                    <th class="text-center" style="width: 20%;">Capacitacion</th>
                    <th class="text-center" style="width: 20%;">Instructor</th>
                    <th class="text-center" style="width: 15%;">Fecha</th>
                    <th class="text-center" style="width: 15%;">Asistencia</th>
                    <th class="text-center" style="width: 15%;">Estatus</th>
                    <th class="text-center" style="width: 100px;">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tabla-resultados">
                  <?php foreach ($capacitaciones as $capa): ?>
                    <tr>
                      <td class="text-center"><?php echo count_id();?></td>
                      <td class="text-center"><?php echo remove_junk(ucwords($capa['nombre']))?></td>
                      <td class="text-center"><?php echo remove_junk(ucwords($capa['instructor']))?></td>
                      <td class="text-center"><?php echo date('d/m/Y', strtotime($capa['fecha'])); ?></td>
                      <td class="text-center">
                        <a href="capa_lista_asistencia_pdf.php?id=<?php echo (int)$capa['id']; ?> " target="_blank"
                          class="btn btn-info btn-xs">
                          Ver Asistencia
                        </a>
                      </td>
                      <!-- ESTATUS -->
                      <td class="text-center">
                        <?php if($capa['estatus'] == 'ACTIVA'){ ?>
                          <span class="label label-success">ACTIVA</span>
                        <?php } else { ?>
                          <span class="label label-danger">FINALIZADA</span>
                        <?php } ?>
                      </td>
                      <td class="text-center">
                        <span class="text-muted">Finalizada</span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="table-footer clearfix">
              <div class="pull-left text-muted" style="font-size:12px; line-height:30px;">
                Mostrando <?php echo count($capacitaciones); ?> de <?php echo $total_registros; ?> registros
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
    </div>
  </div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
