$(function () {

    // CALENDARIO
    Calendario.init({
        modo: "simple",
        bloqueados: [
            "pasado"
        ],
        onChange: actualizarResumen
    });

    // ACTUALIZAR RESUMEN
    function actualizarResumen(datos){
        if(datos.error){
            Swal.fire({
                icon:  "warning",
                title: "Fecha no disponible",
                text:  "No puedes seleccionar esa fecha.",
                confirmButtonColor: "#f1c40f"
            });
            return;
        }
        $("#lblFecha").text(
            datos.inicioTexto ?? "—"
        );
        $("#fecha").val(
            datos.inicio ?? ""
        );
        validarFormulario();
    }

    // CONTADOR DEL MOTIVO
    $("#motivo").on("input", function(){
        $("#contadorMotivo").text(
            this.value.length + " / 300"
        );
        validarFormulario();
    });

    // VALIDAR FORMULARIO
    function validarFormulario(){
        const fecha =
            $("#fecha").val();
        const motivo =
            $("#motivo").val().trim();
        $("#btnEnviar").prop(
            "disabled",
            fecha === "" || motivo === ""
        );
    }

    // LIMPIAR
    $("#btnLimpiar").on("click", function(){
        Calendario.limpiar();
        $("#fecha").val("");
        $("#lblFecha").text("—");
        $("#motivo").val("");
        $("#contadorMotivo").text("0 / 300");
        validarFormulario();
    });

    // ENVIAR SOLICITUD
    $("#formTxt").on("submit", async function(e){
        e.preventDefault();
        const fecha =
            $("#fecha").val();
        const motivo =
            $("#motivo").val().trim();

        // VALIDAR IDENTIDAD
        if(!kioscoAutenticado){

            const modalElement =
                document.getElementById("modalPasswordTxtFutura");

            const modalPassword =
                bootstrap.Modal.getOrCreateInstance(modalElement);

            const inputPassword =
                document.getElementById("passwordTxtFutura");

            const errorPassword =
                document.getElementById("errorPasswordTxtFutura");

            const btnValidar =
                document.getElementById("btnValidarPasswordTxtFutura");

            // Limpiar modal
            inputPassword.value = "";

            errorPassword.classList.add("d-none");
            errorPassword.innerHTML = "";

            btnValidar.disabled = false;
            btnValidar.innerHTML = "Continuar";

            modalPassword.show();

            // Esperar validación
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

                    datos.append(
                        "password",
                        password
                    );

                    try {

                        const response = await fetch(
                            "validar_password_kiosco.php",
                            {
                                method: "POST",
                                body: datos
                            }
                        );

                        const data =
                            await response.json();

                        if(!data.ok){

                            errorPassword.innerHTML =
                                data.mensaje;

                            errorPassword.classList.remove("d-none");

                            inputPassword.value = "";
                            inputPassword.focus();

                            btnValidar.disabled = false;
                            btnValidar.innerHTML =
                                "Continuar";

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
                        btnValidar.innerHTML =
                            "Continuar";
                    }

                };

            });

            if(!autenticado){
                return;
            }

        }

        // VALIDAR FECHA
        if(!fecha){
            Swal.fire({
                icon:  "warning",
                title: "Fecha requerida",
                text:  "Selecciona el día que necesitas faltar.",
                confirmButtonColor: "#f1c40f"
            });
            return;
        }

        // VALIDAR MOTIVO
        if(!motivo){
            Swal.fire({
                icon: "warning",
                title: "Motivo requerido",
                text:
                    "Describe el motivo de la solicitud.",
                confirmButtonColor: "#f1c40f"
            });
            return;
        }

        // CONFIRMAR SOLICITUD
        const confirmacion = await Swal.fire({
            icon: "question",
            title: "¿Enviar solicitud?",
            html:
                "<b>Fecha:</b> " +
                formatearFecha(fecha) +
                "<br><b>Motivo:</b> " +
                motivo,
            showCancelButton: true,
            confirmButtonText:
                "Continuar",
            cancelButtonText:
                "Cancelar",
            confirmButtonColor:
                "#f1c40f",
            cancelButtonColor:
                "#6c757d"
        });


        if(!confirmacion.isConfirmed){
            return;
        }

        // SOLICITAR FIRMA
        await confirmarSolicitudTxtFutura(
            fecha,
            motivo
        );

    });

});

// FORMATEAR FECHA
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

// MODAL DE FIRMA
async function confirmarSolicitudTxtFutura(
    fecha,
    motivo
){

    let firma;

    // USUARIO
    const usuario =
        $("#usuario").val();

    // MODAL DE FIRMA
    const resultado = await Swal.fire({

        title: "Firmar solicitud",

        html: `
            <div class="text-start">
                <p class="mb-2">
                    <strong>Fecha:</strong>
                    ${formatearFecha(fecha)}
                </p>
                <p class="mb-3">
                    <strong>Motivo:</strong>
                    ${motivo}
                </p>
                <label class="form-label">
                    Firma del empleado
                </label>
                <canvas
                    id="canvasFirmaTxtFutura"
                    style="
                        width:100%;
                        height:200px;
                        border:1px solid #ccc;
                        border-radius:8px;
                        background:#fff;
                    ">
                </canvas>
            </div>
        `,

        showCancelButton: true,
        confirmButtonText:
            "Firmar y enviar",
        cancelButtonText:
            "Cancelar",
        confirmButtonColor:
            "#f1c40f",
        cancelButtonColor:
            "#6c757d",

        // CREAR FIRMA
        didOpen: () => {

            firma = new FirmaElectronica({

                canvas:
                    "canvasFirmaTxtFutura",

                modulo:
                    FirmaModulo.TXT,

                registroId:
                    0,

                tipo:
                    FirmaTipo.EMPLEADO,

                usuarioId:
                    usuario

            });

        },

        // VALIDAR FIRMA
        preConfirm: () => {

            if(
                !firma ||
                !firma.tieneFirma()
            ){

                Swal.showValidationMessage(

                    "Debe capturar una firma."

                );

                return false;

            }


            return {

                firma:
                    firma.obtenerBase64()

            };

        }

    });


    if(!resultado.isConfirmed){

        return;

    }

    // ENVIAR AL SERVIDOR
    enviarSolicitudTxtFutura(

        fecha,

        motivo,

        resultado.value.firma

    );

}

// ENVIAR SOLICITUD AL SERVIDOR
async function enviarSolicitudTxtFutura(
    fecha,
    motivo,
    firma
){

    const datos =
        new FormData();


    datos.append(
        "fecha",
        fecha
    );

    datos.append(
        "motivo",
        motivo
    );

    datos.append(
        "firma",
        firma
    );

    // DESHABILITAR BOTÓN
    $("#btnEnviar")
        .prop("disabled", true)
        .html(
            '<span class="spinner-border spinner-border-sm"></span> Enviando...'
        );

    // AJAX
    $.ajax({

        url: "guardar_txt_futura.php",
        type: "POST",
        data: datos,
        processData: false,
        contentType: false,
        dataType: "json",

        // SUCCESS
        success: function(respuesta){

            console.log(
                "RESPUESTA TXT FUTURA:"
            );

            if(respuesta.ok){

                Swal.fire({

                    icon: "success",
                    title: "Solicitud enviada",
                    text:
                        "Tu solicitud fue registrada y enviada " +
                        "a tu jefe para revisión.",

                    confirmButtonText:
                        "Aceptar",

                    confirmButtonColor:
                        "#f1c40f"

                }).then(() => {

                    window.location =
                        "user_tiempo.php";

                });

            }else{

                $("#btnEnviar")
                    .prop("disabled", false)
                    .html(
                        '<i class="bi bi-send"></i> Enviar solicitud'
                    );


                Swal.fire({

                    icon: "error",

                    title:
                        "No fue posible enviar la solicitud",

                    text:
                        respuesta.mensaje ??
                        "Ocurrió un error.",

                    confirmButtonColor:
                        "#f1c40f"

                });

            }

        },

        // ERROR AJAX
        error: function(xhr){

            console.error(
                "ERROR AJAX TXT FUTURA"
            );

            console.error(
                xhr.status
            );

            console.error(
                xhr.responseText
            );


            $("#btnEnviar")
                .prop("disabled", false)
                .html(
                    '<i class="bi bi-send"></i> Enviar solicitud'
                );


            let mensaje =
                "Ocurrió un error al enviar la solicitud.";


            try{

                const respuesta =
                    JSON.parse(
                        xhr.responseText
                    );

                if(respuesta.mensaje){

                    mensaje =
                        respuesta.mensaje;

                }

            }catch(e){

                console.error(e);

            }


            Swal.fire({

                icon: "error",
                title: "Error",
                text: mensaje,
                confirmButtonColor: "#f1c40f"

            });

        }

    });

}