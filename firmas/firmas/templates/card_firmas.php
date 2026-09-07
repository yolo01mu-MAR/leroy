<div class="card">

    <div class="card-header">

        <strong>Firmas</strong>

    </div>

    <div class="card-body">

        <div class="row g-3">

            <?= render_firma_item(
                $firmas[FirmaTipo::EMPLEADO] ?? null,
                FirmaTipo::EMPLEADO
            ); ?>

            <?= render_firma_item(
                $firmas[FirmaTipo::JEFE] ?? null,
                FirmaTipo::JEFE
            ); ?>

            <?= render_firma_item(
                $firmas[FirmaTipo::RH] ?? null,
                FirmaTipo::RH
            ); ?>

        </div>

    </div>

</div>