function volver(){

    // Si estamos viendo el menú principal,
    // regresar al login principal
    if ($("#menuPrincipal").is(":visible")) {
        window.location.href = "../../index.php";
        return;
    }
    // Si estamos dentro de una pantalla secundaria,
    // regresar al menú del kiosco
    if (typeof LectorQR !== "undefined" && LectorQR.detener) {
        LectorQR.detener();
    }

    $("#reader").hide();
    $("#consultaNomina").hide();
    $("#resultado").html("");

    $("#nomina").val("");
    $("#passwordNomina").val("");

    $("#errorNomina").addClass("d-none");
    $("#errorNomina").html("");

    $("#btnLoginNomina")
        .prop("disabled", false)
        .html("Continuar");

    $("#menuPrincipal").show();

}
function mostrarScanner(){

    $("#menuPrincipal").hide();
    $("#consultaNomina").hide();
    $("#resultado").html("");
    $("#reader").show();

    LectorQR.iniciar({
        boton:"#btnScanner",
        reader:"reader",
        resultado:"#resultado",
        ancho:250,
        alto:150,

        onSuccess:function(codigo){
            window.location = "crear_sesion_kiosco.php?id=" + codigo + "&modo=CREDENCIAL";

        }
    });

}
function mostrarNomina(){

    $("#menuPrincipal").hide();
    $("#reader").hide();
    $("#resultado").html("");

    $("#consultaNomina").show();
    $("#nomina").focus();

}
function mostrarPassword(){

    const input = $("#passwordNomina");
    const boton = $("#btnMostrarPassword");
    const icono = boton.find("i");

    if(input.attr("type") === "password"){

        input.attr("type", "text");

        icono
            .removeClass("bi-eye")
            .addClass("bi-eye-slash");

    }else{

        input.attr("type", "password");

        icono
            .removeClass("bi-eye-slash")
            .addClass("bi-eye");

    }

}

// Limpiar campos
$('input[name="opcionAcceso"]').on('change', function() {
    $("#nomina").val("");
    $("#passwordNomina").val("");
    $("#errorNomina").addClass("d-none").html("");
    if (temporizadorError) clearTimeout(temporizadorError);
});
function consultaXnomina(){

    let nomina = $("#nomina").val().trim();
    let password = $("#passwordNomina").val().trim();

    const error = $("#errorNomina");
    const boton = $("#btnLoginNomina");

    error.addClass("d-none");
    error.html("");


    /*
    |--------------------------------------------------------------------------
    | VALIDAR NÓMINA
    |--------------------------------------------------------------------------
    */

    if(nomina === ""){

        error.html("Ingrese su número de nómina.");
        error.removeClass("d-none");

        $("#nomina").focus();

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | SIN CONTRASEÑA
    |--------------------------------------------------------------------------
    |
    | Se permite entrar únicamente en modo CONSULTA.
    |
    | El servidor revisará lastLogin:
    |
    | NULL    → requiere cambio de contraseña
    | CON DATO → puede consultar
    |
    */

    if(password === ""){

        Swal.fire({
            icon: "info",
            title: "¿Quieres continuar?",
            text: "Sin contraseña solo podrás consultar tus trámites. Para solicitar algún trámite necesitas validar tu identidad.",
            showCancelButton: true,
            confirmButtonText: "Ingresar contraseña",
            cancelButtonText: "Solo consultar",
            reverseButtons: true
        }).then((resultado) => {

            /*
            |--------------------------------------------------------------------------
            | QUIERE INGRESAR CONTRASEÑA
            |--------------------------------------------------------------------------
            */

            if(resultado.isConfirmed){

                $("#passwordNomina").focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | SOLO CONSULTAR
            |--------------------------------------------------------------------------
            */

            boton
                .prop("disabled", true)
                .html("Validando...");


            $.ajax({

                url: "validar_acceso_kiosco.php",

                type: "POST",

                dataType: "json",

                data: {
                    nomina: nomina,
                    password: ""
                },


                success: function(respuesta){

                    console.log(
                        "RESPUESTA KIOSCO:",
                        respuesta
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | ERROR
                    |--------------------------------------------------------------------------
                    */

                    if(!respuesta.ok){

                        Swal.fire({
                            icon: "error",
                            title: "No fue posible continuar",
                            text:
                                respuesta.mensaje ||
                                "No fue posible validar al colaborador."
                        });

                        boton
                            .prop("disabled", false)
                            .html("Continuar");

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PRIMER ACCESO
                    |--------------------------------------------------------------------------
                    |
                    | El servidor determina esto utilizando lastLogin.
                    |
                    */

                    if(respuesta.requiere_cambio_password){

                        Swal.fire({

                            icon: "warning",

                            title: "Debes cambiar tu contraseña",

                            text:
                                "Es tu primer acceso. Por seguridad debes establecer una nueva contraseña antes de continuar.",

                            confirmButtonText:
                                "Cambiar contraseña",

                            allowOutsideClick: false,

                            allowEscapeKey: false

                        }).then(function(){

                            window.location =
                                "cambiar_password_kiosco.php?id=" +
                                encodeURIComponent(
                                    respuesta.usuario_id
                                );

                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CONSULTA AUTORIZADA
                    |--------------------------------------------------------------------------
                    */

                    window.location =
                        "crear_sesion_kiosco.php?id=" +
                        encodeURIComponent(
                            respuesta.usuario_id
                        ) +
                        "&modo=CONSULTA";

                },


                error: function(xhr){

                    console.error(
                        xhr.responseText
                    );

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            "No fue posible verificar la cuenta."
                    });

                    boton
                        .prop("disabled", false)
                        .html("Continuar");

                }

            });

        });

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDAR CONTRASEÑA
    |--------------------------------------------------------------------------
    */

    boton
        .prop("disabled", true)
        .html("Validando...");


    $.ajax({

        url: "validar_acceso_kiosco.php",

        type: "POST",

        dataType: "json",

        data: {
            nomina: nomina,
            password: password
        },


        success: function(respuesta){

            /*
            |--------------------------------------------------------------------------
            | CREDENCIALES INCORRECTAS
            |--------------------------------------------------------------------------
            */

            if(!respuesta.ok){

                error.html(
                    respuesta.mensaje ||
                    "La nómina o contraseña son incorrectas."
                );

                error.removeClass("d-none");

                $("#passwordNomina")
                    .val("")
                    .focus();

                boton
                    .prop("disabled", false)
                    .html("Continuar");

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | PRIMER ACCESO
            |--------------------------------------------------------------------------
            */

            if(respuesta.requiere_cambio_password){

                Swal.fire({

                    icon: "warning",

                    title: "Debes cambiar tu contraseña",

                    text:
                        "Es tu primer acceso. Por seguridad debes establecer una nueva contraseña antes de continuar.",

                    confirmButtonText:
                        "Cambiar contraseña",

                    allowOutsideClick: false,

                    allowEscapeKey: false

                }).then(function(){

                    window.location =
                        "cambiar_password_kiosco.php?id=" +
                        encodeURIComponent(
                            respuesta.usuario_id
                        );

                });

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ACCESO NORMAL
            |--------------------------------------------------------------------------
            */

            window.location =
                "crear_sesion_kiosco.php?id=" +
                encodeURIComponent(
                    respuesta.usuario_id
                ) +
                "&modo=CONSULTA";

        },


        error: function(xhr){

            console.error(
                xhr.responseText
            );

            error.html(
                "No fue posible validar los datos."
            );

            error.removeClass("d-none");

            boton
                .prop("disabled", false)
                .html("Continuar");

        }

    });

}
$(document).ready(function(){

    const parametros = new URLSearchParams(window.location.search);

    if(parametros.get('modo') === 'nomina'){

        mostrarNomina();

    }

});
