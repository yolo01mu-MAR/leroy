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


                                            <button
                                                type="button"
                                                class="btn btn-primary btn-block"
                                                onclick="probarConexion(<?php echo $dispositivo['id']; ?>)"
                                            >

                                                <span class="glyphicon glyphicon-transfer"></span>

                                                Probar conexión

                                            </button>


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

</script>


<?php include_once('../../layouts/footer.php'); ?>