$(function () {

    //====================================================
    // Configuración
    //====================================================
    const saldoDisponible = parseInt($("#formVacaciones").data("saldo"));

    //====================================================
    // Inicializar calendario
    //====================================================
    const inicioGuardado =
        $("#inicioGuardado").val();


    Calendario.init({

        modo: "rango",

        inicio:
            inicioGuardado || null,

        fin: null,

        bloqueados: [
            "lleno",
            "bloqueado",
            "pasado",
            "no_laborable"
        ],

        onChange:
            actualizarResumen

    });

    //====================================================
    // Actualizar resumen
    //====================================================
    function actualizarResumen(datos){

        if(datos.error){

            mostrarMensaje(
                "warning",
                "Existe al menos un día no disponible dentro del periodo."
            );

            return;
        }

        $("#outInicio").text(datos.inicioTexto);
        $("#outFin").text(datos.finTexto);
        $("#outDias").text(datos.dias);

        $("#inputInicio").val(datos.inicio ?? "");
        $("#inputFin").val(datos.fin ?? "");

        $("#btnEnviar").prop(
            "disabled",
            !datos.completo
        );

    }

    //====================================================
    // Validar saldo
    //====================================================
    function validarSaldo(){

        const datos = Calendario.estado;

        if(datos.dias > saldoDisponible){

            Swal.fire({

                icon:"warning",
                title:"Saldo insuficiente",

                text:
                    "Solo cuentas con " +
                    saldoDisponible +
                    " días disponibles.",

                confirmButtonColor:"#f1c40f"

            });

            return false;

        }

        return true;

    }

    //====================================================
    // Mensajes
    //====================================================
    function mostrarMensaje(tipo,texto){

        const iconos = {

            success:"success",
            warning:"warning",
            danger:"error",
            info:"info"

        };

        Swal.fire({

            icon: iconos[tipo] ?? "info",
            text: texto,
            confirmButtonColor:"#f1c40f"

        });

    }

    //====================================================
    // Enviar formulario
    //====================================================
    $("#formVacaciones").on("submit",function(e){

        if($(this).data("confirmado")){
            return;
        }

        e.preventDefault();

        const datos = Calendario.estado;

        if(!datos.completo){

            mostrarMensaje(
                "danger",
                "Selecciona un periodo de vacaciones."
            );

            return;

        }

        if(!validarSaldo()){
            return;
        }

        Swal.fire({

            icon:"question",
            title:"¿Enviar solicitud?",

            html:
                "<b>Inicio:</b> " + datos.inicioTexto +
                "<br><b>Fin:</b> " + datos.finTexto +
                "<br><b>Días:</b> " + datos.dias,

            showCancelButton:true,
            confirmButtonText:"Enviar",
            cancelButtonText:"Cancelar",
            confirmButtonColor:"#f1c40f",
            cancelButtonColor:"#6c757d"

        }).then((result)=>{

            if(!result.isConfirmed){
                return;
            }

            $("#btnEnviar")
                .prop("disabled",true)
                .html('<span class="spinner-border spinner-border-sm"></span> Enviando...');

            // ============================================
            // ABRIR FIRMA ELECTRÓNICA
            // ============================================
            confirmarSolicitudVacaciones(
                document.getElementById("formVacaciones")
            );

        });

    });

    //====================================================
    // Limpiar selección
    //====================================================
    $("#btnBorrarFechas").on("click",function(){

        Calendario.limpiar();

    });

});
//====================================================
// NAVEGACIÓN DE MESES
//====================================================

function cargarMes(mes, anio){

    $.ajax({

        url: "ajax/calendario_mes.php",
        type: "GET",
        dataType: "json",

        data: {
            mes: mes,
            anio: anio
        },

        beforeSend: function(){

            $("#calendarioGridDias")
                .css(
                    "opacity",
                    "0.5"
                );

        }

    })

    .done(function(resp){

        if(!resp.ok){

            Swal.fire({

                icon:  "error",
                title: "Error",
                text: resp.mensaje,

                confirmButtonColor:
                    "#f1c40f"

            });

            return;

        }

        //============================================
        // ACTUALIZAR TÍTULO
        //============================================

        $("#tituloMes")
            .text(resp.nombre_mes);

        //============================================
        // ACTUALIZAR GRID
        //============================================

        $("#calendarioGridDias")

            .html(resp.html_grid)
            .attr("data-mes", resp.mes)
            .attr("data-anio", resp.anio);

        //============================================
        // ACTUALIZAR MES ANTERIOR
        //============================================

        $("#btnMesAnterior")

            .data("mes", resp.mesAnterior)
            .data("anio", resp.anioAnterior)
            .prop("disabled", !resp.permitirAnterior);

        //============================================
        // ACTUALIZAR MES SIGUIENTE
        //============================================

        $("#btnMesSiguiente")

            .data("mes", resp.mesSiguiente)
            .data("anio", resp.anioSiguiente);

        //============================================
        // VOLVER A CONECTAR CALENDARIO
        //============================================

        Calendario.eventos();
        Calendario.pintar();

    })


    .fail(function(xhr){

        console.error(
            "Error AJAX calendario:",
            xhr.responseText
        );


        Swal.fire({

            icon: "error",
            title: "Error",
            text: "No fue posible cargar el mes.",
            confirmButtonColor: "#f1c40f"

        });

    })


    .always(function(){

        $("#calendarioGridDias")
            .css(
                "opacity",
                "1"
            );

    });

}


//====================================================
// MES ANTERIOR
//====================================================

$(document).on(
    "click",
    "#btnMesAnterior",
    function(e){

        e.preventDefault();

        if($(this).prop("disabled")){

            return;

        }

        cargarMes(
            $(this).data("mes"),
            $(this).data("anio")
        );

    }
);


//====================================================
// MES SIGUIENTE
//====================================================

$(document).on(
    "click",
    "#btnMesSiguiente",
    function(e){

        e.preventDefault();

        if($(this).prop("disabled")){

            return;

        }

        cargarMes(
            $(this).data("mes"),
            $(this).data("anio")
        );

    }
);