<?php

    require_once __DIR__ . '/../../app/bootstrap.php';
    require_once("includes/validar_kiosco.php");

    $id = (int)$_SESSION['kiosco']['empleado_id'];

    // $scripts_kiosco = [
    //     BASE_URL . '/modules/kiosco/libs/js/user_solicitudes.js'
    // ];

    $empleado = get_datos_kiosco($id);
    $empleado = $empleado[0];

    $nueva = 'user_soporte';
    $pagina = basename($nueva, '.php');

    $resultados = get_historial_user($id);

?>
<?php include_once 'layouts/header.php'; ?>
<link rel="stylesheet" href="libs/css/ticket.css">
<div class="row">
    <!-- PERFIL -->
    <div class="col-lg-3 mb-3">
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
    <div class="col-lg-9">
        <div class="panel panel-default">
        <div class="panel-heading clearfix">

            <div class="pull-left" style="padding-top:6px;">
                <strong>
                    <span class="glyphicon glyphicon-list-alt"></span>
                    <span>Mis Tickets</span>
                </strong>
                <br>
                <small class="text-muted">
                    Consulta el estado de tus solicitudes de soporte.
                </small>
            </div>

            <div class="pull-right">
                <a href="ticket_nuevo.php" class="btn btn-success">
                    <span class="glyphicon glyphicon-plus"></span>
                    Nuevo Ticket
                </a>
            </div>

        </div>
        <div class="panel-body">
            <div class="tabla-scroll">
            <table class="table table-bordered table-striped tabla-fija">
                <thead>
                    <tr>
                        <th width="20%" class="text-center">Ticket</th>
                        <th width="15%" class="text-center">Categoría</th>
                        <th width="15%" class="text-center">Estado</th>
                        <th width="15%" class="text-center">Fecha Alta</th>
                        <th width="20%" class="text-center">Responsable</th>
                        <th width="10%" class="text-center">Ver</th>
                    </tr>
                </thead>
                <tbody id="tabla-resultados">
                <?php if (!empty($resultados)): ?>
                <?php foreach ($resultados as $ticket): ?>
                    <tr>
                    <td>
                        <strong style="font-size:15px;">
                        <?php echo remove_junk($ticket['folio']); ?>
                        </strong>
                        <br>
                        <small class="text-muted">
                        <?php echo remove_junk($ticket['asunto']); ?>
                        </small>
                    </td>
                    <td class="text-center">
                        <?php echo remove_junk($ticket['categoria']); ?>
                    </td>
                    <td class="text-center">
                        <?php
                        switch ($ticket['estatus']) {
                            case 'ABIERTO':
                            echo '<span class="label label-primary">ABIERTO</span>';
                            break;
                            case 'EN_PROCESO':
                            echo '<span class="label label-warning">EN PROCESO</span>';
                            break;
                            case 'ESPERA_USUARIO':
                            echo '<span class="label label-info">ESPERA USUARIO</span>';
                            break;
                            case 'RESUELTO':
                            echo '<span class="label label-success">RESUELTO</span>';
                            break;
                            case 'CERRADO':
                            echo '<span class="label label-default">CERRADO</span>';
                            break;
                            case 'CANCELADO':
                            echo '<span class="label label-danger">CANCELADO</span>';
                            break;
                            default:
                            echo '<span class="label label-default">'.$ticket['estatus'].'</span>';
                            break;
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php echo date('d/m/Y',strtotime($ticket['fecha_creacion'])); ?>
                        <br>
                        <small class="text-muted">
                        <?php echo date('H:i',strtotime($ticket['fecha_creacion'])); ?>
                        </small>
                    </td>
                    <td class="text-center"><?php echo remove_junk($ticket['asignado']);?></td>
                    <td class="text-center">
                        <a href="ticket_detalle_user.php?id=<?php echo (int)$ticket['id']; ?>"
                        title="Ver Ticket">
                            <span class="glyphicon glyphicon-eye-open"></span>
                        </a>
                    </td>
                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                    <td colspan="7" class="text-center">
                        No tienes tickets registrados.
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
<?php include_once 'layouts/footer.php'; ?>