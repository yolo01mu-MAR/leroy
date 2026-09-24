function mostrarModalOlvidePassword(){

    $("#nominaOlvidada").val("");
    $("#errorOlvidePassword")
        .addClass("d-none")
        .html("");

    const modal = new bootstrap.Modal(
        document.getElementById("modalOlvidePassword")
    );

    modal.show();

    setTimeout(function(){
        $("#nominaOlvidada").focus();
    }, 300);
}
function mostrarModalRecuperarDesdeKiosco(){

    const empleadoId = window.kioscoEmpleadoId || 0;

    if(!empleadoId){

        Swal.fire({
            icon: "error",
            title: "No fue posible identificarte",
            text: "No se encontró el colaborador actual."
        });

        return;
    }

    $("#usuarioRecuperacion").val(empleadoId);

    Swal.fire({

        icon: "question",
        title: "¿Olvidaste tu contraseña?",
        html: `
            <p>
                Se enviará una solicitud al área de
                <strong>Sistemas</strong> para restablecer
                tu contraseña.
            </p>

            <p class="mb-0">
                ¿Deseas solicitar el cambio?
            </p>
        `,

        showCancelButton: true,
        confirmButtonText: "Solicitar cambio",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#f1c40f",
        cancelButtonColor: "#6c757d"

    }).then((resultado) => {

        if(!resultado.isConfirmed){
            return;
        }

        solicitarCambioPasswordKiosco(empleadoId);

    });
}
function solicitarCambioPassword(){

    const nomina = $("#nominaOlvidada").val().trim();

    const error = $("#errorOlvidePassword");
    const boton = $("#btnOlvidePassword");

    error
        .addClass("d-none")
        .html("");

    if(nomina === ""){

        error
            .html("Ingrese su número de nómina.")
            .removeClass("d-none");

        $("#nominaOlvidada").focus();

        return;
    }

    if(!/^\d+$/.test(nomina)){

        error
            .html("El número de nómina no es válido.")
            .removeClass("d-none");

        $("#nominaOlvidada").focus();

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | ENVIAR SOLICITUD
    |--------------------------------------------------------------------------
    */

    boton
        .prop("disabled", true)
        .html(`
            <span class="spinner-border spinner-border-sm"></span>
            Enviando...
        `);

    $.ajax({

        url: BASE_URL + "/ajax/solicitar_cambio_password.php",
        type: "POST",
        dataType: "json",
        data: {
            nomina: nomina
        },

        success: function(respuesta){

            if(!respuesta.ok){

                error
                    .html(
                        respuesta.mensaje ||
                        "No fue posible enviar la solicitud."
                    )
                    .removeClass("d-none");

                boton
                    .prop("disabled", false)
                    .html(`
                        <i class="bi bi-send"></i>
                        Solicitar cambio
                    `);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ÉXITO
            |--------------------------------------------------------------------------
            */
            const modalElement = document.getElementById("modalOlvidePassword");
            const modal = bootstrap.Modal.getInstance(modalElement);

            if(modal){
                modal.hide();
            }


            Swal.fire({

                icon: "success",
                title: "Solicitud enviada",
                html: `
                    <p>
                        Tu solicitud fue enviada correctamente
                        al área de Sistemas.
                    </p>
                    <p class="mb-0">
                        <strong>Folio:</strong>
                        ${respuesta.folio}
                    </p>
                `,

                confirmButtonText: "Aceptar"

            });


            boton
                .prop("disabled", false)
                .html(`
                    <i class="bi bi-send"></i>
                    Solicitar cambio
                `);

        },

        error: function(xhr){

            console.error(
                xhr.responseText
            );

            let mensaje =
                "No fue posible enviar la solicitud.";

            try {

                const respuesta =
                    JSON.parse(
                        xhr.responseText
                    );

                if(respuesta.mensaje){
                    mensaje =
                        respuesta.mensaje;
                }

            } catch(e){}


            error
                .html(mensaje)
                .removeClass("d-none");

            boton
                .prop("disabled", false)
                .html(`
                    <i class="bi bi-send"></i>
                    Solicitar cambio
                `);
        }

    });
}
function solicitarCambioPasswordKiosco(usuarioId){

    Swal.fire({

        title: "Enviando solicitud...",

        allowOutsideClick: false,
        allowEscapeKey: false,

        didOpen: () => {
            Swal.showLoading();
        }

    });

    $.ajax({

        url: BASE_URL + "/ajax/solicitar_cambio_password.php",

        type: "POST",

        dataType: "json",

        data: {
            usuario_id: usuarioId
        },

        success: function(respuesta){

            if(!respuesta.ok){

                Swal.fire({

                    icon: "error",

                    title: "No fue posible enviar",

                    text:
                        respuesta.mensaje ||
                        "No fue posible enviar la solicitud."

                });

                return;
            }

Swal.fire({
    icon: "success",
    title: "Solicitud enviada",
    html: `
        <p>
            Tu solicitud fue enviada correctamente
            al área de Sistemas.
        </p>

        <p class="mb-0">
            <strong>Folio:</strong>
            ${respuesta.folio}
        </p>
    `,
    confirmButtonText: "Ir a Soporte",
    allowOutsideClick: false,
    allowEscapeKey: false
}).then((resultado) => {

    if (resultado.isConfirmed) {

        window.location.href =
            BASE_URL + "/modules/kiosco/user_soporte.php";

    }

});

        },

        error: function(xhr){

            console.error(xhr.responseText);

            let mensaje =
                "No fue posible enviar la solicitud.";

            try {

                const respuesta =
                    JSON.parse(xhr.responseText);

                if(respuesta.mensaje){
                    mensaje = respuesta.mensaje;
                }

            } catch(e){}

            Swal.fire({

                icon: "error",

                title: "Error",

                text: mensaje

            });

        }

    });
}
