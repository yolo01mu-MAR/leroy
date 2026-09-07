<canvas
    id="<?= $uid ?>_canvas"
    class="firma-canvas"
    style="
        width:<?= (int)$config['width'] ?>px;
        height:<?= (int)$config['height'] ?>px;
        border:1px solid #CCC;
    "
></canvas>

<div class="mt-3">
    <button
        type="button"
        class="btn btn-secondary btn-limpiar"
    >
        <?= htmlspecialchars($config['btnLimpiar']) ?>
    </button>
    <button
        type="button"
        class="btn btn-primary btn-firmar"
    >
        <?= htmlspecialchars($config['btnGuardar']) ?>
    </button>

</div>