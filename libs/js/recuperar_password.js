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