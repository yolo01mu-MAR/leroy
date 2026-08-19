let solicitudes = [];

$(function () {

    // CARGAR SOLICITUDES
    cargarSolicitudes();

    async function cargarSolicitudes(){

        try {
            const respuesta = await $.ajax({
                url: "ajax/obtener_solicitudes.php",
                type: "GET",
                dataType: "json"
            });

            if(!respuesta.ok){
                throw new Error(
                    respuesta.mensaje ??
                    "No fue posible cargar las solicitudes."
                );
            }

            solicitudes = respuesta.solicitudes ?? [];

            actualizarContadores();
            renderSolicitudes(solicitudes);

        } catch(error){

            console.error(error);

            $("#listaSolicitudes").html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    No fue posible cargar tus solicitudes.
                </div>
            `);

        }

    }

    // CONTADORES
    function actualizarContadores(){

        const total = solicitudes.length;

        const pendientes = solicitudes.filter(function(s){

            return esPendiente(
                s.estatus
            );

        }).length;


        const aprobadas =
            solicitudes.filter(function(s){

                return esAprobada(
                    s.estatus
                );

            }).length;

        const rechazadas =
            solicitudes.filter(function(s){

                return esRechazada(
                    s.estatus
                );

            }).length;


        $("#totalSolicitudes")
            .text(total);


        $("#totalPendientes")
            .text(pendientes);


        $("#totalAprobadas")
            .text(aprobadas);


        $("#totalRechazadas")
            .text(rechazadas);

    }

    // FILTROS
    $("[data-filtro]").on(
        "click",
        function(){

            $("[data-filtro]")
                .removeClass("active");


            $(this)
                .addClass("active");


            const filtro =
                $(this).data("filtro");


            if(filtro === "TODAS"){

                renderSolicitudes(
                    solicitudes
                );

                return;

            }

            renderSolicitudes(

                solicitudes.filter(
                    function(s){

                        return s.tipo === filtro;

                    }
                )

            );

        }
    );

});

// ESTADOS
function esPendiente(estatus){

    return [

        "PENDIENTE_JEFE",
        "PENDIENTE_RH"

    ].includes(estatus);

}


function esAprobada(estatus){

    return [

        "APROBADA",
        "COMPLETADA"

    ].includes(estatus);

}


function esRechazada(estatus){

    return [

        "RECHAZADA_JEFE",
        "RECHAZADA_RH"

    ].includes(estatus);

}

// RENDERIZAR SOLICITUDES
function renderSolicitudes(lista){

    if(!lista.length){

        $("#listaSolicitudes").html(`

            <div class="sin-solicitudes">

                <i class="bi bi-inbox"></i>

                <h5 class="mt-3">

                    No hay solicitudes

                </h5>

                <p class="text-muted mb-0">

                    Aquí aparecerán tus solicitudes recientes.

                </p>

            </div>

        `);

        return;

    }


    let html = "";


    lista.forEach(function(solicitud){

        html += crearTarjeta(
            solicitud
        );

    });


    $("#listaSolicitudes")
        .html(html);

}

// CREAR TARJETA
function crearTarjeta(solicitud){

    const vacaciones =
        solicitud.tipo === "VACACIONES";


    const icono = vacaciones
        ? "bi-calendar-heart"
        : "bi-clock-history";


    const titulo = vacaciones
        ? "Vacaciones"
        : "Tiempo x Tiempo";


    const fecha =
        formatearFecha(
            solicitud.fecha_inicio
        );


    const estado =
        obtenerEstado(
            solicitud.estatus
        );


    return `

        <div class="solicitud-card p-3 mb-2">

            <div class="
                d-flex
                align-items-center
                justify-content-between
            ">

                <div class="
                    d-flex
                    align-items-center
                    gap-3
                ">

                    <div class="
                        solicitud-icon
                        bg-warning-subtle
                    ">

                        <i class="
                            bi ${icono}
                            text-warning
                        "></i>

                    </div>


                    <div>

                        <div class="solicitud-titulo">

                            ${titulo}

                        </div>

                        <div class="solicitud-fecha">

                            ${fecha}

                        </div>

                    </div>

                </div>


                <div class="text-end">

                    <div class="
                        estado
                        ${estado.clase}
                    ">

                        <i class="
                            bi ${estado.icono}
                        "></i>

                        ${estado.texto}

                    </div>


                    <button
                        type="button"
                        class="btn btn-sm btn-link mt-1"
                        data-id="${solicitud.id}"
                        data-tipo="${solicitud.tipo}"
                        onclick="
                            verSolicitud(
                                this.dataset.id,
                                this.dataset.tipo
                            )
                        "
                    >

                        Ver detalles

                        <i class="bi bi-chevron-right"></i>

                    </button>

                </div>

            </div>

        </div>

    `;

}


// ============================================================
// OBTENER ESTADO VISUAL
// ============================================================

function obtenerEstado(estatus){

    switch(estatus){

        case "PENDIENTE_JEFE":

        case "PENDIENTE_RH":

            return {

                texto: "Pendiente",

                clase: "estado-pendiente",

                icono: "bi-hourglass-split"

            };


        case "APROBADA":

        case "COMPLETADA":

            return {

                texto: "Aprobada",

                clase: "estado-aprobada",

                icono: "bi-check-circle"

            };


        case "RECHAZADA_JEFE":

        case "RECHAZADA_RH":

            return {

                texto: "Rechazada",

                clase: "estado-rechazada",

                icono: "bi-x-circle"

            };


        default:

            return {

                texto:
                    estatus ?? "Desconocido",

                clase:
                    "estado-pendiente",

                icono:
                    "bi-info-circle"

            };

    }

}


// ============================================================
// FORMATEAR FECHA
// ============================================================

function formatearFecha(fecha){

    if(!fecha){

        return "—";

    }


    const partes =
        fecha.split("-");


    if(partes.length !== 3){

        return fecha;

    }


    return `${partes[2]}/${partes[1]}/${partes[0]}`;

}


// ============================================================
// FORMATEAR FECHA Y HORA
// ============================================================

function formatearFechaHora(fechaHora){

    if(!fechaHora){

        return "—";

    }


    const partes =
        fechaHora.split(" ");


    if(partes.length < 2){

        return formatearFecha(
            fechaHora
        );

    }


    return (

        formatearFecha(
            partes[0]
        )

        +

        " "

        +

        partes[1].substring(0,5)

    );

}


// ============================================================
// VER SOLICITUD
// ============================================================

function verSolicitud(id, tipo){

    const solicitud =
        solicitudes.find(function(s){

            return (

                String(s.id) ===
                String(id)

                &&

                String(s.tipo) ===
                String(tipo)

            );

        });


    if(!solicitud){

        Swal.fire({

            icon:  "error",
            title: "Solicitud no encontrada",
            text:  "No fue posible obtener la información de esta solicitud."

        });

        return;

    }

    const esVacaciones =
        solicitud.tipo === "VACACIONES";


    const estado =
        obtenerEstado(
            solicitud.estatus
        );


    let html = `

        <div class="text-start">

            <div class="text-center mb-4">

                <div
                    class="
                        solicitud-icon
                        bg-warning-subtle
                        mx-auto
                        mb-2
                    "
                >

                    <i class="
                        bi
                        ${
                            esVacaciones
                            ? "bi-calendar-heart"
                            : "bi-clock-history"
                        }
                        text-warning
                    "></i>

                </div>


                <h5 class="mb-1">

                    ${
                        esVacaciones
                        ? "Solicitud de vacaciones"
                        : "Solicitud de Tiempo x Tiempo"
                    }

                </h5>


                <span class="
                    estado
                    ${estado.clase}
                ">

                    <i class="
                        bi
                        ${estado.icono}
                    "></i>

                    ${estado.texto}

                </span>

            </div>


            <div class="
                border
                rounded
                p-3
                mb-3
            ">

                <div class="row g-3">


                    <div class="col-6">

                        <small class="text-muted">

                            Fecha

                        </small>

                        <div class="fw-semibold">

                            ${formatearFecha(
                                solicitud.fecha_inicio
                            )}

                        </div>

                    </div>

    `;


    // ========================================================
    // VACACIONES
    // ========================================================

    if(esVacaciones){

        html += `

                    <div class="col-6">

                        <small class="text-muted">

                            Hasta

                        </small>

                        <div class="fw-semibold">

                            ${formatearFecha(
                                solicitud.fecha_fin
                            )}

                        </div>

                    </div>


                    <div class="col-6">

                        <small class="text-muted">

                            Días

                        </small>

                        <div class="fw-semibold">

                            ${solicitud.dias}

                        </div>

                    </div>

        `;

    }


    // ========================================================
    // TXT
    // ========================================================

    else{

        html += `

                    <div class="col-6">

                        <small class="text-muted">

                            Tipo

                        </small>

                        <div class="fw-semibold">

                            ${
                                solicitud.tipo_solicitud
                                ?? "Tiempo x Tiempo"
                            }

                        </div>

                    </div>

        `;

    }


    html += `

                </div>

            </div>


            <div class="
                border
                rounded
                p-3
            ">

                <small class="text-muted">

                    Fecha de solicitud

                </small>

                <div class="fw-semibold">

                    ${formatearFechaHora(
                        solicitud.fecha_solicitud
                    )}

                </div>

            </div>

        </div>

    `;


    Swal.fire({

        title:
            "Detalle de solicitud",

        html:
            html,

        confirmButtonText:
            "Cerrar",

        confirmButtonColor:
            "#e0b818",

        width:
            500

    });

}