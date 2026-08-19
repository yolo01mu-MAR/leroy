<?php

$texto = '';

switch ($historico['accion_id']) {
    case 1: $texto = 'Ticket creado'; break;
    case 2: $texto = 'Estado cambiado'; break;
    case 3: $texto = 'Asignado a ' . $historico['name']; break;
    case 4: $texto = 'Reasignado a ' . $historico['name']; break;
    case 5: $texto = 'Comentario agregado'; break;
    case 6: $texto = 'Ticket resuelto'; break;
    case 7: $texto = 'Ticket cerrado'; break;
    case 8: $texto = 'Prioridad actualizada'; break;
}
?>

<li class="tk-timeline-item">

    <div>
        <strong><?php echo $texto; ?></strong>
    </div>

    <?php if ($historico['accion_id'] == 2): ?>
        <small class="text-muted">
            <?php echo $historico['anterior']; ?>
            →
            <?php echo $historico['nuevo']; ?>
        </small>
        <br>
    <?php endif; ?>

    <small class="text-muted">
        <?php echo date('d/m/Y H:i', strtotime($historico['fecha'])); ?>
    </small>

</li>