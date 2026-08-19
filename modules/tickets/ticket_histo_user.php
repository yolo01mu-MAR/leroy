<?php
  $page_title = 'Mis Tickets';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $user = current_user();
  $user_id = $user['id'];

  $resultados = get_historial_user($user_id)

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
      <div class="panel-heading clearfix">

          <div class="pull-left" style="padding-top:6px;">
              <strong>
                  <span class="glyphicon glyphicon-list-alt"></span>
                  <span>Mis Tickets</span>
              </strong>
              <br>
              <small class="text-muted">
                  Consulta el estado de tus solicitudes de soporte.
              </small>
          </div>

          <div class="pull-right">
              <a href="ticket_nuevo.php" class="btn btn-success">
                  <span class="glyphicon glyphicon-plus"></span>
                  Nuevo Ticket
              </a>
          </div>

      </div>
      <div class="panel-body">
        <div class="tabla-scroll">
          <table class="table table-bordered table-striped tabla-fija">
            <thead>
                <tr>
                    <th width="20%" class="text-center">Ticket</th>
                    <th width="15%" class="text-center">Categoría</th>
                    <th width="15%" class="text-center">Estado</th>
                    <th width="15%" class="text-center">Fecha Alta</th>
                    <th width="20%" class="text-center">Responsable</th>
                    <th width="10%" class="text-center">Ver</th>
                </tr>
            </thead>
            <tbody id="tabla-resultados">
              <?php if (!empty($resultados)): ?>
              <?php foreach ($resultados as $ticket): ?>
                <tr>
                  <td>
                    <strong style="font-size:15px;">
                      <?php echo remove_junk($ticket['folio']); ?>
                    </strong>
                    <br>
                    <small class="text-muted">
                      <?php echo remove_junk($ticket['asunto']); ?>
                    </small>
                  </td>
                  <td class="text-center">
                    <?php echo remove_junk($ticket['categoria']); ?>
                  </td>
                  <td class="text-center">
                    <?php
                      switch ($ticket['estatus']) {
                        case 'ABIERTO':
                          echo '<span class="label label-primary">ABIERTO</span>';
                          break;
                        case 'EN_PROCESO':
                          echo '<span class="label label-warning">EN PROCESO</span>';
                          break;
                        case 'ESPERA_USUARIO':
                          echo '<span class="label label-info">ESPERA USUARIO</span>';
                          break;
                        case 'RESUELTO':
                          echo '<span class="label label-success">RESUELTO</span>';
                          break;
                        case 'CERRADO':
                          echo '<span class="label label-default">CERRADO</span>';
                          break;
                        case 'CANCELADO':
                          echo '<span class="label label-danger">CANCELADO</span>';
                          break;
                        default:
                          echo '<span class="label label-default">'.$ticket['estatus'].'</span>';
                          break;
                      }
                    ?>
                  </td>
                  <td class="text-center">
                    <?php echo date('d/m/Y',strtotime($ticket['fecha_creacion'])); ?>
                    <br>
                    <small class="text-muted">
                      <?php echo date('H:i',strtotime($ticket['fecha_creacion'])); ?>
                    </small>
                  </td>
                  <td class="text-center"><?php echo remove_junk($ticket['asignado']);?></td>
                  <td class="text-center">
                    <a href="ticket_detalle_user.php?id=<?php echo (int)$ticket['id']; ?>"
                      title="Ver Ticket">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center">
                    No tienes tickets registrados.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>
