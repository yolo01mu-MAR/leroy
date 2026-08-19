document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS PRINCIPALES
    |--------------------------------------------------------------------------
    */

    var items = document.querySelectorAll(
        '.vacaciones-item'
    );

    var detalle = document.getElementById(
        'detalleSolicitud'
    );

    var buscador = document.getElementById(
        'buscarColaborador'
    );

    var contenedorLista = document.getElementById(
        'listaSolicitudes'
    );

    var filtros = document.querySelectorAll(
        '.vacaciones-tab'
    );


    /*
    |--------------------------------------------------------------------------
    | INICIALES
    |--------------------------------------------------------------------------
    */

    function obtenerIniciales(nombre) {

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


        var nombre =
            item.dataset.nombre || '';

        var puesto =
            item.dataset.puesto || '';

        var departamento =
            item.dataset.departamento || '';

        var cuadrilla =
            item.dataset.cuadrilla || '';

        var grupo =
            item.dataset.grupo || '';

        var fechaSolicitud =
            item.dataset.fechaSolicitud || '';

        var fechaInicio =
            item.dataset.fechaInicio || '';

        var dias =
            item.dataset.dias || '';

        var estatus =
            item.dataset.estatus || '';

        var estatusClase =
            item.dataset.estatusClase || 'estatus-default';

        var estatusIcono =
            item.dataset.estatusIcono || 'glyphicon-question-sign';


        /*
        |--------------------------------------------------------------------------
        | HTML DEL DETALLE
        |--------------------------------------------------------------------------
        */

        detalle.innerHTML =

            '<div class="detalle-encabezado">' +

                '<div class="detalle-avatar">' +

                    obtenerIniciales(nombre) +

                '</div>' +

                '<div>' +

                    '<div class="detalle-nombre">' +

                        nombre +

                    '</div>' +

                    '<div class="detalle-puesto">' +

                        puesto +

                    '</div>' +

                '</div>' +

            '</div>' +


            '<div class="detalle-datos">' +


                '<div class="detalle-dato">' +

                    '<div class="detalle-dato-label">' +
                        'Estatus' +
                    '</div>' +

                    '<div class="detalle-dato-valor">' +

                        '<span class="vacaciones-status ' +
                            estatusClase +
                        '">' +

                            '<span class="glyphicon ' +
                                estatusIcono +
                            '"></span> ' +

                            estatus +

                        '</span>' +

                    '</div>' +

                '</div>' +


                '<div class="detalle-dato">' +

                    '<div class="detalle-dato-label">' +
                        'Fecha solicitud' +
                    '</div>' +

                    '<div class="detalle-dato-valor">' +

                        fechaSolicitud +

                    '</div>' +

                '</div>' +


                '<div class="detalle-dato">' +

                    '<div class="detalle-dato-label">' +
                        'Inicio vacaciones' +
                    '</div>' +

                    '<div class="detalle-dato-valor">' +

                        fechaInicio +

                    '</div>' +

                '</div>' +


                '<div class="detalle-dato">' +

                    '<div class="detalle-dato-label">' +
                        'Días solicitados' +
                    '</div>' +

                    '<div class="detalle-dato-valor">' +

                        dias +

                    '</div>' +

                '</div>' +


                '<div class="detalle-dato">' +

                    '<div class="detalle-dato-label">' +
                        'Departamento' +
                    '</div>' +

                    '<div class="detalle-dato-valor">' +

                        departamento +

                    '</div>' +

                '</div>' +


                '<div class="detalle-dato">' +

                    '<div class="detalle-dato-label">' +
                        'Cuadrilla' +
                    '</div>' +

                    '<div class="detalle-dato-valor">' +

                        grupo +

                        ' · ' +

                        cuadrilla +

                    '</div>' +

                '</div>' +


            '</div>';


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
    | REGISTRAR CLIC EN SOLICITUDES
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

                contenedorLista.innerHTML =

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
                    'ajax/filtro_solicitudes.php',
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

                    return respuesta.text();

                })


                .then(function (html) {


                    /*
                    |--------------------------------------------------------------------------
                    | INSERTAR RESULTADO
                    |--------------------------------------------------------------------------
                    */

                    contenedorLista.innerHTML =
                        html;


                    /*
                    |--------------------------------------------------------------------------
                    | REGISTRAR NUEVOS ITEMS
                    |--------------------------------------------------------------------------
                    */

                    registrarSolicitudes();


                })


                .catch(function (error) {

                    console.error(
                        'Error AJAX:',
                        error
                    );


                    contenedorLista.innerHTML =

                        '<div class="vacaciones-vacio">' +

                            '<span class="glyphicon glyphicon-warning-sign"></span>' +

                            '<p>' +

                                'No fue posible cargar las solicitudes.' +

                            '</p>' +

                        '</div>';

                });

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


                        item.style.display =

                            nombre.indexOf(texto) !== -1

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

});