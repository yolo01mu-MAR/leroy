document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // Colores de vista previa por clasificación (solo UI)
    // =====================================================
    var estilosClasificacion = {
        'seguridad':    { bg: '#eff6ff', color: '#1d4ed8', border: '#bfdbfe', icon: 'glyphicon-shield' },
        'organizacion': { bg: '#f5f3ff', color: '#6d28d9', border: '#ddd6fe', icon: 'glyphicon-cog' },
        'organización': { bg: '#f5f3ff', color: '#6d28d9', border: '#ddd6fe', icon: 'glyphicon-cog' },
        'salud':        { bg: '#ecfdf5', color: '#047857', border: '#a7f3d0', icon: 'glyphicon-heart' }
    };
    var estiloDefault = { bg: '#eff6ff', color: '#2563eb', border: '#bfdbfe', icon: 'glyphicon-tag' };

    var inputCodigo = document.getElementById('input-codigo');
    var inputNombre = document.getElementById('input-nombre');
    var selectClas  = document.getElementById('input-clasificacion');

    var previewCodigo = document.getElementById('preview-codigo');
    var previewNombre = document.getElementById('preview-nombre');
    var previewBadge  = document.getElementById('preview-badge');
    var previewBadgeTexto = document.getElementById('preview-badge-texto');

    function actualizarPreview() {
        previewCodigo.textContent = inputCodigo.value || 'CÓDIGO-000';
        previewNombre.textContent = inputNombre.value || 'Nombre de la norma';

        var opcion = selectClas.options[selectClas.selectedIndex];
        var nombreClas = opcion ? (opcion.getAttribute('data-nombre') || '') : '';
        var estilo = estilosClasificacion[nombreClas] || estiloDefault;

        previewBadge.style.background = estilo.bg;
        previewBadge.style.color = estilo.color;
        previewBadge.style.borderColor = estilo.border;
        previewBadge.querySelector('.glyphicon').className = 'glyphicon ' + estilo.icon;
        previewBadgeTexto.textContent = opcion ? opcion.textContent.trim() : 'Clasificación';
    }

    inputCodigo.addEventListener('input', actualizarPreview);
    inputNombre.addEventListener('input', actualizarPreview);
    selectClas.addEventListener('change', actualizarPreview);
    actualizarPreview();

    // =====================================================
    // Previsualización de imagen (portada + vista previa)
    // =====================================================
    var inputImg = document.getElementById('input-imagen');
    var previewImg = document.getElementById('preview-img');
    var previewIcon = document.getElementById('preview-icon');
    var previewLiveImg = document.getElementById('preview-live-img');
    var previewLiveIcon = document.getElementById('preview-live-icon');
    var dropzone = document.getElementById('dropzone');

    function mostrarImagen(src) {
        previewImg.src = src;
        previewImg.style.display = 'block';
        if (previewIcon) previewIcon.style.display = 'none';

        previewLiveImg.src = src;
        previewLiveImg.style.display = 'block';
        if (previewLiveIcon) previewLiveIcon.style.display = 'none';
    }

    function cargarArchivo(file) {
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (evt) {
            mostrarImagen(evt.target.result);
        };
        reader.readAsDataURL(file);
    }

    if (inputImg) {
        inputImg.addEventListener('change', function (e) {
            cargarArchivo(e.target.files[0]);
        });
    }

    if (dropzone) {
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
            var file = e.dataTransfer.files[0];
            if (file) {
                inputImg.files = e.dataTransfer.files;
                cargarArchivo(file);
            }
        });
    }

    // =====================================================
    // Agregar / eliminar requisitos + contador
    // =====================================================
    var btnAgregar = document.getElementById('btnAgregarPunto');
    var contenedor = document.getElementById('contenedorPuntos');
    var tpl = document.getElementById('tpl-punto').innerHTML;
    var btnAgregar = document.getElementById('btnAgregarPunto');
    
    var contenedor = document.getElementById('contenedorPuntos');
    var tpl = document.getElementById('tpl-punto').innerHTML;

    var indexCounter = contenedor.querySelectorAll('.punto-row').length;

    var contadorPuntosNum = document.getElementById('contadorPuntosNum');

    function actualizarContador() {
        var total = contenedor.querySelectorAll('.punto-row').length;
        contadorPuntosNum.textContent = total;
    }

    function renumerarBadges() {
        var badges = contenedor.querySelectorAll('.punto-badge');
        badges.forEach(function (badge, i) {
            badge.textContent = i + 1;
        });
    }

    btnAgregar.addEventListener('click', function () {
        var mensajeVacio = document.getElementById('mensajeSinPuntos');
        if (mensajeVacio) mensajeVacio.remove();

        var html = tpl.replace(/{INDEX}/g, indexCounter).replace('{NUM}', indexCounter + 1);
        contenedor.insertAdjacentHTML('beforeend', html);
        indexCounter++;
        renumerarBadges();
        actualizarContador();
    });

    contenedor.addEventListener('click', function (e) {
        var btn = e.target.closest('.btnEliminarPunto');
        if (btn) {
            var row = btn.closest('.punto-row');
            if (row) {
                row.remove();
                renumerarBadges();
                actualizarContador();
            }
        }
    });
});