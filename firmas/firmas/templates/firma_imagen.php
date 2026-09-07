<div class="card">

    <div class="card-body text-center">

        <img
            src="../../<?= $firma['archivo'] ?>"
            class="img-fluid border rounded mb-3"
            style="max-width:<?= (int)$config['width'] ?>px;"
        >
        <br>
        <small class="text-muted d-block">
            Firmado por:
            <strong><?= htmlspecialchars($firma['usuario_nombre']) ?></strong>

        </small>

        <small class="text-muted">

            <?= date(
                'd/m/Y H:i',
                strtotime($firma['created_at'])
            ) ?>

        </small>

    </div>

</div>