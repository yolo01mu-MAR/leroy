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
function consultaXnomina(){

    let nomina = $("#nomina").val().trim();
    let password = $("#passwordNomina").val().trim();

    const error = $("#errorNomina");
    const boton = $("#btnLoginNomina");

    error.addClass("d-none");
    error.html("");

    if(nomina === ""){

        error.html("Ingrese su número de nómina.");
        error.removeClass("d-none");

        $("#nomina").focus();

        return;
    }

    if(password === ""){

        Swal.fire({
            icon: "info",
            title: "¿Quieres continuar?",
            text: "Sin contraseña solo podrás consultar tus tramites. Para solicitar algun tramite necesitas validar tu identidad.",
            showCancelButton: true,
            confirmButtonText: "Ingresar contraseña",
            cancelButtonText: "Solo consultar",
            reverseButtons: true
        }).then((resultado) => {

            if(resultado.isConfirmed){

                $("#passwordNomina").focus();

            }else{

                window.location =
                    "crear_sesion_kiosco.php?id=" +
                    encodeURIComponent(nomina) +
                    "&modo=CONSULTA";

            }

        });

        return;
    }

    boton.prop("disabled", true);
    boton.html("Validando...");

    $.ajax({

        url: "validar_acceso_kiosco.php",
        type: "POST",
        dataType: "json",

        data: {
            nomina: nomina,
            password: password
        },

        success: function(respuesta){

            if(respuesta.ok){

                window.location =
                    "crear_sesion_kiosco.php?id=" +
                    encodeURIComponent(nomina) +
                    "&modo=CONSULTA";

                return;
            }

            error.html(respuesta.mensaje);
            error.removeClass("d-none");

            $("#passwordNomina").val("");
            $("#passwordNomina").focus();

            boton.prop("disabled", false);
            boton.html("Continuar");

        },

        error: function(xhr){

            console.error(xhr.responseText);

            error.html(
                "No fue posible validar los datos."
            );

            error.removeClass("d-none");

            boton.prop("disabled", false);
            boton.html("Continuar");

        }

    });

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