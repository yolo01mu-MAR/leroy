async function confirmarSolicitudTxt(asistencia, fecha){

    const respuesta = await Swal.fire({

        title: "Solicitar Tiempo por Tiempo",

        html: `
            <div class="text-start">
                <label class="form-label">
                    Fecha
                </label>
                <input
                    class="form-control mb-3"
                    value="${fecha}"
                    readonly>
                <label class="form-label">
                    Motivo
                </label>
                <textarea
                    id="txtMotivo"
                    class="form-control"
                    rows="4"
                    maxlength="300"
                    placeholder="Describe el motivo"></textarea>
            </div>
        `,

        showCancelButton:true,
        confirmButtonText:"Continuar",
        cancelButtonText:"Cancelar",

        preConfirm:()=>{

            const motivo = $("#txtMotivo")
                .val()
                .trim();

            if(motivo==""){

                Swal.showValidationMessage(
                    "Debe escribir el motivo."
                );

                return false;

            }

            return{

                asistencia,
                fecha,
                motivo

            };

        }

    });

    if(!respuesta.isConfirmed){
        return;
    }

    let firma;
    const usuario = $("#usuario").val();

    const resultado = await Swal.fire({

        title: "Confirmar solicitud",

        html: `
            <canvas
                id="canvasFirma"
                style="width:100%;border:1px solid #ccc;">
            </canvas>
        `,

        showCancelButton: true,
        confirmButtonText: "Firmar y enviar",
        cancelButtonText: "Cancelar",

        didOpen: () => {

            firma = new FirmaElectronica({

                canvas: "canvasFirma",
                modulo: FirmaModulo.TXT,
                registroId: 0,
                tipo: FirmaTipo.EMPLEADO,
                usuarioId: usuario

            });

        },

        preConfirm: () => {

            if(!firma.tieneFirma()){

                Swal.showValidationMessage(
                    "Debe capturar una firma."
                );

                return false;

            }

            return {

                solicitud: respuesta.value,
                firma: firma.obtenerBase64()

            };

        }

    });

    if(!resultado.isConfirmed){
        return;
    }

$.ajax({

    url: "guardar_txt_falta.php",
    type: "POST",

    data: {

        asistencia:
            resultado.value.solicitud.asistencia,

        fecha:
            resultado.value.solicitud.fecha,

        motivo:
            resultado.value.solicitud.motivo,

        firma:
            resultado.value.firma

    },

    dataType: "json",

    success: function(respuesta){

        if(respuesta.ok){

            Swal.fire({

                icon: "success",
                title: "Solicitud enviada",
                text:
                    "Tu solicitud fue registrada y enviada " +
                    "a tu jefe para revisión.",

                confirmButtonText: "Aceptar",

                confirmButtonColor: "#f1c40f"

            }).then(() => {

                window.location = "user_tiempo.php";

            });

        }else{

            Swal.fire({

                icon: "error",

                title: "No fue posible enviar la solicitud",

                text: respuesta.mensaje,

                confirmButtonText: "Aceptar",

                confirmButtonColor: "#f1c40f"

            });

        }

    },

    error: function(xhr){

        console.error(xhr.responseText);

        let mensaje =
            "Ocurrió un error al enviar la solicitud.";

        try{

            const respuesta =
                JSON.parse(xhr.responseText);

            if(respuesta.mensaje){
                mensaje = respuesta.mensaje;
            }

        }catch(e){}

        Swal.fire({

            icon: "error",
            title: "Error",
            text: mensaje,
            confirmButtonText: "Aceptar",
            confirmButtonColor: "#f1c40f"

        });

    }

});

}