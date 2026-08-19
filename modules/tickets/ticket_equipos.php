<?php
    $page_title = 'Soporte Eq. Historial';

    $scripts = [
      'equipo_inventario'
    ];

    require_once __DIR__ . '/../../app/bootstrap.php';
    page_require_level(5);

    $limite = 10;

    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $pagina = ($pagina < 1) ? 1 : $pagina;

    $total_registros = count_view_equipos();
    $total_paginas   = ceil($total_registros / $limite);

    if ($pagina > $total_paginas && $total_paginas > 0) {
        redirect('?page='.$total_paginas);
    }

    $offset = ($pagina - 1) * $limite;

    $equipos = find_view_equipos_historial($limite, $offset);

    // AGRUPAR POR TIPO
    $estructura = [];

    foreach($equipos as $equipo){
        $tipo = $equipo['tipo'];
        $estructura[$tipo][] = $equipo;
    }

?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/ticket.css"/>
<div class="row">
    <div class="col-md-12">

        <div class="panel panel-default">
            <!-- HEADER -->
            <div class="panel-heading clearfix">

                <!-- IZQUIERDA -->
                <div class="pull-left">
                    <div style="
                        font-size:18px;
                        font-weight:600;
                        color:#333;
                        margin-bottom:6px;
                    ">
                        <span class="glyphicon glyphicon-hdd"></span>
                        Historial de fallas por equipo
                    </div>
                <div>
                <span class="label label-primary">
                    <?php echo $total_registros; ?> equipos
                </span>
                <?php foreach($estructura as $tipo => $lista): ?>
                    <span class="label label-default">
                        <?php echo remove_junk($tipo); ?>
                        <span class="badge">
                        <?php echo count($lista); ?>
                        </span>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- DERECHA -->
        <div class="pull-right" style="width:320px; margin-top:10px;">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-search"></span>
                </span>
                <input type="text"
                        id="buscador"
                        class="form-control"
                        placeholder="Buscar código, marca o modelo">

            </div>

        </div>
    </div>
    <!-- BODY -->
    <div class="panel-body">
        <!-- TABS -->
        <ul class="nav nav-tabs" role="tablist">
            <?php
                $i = 0;
                foreach($estructura as $tipo => $lista):
            ?>
                <li role="presentation"
                    class="<?php echo ($i == 0) ? 'active' : ''; ?>">
                    <a href="#tab_<?php echo $i; ?>"
                        aria-controls="tab_<?php echo $i; ?>"
                        role="tab"
                        data-toggle="tab">
                        <?php echo remove_junk($tipo); ?>
                        <span class="badge">
                        <?php echo count($lista); ?>
                        </span>
                    </a>
                </li>
            <?php
                $i++;
                endforeach;
            ?>
        </ul>
        <!-- CONTENIDO -->
        <div class="tab-content" style="margin-top:20px;">
            <?php
                $i = 0;
                foreach($estructura as $tipo => $lista):
            ?>
                <div role="tabpanel" class="tab-pane fade <?php echo ($i == 0) ? 'in active' : ''; ?>" id="tab_<?php echo $i; ?>">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">Equipo</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Ticket Totales</th>
                                    <th class="text-center">Abiertos</th>
                                    <th class="text-center">Ultimo reporte</th>
                                    <th class="text-center">Detalles</th>
                                    <th class="text-center">Historial</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($lista as $e): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo remove_junk($e['marca'] . ' ' . $e['modelo']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo remove_junk($e['codigo_equipo']); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php echo remove_junk($e['tipo']); ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int)$e['total_tickets'] === 0): ?>
                                                <span class="rp-count-pill limpio">0</span>
                                            <?php else: ?>
                                                <span class="rp-count-pill <?php echo (int)$e['total_tickets'] >= 5 ? 'alerta' : ''; ?>">
                                                    <?php echo (int)$e['total_tickets']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo (int)$e['tickets_abiertos']; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($e['ultimo_reporte'])): ?>
                                                <?php echo date('d/m/Y', strtotime($e['ultimo_reporte'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin reportes</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="ver-detalles" data-id="<?php echo (int)$e['id']; ?>">Ver</a>
                                        </td>
                                        <td class="text-center">
                                            <a href="ticket_equipo_historial.php?equipo_id=<?php echo (int)$e['id']; ?>"
                                            class="btn btn-default btn-xs"
                                            title="Ver historial">
                                                <span class="glyphicon glyphicon-time"></span> Historial
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php
                $i++;
                endforeach;
            ?>
        </div>
        <!-- FOOTER -->
        <div class="table-footer clearfix">
            <div class="pull-left text-muted"
                style="font-size:12px; line-height:30px;">
                Mostrando <?php echo count($equipos); ?>
                de <?php echo $total_registros; ?> registros
            </div>
            <div class="pull-right">
                <ul class="pagination pagination-sm no-margin">
                    <li class="<?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                        <a href="?page=<?php echo $pagina - 1; ?>">
                            &laquo;
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="<?php echo ($i == $pagina) ? 'active' : ''; ?>">
                            <a href="?page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="<?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                        <a href="?page=<?php echo $pagina + 1; ?>">
                            &raquo;
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- MODAL -->
<div id="modalDetalles" class="modal fade">
    <div class="modal-dialog modal-xl" style="width:95%; max-width:1400px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button"
                    class="close"
                    data-dismiss="modal">
                    &times;
                </button>
                <h4 class="modal-title">
                    Detalles del equipo
                </h4>
            </div>
            <div class="modal-body"
                style="max-height:80vh; overflow-y:auto;">
                <div id="contenido-detalles"></div>
            </div>
        </div>
    </div>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
