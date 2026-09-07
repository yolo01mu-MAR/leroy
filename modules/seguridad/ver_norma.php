<?php



require_once __DIR__ . '/../../app/bootstrap.php';


// =========================================================
// VALIDAR ID
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
    WHERE sn.id = {$id_norma}
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

$page_title = remove_junk($norma['codigo_norma']);


// =========================================================
// OBTENER PUNTOS
// =========================================================

$sql_puntos = "
    SELECT
        snp.`no` AS punto,
        snp.desc_requisito AS requisito,
        snp.tipo_comprobacion AS comprobacion,
        snp.desc_evidencia AS evidencia
    FROM seguridad_normas_p snp
    WHERE snp.id_norma = {$id_norma}
    ORDER BY snp.`no`
";

$puntos = find_by_sql($sql_puntos);


// =========================================================
// LÓGICA DE PRESENTACIÓN (no toca la BD)
// =========================================================

function color_clasificacion_detalle($clasificacion)
{
    $clas = strtolower(trim($clasificacion));

    $mapa = [
        'seguridad' => [
            'bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe', 'icon' => 'glyphicon-shield'
        ],
        'organizacion' => [
            'bg' => '#f5f3ff', 'color' => '#6d28d9', 'border' => '#ddd6fe', 'icon' => 'glyphicon-cog'
        ],
        'organización' => [
            'bg' => '#f5f3ff', 'color' => '#6d28d9', 'border' => '#ddd6fe', 'icon' => 'glyphicon-cog'
        ],
        'salud' => [
            'bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'icon' => 'glyphicon-heart'
        ],
    ];

    return $mapa[$clas] ?? ['bg' => '#eff6ff', 'color' => '#2563eb', 'border' => '#bfdbfe', 'icon' => 'glyphicon-tag'];
}

$estilo_clas = color_clasificacion_detalle($norma['clasificacion']);

// Conteo por tipo de comprobación, para el mini-resumen
$conteo_comprobacion = [];
foreach ($puntos as $p) {
    $tipo = trim($p['comprobacion']) !== '' ? $p['comprobacion'] : 'Sin especificar';
    if (!isset($conteo_comprobacion[$tipo])) {
        $conteo_comprobacion[$tipo] = 0;
    }
    $conteo_comprobacion[$tipo]++;
}

?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>


<style>

:root {
    --primary-color: #0f172a;
    --accent-color: #2563eb;
    --bg-subtle: #f8fafc;
    --border-color: #e2e8f0;
    --text-muted: #64748b;
    --radius: 14px;
}

/* =========================================================
   CONTENEDOR
========================================================= */

.ver-norma-container {
    margin-bottom: 30px;
}

.btn-volver-top {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    margin-bottom: 16px;
}

.btn-volver-top:hover {
    color: var(--accent-color);
    text-decoration: none;
}


/* =========================================================
   ENCABEZADO
========================================================= */

.norma-header {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-top: 4px solid <?php echo $estilo_clas['color']; ?>;
    border-radius: var(--radius);
    padding: 24px 26px;
    margin-bottom: 18px;
    box-shadow: 0 4px 14px rgba(15,23,42,0.04);
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.norma-header-imagen {
    width: 76px;
    height: 76px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    background: var(--bg-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.norma-header-imagen img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.norma-header-imagen .glyphicon {
    font-size: 26px;
    color: #cbd5e1;
}

.norma-header-info {
    flex: 1;
    min-width: 200px;
}

.norma-header-codigo {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--accent-color);
    letter-spacing: .5px;
    margin-bottom: 5px;
}

.norma-header-nombre {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.35;
    color: var(--primary-color);
}

.norma-header-clasificacion {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 10px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    background: <?php echo $estilo_clas['bg']; ?>;
    color: <?php echo $estilo_clas['color']; ?>;
    border: 1px solid <?php echo $estilo_clas['border']; ?>;
}

.btn-volver {
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    padding: 9px 14px;
    flex-shrink: 0;
}


/* =========================================================
   RESUMEN (KPIs)
========================================================= */

.resumen-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}

.resumen-card {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.resumen-numero {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-color);
    display: block;
    margin-bottom: 2px;
}

.resumen-texto {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .4px;
    font-weight: 600;
}

.resumen-card.comprobacion-mini {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.resumen-card.comprobacion-mini .glyphicon {
    color: #cbd5e1;
    font-size: 16px;
}


/* =========================================================
   BARRA DE BÚSQUEDA DE REQUISITOS
========================================================= */

.requisitos-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.puntos-header-title {
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.puntos-header-title .glyphicon {
    margin-right: 6px;
}

.search-req-box {
    position: relative;
    flex: 1 1 240px;
    max-width: 320px;
}

.search-req-box .glyphicon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 12px;
}

.search-req-input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    outline: none;
    font-size: 13px;
}

.search-req-input:focus {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}


/* =========================================================
   TIMELINE DE REQUISITOS
========================================================= */

.requisitos-timeline {
    position: relative;
    padding-left: 6px;
}

.requisito-item {
    position: relative;
    display: flex;
    gap: 16px;
    padding-bottom: 18px;
}

.requisito-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 21px;
    top: 44px;
    bottom: -2px;
    width: 2px;
    background: var(--border-color);
}

.requisito-punto {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--bg-subtle);
    color: var(--primary-color);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
    z-index: 1;
    background-color: #fff;
}

.requisito-card {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 16px 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: all .2s ease;
    flex: 1;
    min-width: 0;
}

.requisito-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(15,23,42,0.05);
    border-color: #cbd5e1;
}

.requisito-titulo {
    margin: 0;
    font-size: 14.5px;
    font-weight: 600;
    line-height: 1.55;
    color: var(--primary-color);
}

.requisito-meta {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    margin-top: 14px;
}

.requisito-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 5px;
}

.requisito-tipo {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    border-radius: 6px;
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
    color: #475569;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
}

.requisito-evidencia-texto {
    margin: 0;
    color: var(--text-muted);
    font-size: 12.5px;
    line-height: 1.6;
    max-width: 480px;
}

.requisito-item.oculto {
    display: none;
}


/* =========================================================
   SIN PUNTOS / SIN RESULTADOS
========================================================= */

.sin-puntos, .sin-resultados-busqueda {
    background: #ffffff;
    border: 1px dashed var(--border-color);
    border-radius: var(--radius);
    text-align: center;
    padding: 45px 20px;
    color: var(--text-muted);
}

.sin-resultados-busqueda {
    display: none;
}

.sin-puntos .glyphicon, .sin-resultados-busqueda .glyphicon {
    display: block;
    font-size: 30px;
    margin-bottom: 10px;
    color: #cbd5e1;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 767px) {

    .norma-header {
        padding: 18px;
    }

    .norma-header-nombre {
        font-size: 16px;
    }

    .requisito-item {
        gap: 10px;
    }

    .requisito-punto {
        width: 36px;
        height: 36px;
        font-size: 11px;
    }

    .requisito-item:not(:last-child)::before {
        left: 17px;
        top: 36px;
    }

    .requisito-card {
        padding: 13px 15px;
    }

    .requisito-meta {
        gap: 14px;
    }
}

</style>


<div class="ver-norma-container">

    <!-- ENCABEZADO DE NORMA -->
    <div class="norma-header">
        <div class="norma-header-imagen">
            <!-- IMAGEN -->
            <?php
                $foto = !empty($norma['imagen'])
                    ? BASE_URL . '/uploads/normas/' . $norma['imagen']
                    : BASE_URL . '/uploads/normas/no_image.jpg';
            ?>
            <?php if (!empty($norma['imagen'])): ?>
                <img src="<?php echo $foto; ?>" alt="Norma">
            <?php else: ?>
                <span class="glyphicon glyphicon-picture"></span>
            <?php endif; ?>
        </div>
        <div class="norma-header-info">
            <span class="norma-header-codigo">
                <?php echo remove_junk($norma['codigo_norma']); ?>
            </span>
            <h1 class="norma-header-nombre">
                <?php echo remove_junk($norma['nombre_norma']); ?>
            </h1>
            <span class="norma-header-clasificacion">
                <span class="glyphicon <?php echo $estilo_clas['icon']; ?>"></span>
                <?php echo strtoupper(remove_junk($norma['clasificacion'])); ?>
            </span>
        </div>
        <a href="normas.php" class="btn btn-default btn-volver">
            <span class="glyphicon glyphicon-arrow-left"></span>
            Volver
        </a>
        <a href="nueva_inspeccion.php?id=<?php echo (int)$norma['ID']; ?>" class="btn btn-default btn-volver">
            <span class="glyphicon glyphicon-plus"></span>
            Realizar inspeccion
        </a>
    </div>

    <!-- TÍTULO + BUSCADOR  -->

    <div class="requisitos-toolbar">
        <span class="puntos-header-title">
            <span class="glyphicon glyphicon-list-alt"></span>
            Requisitos de la norma
        </span>
        <?php if (!empty($puntos)): ?>
            <div class="search-req-box">
                <span class="glyphicon glyphicon-search"></span>
                <input
                    type="text"
                    id="inputBuscarRequisito"
                    class="search-req-input"
                    placeholder="Buscar dentro de los requisitos..."
                    autocomplete="off"
                >
            </div>
        <?php endif; ?>
    </div>


    <!-- PUNTOS (timeline)  -->
    <?php if (!empty($puntos)): ?>
        <div class="requisitos-timeline" id="listaRequisitos">
            <?php foreach ($puntos as $punto): ?>
                <?php
                    $texto_busqueda_req = strtolower(
                        $punto['requisito'] . ' ' . $punto['comprobacion'] . ' ' . $punto['evidencia']
                    );
                ?>
                <div class="requisito-item" data-busqueda="<?php echo htmlspecialchars($texto_busqueda_req, ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="requisito-punto">
                        <?php echo remove_junk($punto['punto']); ?>
                    </span>
                    <div class="requisito-card">
                        <h2 class="requisito-titulo">
                            <?php echo remove_junk($punto['requisito']); ?>
                        </h2>
                        <div class="requisito-meta">
                            <div>
                                <span class="requisito-label">Tipo de comprobación</span>
                                <span class="requisito-tipo">
                                    <?php echo remove_junk($punto['comprobacion']); ?>
                                </span>
                            </div>
                            <div>
                                <span class="requisito-label">Evidencia requerida</span>
                                <p class="requisito-evidencia-texto">
                                    <?php echo remove_junk($punto['evidencia']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="sin-resultados-busqueda" id="sinResultadosBusqueda">
            <span class="glyphicon glyphicon-search"></span>
            No hay requisitos que coincidan con tu búsqueda.
        </div>
    <?php else: ?>
        <div class="sin-puntos">
            <span class="glyphicon glyphicon-list-alt"></span>
            Esta norma todavía no tiene requisitos registrados.
        </div>
    <?php endif; ?>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('inputBuscarRequisito');
        if (!input) return;

        var items = Array.prototype.slice.call(document.querySelectorAll('#listaRequisitos .requisito-item'));
        var sinResultados = document.getElementById('sinResultadosBusqueda');

        function normalizarTexto(texto) {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        input.addEventListener('input', function () {
            var termino = normalizarTexto(input.value.trim());
            var visibles = 0;

            items.forEach(function (item) {
                var texto = normalizarTexto(item.getAttribute('data-busqueda'));
                var coincide = termino === '' || texto.indexOf(termino) !== -1;

                item.classList.toggle('oculto', !coincide);
                if (coincide) visibles++;
            });

            sinResultados.style.display = visibles === 0 ? 'block' : 'none';
        });
    });
</script>


<?php include_once BASE_PATH . '/layouts/footer.php'; ?>