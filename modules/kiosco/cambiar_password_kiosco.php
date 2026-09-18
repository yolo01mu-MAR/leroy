<?php

require_once __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

/*
|--------------------------------------------------------------------------
| VALIDAR USUARIO
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Usuario no válido.');
}

/*
|--------------------------------------------------------------------------
| BUSCAR USUARIO
|--------------------------------------------------------------------------
*/

$usuario = find_by_id('users', $id);

if (!$usuario) {
    die('Usuario no encontrado.');
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Cambio de contraseña</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f6f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-card {
            width: 100%;
            max-width: 500px;
        }

        .password-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #fff3cd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #856404;
        }

        .password-card .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,.08);
        }

        .form-control {
            height: 48px;
        }

        .btn-cambiar {
            height: 48px;
            font-weight: 600;
        }

        .mensaje {
            display: none;
        }

    </style>

</head>

<body>

<div class="password-card">

    <div class="card">

        <div class="card-body p-4 p-md-5">

            <div class="password-icon">
                <i class="bi bi-shield-lock"></i>
            </div>

            <h3 class="text-center mb-3">
                Cambiar contraseña
            </h3>

            <p class="text-center text-muted mb-4">

                Tu contraseña actual es genérica.

                <br>

                Por seguridad debes establecer una nueva
                contraseña antes de continuar.

            </p>


            <div
                id="mensaje"
                class="alert mensaje"
            ></div>


            <form id="formCambioPassword">

                <input
                    type="hidden"
                    id="usuario_id"
                    value="<?= (int)$usuario['id'] ?>"
                >


                <div class="mb-3">

                    <label
                        for="nueva_password"
                        class="form-label"
                    >
                        Nueva contraseña
                    </label>

                    <div class="input-group">

                        <input
                            type="password"
                            id="nueva_password"
                            class="form-control"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="mostrarPassword('nueva_password', this)"
                        >
                            <i class="bi bi-eye"></i>
                        </button>

                    </div>

                </div>


                <div class="mb-4">

                    <label
                        for="confirmar_password"
                        class="form-label"
                    >
                        Confirmar contraseña
                    </label>

                    <div class="input-group">

                        <input
                            type="password"
                            id="confirmar_password"
                            class="form-control"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="mostrarPassword('confirmar_password', this)"
                        >
                            <i class="bi bi-eye"></i>
                        </button>

                    </div>

                </div>


                <button
                    type="submit"
                    id="btnCambiarPassword"
                    class="btn btn-primary w-100 btn-cambiar"
                >
                    Cambiar contraseña
                </button>

            </form>

        </div>

    </div>

</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>

function mostrarPassword(id, boton){

    const input = document.getElementById(id);
    const icono = boton.querySelector('i');

    if(input.type === 'password'){

        input.type = 'text';

        icono.classList.remove('bi-eye');
        icono.classList.add('bi-eye-slash');

    }else{

        input.type = 'password';

        icono.classList.remove('bi-eye-slash');
        icono.classList.add('bi-eye');

    }

}


$("#formCambioPassword").on("submit", function(e){

    e.preventDefault();

    const usuario_id = $("#usuario_id").val();
    const nueva = $("#nueva_password").val().trim();
    const confirmar = $("#confirmar_password").val().trim();

    const mensaje = $("#mensaje");
    const boton = $("#btnCambiarPassword");


    mensaje.hide().removeClass("alert-danger alert-success");


    /*
    |--------------------------------------------------------------------------
    | VALIDACIONES
    |--------------------------------------------------------------------------
    */

    if(nueva === ""){

        mensaje
            .addClass("alert-danger")
            .html("Ingrese una nueva contraseña.")
            .show();

        $("#nueva_password").focus();

        return;
    }


    if(confirmar === ""){

        mensaje
            .addClass("alert-danger")
            .html("Confirme su nueva contraseña.")
            .show();

        $("#confirmar_password").focus();

        return;
    }


    if(nueva !== confirmar){

        mensaje
            .addClass("alert-danger")
            .html("Las contraseñas no coinciden.")
            .show();

        $("#confirmar_password").focus();

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | ENVIAR CAMBIO
    |--------------------------------------------------------------------------
    */

    boton
        .prop("disabled", true)
        .html("Guardando...");


    $.ajax({

        url: "ajax/cambiar_password_kiosco.php",

        type: "POST",

        dataType: "json",

        data: {
            usuario_id: usuario_id,
            password: nueva
        },

        success: function(respuesta){

            if(!respuesta.ok){

                mensaje
                    .addClass("alert-danger")
                    .html(
                        respuesta.mensaje ||
                        "No fue posible cambiar la contraseña."
                    )
                    .show();

                boton
                    .prop("disabled", false)
                    .html("Cambiar contraseña");

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CAMBIO CORRECTO
            |--------------------------------------------------------------------------
            */

            mensaje
                .addClass("alert-success")
                .html(
                    respuesta.mensaje ||
                    "Contraseña cambiada correctamente."
                )
                .show();

            boton
                .prop("disabled", true)
                .html("Contraseña actualizada");


            setTimeout(function(){

                window.location =
                    "crear_sesion_kiosco.php?id=" +
                    encodeURIComponent(usuario_id) +
                    "&modo=CONSULTA";

            }, 1500);

        },

        error: function(xhr){

            console.error(xhr.responseText);

            mensaje
                .addClass("alert-danger")
                .html(
                    "No fue posible actualizar la contraseña."
                )
                .show();

            boton
                .prop("disabled", false)
                .html("Cambiar contraseña");

        }

    });

});

</script>

</body>

</html>