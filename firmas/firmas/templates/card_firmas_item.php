<div class="col-md-4">

    <div class="card shadow-sm h-100">

        <div class="card-header text-center">

            <strong><?= htmlspecialchars($tipo) ?></strong>

        </div>

        <div class="card-body text-center">

            <?php if($firma): ?>

                <img
                    src="<?= htmlspecialchars($firma['archivo']) ?>"
                    class="img-fluid mb-3"
                    style="max-height:120px;"
                >

                <small class="text-muted d-block">

                    Usuario #<?= (int)$firma['usuario_id'] ?>

                </small>

                <small class="text-muted">

                    <?= htmlspecialchars($firma['created_at']) ?>

                </small>

            <?php else: ?>

                <div class="py-5 text-secondary">

                    <i class="fa fa-pen fa-2x mb-2"></i>

                    <div>Pendiente de firma</div>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>