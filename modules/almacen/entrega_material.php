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
    justify-content: space-between;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.card-panel-title .titulo-izq {
    display: flex;
    align-items: center;
    gap: 8px;
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

.form-control[readonly] {
    background: var(--bg-subtle);
    color: var(--text-muted);
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

/* Resumen */
.resumen-strip {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: var(--border-color);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
}

.resumen-item {
    background: #ffffff;
    padding: 14px 18px;
}

.resumen-item .label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .3px;
    margin-bottom: 4px;
}

.resumen-item .valor {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-color);
}

/* Tabla de productos a retirar */
.tabla-detalle-wrap {
    overflow-x: auto;
}

table.tabla-detalle {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

table.tabla-detalle thead th {
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .3px;
    padding: 8px 10px;
    border-bottom: 2px solid var(--border-color);
    white-space: nowrap;
}

table.tabla-detalle tbody td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: top;
}

table.tabla-detalle tbody tr.fila-detalle.con-error .form-control {
    border-color: #ef4444;
}

.col-producto { min-width: 240px; }
.col-stock { width: 100px; }
.col-cantidad { width: 110px; }
.col-nuevo { width: 110px; }
.col-obs { min-width: 180px; }
.col-quitar { width: 40px; text-align: center; }

.stock-nuevo-valor {
    font-weight: 700;
    font-size: 13.5px;
}

.stock-nuevo-valor.negativo {
    color: #ef4444;
}

.stock-nuevo-valor.ok {
    color: #15803d;
}

.error-fila {
    font-size: 11px;
    color: #ef4444;
    margin-top: 3px;
    display: none;
}

.fila-detalle.con-error .error-fila {
    display: block;
}

.btn-quitar-fila {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 14px;
    padding: 4px;
}

.btn-quitar-fila:hover {
    color: #ef4444;
}

.detalle-vacio {
    text-align: center;
    color: var(--text-muted);
    font-size: 12.5px;
    padding: 28px 10px;
}

.btn-agregar-producto {
    margin-top: 14px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
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

@media (max-width: 767px) {
    .actions-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .resumen-strip {
        grid-template-columns: 1fr;
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

        <div class="salida-header">
            <div>
                <h1>Salida de material</h1>
                <p>Registra el material que sale del almacén y actualiza el inventario</p>
            </div>
            <span class="salida-tag">
                <span class="glyphicon glyphicon-log-out"></span>
                Salida
            </span>
        </div>

        <form id="formSalida" method="POST">

            <input type="hidden" name="tipo" value="SALIDA">

            <!-- INFORMACIÓN GENERAL -->
            <div class="card-panel">
                <h3 class="card-panel-title">
                    <span class="titulo-izq">
                        <span class="glyphicon glyphicon-info-sign"></span>
                        Información general
                    </span>
                </h3>

                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Fecha <span class="req">*</span></label>
                            <input
                                type="date"
                                class="form-control"
                                name="fecha"
                                value="<?php echo date('Y-m-d'); ?>"
                                required>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Motivo <span class="req">*</span></label>
                            <select class="form-control" name="motivo" required>
                                <option value="" selected disabled>Selecciona un motivo</option>
                                <option value="Venta">Venta</option>
                                <option value="Traspaso entre almacenes">Traspaso entre almacenes</option>
                                <option value="Produccion">Consumo en producción</option>
                                <option value="Merma">Merma / baja de inventario</option>
                                <option value="Devolucion a proveedor">Devolución a proveedor</option>
                                <option value="Consumo interno">Consumo interno</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label>Referencia</label>
                            <input
                                type="text"
                                class="form-control"
                                name="referencia"
                                placeholder="No. de orden, folio, remisión...">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Observaciones generales</label>
                            <textarea
                                rows="3"
                                class="form-control"
                                name="observaciones"
                                placeholder="Notas adicionales sobre esta salida (opcional)"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RESUMEN -->
            <div class="resumen-strip">
                <div class="resumen-item">
                    <div class="label">Productos</div>
                    <div class="valor" id="resumen-productos">0</div>
                </div>
                <div class="resumen-item">
                    <div class="label">Piezas totales a retirar</div>
                    <div class="valor" id="resumen-piezas">0</div>
                </div>
                <div class="resumen-item">
                    <div class="label">Productos con stock insuficiente</div>
                    <div class="valor" id="resumen-errores">0</div>
                </div>
            </div>

            <!-- PRODUCTOS -->
            <div class="card-panel">
                <h3 class="card-panel-title">
                    <span class="titulo-izq">
                        <span class="glyphicon glyphicon-list-alt"></span>
                        Productos a retirar
                    </span>
                </h3>

                <div class="tabla-detalle-wrap">
                    <table class="tabla-detalle">
                        <thead>
                            <tr>
                                <th class="col-producto">Producto</th>
                                <th class="col-stock">Stock actual</th>
                                <th class="col-cantidad">Cantidad <span class="req">*</span></th>
                                <th class="col-nuevo">Stock resultante</th>
                                <th class="col-obs">Observaciones</th>
                                <th class="col-quitar"></th>
                            </tr>
                        </thead>
                        <tbody id="detalleBody"></tbody>
                    </table>
                    <p class="detalle-vacio" id="detalleVacio">Aún no has agregado productos a esta salida.</p>
                </div>

                <button type="button" class="btn btn-default btn-agregar-producto" id="btnAgregarProducto">
                    <span class="glyphicon glyphicon-plus"></span>
                    Agregar producto
                </button>
            </div>

            <!-- BARRA DE ACCIONES -->
            <div class="actions-bar">
                <a href="index.php" class="btn btn-default">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-guardar" id="btnGuardar">
                    <span class="glyphicon glyphicon-floppy-disk"></span>
                    Guardar salida
                </button>
            </div>

        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var detalleBody   = document.getElementById('detalleBody');
    var detalleVacio  = document.getElementById('detalleVacio');
    var btnAgregar    = document.getElementById('btnAgregarProducto');
    var form          = document.getElementById('formSalida');

    var contadorFila = 0;

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text == null ? '' : text;
        return div.innerHTML;
    }

    /**
     * Crea una fila de detalle. Cada fila mantiene su propio
     * autocomplete de producto, su stock actual (obtenido al
     * seleccionar el producto) y calcula el stock resultante
     * en vivo según la cantidad capturada.
     *
     * Se espera que ajax/buscar_productos.php responda un arreglo
     * de objetos: { id, texto, detalle, stock }
     * donde "stock" es la existencia actual del producto.
     */
    function agregarFila() {
        contadorFila++;
        var idx = contadorFila;

        var tr = document.createElement('tr');
        tr.className = 'fila-detalle';
        tr.dataset.idx = idx;

        tr.innerHTML =
            '<td class="col-producto">' +
                '<div class="autocomplete-wrapper">' +
                    '<input type="text" class="form-control buscar-producto" autocomplete="off" placeholder="Escribe código o descripción">' +
                    '<input type="hidden" name="detalle[' + idx + '][producto_id]" class="producto_id">' +
                    '<div class="autocomplete-results resultados-producto"></div>' +
                '</div>' +
            '</td>' +
            '<td class="col-stock">' +
                '<input type="text" class="form-control stock-actual-visible" readonly value="—">' +
                '<input type="hidden" name="detalle[' + idx + '][stock_anterior]" class="stock_anterior">' +
            '</td>' +
            '<td class="col-cantidad">' +
                '<input type="number" min="1" step="1" class="form-control cantidad" name="detalle[' + idx + '][cantidad]" disabled>' +
            '</td>' +
            '<td class="col-nuevo">' +
                '<span class="stock-nuevo-valor">—</span>' +
            '</td>' +
            '<td class="col-obs">' +
                '<input type="text" class="form-control" name="detalle[' + idx + '][observaciones]" placeholder="Opcional">' +
            '</td>' +
            '<td class="col-quitar">' +
                '<button type="button" class="btn-quitar-fila" aria-label="Quitar producto"><span class="glyphicon glyphicon-trash"></span></button>' +
            '</td>';

        detalleBody.appendChild(tr);

        var filaError = document.createElement('tr');
        filaError.innerHTML = '<td colspan="6" style="padding:0;border:none;"><div class="error-fila" style="padding:0 10px 6px;">Cantidad supera el stock disponible</div></td>';
        // el mensaje de error se maneja mostrando/ocultando clase en el tr principal
        // (se deja fuera del DOM adicional para mantener la tabla simple)

        configurarAutocompleteProducto(tr);
        configurarCalculoFila(tr);

        actualizarEstadoVacio();
        actualizarResumen();
    }

    function configurarAutocompleteProducto(fila) {
        var input      = fila.querySelector('.buscar-producto');
        var hiddenId    = fila.querySelector('.producto_id');
        var hiddenStock = fila.querySelector('.stock_anterior');
        var stockVisible = fila.querySelector('.stock-actual-visible');
        var cantidadInput = fila.querySelector('.cantidad');
        var resultados  = fila.querySelector('.resultados-producto');

        var temporizador = null;

        input.addEventListener('input', function () {
            var termino = this.value.trim();

            hiddenId.value = '';
            hiddenStock.value = '';
            stockVisible.value = '—';
            cantidadInput.value = '';
            cantidadInput.disabled = true;
            actualizarStockResultante(fila);

            clearTimeout(temporizador);

            if (termino.length < 2) {
                resultados.innerHTML = '';
                resultados.style.display = 'none';
                return;
            }

            temporizador = setTimeout(function () {

                fetch('ajax/buscar_productos.php?q=' + encodeURIComponent(termino))
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
                                '<div class="autocomplete-item"><small>No se encontraron resultados</small></div>';
                            resultados.style.display = 'block';
                            return;
                        }

                        data.forEach(function (item) {
                            var elemento = document.createElement('div');
                            elemento.className = 'autocomplete-item';
                            elemento.innerHTML =
                                '<strong>' + escapeHtml(item.texto) + '</strong>' +
                                (item.detalle ? '<small>' + escapeHtml(item.detalle) + '</small>' : '');

                            elemento.addEventListener('click', function () {
                                input.value = item.texto;
                                hiddenId.value = item.id;
                                hiddenStock.value = item.stock;
                                stockVisible.value = item.stock;

                                cantidadInput.disabled = false;
                                cantidadInput.max = item.stock;
                                cantidadInput.value = '';

                                resultados.innerHTML = '';
                                resultados.style.display = 'none';

                                actualizarStockResultante(fila);
                                cantidadInput.focus();
                            });

                            resultados.appendChild(elemento);
                        });

                        resultados.style.display = 'block';
                    })
                    .catch(function (error) {
                        console.error(error);
                        resultados.innerHTML =
                            '<div class="autocomplete-item"><small>Error al consultar información</small></div>';
                        resultados.style.display = 'block';
                    });

            }, 250);
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !resultados.contains(e.target)) {
                resultados.innerHTML = '';
                resultados.style.display = 'none';
            }
        });
    }

    function configurarCalculoFila(fila) {
        var cantidadInput = fila.querySelector('.cantidad');
        cantidadInput.addEventListener('input', function () {
            actualizarStockResultante(fila);
            actualizarResumen();
        });
    }

    function actualizarStockResultante(fila) {
        var stockActual = parseInt(fila.querySelector('.stock_anterior').value, 10);
        var cantidad    = parseInt(fila.querySelector('.cantidad').value, 10);
        var span        = fila.querySelector('.stock-nuevo-valor');

        if (isNaN(stockActual) || isNaN(cantidad) || cantidad <= 0) {
            span.textContent = '—';
            span.className = 'stock-nuevo-valor';
            fila.classList.remove('con-error');
            return;
        }

        var nuevo = stockActual - cantidad;
        span.textContent = nuevo;

        if (nuevo < 0) {
            span.className = 'stock-nuevo-valor negativo';
            fila.classList.add('con-error');
        } else {
            span.className = 'stock-nuevo-valor ok';
            fila.classList.remove('con-error');
        }
    }

    function actualizarEstadoVacio() {
        detalleVacio.style.display = detalleBody.children.length === 0 ? 'block' : 'none';
    }

    function actualizarResumen() {
        var filas = detalleBody.querySelectorAll('.fila-detalle');
        var totalProductos = filas.length;
        var totalPiezas = 0;
        var totalErrores = 0;

        filas.forEach(function (fila) {
            var cantidad = parseInt(fila.querySelector('.cantidad').value, 10);
            if (!isNaN(cantidad) && cantidad > 0) {
                totalPiezas += cantidad;
            }
            if (fila.classList.contains('con-error')) {
                totalErrores++;
            }
        });

        document.getElementById('resumen-productos').textContent = totalProductos;
        document.getElementById('resumen-piezas').textContent = totalPiezas;
        document.getElementById('resumen-errores').textContent = totalErrores;
    }

    detalleBody.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-quitar-fila');
        if (!btn) return;
        var fila = btn.closest('.fila-detalle');
        fila.remove();
        actualizarEstadoVacio();
        actualizarResumen();
    });

    btnAgregar.addEventListener('click', agregarFila);

    form.addEventListener('submit', function (e) {
        var filas = detalleBody.querySelectorAll('.fila-detalle');

        if (filas.length === 0) {
            e.preventDefault();
            alert('Agrega al menos un producto antes de guardar la salida.');
            return;
        }

        var incompleta = false;
        var conError = false;

        filas.forEach(function (fila) {
            var productoId = fila.querySelector('.producto_id').value;
            var cantidad   = fila.querySelector('.cantidad').value;

            if (!productoId || !cantidad || parseInt(cantidad, 10) <= 0) {
                incompleta = true;
            }
            if (fila.classList.contains('con-error')) {
                conError = true;
            }
        });

        if (incompleta) {
            e.preventDefault();
            alert('Hay productos sin seleccionar o sin cantidad capturada.');
            return;
        }

        if (conError) {
            e.preventDefault();
            alert('Hay productos cuya cantidad supera el stock disponible.');
            return;
        }
    });

    // Primera fila lista al cargar la vista
    agregarFila();
});
</script>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>