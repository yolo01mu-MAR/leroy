<?php

    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once('includes/validar_kiosco.php');
    require_once('includes/tiempo_helpers.php');

    $id = $_SESSION['kiosco']['empleado_id'];

    $scripts_kiosco = [
        BASE_URL . '/firmas/firmas/assets/js/signature_pad.min.js',
        BASE_URL . '/firmas/firmas/assets/js/firmas.js',
        BASE_URL . '/firmas/txt/assets/js/txt_falta.js',
        BASE_URL . '/modules/kiosco/libs/js/txt_falta.js',
    ];

    $nueva = 'user_tiempo';
    $pagina = basename($nueva, '.php');

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];

    $faltas = get_faltas_tiempo($id);

?>
<?php include_once 'layouts/header.php'; ?>
<div class="row">
    <!-- PERFIL -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <?php 
                    $foto = !empty($empleado['image'])
                        ? BASE_URL . '/uploads/users/' . $empleado['image']
                        : BASE_URL . '/uploads/users/no_image.jpg';
                ?>
                <img src="<?php echo $foto; ?>"
                    class="rounded-circle border border-3 border-warning mb-3"
                    width="130"
                    height="130">
                <h4 class="mb-0">
                    <?php echo remove_junk($empleado['nombre']); ?>
                </h4>
                <small class="text-muted">
                    <?php echo remove_junk($empleado['puesto']); ?>
                </small>
                <hr>
                <div class="text-start">
                    <p class="mb-2">
                        <b>Nómina:</b>
                        <?php echo remove_junk($empleado['id']); ?>
                    </p>
                    <p class="mb-2">
                        <b>Departamento:</b><br>
                        <?php echo remove_junk($empleado['departamento'] ." - ". $empleado['lugar']); ?>
                    </p>
                    <p class="mb-2">
                        <b>Grupo:</b>
                        <?php echo remove_junk($empleado['grupos']); ?>
                    </p>
                    <p class="mb-2">
                        <b>Fecha ingreso:</b>
                        <?php echo remove_junk($empleado['fecha_ingreso']); ?>
                    </p>
                </div>
                <a href="user_tiempo.php" class="btn btn-outline-secondary w-100 mt-3">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">
                    <i class="bi bi-calendar-x text-danger"></i>
                    Mis faltas
                </h4>
            </div>
            <div class="card-body">
                <?php if(empty($faltas)): ?>
                    <div class="alert alert-success mb-0">
                        No tienes faltas disponibles para solicitar
                        Tiempo x Tiempo.
                    </div>
                <?php else: ?>
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Observaciones</th>
                                <th width="150"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($faltas as $f): ?>
                            <tr>
                                <td><?php echo date('d/m/Y',strtotime($f['fecha'])); ?></td>
                                <td><?php echo remove_junk($f['tipo']); ?></td>
                                <td><?php echo !empty($f['observaciones']) ? remove_junk($f['observaciones']) : '-';?></td>
                                <td>
                                    <button
                                        class="btn btn-warning btn-sm btnSolicitar"
                                        data-id="<?php echo $f['ID']; ?>"
                                        data-fecha="<?php echo $f['fecha']; ?>">
                                        Solicitar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
    let kioscoAutenticado = <?= !empty($_SESSION['kiosco']['autenticado']) ? 'true' : 'false'; ?>;
</script>
<?php include_once 'layouts/footer.php'; ?>