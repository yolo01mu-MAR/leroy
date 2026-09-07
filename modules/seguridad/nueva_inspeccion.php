<?php

$page_title = 'NUEVA INSPECCIÓN';

require_once __DIR__ . '/../../app/bootstrap.php';


// =========================================================
// VALIDAR NORMA
// =========================================================

$id_norma = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id_norma <= 0) {
    $session->msg(
        "d=Norma no válida.&type=danger"
    );

    redirect('normas.php');
    exit;
}


// =========================================================
// OBTENER NORMA
// =========================================================

$sql_norma = "
    SELECT
        sn.ID,
        sc.nombre AS clasificacion,
        sn.codigo_norma,
        sn.nombre_norma,
        sn.imagen
    FROM seguridad_normas sn
    INNER JOIN seguridad_clasificacion sc
        ON sc.id = sn.id_clasificacion
    WHERE sn.ID = {$id_norma}
    LIMIT 1
";

$result_norma = find_by_sql($sql_norma);

if (empty($result_norma)) {

    $session->msg(
        "d=La norma no existe.&type=danger"
    );

    redirect('normas.php');
    exit;
}

$norma = $result_norma[0];

$page_title = 'NUEVA INSPECCIÓN: ' . remove_junk($norma['codigo_norma']);


// =========================================================
// CONTAR PUNTOS
// =========================================================

$sql_total_puntos = "
    SELECT COUNT(*) AS total
    FROM seguridad_normas_p
    WHERE id_norma = {$id_norma}
";

$result_total = find_by_sql($sql_total_puntos);

$total_puntos = (int) $result_total[0]['total'];


// =========================================================
// PROCESAR FORMULARIO
// =========================================================

if (isset($_POST['crear_inspeccion'])) {

    $fecha = isset($_POST['fecha'])
        ? remove_junk($_POST['fecha'])
        : '';

    $id_dep_plantilla = isset($_POST['id_dep_plantilla'])
        ? (int) $_POST['id_dep_plantilla']
        : 0;

    $id_responsable = isset($_POST['id_responsable'])
        ? (int) $_POST['id_responsable']
        : 0;


    // -----------------------------------------------------
    // VALIDACIONES
    // -----------------------------------------------------

    if (empty($fecha)) {

        $session->msg(
            "d=Debes indicar la fecha de la inspección.&type=danger"
        );

        redirect("nueva_inspeccion.php?id={$id_norma}");
        exit;
    }


    if ($total_puntos <= 0) {

        $session->msg(
            "d=Esta norma no tiene requisitos registrados.&type=danger"
        );

        redirect("ver_norma.php?id={$id_norma}");
        exit;
    }


    // -----------------------------------------------------
    // CREAR INSPECCIÓN
    // -----------------------------------------------------

    $fecha_sql = $db->escape($fecha);

    $sql_inspeccion = "
        INSERT INTO seguridad_inspecciones (
            id_norma,
            id_dep_plantilla,
            fecha,
            id_responsable,
            estatus,
            punto_actual
        )
        VALUES (
            {$id_norma},
            " . ($id_dep_plantilla > 0 ? $id_dep_plantilla : "NULL") . ",
            '{$fecha_sql}',
            " . ($id_responsable > 0 ? $id_responsable : "NULL") . ",
            'EN_PROCESO',
            1
        )
    ";

    if (!$db->query($sql_inspeccion)) {

        $session->msg(
            "d=No fue posible crear la inspección.&type=danger"
        );

        redirect("nueva_inspeccion.php?id={$id_norma}");
        exit;
    }


    // -----------------------------------------------------
    // ID DE LA INSPECCIÓN
    // -----------------------------------------------------

    $id_inspeccion = $db->insert_id();


    // -----------------------------------------------------
    // CREAR LOS PUNTOS DE LA INSPECCIÓN
    // -----------------------------------------------------

    $sql_puntos = "
        INSERT INTO seguridad_inspecciones_p (
            id_inspeccion,
            id_norma_p
        )
        SELECT
            {$id_inspeccion},
            snp.ID
        FROM seguridad_normas_p snp
        WHERE snp.id_norma = {$id_norma}
        ORDER BY snp.`no`
    ";

    if (!$db->query($sql_puntos)) {

        // Si no se pudieron crear los puntos,
        // eliminamos la cabecera para no dejar
        // una inspección incompleta.

        $db->query("
            DELETE FROM seguridad_inspecciones
            WHERE ID = {$id_inspeccion}
        ");

        $session->msg(
            "d=No fue posible generar los puntos de la inspección.&type=danger"
        );

        redirect("nueva_inspeccion.php?id={$id_norma}");
        exit;
    }


    // -----------------------------------------------------
    // TODO CORRECTO
    // -----------------------------------------------------

    $session->msg(
        "s=Inspección creada correctamente. Puedes comenzar la evaluación.&type=success"
    );

    redirect(
        "capturar_inspeccion.php?id={$id_inspeccion}"
    );

    exit;
}

?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>


<style>

/* =========================================================
   CONTENEDOR
========================================================= */

.nueva-inspeccion-container {
    margin-bottom: 30px;
}


/* =========================================================
   HEADER
========================================================= */

.nueva-inspeccion-header {
    background: #ffffff;
    border: 1px solid #eef2f5;
    border-radius: 8px;

    padding: 22px 25px;
    margin-bottom: 20px;

    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.nueva-inspeccion-codigo {
    display: block;

    font-size: 14px;
    font-weight: 700;

    color: #2563eb;

    letter-spacing: .4px;

    margin-bottom: 6px;
}

.nueva-inspeccion-titulo {
    margin: 0;

    font-size: 19px;
    font-weight: 700;

    line-height: 1.45;

    color: #1e293b;
}

.nueva-inspeccion-clasificacion {
    display: inline-block;

    margin-top: 10px;

    padding: 5px 10px;

    border-radius: 5px;

    background: #eff6ff;
    border: 1px solid #bfdbfe;

    color: #2563eb;

    font-size: 10px;
    font-weight: 700;

    text-transform: uppercase;
}


/* =========================================================
   FORMULARIO
========================================================= */

.inspeccion-form-card {
    background: #ffffff;

    border: 1px solid #eef2f5;
    border-radius: 8px;

    padding: 22px 25px;

    box-shadow: 0 3px 10px rgba(0,0,0,0.025);
}

.inspeccion-form-title {
    margin: 0 0 18px;

    font-size: 13px;
    font-weight: 700;

    color: #475569;

    text-transform: uppercase;
    letter-spacing: .5px;
}

.inspeccion-form-title .glyphicon {
    margin-right: 6px;
}

.inspeccion-form-card .form-group label {
    font-size: 11px;
    font-weight: 600;

    color: #475569;

    text-transform: uppercase;
}

.inspeccion-form-card .form-control {
    border-radius: 6px;

    border-color: #cbd5e1;

    box-shadow: none;
}

.inspeccion-form-card .form-control:focus {
    border-color: #94a3b8;
    box-shadow: none;
}


/* =========================================================
   RESUMEN DE PUNTOS
========================================================= */

.inspeccion-puntos-resumen {
    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 6px;

    padding: 12px 15px;

    margin-bottom: 20px;
}

.inspeccion-puntos-numero {
    font-size: 20px;
    font-weight: 700;

    color: #0f172a;
}

.inspeccion-puntos-label {
    margin-left: 6px;

    font-size: 11px;
    color: #64748b;

    text-transform: uppercase;
}


/* =========================================================
   BOTONES
========================================================= */

.inspeccion-form-acciones {
    margin-top: 20px;

    padding-top: 15px;

    border-top: 1px solid #f1f5f9;

    text-align: right;
}

.btn-inspeccion {
    border-radius: 5px;

    font-size: 11px;
    font-weight: 600;

    padding: 8px 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 767px) {

    .nueva-inspeccion-header {
        padding: 18px;
    }

    .nueva-inspeccion-titulo {
        font-size: 16px;
    }

    .inspeccion-form-card {
        padding: 18px;
    }

    .inspeccion-form-acciones {
        text-align: left;
    }

}

</style>


<div class="nueva-inspeccion-container">


    <!-- =====================================================
         INFORMACIÓN DE LA NORMA
    ====================================================== -->

    <div class="nueva-inspeccion-header">

        <span class="nueva-inspeccion-codigo">

            <?php
            echo remove_junk($norma['codigo_norma']);
            ?>

        </span>

        <h1 class="nueva-inspeccion-titulo">

            <?php
            echo remove_junk($norma['nombre_norma']);
            ?>

        </h1>

        <span class="nueva-inspeccion-clasificacion">

            <span class="glyphicon glyphicon-tag"></span>

            <?php
            echo strtoupper(
                remove_junk($norma['clasificacion'])
            );
            ?>

        </span>

    </div>


    <!-- =====================================================
         FORMULARIO
    ====================================================== -->

    <div class="inspeccion-form-card">

        <h2 class="inspeccion-form-title">

            <span class="glyphicon glyphicon-clipboard"></span>

            NUEVA INSPECCIÓN

        </h2>


        <!-- RESUMEN -->

        <div class="inspeccion-puntos-resumen">

            <span class="inspeccion-puntos-numero">
                <?php echo $total_puntos; ?>
            </span>

            <span class="inspeccion-puntos-label">
                REQUISITOS SERÁN EVALUADOS
            </span>

        </div>


        <form
            method="POST"
            action="nueva_inspeccion.php?id=<?php echo $id_norma; ?>"
        >


            <div class="row">


                <!-- FECHA -->

                <div class="col-xs-12 col-sm-4">

                    <div class="form-group">

                        <label for="fecha">
                            FECHA DE INSPECCIÓN
                        </label>

                        <input
                            type="date"
                            name="fecha"
                            id="fecha"
                            class="form-control"
                            value="<?php echo date('Y-m-d'); ?>"
                            required
                        >

                    </div>

                </div>


                <!-- ÁREA -->

                <div class="col-xs-12 col-sm-4">

                    <div class="form-group">

                        <label for="id_dep_plantilla">
                            ÁREA / DEPARTAMENTO
                        </label>

                        <select
                            name="id_dep_plantilla"
                            id="id_dep_plantilla"
                            class="form-control"
                        >

                            <option value="">
                                SELECCIONAR
                            </option>

                            <!--
                                AQUÍ CONECTAREMOS LA CONSULTA
                                DE dep_plantilla DE LE ROY.
                            -->

                        </select>

                    </div>

                </div>


                <!-- RESPONSABLE -->

                <div class="col-xs-12 col-sm-4">

                    <div class="form-group">

                        <label for="id_responsable">
                            RESPONSABLE
                        </label>

                        <select
                            name="id_responsable"
                            id="id_responsable"
                            class="form-control"
                        >

                            <option value="">
                                SELECCIONAR
                            </option>

                            <!--
                                AQUÍ CONECTAREMOS LOS USUARIOS
                                RESPONSABLES DEL SISTEMA.
                            -->

                        </select>

                    </div>

                </div>

            </div>


            <!-- ACCIONES -->

            <div class="inspeccion-form-acciones">

                <a
                    href="ver_norma.php?id=<?php echo $id_norma; ?>"
                    class="btn btn-default btn-inspeccion"
                >

                    <span class="glyphicon glyphicon-arrow-left"></span>

                    CANCELAR

                </a>


                <button
                    type="submit"
                    name="crear_inspeccion"
                    class="btn btn-info btn-inspeccion"
                >

                    <span class="glyphicon glyphicon-play"></span>

                    INICIAR INSPECCIÓN

                </button>

            </div>


        </form>

    </div>

</div>


<?php include_once BASE_PATH . '/layouts/footer.php'; ?>