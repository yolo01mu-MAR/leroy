<?php

    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once("includes/validar_kiosco.php");

    $id = (int)$_SESSION['kiosco']['empleado_id'];

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];

    $nueva = 'user_soporte';
    $pagina = basename($nueva, '.php');

    // COnsulta que revisa los tickets del usuario
    $resultados = get_historial_user($id);

?>
<?php include_once 'layouts/header.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="libs/css/ticket.css">
<div class="container-fluid px-0">
    <div class="row g-4">

        <!-- TARJETA PERFIL USUARIO -->
        <div class="col-lg-4">
            <?php include_once 'perfil_kiosco.php'; ?>
        </div>

        <!-- CONTENIDO -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">

                <!-- HEADER -->
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">

                    <!-- TITULO Y SUBTITULO -->
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">
                            <i class="bi bi-headset me-2"></i>
                            Mis Ayudas
                        </h5>
                        <p class="text-muted small mb-0">
                            Consulta el estado y avance de tus solicitudes de soporte técnico.
                        </p>
                    </div>

                </div>

                <!-- TABLA -->
                <br>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="20%" class="text-center">Ticket</th>
                                <th width="15%" class="text-center">Categoría</th>
                                <th width="15%" class="text-center">Estado</th>
                                <th width="15%" class="text-center">Fecha Alta</th>
                                <th width="20%" class="text-center">Responsable</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-resultados">
                        <?php if (!empty($resultados)): ?>
                        <?php foreach ($resultados as $ticket): ?>
                            <tr>
                                <!-- TICKET -->
                                <td class="ps-3 py-3">
                                    <span class="fw-bold d-block mb-1">
                                        <?php echo remove_junk($ticket['folio']); ?>
                                    </span>
                                    <div class="text-muted small text-wrap lh-sm">
                                        <?php echo remove_junk($ticket['asunto']); ?>
                                    </div>
                                </td>

                                <!-- CATEGORIA -->
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        <?php echo remove_junk($ticket['categoria']); ?>
                                    </span>
                                </td>

                                <!-- ESTADO / ESTATUS -->
                                <td class="text-center">
                                    <?php
                                    switch ($ticket['estatus']) {
                                        case 'ABIERTO':
                                        echo '<span class="badge bg-primary">ABIERTO</span>';
                                        break;
                                        case 'EN_PROCESO':
                                            echo '<span class="badge bg-warning text-dark">EN PROCESO</span>';
                                            break;
                                        case 'ESPERA_USUARIO':
                                            echo '<span class="badge bg-info text-dark">ESPERA USUARIO</span>';
                                            break;
                                        case 'RESUELTO':
                                            echo '<span class="badge bg-success">RESUELTO</span>';
                                            break;
                                        case 'CERRADO':
                                            echo '<span class="badge bg-secondary">CERRADO</span>';
                                            break;
                                        case 'CANCELADO':
                                            echo '<span class="badge bg-danger">CANCELADO</span>';
                                            break;
                                        default:
                                            echo '<span class="badge bg-secondary">'.remove_junk($ticket['estatus']).'</span>';
                                            break;
                                        }
                                        ?>
                                </td>
                                
                                <!-- FECHA -->
                                <td class="text-center">
                                    <div class="fw-medium small">
                                        <?php echo date('d/m/Y', strtotime($ticket['fecha_creacion'])); ?>
                                    </div>
                                    <div class="text-muted style-small" style="font-size: 0.75rem;">
                                        <?php echo date('H:i', strtotime($ticket['fecha_creacion'])); ?> hrs
                                    </div>
                                </td>

                                <!-- RESPONSABLE -->
                                <td class="text-center small">
                                    <i class="bi bi-person me-1 text-muted"></i>
                                    <?php echo !empty($ticket['asignado']) ? remove_junk($ticket['asignado']) : '<span class="text-muted fs-7">Sin asignar</span>'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    No tienes solicitudes de ayuda registradas.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once 'layouts/footer.php'; ?>