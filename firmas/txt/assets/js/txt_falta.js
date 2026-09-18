async function confirmarSolicitudTxt(asistencia, fecha){

    // Si todavía no está autenticado,
    // pedir contraseña antes de continuar.
    if (!kioscoAutenticado) {

        const modalElement =
            document.getElementById("modalPassword");

        const modalPassword =
            bootstrap.Modal.getOrCreateInstance(modalElement);

        const inputPassword =
            document.getElementById("passwordKiosco");

        const errorPassword =
            document.getElementById("errorPassword");

        const btnValidar =
            document.getElementById("btnValidarPassword");

        // Limpiar modal
        inputPassword.value = "";
        errorPassword.classList.add("d-none");
        errorPassword.innerHTML = "";

        btnValidar.disabled = false;
        btnValidar.innerHTML = "Continuar";

        modalPassword.show();

        // Esperar a que el usuario valide
        const autenticado = await new Promise((resolve) => {

            btnValidar.onclick = async function(){

                const password =
                    inputPassword.value.trim();

                errorPassword.classList.add("d-none");
                errorPassword.innerHTML = "";

                if(password === ""){

                    errorPassword.innerHTML =
                        "Ingresa tu contraseña.";

                    errorPassword.classList.remove("d-none");

                    inputPassword.focus();

                    return;
                }

                btnValidar.disabled = true;
                btnValidar.innerHTML = "Validando...";

                const datos = new FormData();

                datos.append("password", password);

                try {

                    const response = await fetch(
                        "validar_password_kiosco.php",
                        {
                            method: "POST",
                            body: datos
                        }
                    );

                    const data = await response.json();

                    if(!data.ok){

                        errorPassword.innerHTML =
                            data.mensaje;

                        errorPassword.classList.remove("d-none");

                        inputPassword.value = "";
                        inputPassword.focus();

                        btnValidar.disabled = false;
                        btnValidar.innerHTML = "Continuar";

                        return;
                    }

                    // Contraseña correcta
                    kioscoAutenticado = true;

                    modalPassword.hide();

                    resolve(true);

                } catch(error){

                    console.error(error);

                    errorPassword.innerHTML =
                        "No fue posible validar la contraseña.";

                    errorPassword.classList.remove("d-none");

                    btnValidar.disabled = false;
                    btnValidar.innerHTML = "Continuar";

                }

            };

        });

        if(!autenticado){
            return;
        }
    }

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