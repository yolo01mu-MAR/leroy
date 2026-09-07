<?php

$page_title = 'Diagnóstico de Seguridad y Salud';

require_once __DIR__ . '/../../app/bootstrap.php';

// =========================================================
// CONSULTA
// =========================================================
$all_normas = find_all_normas();


// =========================================================
// COLORES POR CLASIFICACIÓN
// =========================================================
function color_clasificacion($clasificacion)
{
    $clas = strtolower(trim($clasificacion));

    $mapa = [
        'seguridad' => [
            'bg'     => '#eff6ff',
            'color'  => '#2563eb',
            'border' => '#bfdbfe',
            'icon'   => 'glyphicon-warning-sign'
        ],

        'organizacion' => [
            'bg'     => '#f5f3ff',
            'color'  => '#7c3aed',
            'border' => '#ddd6fe',
            'icon'   => 'glyphicon-cog'
        ],

        'organización' => [
            'bg'     => '#f5f3ff',
            'color'  => '#7c3aed',
            'border' => '#ddd6fe',
            'icon'   => 'glyphicon-cog'
        ],

        'salud' => [
            'bg'     => '#ecfdf5',
            'color'  => '#059669',
            'border' => '#a7f3d0',
            'icon'   => 'glyphicon-heart'
        ]
    ];

    return isset($mapa[$clas])
        ? $mapa[$clas]
        : [
            'bg'     => '#f8fafc',
            'color'  => '#64748b',
            'border' => '#e2e8f0',
            'icon'   => 'glyphicon-tag'
        ];
}


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
<style>

    /* =========================================================
    CONTENEDOR GENERAL
    ========================================================= */

    .seguridad-container {
        margin-bottom: 30px;
    }


    /* =========================================================
    HEADER DEL MÓDULO
    ========================================================= */

    .seguridad-header {
        background: #ffffff;
        border: 1px solid #eef2f5;
        border-radius: 8px;
        padding: 22px 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .seguridad-header-title {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
    }

    .seguridad-header-title .glyphicon {
        margin-right: 8px;
        color: #2c3e50;
    }

    .seguridad-header-subtitle {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
    }


    /* =========================================================
    BOTÓN AGREGAR
    ========================================================= */

    .btn-agregar-norma {
        margin-top: 4px;
    }


    /* =========================================================
    BARRA DE HERRAMIENTAS
    ========================================================= */

    .herramientas-normas {
        background: #ffffff;
        border: 1px solid #eef2f5;
        border-radius: 8px;
        padding: 15px 18px;
        margin-top: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.025);
    }

    .busqueda-norma {
        position: relative;
    }

    .busqueda-norma .glyphicon {
        position: absolute;
        left: 12px;
        top: 10px;
        color: #94a3b8;
        z-index: 2;
    }

    .busqueda-norma input {
        padding-left: 34px;
        border-radius: 6px;
        border-color: #cbd5e1;
        box-shadow: none;
    }

    .busqueda-norma input:focus {
        border-color: #94a3b8;
        box-shadow: none;
    }


    /* =========================================================
    FILTROS
    ========================================================= */
    .filtros-normas {
        text-align: right;
    }

    .btn-filtro {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        border-radius: 18px;
        padding: 6px 14px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 4px;
        margin-bottom: 4px;
        transition: all 0.2s ease;
    }

    .btn-filtro:hover {
        background: #f8fafc;
    }

    .btn-filtro.active {
        background: #f1c40f;
        color: #050000;
        border-color: #f1c40f;
    }


    /* =========================================================
    TITULO DE SECCIÓN
    ========================================================= */

    .seccion-normas {
        margin-bottom: 12px;
    }

    .seccion-normas-title {
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .seccion-normas-title .glyphicon {
        margin-right: 6px;
    }


    /* =========================================================
    FILA DE NORMA
    ========================================================= */

    .norma-item {
        position: relative;
        background: #ffffff;
        border: 1px solid #eef2f5;
        border-radius: 8px;
        margin-bottom: 12px;
        padding: 18px 20px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.025);
        transition: all 0.2s ease;
    }

    .norma-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 18px rgba(0,0,0,0.05);
        border-color: #e2e8f0;
    }


    /* =========================================================
    IMAGEN
    ========================================================= */

    .norma-imagen {
        width: 75px;
        height: 75px;
        border-radius: 7px;
        border: 1px solid #eef2f5;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .norma-imagen img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .norma-imagen .glyphicon {
        font-size: 24px;
        color: #cbd5e1;
    }


    /* =========================================================
    INFORMACIÓN
    ========================================================= */

    .norma-codigo {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #00277b;
        letter-spacing: .4px;
        margin-bottom: 5px;
    }

    .norma-nombre {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.45;
        color: #1e293b;
    }


    /* =========================================================
    BADGE
    ========================================================= */

    .norma-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 4px 9px;
        border-radius: 5px;
        margin-top: 9px;
    }


    /* =========================================================
    CONTADOR DE PUNTOS
    ========================================================= */

    .norma-puntos {
        text-align: center;
        padding: 0 10px;
    }

    .norma-puntos-numero {
        display: block;
        font-size: 21px;
        font-weight: 700;
        color: #0f172a;
    }

    .norma-puntos-label {
        display: block;
        font-size: 10px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .4px;
    }


    /* =========================================================
    BOTONES DE ACCIÓN
    ========================================================= */

    .norma-acciones-col {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        height: 100%;
        min-height: 75px;
    }

    .norma-acciones {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 6px;
        width: 100%;
    }

    .btn-accion-norma {
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 8px 10px;
        white-space: nowrap;
    }

    .btn-accion-norma .glyphicon {
        margin-right: 4px;
    }


    /* =========================================================
    MENSAJE SIN RESULTADOS
    ========================================================= */

    .sin-resultados {
        display: none;
        text-align: center;
        color: #94a3b8;
        padding: 50px 0;
    }

    .sin-resultados .glyphicon {
        font-size: 32px;
        display: block;
        margin-bottom: 10px;
    }


    /* =========================================================
    RESPONSIVE
    ========================================================= */

    @media (max-width: 991px) {

        .norma-acciones-col {
            min-height: auto;
            margin-top: 15px;
        }

        .norma-acciones {
            justify-content: flex-start;
            flex-wrap: wrap;
        }

    }


    @media (max-width: 767px) {

        .seguridad-header {
            padding: 18px;
        }

        .seguridad-header-title {
            font-size: 17px;
        }

        .btn-agregar-norma {
            margin-top: 15px;
            float: none !important;
        }

        .herramientas-normas {
            padding: 12px;
        }

        .filtros-normas {
            text-align: left;
            margin-top: 12px;
        }

        .btn-filtro {
            margin-left: 0;
            margin-right: 4px;
        }

        .norma-item {
            padding: 15px;
        }

        .norma-imagen {
            margin-bottom: 12px;
        }

        .norma-puntos {
            text-align: left;
            padding: 15px 0;
        }

        .norma-acciones {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 6px;
        }

        .btn-accion-norma {
            width: 100%;
            text-align: center;
        }

    }


    /* =========================================================
    OCULTAR
    ========================================================= */

    .norma-item.oculto {
        display: none;
    }

</style>

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
                <a href="add.php" class="btn btn-roy btn-agregar-norma">
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
                                DOCUMENTOS
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