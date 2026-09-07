<?php

$page_title = 'CAPTURAR INSPECCIÓN';

require_once __DIR__ . '/../../app/bootstrap.php';


// =========================================================
// VALIDAR ID DE INSPECCIÓN
// =========================================================

$id_inspeccion = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id_inspeccion <= 0) {

    $session->msg(
        "d=Inspección no válida.&type=danger"
    );

    redirect('index.php');
    exit;
}


// =========================================================
// OBTENER INSPECCIÓN
// =========================================================

$sql_inspeccion = "
    SELECT
        si.ID,
        si.id_norma,
        si.id_dep_plantilla,
        si.fecha,
        si.id_responsable,
        si.observaciones,
        si.estatus,
        si.punto_actual,
        si.fecha_finalizacion,

        sn.codigo_norma,
        sn.nombre_norma,

        sc.nombre AS clasificacion

    FROM seguridad_inspecciones si

    INNER JOIN seguridad_normas sn
        ON sn.ID = si.id_norma

    INNER JOIN seguridad_clasificacion sc
        ON sc.id = sn.id_clasificacion

    WHERE si.ID = {$id_inspeccion}

    LIMIT 1
";

$result_inspeccion = find_by_sql($sql_inspeccion);

if (empty($result_inspeccion)) {

    $session->msg(
        "d=La inspección no existe.&type=danger"
    );

    redirect('index.php');
    exit;
}

$inspeccion = $result_inspeccion[0];


// =========================================================
// VERIFICAR ESTATUS
// =========================================================

if ($inspeccion['estatus'] === 'FINALIZADA') {

    $session->msg(
        "d=Esta inspección ya fue finalizada.&type=danger"
    );

    redirect(
        "ver_inspeccion.php?id={$id_inspeccion}"
    );

    exit;
}


// =========================================================
// OBTENER ESTADOS
// =========================================================

$estados = find_by_sql("
    SELECT
        id,
        nombre
    FROM seguridad_estado
    ORDER BY id ASC
");


// =========================================================
// OBTENER RIESGOS
// =========================================================

$riesgos = find_by_sql("
    SELECT
        id,
        nombre
    FROM seguridad_riesgo
    ORDER BY id ASC
");


// =========================================================
// OBTENER PRIORIDADES
// =========================================================

$prioridades = find_by_sql("
    SELECT
        id,
        nombre
    FROM seguridad_prioridad
    ORDER BY id ASC
");


// =========================================================
// OBTENER PUNTOS
// =========================================================

$sql_puntos = "
    SELECT
        sip.id AS id_inspeccion_p,
        sip.id_norma_p,
        sip.id_estado,
        sip.documentacion,
        sip.observaciones,
        sip.accion_correctiva,
        sip.id_responsable,
        sip.fecha_compromiso,
        sip.id_riesgo,
        sip.id_prioridad,
        snp.`no` AS punto,
        snp.desc_requisito AS requisito,
        snp.tipo_comprobacion AS comprobacion,
        snp.desc_evidencia AS evidencia
    FROM seguridad_inspecciones_p sip
    INNER JOIN seguridad_normas_p snp ON snp.ID = sip.id_norma_p
    WHERE sip.id_inspeccion = {$id_inspeccion}
    ORDER BY snp.`no` ASC
";

$puntos = find_by_sql($sql_puntos);

$total_puntos = count($puntos);


// =========================================================
// VALIDAR QUE EXISTAN PUNTOS
// =========================================================

if ($total_puntos <= 0) {

    $session->msg(
        "d=Esta inspección no tiene puntos para evaluar.&type=danger"
    );

    redirect(
        "ver_norma.php?id={$inspeccion['id_norma']}"
    );

    exit;
}


// =========================================================
// DETERMINAR PUNTO ACTUAL
// =========================================================

$punto_actual = (int) $inspeccion['punto_actual'];

if ($punto_actual < 1) {
    $punto_actual = 1;
}

if ($punto_actual > $total_puntos) {
    $punto_actual = $total_puntos;
}


// Índice del array
$indice_actual = $punto_actual - 1;

$punto = $puntos[$indice_actual];


// =========================================================
// PROGRESO
// =========================================================

$puntos_evaluados = 0;

foreach ($puntos as $p) {

    if (!empty($p['id_estado'])) {
        $puntos_evaluados++;
    }
}

$avance = $total_puntos > 0
    ? round(($puntos_evaluados / $total_puntos) * 100)
    : 0;


// =========================================================
// DETERMINAR SI ES ÚLTIMO
// =========================================================

$es_ultimo = ($punto_actual >= $total_puntos);


// =========================================================
// TÍTULO
// =========================================================

$page_title =
    'INSPECCIÓN: ' .
    remove_junk($inspeccion['codigo_norma']);

?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>


<style>

/* =========================================================
   CONTENEDOR
========================================================= */

.capturar-inspeccion-container {
    margin-bottom: 30px;
}


/* =========================================================
   HEADER
========================================================= */

.inspeccion-header {
    background: #ffffff;

    border: 1px solid #eef2f5;
    border-radius: 8px;

    padding: 20px 25px;

    margin-bottom: 20px;

    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.inspeccion-header-codigo {
    display: block;

    font-size: 14px;
    font-weight: 700;

    color: #2563eb;

    letter-spacing: .4px;

    margin-bottom: 5px;
}

.inspeccion-header-nombre {
    margin: 0;

    font-size: 18px;
    font-weight: 700;

    line-height: 1.45;

    color: #1e293b;
}

.inspeccion-header-meta {
    margin-top: 10px;

    font-size: 11px;

    color: #64748b;
}

.inspeccion-header-meta span {
    margin-right: 18px;
}

.inspeccion-header-meta .glyphicon {
    margin-right: 4px;
}


/* =========================================================
   PROGRESO
========================================================= */

.inspeccion-progreso {
    background: #ffffff;

    border: 1px solid #eef2f5;
    border-radius: 8px;

    padding: 15px 20px;

    margin-bottom: 20px;

    box-shadow: 0 3px 10px rgba(0,0,0,0.025);
}

.progreso-info {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 8px;
}

.progreso-texto {
    font-size: 11px;

    font-weight: 700;

    color: #475569;

    text-transform: uppercase;
}

.progreso-porcentaje {
    font-size: 11px;

    font-weight: 700;

    color: #2563eb;
}

.progreso-barra {
    height: 7px;

    background: #e2e8f0;

    border-radius: 10px;

    overflow: hidden;
}

.progreso-barra-llenado {
    height: 100%;

    background: #2563eb;

    border-radius: 10px;

    transition: width .3s ease;
}


/* =========================================================
   TARJETA DEL PUNTO
========================================================= */

.punto-card {
    background: #ffffff;

    border: 1px solid #eef2f5;

    border-radius: 8px;

    padding: 22px 25px;

    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}


/* =========================================================
   CABECERA DEL PUNTO
========================================================= */

.punto-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 18px;
}

.punto-numero {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-width: 55px;

    padding: 7px 10px;

    background: #f1f5f9;

    border: 1px solid #e2e8f0;

    border-radius: 5px;

    color: #334155;

    font-size: 13px;

    font-weight: 700;
}

.punto-contador {
    font-size: 11px;

    color: #94a3b8;

    font-weight: 600;
}


/* =========================================================
   REQUISITO
========================================================= */

.punto-requisito {
    margin-bottom: 20px;
}

.punto-label {
    display: block;

    font-size: 10px;

    font-weight: 700;

    color: #94a3b8;

    text-transform: uppercase;

    letter-spacing: .5px;

    margin-bottom: 6px;
}

.punto-requisito-texto {
    margin: 0;

    font-size: 14px;

    line-height: 1.65;

    color: #1e293b;
}


/* =========================================================
   COMPROBACIÓN
========================================================= */

.punto-comprobacion {
    margin-bottom: 20px;
}

.punto-comprobacion-badge {
    display: inline-block;

    padding: 5px 10px;

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 5px;

    color: #475569;

    font-size: 10px;

    font-weight: 700;

    text-transform: uppercase;
}


/* =========================================================
   EVIDENCIA
========================================================= */

.punto-evidencia {
    background: #f8fafc;

    border: 1px solid #eef2f5;

    border-radius: 6px;

    padding: 12px 15px;

    margin-bottom: 22px;
}

.punto-evidencia-texto {
    margin: 0;

    font-size: 12px;

    line-height: 1.6;

    color: #64748b;
}


/* =========================================================
   FORMULARIO
========================================================= */

.punto-form {
    border-top: 1px solid #f1f5f9;

    padding-top: 20px;
}

.punto-form label {
    font-size: 10px;

    font-weight: 700;

    color: #475569;

    text-transform: uppercase;
}

.punto-form .form-control {
    border-radius: 6px;

    border-color: #cbd5e1;

    box-shadow: none;

    font-size: 12px;
}

.punto-form .form-control:focus {
    border-color: #94a3b8;

    box-shadow: none;
}


/* =========================================================
   BOTONES
========================================================= */

.inspeccion-acciones {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 8px;

    margin-top: 20px;

    padding-top: 18px;

    border-top: 1px solid #f1f5f9;
}

.inspeccion-acciones-izquierda,
.inspeccion-acciones-derecha {
    display: flex;

    align-items: center;

    gap: 6px;
}

.btn-inspeccion {
    border-radius: 5px;

    font-size: 11px;

    font-weight: 600;

    padding: 8px 12px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 767px) {

    .inspeccion-header {
        padding: 18px;
    }

    .inspeccion-header-nombre {
        font-size: 16px;
    }

    .inspeccion-header-meta span {
        display: block;

        margin-bottom: 5px;
    }

    .punto-card {
        padding: 17px;
    }

    .punto-header {
        align-items: flex-start;
    }

    .inspeccion-acciones {
        flex-direction: column;

        align-items: stretch;
    }

    .inspeccion-acciones-izquierda,
    .inspeccion-acciones-derecha {
        width: 100%;
    }

    .inspeccion-acciones .btn-inspeccion {
        flex: 1;
    }

}

</style>


<div class="capturar-inspeccion-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="inspeccion-header">

        <span class="inspeccion-header-codigo">

            <?php
            echo remove_junk(
                $inspeccion['codigo_norma']
            );
            ?>

        </span>


        <h1 class="inspeccion-header-nombre">

            <?php
            echo remove_junk(
                $inspeccion['nombre_norma']
            );
            ?>

        </h1>


        <div class="inspeccion-header-meta">

            <span>

                <span class="glyphicon glyphicon-calendar"></span>

                <?php
                echo date(
                    'd/m/Y',
                    strtotime($inspeccion['fecha'])
                );
                ?>

            </span>


            <span>

                <span class="glyphicon glyphicon-tag"></span>

                <?php
                echo strtoupper(
                    remove_junk(
                        $inspeccion['clasificacion']
                    )
                );
                ?>

            </span>


            <span>

                <span class="glyphicon glyphicon-edit"></span>

                INSPECCIÓN EN PROCESO

            </span>

        </div>

    </div>



    <!-- =====================================================
         PROGRESO
    ====================================================== -->

    <div class="inspeccion-progreso">

        <div class="progreso-info">

            <span class="progreso-texto">

                PUNTO
                <?php echo $punto_actual; ?>
                DE
                <?php echo $total_puntos; ?>

            </span>


            <span class="progreso-porcentaje">

                <?php echo $avance; ?>%

            </span>

        </div>


        <div class="progreso-barra">

            <div
                class="progreso-barra-llenado"
                style="width: <?php echo $avance; ?>%;"
            ></div>

        </div>

    </div>



    <!-- =====================================================
         TARJETA
    ====================================================== -->

    <div class="punto-card">


        <!-- CABECERA -->

        <div class="punto-header">

            <span class="punto-numero">

                <?php
                echo remove_junk(
                    $punto['punto']
                );
                ?>

            </span>


            <span class="punto-contador">

                <?php echo $punto_actual; ?>
                /
                <?php echo $total_puntos; ?>

            </span>

        </div>



        <!-- REQUISITO -->

        <div class="punto-requisito">

            <span class="punto-label">
                REQUISITO
            </span>

            <p class="punto-requisito-texto">

                <?php
                echo remove_junk(
                    $punto['requisito']
                );
                ?>

            </p>

        </div>



        <!-- COMPROBACIÓN -->

        <div class="punto-comprobacion">

            <span class="punto-label">
                TIPO DE COMPROBACIÓN
            </span>

            <span class="punto-comprobacion-badge">

                <span class="glyphicon glyphicon-search"></span>

                <?php
                echo remove_junk(
                    $punto['comprobacion']
                );
                ?>

            </span>

        </div>



        <!-- EVIDENCIA -->

        <div class="punto-evidencia">

            <span class="punto-label">
                EVIDENCIA REQUERIDA
            </span>

            <p class="punto-evidencia-texto">

                <?php
                echo remove_junk(
                    $punto['evidencia']
                );
                ?>

            </p>

        </div>



        <!-- =================================================
             FORMULARIO DEL PUNTO
        ================================================== -->

        <form
            method="POST"
            action="guardar_inspeccion_p.php"
            class="punto-form"
            id="formPunto"
        >

            <input
                type="hidden"
                name="id_inspeccion"
                value="<?php echo $id_inspeccion; ?>"
            >

            <input
                type="hidden"
                name="id_inspeccion_p"
                value="<?php echo (int)$punto['id_inspeccion_p']; ?>"
            >

            <input
                type="hidden"
                name="punto_actual"
                value="<?php echo $punto_actual; ?>"
            >


            <!-- DOCUMENTACIÓN -->

            <div class="form-group">

                <label for="documentacion">
                    DOCUMENTACIÓN / EVIDENCIA
                </label>

                <textarea
                    name="documentacion"
                    id="documentacion"
                    class="form-control"
                    rows="2"
                    placeholder="Indica el documento o evidencia encontrada..."
                ><?php
                    echo !empty($punto['documentacion'])
                        ? remove_junk($punto['documentacion'])
                        : '';
                ?></textarea>

            </div>



            <!-- ESTADO -->

            <div class="form-group">

                <label for="id_estado">
                    ESTADO
                </label>

                <select
                    name="id_estado"
                    id="id_estado"
                    class="form-control"
                    required
                >

                    <option value="">
                        SELECCIONAR ESTADO
                    </option>

                    <?php foreach ($estados as $estado): ?>

                        <option
                            value="<?php echo (int)$estado['id']; ?>"
                            <?php
                            echo (
                                (int)$punto['id_estado']
                                === (int)$estado['id']
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >

                            <?php
                            echo strtoupper(
                                remove_junk(
                                    $estado['nombre']
                                )
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>



            <!-- CAMPOS CORRECTIVOS -->

            <div
                id="camposCorrectivos"
                style="<?php
                    echo empty($punto['id_estado'])
                        ? 'display:none;'
                        : '';
                ?>"
            >


                <!-- OBSERVACIONES -->

                <div class="form-group">

                    <label for="observaciones">
                        OBSERVACIONES
                    </label>

                    <textarea
                        name="observaciones"
                        id="observaciones"
                        class="form-control"
                        rows="3"
                        placeholder="Describe los hallazgos encontrados..."
                    ><?php
                        echo !empty($punto['observaciones'])
                            ? remove_junk($punto['observaciones'])
                            : '';
                    ?></textarea>

                </div>


                <!-- ACCIÓN -->

                <div class="form-group">

                    <label for="accion_correctiva">
                        ACCIÓN CORRECTIVA
                    </label>

                    <textarea
                        name="accion_correctiva"
                        id="accion_correctiva"
                        class="form-control"
                        rows="3"
                        placeholder="Indica la acción correctiva..."
                    ><?php
                        echo !empty($punto['accion_correctiva'])
                            ? remove_junk($punto['accion_correctiva'])
                            : '';
                    ?></textarea>

                </div>


                <div class="row">


                    <!-- RESPONSABLE -->

                    <div class="col-xs-12 col-sm-4">

                        <div class="form-group">

                            <label for="responsable">
                                RESPONSABLE
                            </label>

                            <input
                                type="text"
                                name="responsable"
                                id="responsable"
                                class="form-control"
                                value="<?php
                                    echo !empty($punto['responsable'])
                                        ? remove_junk(
                                            $punto['responsable']
                                        )
                                        : '';
                                ?>"
                            >

                        </div>

                    </div>


                    <!-- FECHA -->

                    <div class="col-xs-12 col-sm-4">

                        <div class="form-group">

                            <label for="fecha_compromiso">
                                FECHA COMPROMISO
                            </label>

                            <input
                                type="date"
                                name="fecha_compromiso"
                                id="fecha_compromiso"
                                class="form-control"
                                value="<?php
                                    echo !empty(
                                        $punto['fecha_compromiso']
                                    )
                                        ? $punto['fecha_compromiso']
                                        : '';
                                ?>"
                            >

                        </div>

                    </div>


                    <!-- RIESGO -->

                    <div class="col-xs-12 col-sm-2">

                        <div class="form-group">

                            <label for="id_riesgo">
                                RIESGO
                            </label>

                            <select
                                name="id_riesgo"
                                id="id_riesgo"
                                class="form-control"
                            >

                                <option value="">
                                    N/A
                                </option>

                                <?php foreach ($riesgos as $riesgo): ?>

                                    <option
                                        value="<?php echo (int)$riesgo['id']; ?>"
                                        <?php
                                        echo (
                                            (int)$punto['id_riesgo']
                                            === (int)$riesgo['id']
                                        )
                                            ? 'selected'
                                            : '';
                                        ?>
                                    >

                                        <?php
                                        echo strtoupper(
                                            remove_junk(
                                                $riesgo['nombre']
                                            )
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>


                    <!-- PRIORIDAD -->

                    <div class="col-xs-12 col-sm-2">

                        <div class="form-group">

                            <label for="id_prioridad">
                                PRIORIDAD
                            </label>

                            <select
                                name="id_prioridad"
                                id="id_prioridad"
                                class="form-control"
                            >

                                <option value="">
                                    N/A
                                </option>

                                <?php foreach ($prioridades as $prioridad): ?>

                                    <option
                                        value="<?php echo (int)$prioridad['id']; ?>"
                                        <?php
                                        echo (
                                            (int)$punto['id_prioridad']
                                            === (int)$prioridad['id']
                                        )
                                            ? 'selected'
                                            : '';
                                        ?>
                                    >

                                        <?php
                                        echo strtoupper(
                                            remove_junk(
                                                $prioridad['nombre']
                                            )
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 BOTONES
            ================================================== -->

            <div class="inspeccion-acciones">


                <!-- IZQUIERDA -->

                <div class="inspeccion-acciones-izquierda">

                    <?php if ($punto_actual > 1): ?>

                        <button
                            type="submit"
                            name="accion"
                            value="anterior"
                            class="btn btn-default btn-inspeccion"
                        >

                            <span class="glyphicon glyphicon-chevron-left"></span>

                            ANTERIOR

                        </button>

                    <?php endif; ?>

                </div>



                <!-- DERECHA -->

                <div class="inspeccion-acciones-derecha">


                    <!-- GUARDAR PARCIAL -->

                    <button
                        type="submit"
                        name="accion"
                        value="parcial"
                        class="btn btn-default btn-inspeccion"
                    >

                        <span class="glyphicon glyphicon-floppy-disk"></span>

                        GUARDAR PARCIAL

                    </button>


                    <?php if ($es_ultimo): ?>

                        <button
                            type="submit"
                            name="accion"
                            value="finalizar"
                            class="btn btn-success btn-inspeccion"
                            onclick="
                                return confirm(
                                    '¿Estás seguro de que deseas finalizar esta inspección?'
                                );
                            "
                        >

                            <span class="glyphicon glyphicon-ok"></span>

                            FINALIZAR INSPECCIÓN

                        </button>

                    <?php else: ?>

                        <button
                            type="submit"
                            name="accion"
                            value="siguiente"
                            class="btn btn-info btn-inspeccion"
                        >

                            SIGUIENTE

                            <span class="glyphicon glyphicon-chevron-right"></span>

                        </button>

                    <?php endif; ?>


                </div>

            </div>


        </form>


    </div>

</div>



<script>

document.addEventListener('DOMContentLoaded', function () {

    var estado = document.getElementById('id_estado');
    var campos = document.getElementById('camposCorrectivos');

    if (!estado || !campos) {
        return;
    }


    function actualizarCampos() {

        if (estado.value === '') {

            campos.style.display = 'none';

        } else {

            campos.style.display = 'block';

        }

    }


    estado.addEventListener(
        'change',
        actualizarCampos
    );


    actualizarCampos();

});

</script>


<?php include_once BASE_PATH . '/layouts/footer.php'; ?>