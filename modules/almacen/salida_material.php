<?php
  $page_title = 'Salida de Material';
  require_once __DIR__ . '/../../app/bootstrap.php';
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

.salida-container {
    margin-bottom: 90px; /* espacio para la barra sticky */
}

.salida-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 18px;
}

.salida-header h1 {
    font-size: 21px;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0;
    letter-spacing: -.3px;
}

.salida-header p {
    margin: 2px 0 0;
    color: var(--text-muted);
    font-size: 13px;
}

.salida-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.card-panel {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(15,23,42,0.03);
}

.card-panel-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.card-panel-title .glyphicon {
    color: var(--accent-color);
}

.form-group label {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 5px;
}

.form-group label .req {
    color: #ef4444;
}

.form-control {
    border-radius: 8px;
    border: 1px solid var(--border-color);
    box-shadow: none;
    font-size: 13.5px;
}

.form-control:focus {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

/* Dropzone de evidencias */
.dropzone {
    border: 2px dashed var(--border-color);
    border-radius: 10px;
    padding: 35px 355px;
    text-align: center;
    cursor: pointer;
    transition: all .2s ease;
    background: var(--bg-subtle);
}

.dropzone:hover, .dropzone.dragover {
    border-color: var(--accent-color);
    background: #eff6ff;
}

.dropzone .glyphicon {
    font-size: 26px;
    color: #94a3b8;
    margin-bottom: 8px;
    display: block;
}

.dropzone strong {
    color: var(--primary-color);
    font-size: 13.5px;
}

.dropzone small {
    display: block;
    color: var(--text-muted);
    margin-top: 4px;
    font-size: 12px;
}

.dropzone input[type="file"] {
    display: none;
}

.evidencias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 10px;
    margin-top: 16px;
}

.evidencia-thumb {
    position: relative;
    width: 100%;
    padding-top: 100%;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    background: var(--bg-subtle);
}

.evidencia-thumb img {
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.evidencia-thumb .btn-quitar-evidencia {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(15,23,42,0.65);
    color: #fff;
    border: none;
    font-size: 11px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.evidencia-thumb .btn-quitar-evidencia:hover {
    background: #ef4444;
}

.evidencias-vacio {
    text-align: center;
    color: var(--text-muted);
    font-size: 12.5px;
    margin-top: 12px;
}

/* Barra de acciones sticky */
.actions-bar {
    position: sticky;
    bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 14px 22px;
    box-shadow: 0 8px 24px rgba(15,23,42,0.08);
    z-index: 10;
}

.btn-guardar {
    border-radius: 9px;
    font-weight: 700;
    padding: 10px 22px;
    font-size: 13px;
    box-shadow: 0 2px 6px rgba(37,99,235,.25);
}
.autocomplete-wrapper {
    position: relative;
}

.autocomplete-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 8px 20px rgba(15,23,42,.10);
    z-index: 1000;
    display: none;
    max-height: 240px;
    overflow-y: auto;
}

.autocomplete-item {
    padding: 10px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}

.autocomplete-item:hover {
    background: #f8fafc;
}

.autocomplete-item strong {
    display: block;
    color: var(--primary-color);
}

.autocomplete-item small {
    display: block;
    color: var(--text-muted);
    margin-top: 2px;
}
@media (max-width: 767px) {
    .actions-bar {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<div class="row">
   <div class="col-md-12">
     <?php echo display_msg($msg); ?>
   </div>
</div>

<div class="row">
    <div class="col-md-12 salida-container">

        <form id="formSalida" method="POST" enctype="multipart/form-data">

            <!-- INFORMACIÓN -->
            <div class="card-panel">
                <h3 class="card-panel-title">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    Informacion requerida:
                </h3>

                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Motivo</label>

                            <input
                                type="text"
                                class="form-control"
                                value="Salida de material"
                                readonly>

                            <input
                                type="hidden"
                                name="motivo"
                                value="Salida de material">
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Fecha <span class="req">*</span></label>
                            <input type="date" class="form-control" name="fecha">
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Proveedor</label>
                            <input type="text" class="form-control" name="proveedor">
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Refacción</label>

                            <div class="autocomplete-wrapper">
                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscar-refaccion"
                                    name="refaccion_busqueda"
                                    autocomplete="off"
                                    placeholder="Escribe código o descripción">

                                <input
                                    type="hidden"
                                    id="refaccion_id"
                                    name="refaccion_id">

                                <div
                                    class="autocomplete-results"
                                    id="resultados-refaccion">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Área</label>

                            <div class="autocomplete-wrapper">
                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscar-area"
                                    name="area_busqueda"
                                    autocomplete="off"
                                    placeholder="Escribe el área">

                                <input
                                    type="hidden"
                                    id="area_id"
                                    name="area_id">

                                <div
                                    class="autocomplete-results"
                                    id="resultados-area">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Solicitante <span class="req">*</span></label>

                            <div class="autocomplete-wrapper">
                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscar-solicitante"
                                    name="solicitante_busqueda"
                                    autocomplete="off"
                                    placeholder="Escribe nombre o nómina">

                                <input
                                    type="hidden"
                                    id="solicitante_id"
                                    name="solicitante_id">

                                <div
                                    class="autocomplete-results"
                                    id="resultados-solicitante">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea rows="4" class="form-control" name="descripcion" placeholder="Describe brevemente la incidencia..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EVIDENCIAS -->
            <div class="card-panel">
                <h3 class="card-panel-title">
                    <span class="glyphicon glyphicon-picture"></span>
                    Evidencias
                </h3>

                <label class="dropzone" id="dropzoneEvidencias" for="input-evidencias">
                    <span class="glyphicon glyphicon-cloud-upload"></span>
                    <strong>Arrastra fotos aquí o toca para tomar/elegir una</strong>
                    <small>Puedes agregar varias imágenes (JPG, PNG)</small>
                    <input
                        type="file"
                        id="input-evidencias"
                        name="evidencias[]"
                        accept="image/*"
                        capture="environment"
                        multiple>
                </label>

                <div class="evidencias-grid" id="evidenciasGrid"></div>
                <p class="evidencias-vacio" id="evidenciasVacio">Aún no has agregado evidencias.</p>
            </div>

            <!-- BARRA DE ACCIONES -->
            <div class="actions-bar">
                <a href="index.php" class="btn btn-default">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-guardar">
                    <span class="glyphicon glyphicon-floppy-disk"></span>
                    Guardar incidencia
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

    function configurarAutocomplete(config) {

        var input = document.getElementById(config.input);
        var hidden = document.getElementById(config.hidden);
        var resultados = document.getElementById(config.resultados);

        var temporizador = null;

        input.addEventListener('input', function () {

            var termino = this.value.trim();

            // Si el usuario vuelve a escribir,
            // ya no tenemos una selección válida.
            hidden.value = '';

            clearTimeout(temporizador);

            if (termino.length < 2) {
                resultados.innerHTML = '';
                resultados.style.display = 'none';
                return;
            }

            temporizador = setTimeout(function () {

                fetch(config.url + '?q=' + encodeURIComponent(termino))
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Error en la petición AJAX');
                        }

                        return response.json();
                    })
                    .then(function (data) {

                        resultados.innerHTML = '';

                        if (!data || data.length === 0) {

                            resultados.innerHTML =
                                '<div class="autocomplete-item">' +
                                '<small>No se encontraron resultados</small>' +
                                '</div>';

                            resultados.style.display = 'block';

                            return;
                        }

                        data.forEach(function (item) {

                            var elemento =
                                document.createElement('div');

                            elemento.className =
                                'autocomplete-item';

                            elemento.innerHTML =
                                '<strong>' +
                                escapeHtml(item.texto) +
                                '</strong>' +
                                (item.detalle
                                    ? '<small>' +
                                      escapeHtml(item.detalle) +
                                      '</small>'
                                    : '');

                            elemento.addEventListener('click', function () {

                                input.value = item.texto;
                                hidden.value = item.id;

                                resultados.innerHTML = '';
                                resultados.style.display = 'none';
                            });

                            resultados.appendChild(elemento);
                        });

                        resultados.style.display = 'block';
                    })
                    .catch(function (error) {

                        console.error(error);

                        resultados.innerHTML =
                            '<div class="autocomplete-item">' +
                            '<small>Error al consultar información</small>' +
                            '</div>';

                        resultados.style.display = 'block';
                    });

            }, 250);
        });


        document.addEventListener('click', function (e) {

            if (!input.contains(e.target) &&
                !resultados.contains(e.target)) {

                resultados.innerHTML = '';
                resultados.style.display = 'none';
            }
        });
    }


    function escapeHtml(text) {

        var div = document.createElement('div');

        div.textContent = text == null ? '' : text;

        return div.innerHTML;
    }


    /*
     * REFACCIONES
     */
    configurarAutocomplete({
        input: 'buscar-refaccion',
        hidden: 'refaccion_id',
        resultados: 'resultados-refaccion',
        url: 'ajax/buscar_refacciones.php'
    });


    /*
     * ÁREAS
     */
    configurarAutocomplete({
        input: 'buscar-area',
        hidden: 'area_id',
        resultados: 'resultados-area',
        url: 'ajax/buscar_areas.php'
    });


    /*
     * SOLICITANTES
     */
    configurarAutocomplete({
        input: 'buscar-solicitante',
        hidden: 'solicitante_id',
        resultados: 'resultados-solicitante',
        url: 'ajax/buscar_solicitantes.php'
    });

    var dropzone = document.getElementById('dropzoneEvidencias');
    var input = document.getElementById('input-evidencias');
    var grid = document.getElementById('evidenciasGrid');
    var vacio = document.getElementById('evidenciasVacio');
    var archivosSeleccionados = [];

    function renderEvidencias() {
        grid.innerHTML = '';
        vacio.style.display = archivosSeleccionados.length === 0 ? 'block' : 'none';

        archivosSeleccionados.forEach(function (file, idx) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var div = document.createElement('div');
                div.className = 'evidencia-thumb';
                div.innerHTML =
                    '<img src="' + e.target.result + '" alt="Evidencia ' + (idx + 1) + '">' +
                    '<button type="button" class="btn-quitar-evidencia" data-idx="' + idx + '">&times;</button>';
                grid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    function sincronizarInput() {
        var dt = new DataTransfer();
        archivosSeleccionados.forEach(function (file) {
            dt.items.add(file);
        });
        input.files = dt.files;
    }

    function agregarArchivos(fileList) {
        Array.prototype.forEach.call(fileList, function (file) {
            archivosSeleccionados.push(file);
        });
        sincronizarInput();
        renderEvidencias();
    }

    input.addEventListener('change', function (e) {
        agregarArchivos(e.target.files);
    });

    ['dragover', 'dragenter'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', function (e) {
        if (e.dataTransfer.files.length) {
            agregarArchivos(e.dataTransfer.files);
        }
    });

    grid.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-quitar-evidencia');
        if (btn) {
            var idx = parseInt(btn.getAttribute('data-idx'), 10);
            archivosSeleccionados.splice(idx, 1);
            sincronizarInput();
            renderEvidencias();
        }
    });

    renderEvidencias();
});
</script>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>