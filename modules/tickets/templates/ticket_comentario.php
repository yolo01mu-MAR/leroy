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

$esEmpleado = ($comentario['usuario_id'] == $ticketUsuarioId);

$avatarClass = $esEmpleado ? 'user' : 'agent';
$msgClass    = $esEmpleado ? 'user' : 'agent';
$labelClass  = $esEmpleado ? 'label-default' : 'label-success';
$labelTexto  = $esEmpleado ? 'Empleado' : 'Sistemas';
?>

<div class="tk-clearfix" id="comentario-<?php echo (int)$comentario['id']; ?>">

    <div class="tk-avatar <?php echo $avatarClass; ?>">
        <?php echo $avatar; ?>
    </div>

    <div class="tk-media-body">

        <strong><?php echo remove_junk($comentario['name']); ?></strong>

        <span class="label <?php echo $labelClass; ?>" style="font-weight:400;">
            <?php echo $labelTexto; ?>
        </span>

        <?php if (!$esEmpleado && !empty($comentario['requiere_respuesta'])): ?>
            <span class="label label-warning tk-requiere-respuesta">
                Requiere respuesta
            </span>
        <?php endif; ?>

        <small class="text-muted pull-right">
            <?php echo date('d/m/Y H:i', strtotime($comentario['fecha'])); ?>
        </small>

        <div class="tk-msg <?php echo $msgClass; ?>">
            <?php echo nl2br(remove_junk($comentario['comentario'])); ?>
        </div>

    </div>

</div>