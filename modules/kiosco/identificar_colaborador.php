<?php

    require_once __DIR__ . '/../../app/bootstrap.php';
    $tramite = $_GET['op'] ?? 'vacaciones';
    $_SESSION['kiosco']['tramite'] = $tramite;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal RH | LE ROY</title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>/libs/images/logo.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="libs/css/kiosco.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-lg-6">
            <div class="card card-login">
                <!-- MENSAJES -->
                <div class="row home-message-wrapper">
                    <div class="col-md-12">
                        <?php echo display_msg($msg); ?>
                    </div>
                </div>
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="<?= BASE_URL ?>/libs/images/leroy.png" class="logo">
                        <h2 class="titulo mt-3">
                            Portal de Recursos Humanos
                        </h2>
                    </div>
                    <!-- BOTONES PRINCIPALES -->
                    <div id="menuPrincipal">
                        <div class="d-grid gap-3">
                            <button
                                id="btnScanner"
                                class="btn btn-roy btn-identificar"
                                onclick="mostrarScanner()">
                                <i class="bi bi-qr-code-scan"></i>
                                Escanear credencial
                            </button>
                            <button
                                class="btn btn-outline-secondary btn-identificar"
                                onclick="mostrarNomina()">
                                <i class="bi bi-keyboard"></i>
                                ¿Olvidaste tu credencial?
                            </button>
                        </div>
                    </div>
                    <!-- SCANNER -->
                    <div id="reader" style="display:none;"></div>
                    <div id="resultado" class="mt-3"></div>
                    <!-- LOGIN POR NÓMINA -->
                    <div id="consultaNomina">
                        <div class="login-kiosco">
                            <!-- NÓMINA -->
                            <div class="mb-3 text-start">
                                <label for="nomina" class="form-label">
                                    Número de nómina
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">
                                        <i class="bi bi-person-vcard"></i>
                                    </span>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="nomina"
                                        placeholder="Ej. 19009"
                                        autocomplete="off">
                                </div>
                            </div>
                            <!-- CONTRASEÑA -->
                            <div class="mb-3 text-start">
                                <label for="passwordNomina" class="form-label">
                                    Contraseña
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="passwordNomina"
                                        placeholder="Ingresa tu contraseña"
                                        autocomplete="off">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        id="btnMostrarPassword"
                                        onclick="mostrarPassword()">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- ERROR -->
                            <div
                                id="errorNomina"
                                class="alert alert-danger d-none text-start">
                            </div>
                            <!-- BOTÓN -->
                            <div class="d-grid mt-4">
                                <button
                                    class="btn btn-primary btn-lg"
                                    id="btnLoginNomina"
                                    onclick="consultaXnomina()">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                    Continuar
                                </button>
                            </div>
                            <div class="text-center mt-3">
                                <p class="mb-1">¿Olvidaste tu contraseña?</p>

                                <button
                                    type="button"
                                    class="btn btn-link"
                                    onclick="mostrarModalOlvidePassword()">
                                    Haz click aquí
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button class="btn" onclick="volver()">
                            ← Regresar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL OLVIDÉ MI CONTRASEÑA -->
<div
    class="modal fade"
    id="modalOlvidePassword"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="bi bi-key"></i>
                    Recuperar contraseña
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <p class="text-muted">
                    Ingresa tu número de nómina para solicitar
                    el restablecimiento de tu contraseña.
                </p>

                <div class="mb-3">

                    <label
                        for="nominaOlvidada"
                        class="form-label">
                        Número de nómina
                    </label>

                    <input
                        type="number"
                        class="form-control form-control-lg"
                        id="nominaOlvidada"
                        placeholder="Ej. 19009"
                        autocomplete="off">

                </div>

                <div
                    id="errorOlvidePassword"
                    class="alert alert-danger d-none">
                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnOlvidePassword"
                    onclick="solicitarCambioPassword()">

                    <i class="bi bi-send"></i>
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
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="libs/js/identificar_colaborador.js"></script>
<script src="<?= BASE_URL ?>/libs/js/leer_codigo_barras.js"></script>
<script src="<?= BASE_URL ?>/libs/js/recuperar_password.js"></script>
</body>
</html>
