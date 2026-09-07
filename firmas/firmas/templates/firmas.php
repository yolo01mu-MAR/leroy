<?php if (!empty($config['titulo'])): ?>
    <h5 class="mb-3">
        <?= htmlspecialchars($config['titulo']) ?>
    </h5>
<?php endif; ?>
<div
    class="firma-componente"
    data-modulo="<?= $config['modulo'] ?>"
    data-registro="<?= $config['registro_id'] ?>"
    data-tipo="<?= $config['tipo'] ?>"
    data-usuario="<?= $config['usuario_id'] ?>"
    data-height="<?= (int)$config['height'] ?>"
    data-firmada="<?= $firma ? 1 : 0 ?>"
>
    <?php

        if ($firma || $config['readonly']) {
            include(__DIR__.'/firma_imagen.php');
        } else {
            include(__DIR__.'/firma_canvas.php');
        }

    ?>
</div>