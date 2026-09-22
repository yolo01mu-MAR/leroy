<?php

ob_start();

require_once __DIR__ . '/app/bootstrap.php';

if ($session->isUserLoggedIn(true)) {
    redirect('home.php', false);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asistencia | Le Roy</title>
    <link rel="shortcut icon" href="libs/images/logo.ico" type="image/x-icon">
    <link rel="icon" href="libs/images/logo.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"rel="stylesheet">
    <link rel="stylesheet"href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS -->
    <link
        rel="stylesheet"href="libs/css/login.css?v=<?php echo time(); ?>">
</head>


<body>
    <main class="login-page d-flex align-items-center justify-content-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

                    <!-- TARJETA LOGIN -->
                    <div class="card login-card border-0">

                        <div class="card-body p-4 p-sm-5">

                            <!-- REGRESAR -->
                            <div class="login-back">
                                <a
                                    href="javascript:history.back()"
                                    class="btn btn-link p-0"
                                    title="Regresar"
                                    aria-label="Regresar">
                                    <i class="bi bi-arrow-left"></i>
                                </a>
                            </div>

                            <!-- LOGO -->
                            <div class="text-center mb-4">
                                <img
                                    src="libs/images/leroy.png"
                                    alt="Logo LE ROY"
                                    class="img-fluid logo-img">
                            </div>

                            <!-- ENCABEZADO -->
                            <div class="text-center mb-4">
                                <h1 class="login-title mb-2">
                                    Bienvenido
                                </h1>
                                <p class="login-subtitle mb-0">
                                    Ingresa tus credenciales para continuar
                                </p>
                            </div>

                            <!-- MENSAJE -->
                            <?php echo display_msg($msg); ?>

                            <!-- FORMULARIO -->
                            <form action="auth.php" method="POST">
                                <!-- USUARIO -->
                                <div class="mb-3">
                                    <label
                                        for="username"
                                        class="form-label">
                                        Usuario
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input
                                            type="text"
                                            id="username"
                                            name="username"
                                            class="form-control"
                                            placeholder="Ingresa tu usuario"
                                            autocomplete="username"
                                            required>
                                    </div>
                                </div>

                                <!-- CONTRASEÑA -->
                                <div class="mb-4">
                                    <label
                                        for="newPass"
                                        class="form-label">
                                        Contraseña
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input
                                            type="password"
                                            id="newPass"
                                            name="password"
                                            class="form-control"
                                            placeholder="Ingresa tu contraseña"
                                            autocomplete="current-password"
                                            required>
                                        <button
                                            type="button"
                                            class="btn btn-password"
                                            id="btnMostrarPassword"
                                            onclick="togglePassword()"
                                            title="Mostrar contraseña"
                                            aria-label="Mostrar contraseña">
                                            <i
                                                class="bi bi-eye-slash"
                                                id="iconEye">
                                            </i>
                                        </button>
                                    </div>
                                </div>

                                <!-- INICIAR SESIÓN -->
                                <div class="d-grid">
                                    <button
                                        type="submit"
                                        class="btn btn-login btn-lg">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Iniciar sesión
                                    </button>
                                </div>

                                <!-- RECUPERAR CONTRASEÑA -->
                                <div class="text-center mt-4">
                                    <p class="forgot-text mb-1">
                                        ¿Olvidaste tu contraseña?
                                    </p>
                                    <button
                                        type="button"
                                        class="btn btn-link forgot-link p-0"
                                        onclick="mostrarModalOlvidePassword()">
                                        Recuperar contraseña
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>

    <!-- ========================================= -->
    <!-- MODAL RECUPERAR CONTRASEÑA -->
    <!-- ========================================= -->
    <div
        class="modal fade"
        id="modalOlvidePassword"
        tabindex="-1"
        aria-labelledby="modalOlvidePasswordLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content border-0 shadow">

                <!-- HEADER -->
                <div class="modal-header">
                    <h5
                        class="modal-title"
                        id="modalOlvidePasswordLabel">
                        <i class="bi bi-key me-2"></i>
                        Recuperar contraseña
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                    </button>
                </div>


                <!-- BODY -->
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">
                        Ingresa tu número de nómina para solicitar
                        el restablecimiento de tu contraseña.
                    </p>

                    <div class="mb-3">
                        <label
                            for="nominaOlvidada"
                            class="form-label">
                            Número de nómina
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <input
                                type="number"
                                class="form-control"
                                id="nominaOlvidada"
                                placeholder="Ej. 19009"
                                autocomplete="off">
                        </div>
                    </div>

                    <!-- ERROR -->
                    <div
                        id="errorOlvidePassword"
                        class="alert alert-danger d-none mb-0">
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer px-4 pb-4">
                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn btn-primary btn-modal-primary"
                        id="btnOlvidePassword"
                        onclick="solicitarCambioPassword()">
                        <i class="bi bi-send me-1"></i>
                        Solicitar cambio
                    </button>

                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>

    <script src="<?= BASE_URL ?>/libs/js/recuperar_password.js"></script>
    <script src="<?= BASE_URL ?>/libs/js/login.js"></script>

    <script>
        function togglePassword() {

            const passInput = document.getElementById("newPass");
            const icon = document.getElementById("iconEye");
            const button = document.getElementById("btnMostrarPassword");

            if (passInput.type === "password") {

                passInput.type = "text";

                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");

                button.setAttribute("title", "Ocultar contraseña");
                button.setAttribute("aria-label", "Ocultar contraseña");

            } else {

                passInput.type = "password";

                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");

                button.setAttribute("title", "Mostrar contraseña");
                button.setAttribute("aria-label", "Mostrar contraseña");
            }
        }
    </script>
</body>
</html>
