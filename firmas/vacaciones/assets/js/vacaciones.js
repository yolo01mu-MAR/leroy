async function confirmarSolicitudVacaciones(form){

    let firma;
    const usuario = form.dataset.usuario;

    const resultado = await Swal.fire({

        title: 'Confirmar solicitud',

        html: `
            <canvas id="canvasFirma" style="width:100%; border:1px solid #ccc;"></canvas>
        `,

        showCancelButton: true,
        confirmButtonText: 'Firmar y enviar',
        cancelButtonText: 'Cancelar',

        didOpen: () => {

            firma = new FirmaElectronica({

                canvas: "canvasFirma",
                modulo: FirmaModulo.VACACIONES,
                registroId: 0,
                tipo: FirmaTipo.EMPLEADO,
                usuarioId: usuario

            });

        },

        preConfirm: () => {
            if(!firma.tieneFirma()){
                Swal.showValidationMessage("Debe capturar una firma.");
                return false;
            }
            return firma.obtenerDatos();
        }

    });

    if(!resultado.isConfirmed){
        return;
    }

    const datos = new FormData(form);
    datos.append("firma", resultado.value.firma);

    $.ajax({

        url: form.action,
        type: "POST",
        data: datos,
        processData: false,
        contentType: false,
        dataType: "json",

        success: function(respuesta){

            if(respuesta.ok){

                Swal.fire({
                    icon: "success",
                    title: "Solicitud enviada",
                    text: respuesta.mensaje
                }).then(() => {

                    window.location = "user_vacaciones.php";

                });

            } else {

                Swal.fire({
                    icon: "warning",
                    title: "No se puede realizar la solicitud",
                    text: respuesta.mensaje ||
                        "No fue posible registrar la solicitud.",
                    confirmButtonColor: "#f1c40f"
                });

            }

        },

        error: function(xhr){

            $("#btnEnviar").prop("disabled", false).html("Enviar");

//             console.log("STATUS:", xhr.status);
//             console.log("RESPUESTA SERVIDOR:", xhr.responseText);

            let respuesta;

            try {

                respuesta = JSON.parse(xhr.responseText);

            } catch(e) {

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No fue posible procesar la solicitud."
                });

                return;
            }

            Swal.fire({

                icon: "warning",

                title: "No se puede realizar la solicitud",

                text: respuesta.mensaje ||
                    "No fue posible registrar la solicitud.",

                confirmButtonColor: "#f1c40f"

            });

        }

    });
}
