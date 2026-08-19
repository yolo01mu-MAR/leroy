<?php
$page_title = 'Historial de Equipo';
require_once __DIR__ . '/../../app/bootstrap.php';
page_require_level(5);

// $equipo_id = (int)$_GET['equipo_id'];

// $sqlEquipo = "SELECT
//                     e.id, e.codigo_equipo, e.marca, e.modelo,
//                     et.nombre AS tipo
//                 FROM equipo e
//                     LEFT JOIN equipo_tipo et ON et.id = e.tipo_equipo
//                 WHERE e.id = {$equipo_id}
//                 LIMIT 1";

// $equipo = find_by_sql($sqlEquipo);

// if (empty($equipo)) {
//     redirect('ticket_reportes.php');
// }

// $equipo = $equipo[0];

// $sqlTickets = "SELECT
//                     t.id, t.folio, t.asunto, t.descripcion,
//                     t.fecha_creacion, t.fecha_actualizacion,
//                     te.nombre_estatus, tp.nombre_prioridad,
//                     u.name AS empleado,
//                     asig.name AS responsable
//                 FROM ticket t
//                     INNER JOIN ticket_estatus te ON te.id = t.estatus_id
//                     LEFT JOIN ticket_prioridad tp ON tp.id = t.prioridad_id
//                     INNER JOIN users u ON u.id = t.usuario_id
//                     LEFT JOIN users asig ON asig.id = t.asignado_a
//                 WHERE t.equipo_id = {$equipo_id}
//                 ORDER BY t.fecha_creacion DESC";

// $tickets = find_by_sql($sqlTickets);

// $totalTickets = count($tickets);
// $resueltos = 0;
// $horas = [];

// foreach ($tickets as $tk) {
//     if (!empty($tk['fecha_actualizacion']) && in_array(strtoupper($tk['nombre_estatus']), ['RESUELTO', 'CERRADO'])) {
//         $resueltos++;
//         $horas[] = (strtotime($tk['fecha_actualizacion']) - strtotime($tk['fecha_creacion'])) / 3600;
//     }
// }

$promedioHoras = !empty($horas) ? round(array_sum($horas) / count($horas), 1) : null;

?>

<?php
  $page_title = 'Inventario equipos';

  require_once __DIR__ . '/../../app/bootstrap.php';

  page_require_level(1);

  $limite = 10;

  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $pagina = ($pagina < 1) ? 1 : $pagina;

  $total_registros = count_view_equipos();
  $total_paginas   = ceil($total_registros / $limite);

  if ($pagina > $total_paginas && $total_paginas > 0) {
    redirect('?page='.$total_paginas);
  }

  $offset = ($pagina - 1) * $limite;

  $equipos = find_view_equipos($limite, $offset);

  // AGRUPAR POR TIPO
  $estructura = [];

  foreach($equipos as $equipo){

    $tipo = $equipo['tipo'];

    $estructura[$tipo][] = $equipo;
  }
?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/ticket.css"/>
<div class="row">

  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>

</div>

<div class="row">

  <div class="col-md-12">

    <div class="panel panel-default">

      <!-- HEADER -->
      <div class="panel-heading clearfix">

        <!-- IZQUIERDA -->
        <div class="pull-left">

          <div style="
            font-size:18px;
            font-weight:600;
            color:#333;
            margin-bottom:6px;
          ">

            <span class="glyphicon glyphicon-hdd"></span>

            Historial de fallas por equipo

          </div>

          <div>

            <span class="label label-primary">
              <?php echo $total_registros; ?> equipos
            </span>

            <?php foreach($estructura as $tipo => $lista): ?>

              <span class="label label-default">

                <?php echo remove_junk($tipo); ?>

                <span class="badge">
                  <?php echo count($lista); ?>
                </span>

              </span>

            <?php endforeach; ?>

          </div>

        </div>

        <!-- DERECHA -->
        <div class="pull-right" style="width:320px; margin-top:10px;">

          <div class="input-group input-group-sm">

            <span class="input-group-addon">
              <span class="glyphicon glyphicon-search"></span>
            </span>

            <input type="text"
                  id="buscador"
                  class="form-control"
                  placeholder="Buscar código, marca o modelo">

          </div>

        </div>

      </div>

      <!-- BODY -->
      <div class="panel-body">

        <!-- TABS -->
        <ul class="nav nav-tabs" role="tablist">

          <?php
            $i = 0;
            foreach($estructura as $tipo => $lista):
          ?>

            <li role="presentation"
                class="<?php echo ($i == 0) ? 'active' : ''; ?>">

              <a href="#tab_<?php echo $i; ?>"
                aria-controls="tab_<?php echo $i; ?>"
                role="tab"
                data-toggle="tab">

                <?php echo remove_junk($tipo); ?>

                <span class="badge">
                  <?php echo count($lista); ?>
                </span>

              </a>

            </li>

          <?php
            $i++;
            endforeach;
          ?>

        </ul>

        <!-- CONTENIDO -->
        <div class="tab-content" style="margin-top:20px;">

          <?php
            $i = 0;
            foreach($estructura as $tipo => $lista):
          ?>

            <div role="tabpanel"
                class="tab-pane fade <?php echo ($i == 0) ? 'in active' : ''; ?>"
                id="tab_<?php echo $i; ?>">

              <div class="table-responsive">

                <table class="table table-bordered table-striped table-hover">

                  <thead>

                    <tr>
                      <th class="text-center">Equipo</th>
                      <th class="text-center">Tipo</th>
                      <th class="text-center">Ticket Totales</th>
                      <th class="text-center">Abiertos</th>
                      <th class="text-center">Ultimo reporte</th>
                      <th class="text-center">Detalles</th>
                      <th class="text-center">Historial</th>
                    </tr>

                  </thead>

                  <tbody>

                    <?php foreach($lista as $e): ?>

                        <tr>
                            <td>
                                <strong><?php echo remove_junk($e['marca'] . ' ' . $e['modelo']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo remove_junk($e['codigo_equipo']); ?></small>
                            </td>
                            <td class="text-center">
                                <?php echo remove_junk($e['tipo']); ?>
                            </td>

                        <td class="text-center">

                          <a href="#"
                            class="ver-detalles"
                            data-id="<?php echo (int)$e['id']; ?>">

                            Ver

                          </a>

                        </td>

                        <td class="text-center">

                          <div class="btn-group">

                            <a href="edit_user.php?id=<?= (int)$e['id']; ?>&return=adminUsers.php"
                              class="btn btn-xs btn-warning"
                              data-toggle="tooltip"
                              title="Editar">

                              <i class="glyphicon glyphicon-pencil"></i>

                            </a>

                            <a href="delete_user.php?id=<?php echo (int)$e['id'];?>"
                              class="btn btn-xs btn-danger"
                              data-toggle="tooltip"
                              title="Eliminar">

                              <i class="glyphicon glyphicon-remove"></i>

                            </a>

                          </div>

                        </td>

                      </tr>

                    <?php endforeach; ?>

                  </tbody>

                </table>

              </div>

            </div>

          <?php
            $i++;
            endforeach;
          ?>

        </div>

        <!-- FOOTER -->
        <div class="table-footer clearfix">

          <div class="pull-left text-muted"
              style="font-size:12px; line-height:30px;">

            Mostrando <?php echo count($equipos); ?>
            de <?php echo $total_registros; ?> registros

          </div>

          <div class="pull-right">

            <ul class="pagination pagination-sm no-margin">

              <li class="<?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">

                <a href="?page=<?php echo $pagina - 1; ?>">
                  &laquo;
                </a>

              </li>

              <?php for ($i = 1; $i <= $total_paginas; $i++): ?>

                <li class="<?php echo ($i == $pagina) ? 'active' : ''; ?>">

                  <a href="?page=<?php echo $i; ?>">
                    <?php echo $i; ?>
                  </a>

                </li>

              <?php endfor; ?>

              <li class="<?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">

                <a href="?page=<?php echo $pagina + 1; ?>">
                  &raquo;
                </a>

              </li>

            </ul>

          </div>

        </div>

      </div>

    </div>

  </div>

</div>

<!-- MODAL -->
<div id="modalDetalles" class="modal fade">

  <div class="modal-dialog modal-xl" style="width:95%; max-width:1400px;">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button"
                class="close"
                data-dismiss="modal">

          &times;

        </button>

        <h4 class="modal-title">
          Detalles del equipo
        </h4>

      </div>

      <div class="modal-body"
          style="max-height:80vh; overflow-y:auto;">

        <div id="contenido-detalles"></div>

      </div>

    </div>

  </div>

</div>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
