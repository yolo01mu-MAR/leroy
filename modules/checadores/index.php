<?php

$page_title = 'Checadores';

require_once __DIR__ . '/../../app/bootstrap.php';
page_require_level(1);

global $db;


/*
|--------------------------------------------------------------------------
| Obtener dispositivos
|--------------------------------------------------------------------------
*/

$sql = "SELECT *
        FROM checadores_dispositivos
        WHERE activo = 1
        ORDER BY nombre ASC";

$dispositivos = find_by_sql($sql);

?>

<?php include_once('../../layouts/header.php'); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>
                        <span class="glyphicon glyphicon-time"></span>
                        Checadores
                    </strong>
                </div>
                <div class="panel-body">
                    <?php if (empty($dispositivos)): ?>
                        <div class="alert alert-warning">
                            No hay checadores activos registrados.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($dispositivos as $dispositivo): ?>
                                <div class="col-md-4">
                                    <div class="panel panel-default">

                                        <div class="panel-heading">

                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $dispositivo['nombre']
                                                );
                                                ?>

                                            </strong>

                                        </div>


                                        <div class="panel-body">


                                            <p>

                                                <strong>Modelo:</strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $dispositivo['modelo']
                                                );
                                                ?>

                                            </p>


                                            <p>

                                                <strong>IP:</strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $dispositivo['ip']
                                                );
                                                ?>

                                            </p>


                                            <p>

                                                <strong>Puerto:</strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $dispositivo['puerto']
                                                );
                                                ?>

                                            </p>


                                            <p>

                                                <strong>Estado:</strong>

                                                <span
                                                    class="label label-default"
                                                    id="estado-<?php echo $dispositivo['id']; ?>"
                                                >

                                                    Sin comprobar

                                                </span>

                                            </p>


                                            <hr>


                                            <div class="row">

                                                <div class="col-sm-6">
                                                    <button
                                                        type="button"
                                                        class="btn btn-primary btn-block"
                                                        onclick="probarConexion(<?php echo $dispositivo['id']; ?>)"
                                                    >
                                                        <span class="glyphicon glyphicon-transfer"></span>
                                                        Probar conexión
                                                    </button>
                                                </div>

                                                <div class="col-sm-6">
                                                    <button
                                                        type="button"
                                                        class="btn btn-success btn-block"
                                                        onclick="sincronizar(<?php echo $dispositivo['id']; ?>)"
                                                    >
                                                        <span class="glyphicon glyphicon-download-alt"></span>
                                                        Sincronizar
                                                    </button>
                                                </div>

                                            </div>

                                            <div
                                                id="resultado-sync-<?php echo $dispositivo['id']; ?>"
                                                style="margin-top:15px;"
                                            ></div>


                                        </div>

                                    </div>

                                </div>


                            <?php endforeach; ?>

                        </div>


                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

function probarConexion(id) {

    const estado = document.getElementById(
        'estado-' + id
    );

    estado.className = 'label label-warning';

    estado.innerHTML = 'Comprobando...';


    fetch('probar_conexion.php?id=' + id)

        .then(response => response.json())

        .then(data => {

            if (data.ok) {

                estado.className =
                    'label label-success';

                estado.innerHTML =
                    'Conectado';

            } else {

                estado.className =
                    'label label-danger';

                estado.innerHTML =
                    'Sin conexión';

            }

        })

        .catch(error => {

            console.error(error);

            estado.className =
                'label label-danger';

            estado.innerHTML =
                'Error';

        });

}
function sincronizar(id) {

    const resultado = document.getElementById(
        'resultado-sync-' + id
    );

    resultado.innerHTML = `
        <div class="alert alert-info">
            <span class="glyphicon glyphicon-refresh"></span>
            Sincronizando...
        </div>
    `;


    fetch('sincronizar.php?id=' + id)

        .then(response => response.json())

        .then(data => {

            if (!data.ok) {

                resultado.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong>
                        ${data.error || 'No fue posible sincronizar.'}
                    </div>
                `;

                return;
            }


            const r = data.resumen;


            resultado.innerHTML = `
                <div class="alert alert-success">

                    <strong>
                        Sincronización completada
                    </strong>

                    <hr style="margin:8px 0;">

                    <div>
                        Encontrados:
                        <strong>${r.encontrados}</strong>
                    </div>

                    <div>
                        Nuevos:
                        <strong>${r.nuevos}</strong>
                    </div>

                    <div>
                        Ya existentes:
                        <strong>${r.existentes}</strong>
                    </div>

                    <div>
                        Errores:
                        <strong>${r.errores}</strong>
                    </div>

                </div>
            `;

        })

        .catch(error => {

            console.error(error);

            resultado.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Error:</strong>
                    No fue posible comunicarse con el servidor.
                </div>
            `;

        });

}
</script>


<?php include_once('../../layouts/footer.php'); ?>