<?php 
    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once('includes/validar_kiosco.php');
    require_once('includes/tiempo_helpers.php');
    require_once('includes/funtions.php');

    $id = $_SESSION['kiosco']['empleado_id'];
    
    $pagina = basename($_SERVER['PHP_SELF'], '.php');

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];

    $solicitudes = get_solicitudes_TXT_usuario($empleado['id']);

?>
<?php include_once 'layouts/header.php'; ?>
<div class="row">
<!-- TARJETA PERFIL USUARIO -->
        <div class="col-lg-4">
            <?php include_once 'perfil_kiosco.php'; ?>
        </div>

<!-- CONTENIDO -->
<div class="col-lg-8">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0">
                <i class="bi bi-clock-history text-warning"></i>
                Tiempo x Tiempo
            </h4>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">
                Selecciona el tipo de solicitud que deseas realizar.
            </p>
            <div class="row">
                <!-- YA FALTE -->
                <div class="col-md-4 mb-4">
                    <a href="txt_faltas.php" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm border-0 tarjeta-opcion">
                            <div class="card-body text-center">
                                <i class="bi bi-calendar-x fs-1 text-danger"></i>
                                <h4 class="mt-3">
                                    Ya tengo una falta
                                </h4>
                                <p class="text-muted">
                                    Solicita un Tiempo x Tiempo por una
                                    falta registrada.
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- VOY A FALTAR -->
                <div class="col-md-4 mb-4">
                    <a href="txt_futura.php" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm border-0 tarjeta-opcion">
                            <div class="card-body text-center">
                                <i class="bi bi-calendar-plus fs-1 text-primary"></i>
                                <h4 class="mt-3">
                                    Voy a faltar
                                </h4>
                                <p class="text-muted">
                                    Solicita un Tiempo x Tiempo antes
                                    de faltar.
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- CUBRIR TURNO -->
                <div class="col-md-4 mb-4">
                    <a href="txt_cubrir.php" class="text-decoration-none text-dark">
                    <!-- <a href="txt_faltas.php" class="text-decoration-none text-dark"> -->
                        <div class="card h-100 shadow-sm border-0 tarjeta-opcion">
                            <div class="card-body text-center">
                                <i class="bi bi-people-fill fs-1 text-success"></i>
                                <h4 class="mt-3">
                                    Cubrir turno
                                </h4>
                                <p class="text-muted">
                                    Solicita que un compañero cubra tu turno o registra que cubrirás el de otro colaborador.
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
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
                        <th>Fecha Falta</th>
                        <th>TxT</th>
                        <th>Fecha Programada</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($solicitudes as $soli): ?>
                    <tr>
                        <td class="text-center"><?php echo remove_junk($soli['fecha_solicitud']); ?></td>
                        <td class="text-center"><?php echo remove_junk($soli['fecha_falta']); ?></td>
                        <td class="text-center"><?php echo remove_junk($soli['origen']);?></td>
                        <td class="text-center"><?php echo !empty($soli['Fecha Programada']) ? remove_junk($soli['Fecha Programada']) : 'Pendiente'; ?></td>
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