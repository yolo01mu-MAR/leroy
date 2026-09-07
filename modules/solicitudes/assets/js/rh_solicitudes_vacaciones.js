/**
 * ============================================================
 * ESTADO RH
 * ============================================================
 */

let solicitudesRH = [];
let estatusRHActual = 'PENDIENTE_RH';


/**
 * ============================================================
 * INICIALIZACIÓN
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function () {

    registrarSolicitudesRH();

});


/**
 * ============================================================
 * REGISTRAR VISTA RH
 * ============================================================
 */

function registrarSolicitudesRH() {

    const lista =
        document.getElementById('listaSolicitudes');

    const detalle =
        document.getElementById('detalleSolicitud');

    if (!lista || !detalle) {

        console.warn(
            'Vista de solicitudes RH no encontrada.'
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.vacaciones-tab')
        .forEach(function (tab) {

            tab.addEventListener(
                'click',
                function (e) {

                    e.preventDefault();

                    const estatus =
                        this.dataset.estatus;

                    if (!estatus) {
                        return;
                    }

                    aplicarFiltroRH(
                        estatus
                    );

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | BUSCADOR
    |--------------------------------------------------------------------------
    */

    const buscador =
        document.getElementById(
            'buscarColaborador'
        );


    if (buscador) {

        buscador.addEventListener(
            'input',
            function () {

                buscarColaboradorRH(
                    this.value
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ESTATUS INICIAL
    |--------------------------------------------------------------------------
    */

    const tabActiva =
        document.querySelector(
            '.vacaciones-tab.activo'
        );


    if (tabActiva) {

        estatusRHActual =
            tabActiva.dataset.estatus
            || 'PENDIENTE_RH';

    }


    /*
    |--------------------------------------------------------------------------
    | REGISTRAR SOLICITUDES EXISTENTES
    |--------------------------------------------------------------------------
    */

    registrarItemsRH();

}

function registrarItemsRH() {

    document
        .querySelectorAll('.vacaciones-item')
        .forEach(function (item) {

            item.addEventListener(
                'click',
                function () {

                    seleccionarSolicitudRH(
                        this
                    );

                }
            );

        });

}
/**
 * ============================================================
 * APLICAR FILTRO RH
 * ============================================================
 */

function aplicarFiltroRH(estatus) {

    estatusRHActual = estatus;

    /*
    |--------------------------------------------------------------------------
    | MARCAR TAB ACTIVO
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.vacaciones-tab')
        .forEach(function (tab) {

            tab.classList.remove(
                'activo'
            );

        });


    const tabActivo =
        document.querySelector(
            '.vacaciones-tab[data-estatus="' +
            estatus +
            '"]'
        );


    if (tabActivo) {

        tabActivo.classList.add(
            'activo'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CONTENEDOR
    |--------------------------------------------------------------------------
    */

    const lista =
        document.getElementById(
            'listaSolicitudes'
        );

    const detalle =
        document.getElementById(
            'detalleSolicitud'
        );


    if (!lista) {

        console.error(
            'No existe #listaSolicitudes'
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | LIMPIAR DETALLE
    |--------------------------------------------------------------------------
    */

    if (detalle) {

        detalle.innerHTML = `

            <div class="vacaciones-detalle-vacio">

                <span
                    class="glyphicon glyphicon-hand-left"
                ></span>

                <p>
                    Selecciona una solicitud
                    para ver el detalle.
                </p>

            </div>

        `;

    }


    /*
    |--------------------------------------------------------------------------
    | LOADING
    |--------------------------------------------------------------------------
    */

    lista.innerHTML = `

        <div
            class="vacaciones-cargando"
            style="
                padding:30px;
                text-align:center;
            "
        >

            <span
                class="
                    glyphicon
                    glyphicon-refresh
                    glyphicon-refresh-animate
                "
            ></span>

            <p>
                Cargando solicitudes...
            </p>

        </div>

    `;


    /*
    |--------------------------------------------------------------------------
    | AJAX
    |--------------------------------------------------------------------------
    */

    const datos =
        new FormData();

    datos.append(
        'estatus',
        estatus
    );


    $.ajax({

        url: 'ajax/filtro_solicitudes_rh.php',
        type: 'POST',
        data: datos,
        processData: false,
        contentType: false,
        dataType: 'json',

        success:
            function (respuesta) {

                if (!respuesta.success) {

                    lista.innerHTML = `

                        <div
                            class="vacaciones-vacio"
                        >

                            <span
                                class="
                                    glyphicon
                                    glyphicon-warning-sign
                                "
                            ></span>

                            <p>
                                ${
                                    respuesta.message ||
                                    'No fue posible cargar las solicitudes.'
                                }
                            </p>

                        </div>

                    `;

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | ACTUALIZAR LISTA
                |--------------------------------------------------------------------------
                */

                lista.innerHTML =
                    respuesta.html;


                /*
                |--------------------------------------------------------------------------
                | ACTUALIZAR CONTADOR
                |--------------------------------------------------------------------------
                */

                const tabPendientes =
                    document.querySelector(
                        '.vacaciones-tab[data-estatus="PENDIENTE_RH"]'
                    );


                if (tabPendientes) {

                    let contador =
                        tabPendientes.querySelector(
                            '.vacaciones-contador'
                        );


                    if (
                        respuesta.total_pendientes > 0
                    ) {

                        if (!contador) {

                            contador =
                                document.createElement(
                                    'span'
                                );

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


                /*
                |--------------------------------------------------------------------------
                | REGISTRAR NUEVOS ITEMS
                |--------------------------------------------------------------------------
                */

                registrarItemsRH();

            },


        error:
            function (xhr) {

                console.error(
                    'ERROR FILTRO RH:',
                    xhr.status,
                    xhr.responseText
                );


                lista.innerHTML = `

                    <div
                        class="vacaciones-vacio"
                    >

                        <span
                            class="
                                glyphicon
                                glyphicon-warning-sign
                            "
                        ></span>

                        <p>
                            No fue posible cargar
                            las solicitudes.
                        </p>

                    </div>

                `;

            }

    });

}
// SELECCIONAR SOLICITUD
function seleccionarSolicitudRH(item) {

    document
        .querySelectorAll('.vacaciones-item')
        .forEach(function (elemento) {

            elemento.classList.remove(
                'seleccionado'
            );

        });


    item.classList.add(
        'seleccionado'
    );


    mostrarDetalleRH(
        item
    );

}
/**
 * ============================================================
 * MOSTRAR DETALLE RH
 * ============================================================
 */

function mostrarDetalleRH(item) {

    const detalle =
        document.getElementById(
            'detalleSolicitud'
        );


    if (!detalle || !item) {
        return;
    }


    const id =
        item.dataset.id;

    const nombre =
        item.dataset.nombre || '';

    const puesto =
        item.dataset.puesto || '';

    const departamento =
        item.dataset.departamento || '';

    const cuadrilla =
        item.dataset.cuadrilla || '';

    const grupo =
        item.dataset.grupo || '';

    const fechaSolicitud =
        item.dataset.fechaSolicitud || '';

    const fechaInicio =
        item.dataset.fechaInicio || '';

    const dias =
        item.dataset.dias || '0';

    const estatus =
        item.dataset.estatus || '';

    const estatusClase =
        item.dataset.estatusClase ||
        'estatus-default';

    const estatusIcono =
        item.dataset.estatusIcono ||
        'glyphicon-question-sign';


    /*
    |--------------------------------------------------------------------------
    | DETALLE
    |--------------------------------------------------------------------------
    */

    detalle.innerHTML = `

        <div class="detalle-encabezado">
            <div class="detalle-avatar">
                ${obtenerInicialesRH(nombre)}
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
            estatus === 'En RH' ||
            estatus === 'Pendientes'
            ?

            `
                <div class="detalle-acciones">

                    <button
                        type="button"
                        class="
                            btn
                            btn-vacaciones
                            btn-rechazar-rh
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
                            btn-aprobar-rh
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

    const botonAprobar =
        detalle.querySelector(
            '.btn-aprobar-rh'
        );


    if (botonAprobar) {

        botonAprobar.addEventListener(
            'click',
            function (e) {

                e.stopPropagation();

                confirmarFirmaVacacionesRH(id);

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
            '.btn-rechazar-rh'
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
/**
 * ============================================================
 * OBTENER INICIALES
 * ============================================================
 */

function obtenerInicialesRH(nombre) {

    if (!nombre) {
        return '?';
    }

    const partes = nombre
        .trim()
        .split(/\s+/);

    return partes
        .slice(0, 2)
        .map(function (parte) {

            return parte
                .charAt(0)
                .toUpperCase();

        })
        .join('');

}
/**
 * ============================================================
 * NO APROBAR SOLICITUD RH
 * ============================================================
 */

async function confirmarDecisionRH(id, accion) {

    if (accion !== 'rechazar') {
        return;
    }


    const resultado = await Swal.fire({

        title:
            'No aprobar solicitud',

        width:
            '600px',

        html: `

            <div style="text-align:left;">

                <p>
                    Indica el motivo por el cual
                    no se aprueba esta solicitud.
                </p>

                <label
                    for="observacionRH"
                    style="font-weight:600;"
                >
                    Observación
                </label>

                <textarea
                    id="observacionRH"
                    class="swal2-textarea"
                    placeholder="Escribe el motivo..."
                    style="
                        width:100%;
                        min-height:120px;
                        margin:10px 0 0 0;
                        resize:vertical;
                    "
                ></textarea>

            </div>

        `,

        showCancelButton:
            true,

        confirmButtonText:
            'No aprobar solicitud',

        cancelButtonText:
            'Cancelar',

        confirmButtonColor:
            '#dc3545',


        preConfirm:
            function () {

                const campo =
                    document.getElementById(
                        'observacionRH'
                    );


                const observacion =
                    campo
                        ? campo.value.trim()
                        : '';


                if (!observacion) {

                    Swal.showValidationMessage(
                        'Debes indicar una observación.'
                    );

                    return false;

                }


                return {
                    observacion:
                        observacion
                };

            }

    });


    if (!resultado.isConfirmed) {
        return;
    }


    enviarDecisionRH(
        id,
        'rechazar',
        resultado.value.observacion
    );

}
/**
 * ============================================================
 * ENVIAR DECISIÓN
 * ============================================================
 */

function enviarDecisionRH(
    id,
    accion,
    observacion
) {

    const datos = new FormData();

    datos.append(
        'id',
        id
    );

    datos.append(
        'accion',
        accion
    );

    datos.append(
        'observacion',
        observacion
    );


    $.ajax({

        url: 'ajax/procesar_solicitud_rh.php',
        type: 'POST',
        data: datos,
        processData: false,
        contentType: false,
        dataType:'json',

        success:
            function (respuesta) {

                if (respuesta.success) {

                    Swal.fire({

                        icon:
                            'success',

                        title:
                            accion === 'aprobar'
                                ? 'Solicitud aprobada'
                                : 'Solicitud no aprobada',

                        text:
                            respuesta.message,

                        confirmButtonText:
                            'Aceptar'

                    }).then(function () {

                        /*
                        |--------------------------------------------------------------------------
                        | ACTUALIZAR BANDEJA
                        |--------------------------------------------------------------------------
                        */

                        const filtroActivo =
                            document.querySelector(
                                '.vacaciones-tab.activo'
                            );


                        if (filtroActivo) {
                            filtroActivo.click();
                        }

                    });

                    return;
                }


                Swal.fire({

                    icon:'warning',
                    title:'No se puede realizar la acción',
                    text:
                        respuesta.message ||
                        'No fue posible procesar la solicitud.'

                });

            },


        error:
            function (xhr) {

                console.error(
                    'ERROR AJAX RH:',
                    xhr.status,
                    xhr.responseText
                );


                Swal.fire({

                    icon: 'error',
                    title: 'Error',
                    text: 'No fue posible procesar la solicitud.'

                });

            }

    });

}
/**
 * ============================================================
 * BUSCAR COLABORADOR RH
 * ============================================================
 */

function buscarColaboradorRH(texto) {

    const busqueda =
        texto
            .trim()
            .toLowerCase();


    document
        .querySelectorAll('.vacaciones-item')
        .forEach(function (item) {

            const nombre =
                (
                    item.dataset.nombre || ''
                ).toLowerCase();

            const puesto =
                (
                    item.dataset.puesto || ''
                ).toLowerCase();

            const departamento =
                (
                    item.dataset.departamento || ''
                ).toLowerCase();


            const coincide =
                nombre.includes(busqueda) ||
                puesto.includes(busqueda) ||
                departamento.includes(busqueda);


            item.style.display =
                coincide
                    ? ''
                    : 'none';

        });

}