/**
 * ============================================================
 * SOLICITUDES DE VACACIONES RH
 * ============================================================
 */

let solicitudes = [];
let estatusActual = 'PENDIENTE_RH';

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS PRINCIPALES
    |--------------------------------------------------------------------------
    */

    var items    = document.querySelectorAll('.vacaciones-item');
    var detalle  = document.getElementById('detalleSolicitud');
    var buscador = document.getElementById('buscarColaborador');
    var lista    = document.getElementById('listaSolicitudes');
    var filtros  = document.querySelectorAll('.vacaciones-tab');

    /*
    |--------------------------------------------------------------------------
    | INICIALES
    |--------------------------------------------------------------------------
    */

    function obtenerIniciales(nombre) {

        if (!nombre) {
            return '?';
        }

        var partes = nombre
            .trim()
            .split(/\s+/)
            .slice(0, 2);

        return partes
            .map(function (parte) {
                return parte
                    .charAt(0)
                    .toUpperCase();
            })
            .join('');
    }

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR DETALLE
    |--------------------------------------------------------------------------
    */

    function mostrarDetalle(item) {

        if (!item || !detalle) {
            return;
        }

        items.forEach(function (elemento) {

            elemento.classList.remove(
                'seleccionado'
            );

        });

        item.classList.add(
            'seleccionado'
        );

        var nombre = item.dataset.nombre || '';
        var puesto = item.dataset.puesto || '';
        var departamento = item.dataset.departamento || '';
        var cuadrilla = item.dataset.cuadrilla || '';
        var grupo = item.dataset.grupo || '';
        var fechaSolicitud = item.dataset.fechaSolicitud || '';
        var fechaInicio = item.dataset.fechaInicio || '';
        var dias = item.dataset.dias || '';
        var id = item.dataset.id || '';
        var estatus = item.dataset.estatus || '';
        var estatusClase = item.dataset.estatusClase || 'estatus-default';
        var estatusIcono = item.dataset.estatusIcono || 'glyphicon-question-sign';

        /*
        |--------------------------------------------------------------------------
        | HTML DEL DETALLE
        |--------------------------------------------------------------------------
        */

        detalle.innerHTML = `

            <div class="detalle-encabezado">
                <div class="detalle-avatar">
                    ${obtenerIniciales(nombre)}
                </div>
                <div>
                    <div class="detalle-nombre">
                        ${nombre}
                    </div>
                    <div class="detalle-puesto">
                        ${puesto}
                    </div>
                </div>
            </div>
            <div class="detalle-datos">
                <div class="detalle-dato">
                    <div class="detalle-dato-label">
                        Estatus
                    </div>
                    <div class="detalle-dato-valor">
                        <span class="vacaciones-status ${estatusClase}">
                            <span class="glyphicon ${estatusIcono}"></span>
                            ${estatus}
                        </span>
                    </div>
                </div>
                <div class="detalle-dato">
                    <div class="detalle-dato-label">
                        Fecha solicitud
                    </div>
                    <div class="detalle-dato-valor">
                        ${fechaSolicitud}
                    </div>
                </div>
                <div class="detalle-dato">
                    <div class="detalle-dato-label">
                        Inicio vacaciones
                    </div>
                    <div class="detalle-dato-valor">
                        ${fechaInicio}
                    </div>
                </div>
                <div class="detalle-dato">
                    <div class="detalle-dato-label">
                        Días solicitados
                    </div>
                    <div class="detalle-dato-valor">
                        ${dias}
                    </div>
                </div>
                <div class="detalle-dato">
                    <div class="detalle-dato-label">
                        Departamento
                    </div>
                    <div class="detalle-dato-valor">
                        ${departamento}
                    </div>
                </div>
                <div class="detalle-dato">
                    <div class="detalle-dato-label">
                        Cuadrilla
                    </div>
                    <div class="detalle-dato-valor">
                        ${grupo} · ${cuadrilla}
                    </div>
                </div>
            </div>

            ${
                estatus === 'PENDIENTE_RH' ||
                estatus === 'Pendientes'
                ?

                `
                    <div class="detalle-acciones">

                        <button
                            type="button"
                            class="
                                btn
                                btn-vacaciones
                                btn-rechazar-jefe
                            "
                            data-id="${id}"
                        >
                            <span class="glyphicon glyphicon-remove"></span>
                            No aprobar
                        </button>

                        <button
                            type="button"
                            class="
                                btn
                                btn-vacaciones
                                btn-vacaciones-primary
                                btn-aprobar-jefe
                            "
                            data-id="${id}"
                        >
                            <span class="glyphicon glyphicon-ok"></span>
                            Aprobar
                        </button>

                    </div>
                `
                :
                ''
            }

        `;

        /*
        |--------------------------------------------------------------------------
        | BOTÓN APROBAR
        |--------------------------------------------------------------------------
        */

    const botonAprobar = detalle.querySelector('.btn-aprobar-jefe');

    if (botonAprobar) {

        botonAprobar.addEventListener(
            'click',
            function (e) {

                e.stopPropagation();

                // Evitar doble clic
                if (botonAprobar.disabled) {
                    return;
                }

                // Desactivar botón
                botonAprobar.disabled = true;

                // Cambiar contenido a "Cargando..."
                botonAprobar.innerHTML = `
                    <span class="glyphicon glyphicon-refresh glyphicon-spin"></span>
                    Aprobando...
                `;

                // Abrir flujo de aprobación
                confirmarDecisionRH(
                    id,
                    'aprobar'
                );

            }
        );

    }


        /*
        |--------------------------------------------------------------------------
        | BOTÓN RECHAZAR
        |--------------------------------------------------------------------------
        */

        const botonRechazar =
            detalle.querySelector(
                '.btn-rechazar-jefe'
            );

        if (botonRechazar) {

            botonRechazar.addEventListener(
                'click',
                function (e) {

                    e.stopPropagation();

                    confirmarDecisionRH(
                        id,
                        'rechazar'
                    );

                }
            );

        }

    }

    function limpiarDetalle() {

        if (!detalle) {
            return;
        }


        detalle.innerHTML =

            '<div class="vacaciones-detalle-vacio">' +

                '<span class="glyphicon glyphicon-hand-left"></span>' +

                '<p>' +
                    'Selecciona una solicitud' +
                '</p>' +

                '<small>' +
                    'Aquí aparecerán los detalles de la solicitud.' +
                '</small>' +

            '</div>';

    }

    /*
    |--------------------------------------------------------------------------
    | REGISTRAR SOLICITUDES
    |--------------------------------------------------------------------------
    */

    function registrarSolicitudes() {

        items =
            document.querySelectorAll(
                '.vacaciones-item'
            );


        items.forEach(function (item) {

            item.addEventListener(
                'click',
                function () {

                    mostrarDetalle(item);

                }
            );

        });

    }



    /*
    |--------------------------------------------------------------------------
    | ABRIR SOLICITUD DESDE URL
    |--------------------------------------------------------------------------
    */

    function abrirSolicitudDesdeURL() {

        var parametros =
            new URLSearchParams(
                window.location.search
            );


        var solicitudId =
            parametros.get('id');


        if (!solicitudId) {
            return;
        }


        var solicitud =
            document.querySelector(
                '.vacaciones-item[data-id="' +
                solicitudId +
                '"]'
            );


        if (!solicitud) {

            console.warn(
                'No se encontró la solicitud:',
                solicitudId
            );

            return;

        }


        mostrarDetalle(
            solicitud
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FILTROS AJAX
    |--------------------------------------------------------------------------
    */

    filtros.forEach(function (filtro) {


        filtro.addEventListener(
            'click',
            function (e) {

                e.preventDefault();


                var estatus =
                    filtro.dataset.estatus;
                    
                
                    limpiarDetalle();

                /*
                |--------------------------------------------------------------------------
                | TAB ACTIVO
                |--------------------------------------------------------------------------
                */

                filtros.forEach(function (item) {

                    item.classList.remove(
                        'activo'
                    );

                });


                filtro.classList.add(
                    'activo'
                );


                /*
                |--------------------------------------------------------------------------
                | CARGANDO
                |--------------------------------------------------------------------------
                */

                lista.innerHTML = 

                    '<div class="vacaciones-vacio">' +

                        '<span class="glyphicon glyphicon-refresh"></span>' +

                        '<p>' +

                            'Cargando solicitudes...' +

                        '</p>' +

                    '</div>';


                /*
                |--------------------------------------------------------------------------
                | AJAX
                |--------------------------------------------------------------------------
                */

                var datos =
                    new FormData();


                datos.append(
                    'estatus',
                    estatus
                );


                fetch(
                    'ajax/filtro_solicitudes_rh.php',
                    {
                        method: 'POST',
                        body: datos
                    }
                )

                .then(function (respuesta) {

                    if (!respuesta.ok) {

                        throw new Error(
                            'HTTP ' +
                            respuesta.status
                        );

                    }

                    return respuesta.json();

                })

                .then(function (respuesta) {

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDAR RESPUESTA
                    |--------------------------------------------------------------------------
                    */

                    if (!respuesta.success) {

                        lista.innerHTML =
                            '<div class="vacaciones-vacio">' +

                                '<span class="glyphicon glyphicon-warning-sign"></span>' +

                                '<p>' +

                                    (
                                        respuesta.message ||
                                        'No fue posible cargar las solicitudes.'
                                    ) +

                                '</p>' +

                            '</div>';

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | INSERTAR RESULTADO
                    |--------------------------------------------------------------------------
                    */

                    lista.innerHTML =
                        respuesta.html;


                    /*
                    |--------------------------------------------------------------------------
                    | REGISTRAR NUEVOS ITEMS
                    |--------------------------------------------------------------------------
                    */

                    registrarSolicitudes();


                    /*
                    |--------------------------------------------------------------------------
                    | CONTADOR PENDIENTES
                    |--------------------------------------------------------------------------
                    */

                    var tabPendientes =
                        document.querySelector(
                            '.vacaciones-tab[data-estatus="PENDIENTE_RH"]'
                        );


                    if (tabPendientes) {

                        var contador =
                            tabPendientes.querySelector(
                                '.vacaciones-contador'
                            );


                        if (respuesta.total_pendientes > 0) {

                            if (!contador) {

                                contador =
                                    document.createElement('span');

                                contador.className =
                                    'vacaciones-contador';

                                tabPendientes.appendChild(
                                    contador
                                );

                            }

                            contador.textContent =
                                respuesta.total_pendientes;

                        } else {

                            if (contador) {

                                contador.remove();

                            }

                        }

                    }

                })

            }
        );

    }); 


    /*
    |--------------------------------------------------------------------------
    | BUSCADOR
    |--------------------------------------------------------------------------
    */

    if (buscador) {

        buscador.addEventListener(
            'keyup',
            function () {

                var texto =
                    buscador.value
                        .trim()
                        .toLowerCase();


                items.forEach(
                    function (item) {

                        var nombre =
                            (
                                item.dataset.nombre ||
                                ''
                            ).toLowerCase();


                        var puesto =
                            (
                                item.dataset.puesto ||
                                ''
                            ).toLowerCase();


                        var departamento =
                            (
                                item.dataset.departamento ||
                                ''
                            ).toLowerCase();


                        var coincide =
                            nombre.includes(texto) ||
                            puesto.includes(texto) ||
                            departamento.includes(texto);


                        item.style.display =
                            coincide
                                ? ''
                                : 'none';

                    }
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | INICIALIZAR
    |--------------------------------------------------------------------------
    */

    registrarSolicitudes();
    abrirSolicitudDesdeURL();

});