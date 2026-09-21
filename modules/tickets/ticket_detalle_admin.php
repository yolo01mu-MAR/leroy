<?php
$page_title = 'Detalle Ticket';

$scripts = [
    'ticket_detalle_admin'
];

require_once __DIR__ . '/../../app/bootstrap.php';
require_once('includes/ticket_helpers.php');

$id = (int)$_GET['id'];
$usuario = current_user();

$ticket = get_detalle_ticket($id);

if (empty($ticket)) {
    redirect('ticket_detalle_admin.php');
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
                    <a href="ticket_histo_admin.php" class="btn btn-default btn-xs" style="margin-bottom:10px;">
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
                    <span class="tk-status <?php echo $statusClass; ?>">
                        <?php echo strtoupper(remove_junk($ticket['nombre_estatus'])); ?>
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
        <!-- Gestion -->
        <div class="tk-card">
            <div class="tk-card-heading">
                <span class="glyphicon glyphicon-cog"></span>Gestión del ticket
            </div>
            <div class="tk-card-body">
                <form method="post" id="formGestion" data-ticket="<?php echo (int)$id; ?>">
                    <div class="tk-gestion-row">
                        <div class="tk-gestion-col">
                            <label>Prioridad</label>
                            <div class="tk-priority-picker">
                                <?php foreach ($prioridades as $prio):
                                    $slug = strtolower(remove_junk($prio['nombre_prioridad']));
                                    $slug = preg_replace('/[^a-z]/', '', $slug); // baja/media/alta/critica
                                ?>
                                    <input type="radio"
                                        name="prioridad_id"
                                        id="prio_<?php echo (int)$prio['id']; ?>"
                                        value="<?php echo (int)$prio['id']; ?>"
                                        <?php echo ((int)$ticket['prioridad_id'] === (int)$prio['id']) ? 'checked' : ''; ?>>
                                    <label for="prio_<?php echo (int)$prio['id']; ?>" class="p-<?php echo $slug; ?>">
                                        <?php echo remove_junk($prio['nombre_prioridad']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="tk-gestion-col">
                            <label>Asignar a</label>
                            <select class="form-control" name="asignado_a">
                                <option value="">Sin asignar</option>
                                <?php foreach ($agentes as $agente): ?>
                                    <option value="<?php echo (int)$agente['id']; ?>"
                                        <?php echo ((int)$ticket['asignado_a'] === (int)$agente['id']) ? 'selected' : ''; ?>>
                                        <?php echo remove_junk($agente['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
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
                        <span class="label label-default" style="font-weight:400;">Empleado</span>
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
                            <?php
                                $avatar = '--';
                                if (!empty($comentario['name'])) {
                                    $partes = preg_split('/\s+/', trim($comentario['name']));

                                    if (count($partes) >= 3) {
                                        $avatar = strtoupper(substr($partes[2], 0, 1) . substr($partes[0], 0, 1));
                                    } elseif (count($partes) == 2) {
                                        $avatar = strtoupper(substr($partes[1], 0, 1) . substr($partes[0], 0, 1));
                                    } else {
                                        $avatar = strtoupper(substr($partes[0], 0, 2));
                                    }
                                }
                            ?>
                            <?php
                                // ¿El comentario lo escribió el empleado?
                                $esEmpleado = ($comentario['usuario_id'] == $ticket['usuario_id']);

                                $avatarClass = $esEmpleado ? 'user' : 'agent';
                                $msgClass    = $esEmpleado ? 'user' : 'agent';
                                $labelClass  = $esEmpleado ? 'label-default' : 'label-success';
                                $labelTexto  = $esEmpleado ? 'Empleado' : 'Sistemas';
                            ?>

                            <div class="tk-clearfix" data-comentario="<?php echo (int)$comentario['id']; ?>">
                                <div class="tk-avatar <?php echo $avatarClass; ?>">
                                    <?php echo $avatar; ?>
                                </div>

                                <div class="tk-media-body">

                                    <strong><?php echo remove_junk($comentario['name']); ?></strong>

                                    <span class="label <?php echo $labelClass; ?>" style="font-weight:400;">
                                        <?php echo $labelTexto; ?>
                                    </span>

                                    <?php if (!$esEmpleado && !empty($comentario['requiere_respuesta'])): ?>
                                        <span class="label label-warning">Requiere respuesta</span>
                                    <?php endif; ?>

                                    <small class="text-muted pull-right">
                                        <?php echo date('d/m/Y H:i', strtotime($comentario['fecha'])); ?>
                                    </small>

                                    <div class="tk-msg <?php echo $msgClass; ?>">
                                        <?php echo nl2br(remove_junk($comentario['comentario'])); ?>
                                    </div>

                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ($ticket['estatus_id'] != 4) { ?>
                    <hr>
                    <form method="post" id="formSeguimiento" data-ticket="<?php echo (int)$id; ?>">
                        <div class="tk-clearfix">
                            <div class="tk-avatar agent">
                                <?php echo $iniciales; ?>
                            </div>
                            <div class="tk-media-body">
                                <strong><?php echo remove_junk($user['name']); ?></strong>
                                <span class="label label-success">Sistemas</span>
                                <div class="tk-msg agent">
                                    <!-- ¿Qué se hizo para resolverlo? -->
                                    <span class="tk-resolucion-label">¿Qué se hizo para resolverlo? (opcional)</span>
                                <textarea
                                    class="form-control"
                                    id="comentarioSeguimiento"
                                    name="comentario"
                                    placeholder="Describe las acciones realizadas..."
                                    required></textarea>
                                    <div class="tk-resolucion-hint">
                                        <span class="glyphicon glyphicon-info-sign"></span>
                                        <span>
                                            <strong>Registrar seguimiento</strong> guarda el mensaje sin cerrar el ticket. <br>
                                            <strong>Marcar como resuelto</strong> guarda este mensaje y cierra el ticket como resuelto.
                                        </span>
                                    </div>
                                    <div class="tk-acciones-row">
                                        <div class="btn-secundario-grp">
                                            <button
                                                type="submit"
                                                name="btnSeguimiento"
                                                class="btn tk-btn-secundario">
                                                <span class="glyphicon glyphicon-comment"></span>
                                                Registrar seguimiento
                                            </button>
                                            <button
                                                type="submit"
                                                name="btnSolicitar"
                                                class="btn tk-btn-secundario">
                                                <span class="glyphicon glyphicon-question-sign"></span>
                                                Solicitar respuesta
                                            </button>
                                        </div>
                                        <button
                                            type="submit"
                                            name="btnResolver"
                                            class="btn btn-success tk-btn-resolver">
                                            <span class="glyphicon glyphicon-ok-circle"></span>
                                            Marcar como resuelto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php } ?>

            </div>
        </div>
        <hr>
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
            <div class="tk-card">
                <div class="tk-card-heading">
                    <span class="glyphicon glyphicon-time"></span> Historial
                </div>
                <div class="tk-card-body">
                    <ul class="tk-timeline" id="ticketHistorial">
                    <?php foreach ($historial as $h): ?>
                        <?php
                        $texto = '';
                        switch ($h['accion_id']) {
                            case 1: $texto = 'Ticket creado'; break;
                            case 2: $texto = 'Estado cambiado'; break;
                            case 3: $texto = 'Asignado a ' . $h['name']; break;
                            case 4: $texto = 'Reasignado a ' . $h['name']; break;
                            case 5: $texto = 'Comentario agregado'; break;
                            case 6: $texto = 'Ticket resuelto'; break;
                            case 7: $texto = 'Ticket cerrado'; break;
                            case 8: $texto = 'Prioridad actualizada'; break;
                        }
                        ?>
                        <li class="tk-timeline-item">
                            <div><strong><?php echo $texto; ?></strong></div>
                            <?php if ($h['accion_id'] == 2): ?>
                                <small class="text-muted">
                                    <?php echo $h['anterior']; ?> → <?php echo $h['nuevo']; ?>
                                </small><br>
                            <?php endif; ?>
                            <small class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($h['fecha'])); ?>
                            </small>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>         
    </div>
</div>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>