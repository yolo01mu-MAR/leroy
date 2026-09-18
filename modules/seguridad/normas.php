<?php

$page_title = 'Diagnóstico de Seguridad y Salud';

require_once __DIR__ . '/../../app/bootstrap.php';

// =========================================================
// CONSULTA
// =========================================================
$all_normas = find_all_normas();

// =========================================================
// CONTADORES
// =========================================================
$total_normas = count($all_normas);

$total_seguridad = 0;
$total_organizacion = 0;
$total_salud = 0;

foreach ($all_normas as $norma) {

    $clasificacion = strtolower(trim($norma['clasificacion']));

    if ($clasificacion === 'seguridad') {
        $total_seguridad++;
    }

    if (
        $clasificacion === 'organizacion' ||
        $clasificacion === 'organización'
    ) {
        $total_organizacion++;
    }

    if ($clasificacion === 'salud') {
        $total_salud++;
    }
}


// =========================================================
// CLASIFICACIONES PARA FILTRO
// =========================================================
$clasificaciones_unicas = [];

foreach ($all_normas as $norma) {

    $clasificacion = trim($norma['clasificacion']);

    if (
        $clasificacion !== '' &&
        !in_array($clasificacion, $clasificaciones_unicas)
    ) {
        $clasificaciones_unicas[] = $clasificacion;
    }
}

?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/normas.css">
<div class="seguridad-container">
    <!-- HEADER -->
    <div class="seguridad-header">
        <div class="row">
            <div class="col-sm-8">
                <h1 class="seguridad-header-title">
                    <span class="glyphicon glyphicon-warning-sign"></span>
                    DIAGNÓSTICO DE SEGURIDAD Y SALUD
                </h1>
                <p class="seguridad-header-subtitle">
                    CONTROL Y SEGUIMIENTO DEL CUMPLIMIENTO NORMATIVO
                </p>
            </div>
            <div class="col-sm-4 text-right">
                <a href="agregar_norma.php" class="btn btn-roy btn-agregar-norma">
                    <span class="glyphicon glyphicon-plus"></span>
                    AGREGAR NORMA
                </a>
            </div>
        </div>
    </div>

    <!-- HERRAMIENTAS -->
    <div class="herramientas-normas">
        <div class="row">
            <!-- BUSCADOR -->
            <div class="col-sm-5">
                <div class="busqueda-norma">
                    <span class="glyphicon glyphicon-search"></span>
                    <input
                        type="text"
                        id="buscarNorma"
                        class="form-control"
                        placeholder="Buscar por NOM o nombre..."
                        autocomplete="off"
                    >
                </div>
            </div>

            <!-- FILTROS -->
            <div class="col-sm-7">
                <div class="filtros-normas" id="filtrosNormas">
                    <button type="button" class="btn btn-filtro active" data-filtro="todas">
                        TODAS
                    </button>
                    <?php foreach ($clasificaciones_unicas as $clas): ?>
                        <button
                            type="button"
                            class="btn btn-filtro"
                            data-filtro="<?php echo strtolower(remove_junk($clas)); ?>"
                        >
                            <?php echo strtoupper(remove_junk($clas)); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTADO -->
    <div class="seccion-normas">
        <span class="seccion-normas-title">
            <span class="glyphicon glyphicon-list"></span>
            NORMAS Y REQUISITOS
        </span>
    </div>
    <div id="listaNormas">
        <?php foreach ($all_normas as $norma): ?>
            <?php
                $estilo = color_clasificacion(
                    $norma['clasificacion']
                );

                $clasificacion_data = strtolower(
                    trim(
                        remove_junk(
                            $norma['clasificacion']
                        )
                    )
                );
                $texto_busqueda = strtolower(
                    $norma['codigo_norma']
                    . ' '
                    . $norma['nombre_norma']
                );
            ?>
            <div
                class="norma-item"
                data-clasificacion="<?php echo $clasificacion_data; ?>"
                data-busqueda="<?php echo htmlspecialchars($texto_busqueda, ENT_QUOTES, 'UTF-8'); ?>"
            >
                <div class="row">
                    <!-- IMAGEN -->
                    <?php
                        $foto = !empty($norma['imagen'])
                            ? BASE_URL . '/uploads/normas/' . $norma['imagen']
                            : BASE_URL . '/uploads/normas/no_image.jpg';
                    ?>
                    <div class="col-xs-12 col-sm-2 col-md-1">
                        <div class="norma-imagen">
                            <?php if (!empty($norma['imagen'])): ?>
                                <img
                                    src="<?php echo $foto; ?>"
                                    alt="Norma"
                                >
                            <?php else: ?>
                                <span class="glyphicon glyphicon-picture"></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- INFORMACIÓN -->
                    <div class="col-xs-12 col-sm-5 col-md-5">
                        <span class="norma-codigo">
                            <?php echo remove_junk($norma['codigo_norma']);?>
                        </span>
                        <h3 class="norma-nombre">
                            <?php echo remove_junk($norma['nombre_norma']);?>
                        </h3>
                        <span 
                            class="norma-badge"
                            style="
                                background-color: <?php echo $estilo['bg']; ?>;
                                color: <?php echo $estilo['color']; ?>;
                                border: 1px solid <?php echo $estilo['border']; ?>;
                            "
                        >
                            <span class="glyphicon <?php echo $estilo['icon']; ?>"></span>
                            <?php echo strtoupper(remove_junk($norma['clasificacion']));?>
                        </span>
                    </div>

                    <!-- PUNTOS -->
                    <div class="col-xs-12 col-sm-2 col-md-2">
                        <div class="norma-puntos">
                            <span class="norma-puntos-numero">
                                <?php echo (int)$norma['puntos']; ?>
                            </span>
                            <span class="norma-puntos-label">
                                Requisitos
                            </span>
                        </div>
                    </div>

                    <!-- ACCIÓN -->
                    <div class="col-xs-12 col-sm-3 col-md-4 norma-acciones-col text-right">
                        <div class="norma-acciones">
                            <a
                                href="ver_norma.php?id=<?php echo (int)$norma['ID']; ?>"
                                class="btn btn-success btn-accion-norma"
                                title="Ver requisitos de la norma"
                            >
                                <span class="glyphicon glyphicon-list-alt"></span>
                                VER NORMA
                            </a>

                            <a
                                href="documentos.php?id=<?php echo (int)$norma['ID']; ?>"
                                class="btn btn-roy btn-accion-norma"
                                title="Ver documentos"
                            >
                                <span class="glyphicon glyphicon-folder-open"></span>
                                INSPECCIONES
                            </a>

                            <a
                                href="editar_norma.php?id=<?php echo (int)$norma['ID']; ?>"
                                class="btn btn-info btn-accion-norma"
                                title="Editar norma"
                            >
                                <span class="glyphicon glyphicon-pencil"></span>
                                EDITAR
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- SIN RESULTADOS -->
    <div class="sin-resultados" id="sinResultados">
        <span class="glyphicon glyphicon-search"></span>
        No se encontraron normas con los criterios seleccionados.
    </div>
</div>

<script>
    (function () {

        var botones = document.querySelectorAll(
            '#filtrosNormas .btn-filtro'
        );

        var normas = document.querySelectorAll(
            '#listaNormas .norma-item'
        );

        var buscador = document.getElementById(
            'buscarNorma'
        );

        var sinResultados = document.getElementById(
            'sinResultados'
        );

        var filtroActual = 'todas';


        function filtrarNormas() {

            var texto = buscador.value
                .toLowerCase()
                .trim();

            var visibles = 0;


            normas.forEach(function (norma) {

                var clasificacion =
                    norma.getAttribute(
                        'data-clasificacion'
                    );

                var busqueda =
                    norma.getAttribute(
                        'data-busqueda'
                    );


                var coincideFiltro =
                    filtroActual === 'todas' ||
                    clasificacion === filtroActual;


                var coincideBusqueda =
                    texto === '' ||
                    busqueda.indexOf(texto) !== -1;


                if (
                    coincideFiltro &&
                    coincideBusqueda
                ) {

                    norma.classList.remove(
                        'oculto'
                    );

                    visibles++;

                } else {

                    norma.classList.add(
                        'oculto'
                    );

                }

            });


            sinResultados.style.display =
                visibles === 0
                    ? 'block'
                    : 'none';

        }


        // =====================================================
        // FILTROS
        // =====================================================

        botones.forEach(function (boton) {

            boton.addEventListener(
                'click',
                function () {

                    filtroActual =
                        boton.getAttribute(
                            'data-filtro'
                        );


                    botones.forEach(function (b) {

                        b.classList.remove(
                            'active'
                        );

                    });


                    boton.classList.add(
                        'active'
                    );


                    filtrarNormas();

                }
            );

        });


        // =====================================================
        // BUSCADOR
        // =====================================================

        buscador.addEventListener(
            'input',
            function () {

                filtrarNormas();

            }
        );


    })();
</script>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>