document.addEventListener("DOMContentLoaded", () => {

    const buscador = document.getElementById("buscador");
    const tabla    = document.getElementById("tabla-resultados");

    const tablaOriginal = tabla.innerHTML;
    let timeout = null;

    buscador.addEventListener("keyup", () => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            const texto = buscador.value.trim();

            if (texto === '') {
                tabla.innerHTML = tablaOriginal;
                return;
            }

            fetch("ajax/buscar_usuario_saldo.php?q=" + encodeURIComponent(texto))
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Respuesta no válida del servidor');
                    }
                    return res.text();
                })
                .then(html => {
                    tabla.innerHTML = html;
                })
                .catch(err => {
                    tabla.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding:20px;">
                                <strong>Ocurrió un error al buscar. Intenta de nuevo.</strong>
                            </td>
                        </tr>
                    `;
                    console.error('Error en búsqueda de saldo:', err);
                });

        }, 300);
    });


    /* ---------------------------------------------------- */
    /* MODAL DE DETALLE                                      */
    /* Este bloque se ejecuta aquí (y no en la vista) porque  */
    /* jQuery/Bootstrap ya están cargados para cuando este    */
    /* archivo corre (se incluye vía $scripts en el footer). */
    /* ---------------------------------------------------- */
 
    if (typeof $ === 'undefined') {
        console.error(
            'buscar_usuario_saldo.js: jQuery no está disponible todavía. ' +
            'Revisa que este script se cargue después de jquery.js en el footer.'
        );
        return;
    }
 
$(document).on("click", ".fila-saldo", function(){

    const fila = $(this);

    $("#detalle-nomina").text(
        fila.data("nomina")
    );

    $("#detalle-nombre").text(
        fila.data("nombre")
    );

    $("#detalle-nombre-titulo").text(
        fila.data("nombre")
    );

    $("#detalle-ingreso").text(
        fila.data("ingreso")
    );

    $("#detalle-inicio").text(
        fila.data("inicio")
    );

    $("#detalle-fin").text(
        fila.data("fin")
    );

    $("#detalle-otorgados").text(
        fila.data("otorgados")
    );

    $("#detalle-disfrutados").text(
        fila.data("disfrutados")
    );

    $("#detalle-pendientes").text(
        fila.data("pendientes")
    );

    $("#detalle-creo").text(
        fila.data("creo")
    );

    $("#detalle-actualizo").text(
        fila.data("actualizo")
    );

    // ESTADO
    const estado =
        fila.data("estatus");

    const $estado =
        $("#detalle-estado");

    $estado
        .removeClass(
            "label-success label-default label-warning label-info"
        );

    if(estado === "VIGENTE"){

        $estado
            .addClass("label-success")
            .text("VIGENTE");

    }else if(estado === "FINALIZADO"){

        $estado
            .addClass("label-default")
            .text("INACTIVO");

    }else{

        $estado
            .addClass("label-default")
            .text(estado || "DESCONOCIDO");

    }

    $("#modalDetalleEncargado").modal("show");

});


});