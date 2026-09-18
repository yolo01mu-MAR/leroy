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
$result_norma = get_norma_clasificaciones($id_norma);

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
$puntos = get_puntos_norma($id_norma);

$clasificaciones = find_clasificaciones_normas();

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
<link rel="stylesheet" href="assets/css/ver_normas.css">
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
            <?php 
                $estilo = color_clasificacion(
                    $norma['clasificacion']
                );
            ?>
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
        <a href="normas.php" class="btn btn-default btn-volver">
            <span class="glyphicon glyphicon-arrow-left"></span>
            Volver
        </a>
        <a href="nueva_inspeccion.php?id=<?php echo (int)$norma['id']; ?>" class="btn btn-default btn-volver">
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