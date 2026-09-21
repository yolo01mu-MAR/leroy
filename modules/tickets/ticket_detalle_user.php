<?php
$page_title = 'Detalle Ticket';

require_once __DIR__ . '/../../app/bootstrap.php';
require_once('includes/ticket_helpers.php');

$scripts = [
    'ticket_detalle_user'
];
// page_require_level(5);

$id = (int)$_GET['id'];
$usuario = current_user();

if(isset($_POST['btnCerrarTicket'])){

    $usuario = current_user();

    $resultado = cerrar_ticket(
        $id,
        $usuario['id']
    );

    $session->msg(
        $resultado['ok'] ? 's' : 'd',
        $resultado['mensaje']
    );

    redirect("ticket_detalle_user.php?id=".$id,false);
}
if(isset($_POST['btnReabrir'])){

    $usuario = current_user();

    $resultado = reabrir_ticket(
        $id,
        $usuario['id'],
        $_POST['comentario']
    );

    $session->msg(
        $resultado['ok'] ? 's' : 'd',
        $resultado['mensaje']
    );

    redirect("ticket_detalle_user.php?id=".$id,false);
}

$ticket = get_detalle_ticket($id);

if (empty($ticket)) {
    redirect('ticket_detalle_user.php');
}

$ticket = $ticket[0];

$agentes = get_ticket_agentes();
$prioridades = get_ticket_prioridades();

$iniciales = '--';

if (!empty($ticket['responsable'])) {

    $partes = preg_split('/\s+/', trim($ticket['responsable']));

    if (count($partes) >= 3) {
        $iniciales = strtoupper(substr($partes[2], 0, 1) . substr($partes[0], 0, 1));
    } elseif (count($partes) == 2) {
        $iniciales = strtoupper(substr($partes[1], 0, 1) . substr($partes[0], 0, 1));
    } else {
        $iniciales = strtoupper(substr($partes[0], 0, 2));
    }
}

$historial = get_ticket_historico($id);
$historialCierre = get_ticket_historico_cierre($id);
$comentarios = get_ticket_comentarios($id);

$statusClass = '';

switch ($ticket['estatus_id']) {
    case 1: $statusClass = 'abierto'; break;
    case 2: $statusClass = 'proceso'; break;
    case 3: $statusClass = 'espera'; break;
    case 4: $statusClass = 'resuelto'; break;
    case 5: $statusClass = 'cerrado'; break;
    case 6: $statusClass = 'cancelado'; break;
}

// Prioridad -> clase y color de acento
$priority = 'none';
$priorityColor = '#ccc';

switch ((int)$ticket['prioridad_id']) {
    case 1: $priority = 'baja';    $priorityColor = '#5cb85c'; break;
    case 2: $priority = 'media';   $priorityColor = '#f0ad4e'; break;
    case 3: $priority = 'alta';    $priorityColor = '#d9534f'; break;
    case 4: $priority = 'critica'; $priorityColor = '#843534'; break;
    default: $priority = 'none';   $priorityColor = '#ccc'; break;
}

// Días desde creación (útil para que el admin vea qué tan viejo está el ticket)
$diasAbierto = floor((time() - strtotime($ticket['fecha_creacion'])) / 86400);

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
        <!-- Header -->
       <div class="tk-header" style="--tk-priority-color: <?php echo htmlspecialchars($priorityColor); ?>;">
            <div class="row">
                <div class="col-md-8">
                    <a href="ticket_histo_user.php" class="btn btn-default btn-xs" style="margin-bottom:10px;">
                        <span class="glyphicon glyphicon-arrow-left"></span> Regresar
                    </a>

                    <div>
                        <span class="tk-folio"><?php echo remove_junk($ticket['folio']); ?></span>
                        <span class="tk-priority <?php echo $priority; ?>">
                            <?php echo !empty($ticket['nombre_prioridad']) ? remove_junk($ticket['nombre_prioridad']) : 'Sin prioridad'; ?>
                        </span>
                    </div>

                    <p class="tk-asunto"><?php echo remove_junk($ticket['asunto']); ?></p>

                    <div>
                        <span class="tk-meta-item">
                            <span class="glyphicon glyphicon-user"></span>
                            <?php echo remove_junk($ticket['empleado']); ?>
                        </span>
                        <span class="tk-meta-item">
                            <span class="glyphicon glyphicon-time"></span>
                            <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?>
                        </span>
                        <span class="tk-meta-item">
                            <span class="glyphicon glyphicon-tag"></span>
                            <?php echo remove_junk($ticket['nombre_categoria']); ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-4 text-right">
                    <span id="badgeEstatus" class="tk-status <?php echo $statusClass; ?>">
                        <?php echo remove_junk($ticket['nombre_estatus']); ?>
                    </span>
                    <div style="margin-top:10px;">
                        <span class="tk-dias-abierto <?php echo $diasAbierto >= 3 ? 'alerta' : ''; ?>">
                            <?php if ($diasAbierto == 0): ?>
                                Abierto hoy
                            <?php else: ?>
                                Abierto hace <?php echo (int)$diasAbierto; ?> día<?php echo $diasAbierto == 1 ? '' : 's'; ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Columna Izquierda -->
    <div class="col-md-8">
        <!-- Conversacion -->
        <div class="tk-card">
            <div class="tk-card-heading">
                <span class="glyphicon glyphicon-comment"></span> Conversación
            </div>
            <div class="tk-card-body">

                <?php
                    $avatarTicket = '--';

                    if (!empty($ticket['empleado'])) {
                        $partes = preg_split('/\s+/', trim($ticket['empleado']));

                        if (count($partes) >= 3) {
                            $avatarTicket = strtoupper(substr($partes[2], 0, 1) . substr($partes[0], 0, 1));
                        } elseif (count($partes) == 2) {
                            $avatarTicket = strtoupper(substr($partes[1], 0, 1) . substr($partes[0], 0, 1));
                        } else {
                            $avatarTicket = strtoupper(substr($partes[0], 0, 2));
                        }
                    }
                ?>

                <div class="tk-clearfix">
                    <div class="tk-avatar user"><?php echo $avatarTicket; ?></div>
                    <div class="tk-media-body">
                        <strong><?php echo remove_junk($ticket['empleado']); ?></strong>
                        <span class="label label-default" style="font-weight:400;">Yo</span>
                        <small class="text-muted pull-right">
                            <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?>
                        </small>
                        <div class="tk-msg user">
                            <?php echo nl2br(remove_junk($ticket['descripcion'])); ?>
                        </div>
                    </div>
                </div>
                <div id="ticketConversacion">
                    <?php if (!empty($comentarios)): ?>
                        <?php foreach ($comentarios as $comentario): ?>
                            <?php echo render_ticket_comentario($comentario, $ticket['usuario_id']); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if($ticket['estatus_id']==3): ?>
                    <div class="tk-card">
                        <div class="tk-card-heading">
                            Acciones
                        </div>
                        <form method="post" id="formSeguimiento">
                            <input type="hidden" id="ticket_id" value="<?php echo (int)$ticket['id']; ?>">
                            <div class="tk-clearfix">
                                <div class="tk-avatar user">
                                    <?php echo $iniciales; ?>
                                </div>
                                <div class="tk-media-body">
                                    <strong><?php echo remove_junk($user['name']); ?></strong>
                                    <span class="label label-default" style="font-weight:400;">Yo</span>
                                    <div class="tk-msg user">
                                        <textarea
                                            class="form-control"
                                            id="comentarioSeguimiento"
                                            name="comentario"
                                            placeholder="Responde al sistema ..."
                                            required></textarea>

                                        <div class="tk-acciones-row">
                                            <button
                                                type="submit"
                                                name="btnResponder"
                                                class="btn btn-primary tk-btn-enviar">
                                                <span class="glyphicon glyphicon-send"></span>
                                                Enviar respuesta
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <?php if($ticket['estatus_id'] == 4): ?>
                    <div class="tk-card">
                        <div class="tk-card-heading">
                            <span class="glyphicon glyphicon-ok-circle"></span>
                            Confirmación de solución
                        </div>
                        <div class="tk-card-body text-center">
                            <h4 style="margin-top:0;">
                                Sistemas marcó este ticket como resuelto.
                            </h4>
                            <p class="text-muted">
                                Confirma si el problema quedó solucionado.
                            </p>

                            <form method="post" id="formConfirmacion">
                                <button
                                    type="submit"
                                    name="btnCerrarTicket"
                                    class="btn btn-success btn-lg"
                                    onclick="return confirm('¿Confirmas que el problema ya quedó resuelto? El ticket se cerrará.');">
                                    <span class="glyphicon glyphicon-ok"></span>
                                    Sí, cerrar ticket
                                </button>

                                <button
                                    type="button"
                                    id="btnNoResuelto"
                                    class="btn btn-default btn-lg"
                                    style="margin-left:10px;">
                                    <span class="glyphicon glyphicon-remove"></span>
                                    No, continúa el problema
                                </button>

                                <!-- Se muestra solo si el usuario dice que sigue el problema -->
                                <div id="panelReabrir" style="display:none; text-align:left; margin-top:16px;">
                                    <label class="tk-resolucion-label">Cuéntanos qué sigue fallando</label>
                                    <textarea
                                        class="form-control"
                                        name="comentario"
                                        rows="3"
                                        placeholder="Ej. Ya cambié el tóner pero sigue sin imprimir a color..."></textarea>

                                    <button
                                        type="submit"
                                        name="btnReabrir"
                                        class="btn btn-warning"
                                        style="margin-top:10px;">
                                        <span class="glyphicon glyphicon-refresh"></span>
                                        Reabrir ticket
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Columna Derecha -->
    <div class="col-md-4 tk-sticky">
        <!-- Informacion -->
        <div>
            <div class="tk-card">
                <div class="tk-card-heading">
                    <span class="glyphicon glyphicon-info-sign"></span> Información
                </div>
                <div class="tk-card-body" style="padding-top:6px;padding-bottom:6px;">
                    <table class="table tk-info-table" style="margin-bottom:0;">
                        <tr>
                            <th>Empleado</th>
                            <td><?php echo remove_junk($ticket['empleado']); ?></td>
                        </tr>
                        <tr>
                            <th>Categoría</th>
                            <td><strong><?php echo remove_junk($ticket['nombre_categoria']); ?></strong></td>
                        </tr>
                        <?php switch ($ticket['categoria_id']):
                            case 2:?>
                                <tr>
                                    <th>Equipo</th>
                                    <td><?php echo remove_junk($ticket['codigo_equipo']); ?></td>
                                </tr>
                                <tr>
                                    <th>Marca</th>
                                    <td><?php echo remove_junk($ticket['marca']); ?></td>
                                </tr>
                                <tr>
                                    <th>Modelo</th>
                                    <td><?php echo remove_junk($ticket['modelo']); ?></td>
                                </tr>
                                <tr>
                                    <th>Serie</th>
                                    <td><?php echo remove_junk($ticket['serie']); ?></td>
                                </tr>
                                <?php break;
                            case 6:?>
                                <tr>
                                    <th>Modelo</th>
                                    <td><?php echo remove_junk($ticket['marca']." - ".$ticket['modelo']); ?></td>
                                </tr>
                                <tr>
                                    <th>Lugar</th>
                                    <td><?php echo remove_junk($ticket['departamento']); ?></td>
                                </tr>
                            <?php break;
                        endswitch; ?>
                        <tr>
                            <th>Prioridad</th>
                            <td>
                                <span class="tk-priority <?php echo $priority; ?>">
                                    <?php echo !empty($ticket['nombre_prioridad']) ? remove_junk($ticket['nombre_prioridad']) : 'Sin asignar'; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Responsable</th>
                            <td>
                                <?php if (!empty($ticket['responsable'])): ?>
                                    <span class="tk-avatar agent small"><?php echo $iniciales; ?></span>
                                    <?php echo remove_junk($ticket['responsable']); ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
        <!-- Historial -->
        <div>
            <?php if($ticket['estatus_id'] == 5){?>
                <div class="tk-card">
                    <div class="tk-card-heading">
                        <span class="glyphicon glyphicon-time"></span> Historial
                    </div>
                    <div class="tk-card-body">
                        <ul class="tk-timeline" id="ticketHistorico">
                            <?php foreach ($historialCierre as $historico){
                                echo render_ticket_historico($historico);
                            } ?>
                        </ul>
                    </div>
                </div>
            <?php } else { ?>
                <div class="tk-card">
                    <div class="tk-card-heading">
                        <span class="glyphicon glyphicon-time"></span> Historial
                    </div>
                    <div class="tk-card-body">
                        <ul class="tk-timeline" id="ticketHistorico">
                            <?php foreach ($historial as $historico){ 
                                echo render_ticket_historico($historico); 
                            } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>
        </div>         
    </div>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
