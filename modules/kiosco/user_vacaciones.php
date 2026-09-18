<?php

    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once 'includes/validar_kiosco.php';
    require_once 'includes/funtions.php';

    $id = $_SESSION['kiosco']['empleado_id'];

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];

    if (empty($empleado)) {
    redirect('identificar_colaborador.php');
    }

    $solicitudes = get_solicitudes_vacaciones_usuario(
    $empleado['id']
    );
    
    $pagina = basename($_SERVER['PHP_SELF'], '.php');
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
                <a class="btn btn-sm btn-outline-danger w-100 mt-3" onclick="Kiosco.cerrarSesion()">
                    <i class="bi bi-arrow-left"></i>
                    Cerrar sesion
                </a>
            </div>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="col-lg-8">
        <h2 class="mb-4">
            <i class="bi bi-calendar2-check"></i>
            Mis vacaciones
        </h2>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center h-100">
                    <div class="card-body">
                        <h1 class="text-primary">
                            <?php echo remove_junk($empleado['dias_otorgados']); ?>
                        </h1>
                        <p class="text-muted mb-0">
                            Días otorgados
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center h-100">
                    <div class="card-body">
                        <h1 class="text-danger">
                            <?php echo remove_junk($empleado['dias_disfrutados']); ?>
                        </h1>
                        <p class="text-muted mb-0">
                            Disfrutados
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center h-100">
                    <div class="card-body">
                        <h1 class="text-success">
                            <?php echo remove_junk($empleado['dias_disponibles']); ?>
                        </h1>
                        <p class="text-muted mb-0">
                            Disponibles
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h5 class="mb-3">
                    Periodo vigente
                </h5>
                <p class="mb-2">
                    <b>Inicio:</b>
                    <?php echo remove_junk($empleado['inicio']); ?>
                    &nbsp;&nbsp;
                    <b>Fin:</b>
                    <?php echo remove_junk($empleado['fin']); ?>
                </p>
                <?php
                    $porcentaje = ($empleado['dias_otorgados'] > 0)
                        ? ($empleado['dias_disponibles'] * 100) / $empleado['dias_otorgados']
                        : 0;
                ?>
                <div class="progress" style="height:18px;">
                    <div class="progress-bar bg-success"
                        role="progressbar"
                        style="width: <?php echo $porcentaje; ?>%;">
                        <?php echo $empleado['dias_disponibles']; ?>
                        disponibles
                    </div>
                </div>
            </div>
        </div>
        <?php
            $autenticado = !empty($_SESSION['kiosco']['autenticado']);
        ?>
        <div class="d-grid mt-4">
            <?php if ($autenticado): ?>
                <!-- YA ESTÁ AUTENTICADO -->
                <a
                    href="vacaciones_nueva.php"
                    class="btn btn-primary">
                    <i class="bi bi-calendar-plus"></i>
                    Solicitar vacaciones
                </a>
            <?php else: ?>
                <!-- ENTRÓ POR CREDENCIAL -->
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalPassword">
                    <i class="bi bi-calendar-plus"></i>
                    Solicitar vacaciones
                </button>
            <?php endif; ?>
        </div>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <b>Últimas solicitudes</b>
            </div>
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Fecha Solicitud</th>
                            <th>Días</th>
                            <th>Fechas de los dias</th>
                            <th>Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($solicitudes as $soli): ?>
                        <tr>
                            <td class="text-center"><?php echo remove_junk($soli['fecha_solicitud']); ?></td>
                            <td class="text-center"><?php echo remove_junk($soli['dias']); ?></td>
                            <td class="text-center"><?php echo remove_junk($soli['fecha_inicio'] . " - " . $soli['fecha_fin']);?></td>
                            <td class="text-center"><?= badge_estatus($soli['estatus']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include_once 'layouts/footer.php'; ?>