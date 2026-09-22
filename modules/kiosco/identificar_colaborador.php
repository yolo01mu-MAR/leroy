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
    <link rel="stylesheet" href="libs/css/kiosco.css?v=<?php echo time(); ?>">
</head>
<body class="identificar_colaborador">

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <!-- CONTENIDO CARD LOGIN -->
            <div class="card card-login position-relative">
 
                <!-- BOTÓN REGRESAR -->
                <a href="#" class="btn-back-top" onclick="volver(); return false;" title="Regresar">
                    <i class="bi bi-arrow-left"></i>
                </a>

                <div class="card-body p-4 p-md-5">
                    
                    <!-- LOGO Y TÍTULO -->
                    <div class="text-center mb-4">
                        <img src="<?= BASE_URL ?>/libs/images/leroy.png" class="logo-img mb-3" alt="Le Roy Logo">
                        <h2 class="title-text fw-bold mb-1">
                            Portal de Recursos Humanos
                        </h2>
                    </div>
                    
                    <!-- BOTONES PRINCIPALES -->
                    <div id="menuPrincipal">
                        <div class="d-grid gap-4">
                            <button
                                type="button"
                                id="btnScanner"
                                class="btn btn-roy btn-identificar shadow-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalAvisoCredencial">
                                <i class="bi bi-qr-code-scan me-2"></i>
                                Escanear credencial
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-dark btn-identificar"
                                onclick="mostrarNomina()">
                                <i class="bi bi-keyboard me-2"></i>
                                ¿Olvidaste tu credencial?
                            </button>

                        </div>
                    </div>

                    <!-- SCANNER -->
                    <div id="reader" style="display:none;" class="mt-3"></div>
                    <div id="resultado" class="mt-3"></div>

                    <!-- LOGIN POR NÓMINA -->
                    <div id="consultaNomina">

                        <form class="login-kiosco" onsubmit="consultaXnomina(); return false;">

                            <!-- OPCIONES -->
                                <input type="radio" class="btn-check" name="opcionAcceso" id="opcionConsulta" autocomplete="off" checked>
                                <input type="radio" class="btn-check" name="opcionAcceso" id="opcionTramites" autocomplete="off">

                            <!-- BOTONES VISUALES -->
                            <div class="btn-group w-100 mb-4 p-1 bg-light rounded-3" role="group" aria-label="Tipo de acceso">
                                <label class="btn btn-opcion text-secondary fw-bold py-2 rounded-2 border-0" for="opcionConsulta">
                                    Consulta
                                </label>
                                <label class="btn btn-opcion text-secondary fw-bold py-2 rounded-2 border-0" for="opcionTramites">
                                    Realizar trámite
                                </label>
                            </div>

                            <!-- INPUT NÓMINA -->
                            <div class="mb-3 text-start">
                                <label for="nomina" class="form-label font-weight-bold">
                                    Número de nómina
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-person-vcard"></i>
                                    </span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        class="form-control custom-input border-start-0"
                                        id="nomina"
                                        placeholder="Ej. 19009"
                                        autocomplete="off"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>

                            <!-- INPUT CONTRASEÑA Y OLVIDÉ CONTRASEÑA -->
                            <div class="mb-3 text-start campo-password">
                                <label for="passwordNomina" class="form-label font-weight-bold">
                                    Contraseña
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input
                                        type="password"
                                        class="form-control custom-input border-start-0 border-end-0"
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

                                <!-- OLVIDÉ CONTRASEÑA (LINK BOTON) -->
   
                                <div class="d-flex align-items-center mt-2">
                                    <span class="text-muted small">¿Olvidaste tu contraseña?</span>
                                    <button 
                                        type="button" 
                                        class="btn btn-link p-0 ms-2 link-forgot"
                                        onclick="mostrarModalOlvidePassword()">
                                        Haz clic aquí
                                    </button>
                                </div>
                            </div>
                            <!-- ERROR -->
                            <div
                                id="errorNomina"
                                class="alert alert-danger d-none text-start small">
                            </div>
                            <!-- MENSAJES -->
                            <div class="row home-message-wrapper mb-3">
                                <div class="col-md-12">
                                    <?php echo display_msg($msg); ?>
                                </div>
                            </div>
                            <!-- BOTÓN -->
                            <div class="d-grid mt-4">
                                <button
                                    type="submit"
                                    class="btn btn-roy btn-lg shadow-sm fw-bold"
                                    id="btnLoginNomina">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Continuar
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL OLVIDÉ MI CONTRASEÑA -->
<div class="modal fade" id="modalOlvidePassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-key me-2 text-warning"></i>
                    Recuperar contraseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-muted small">
                    Ingresa tu número de nómina para solicitar el restablecimiento de tu contraseña.
                </p>
                <div class="mb-3 text-start">
                    <label for="nominaOlvidada" class="form-label fw-bold small">
                        Número de nómina
                    </label>
                    <input
                        type="number"
                        class="form-control form-control-lg rounded-3"
                        id="nominaOlvidada"
                        placeholder="Ej. 19009"
                        autocomplete="off">
                </div>
                <div id="errorOlvidePassword" class="alert alert-danger d-none small"></div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-roy rounded-3 px-4"
                    id="btnOlvidePassword"
                    onclick="solicitarCambioPassword()">
                    <i class="bi bi-send me-1"></i>
                    Solicitar cambio
                </button>
            </div>
        </div>
    </div>
</div>
<!-- MODAL AVISO CREDENCIAL -->
<div class="modal fade" id="modalAvisoCredencial" tabindex="-1" aria-labelledby="modalAvisoCredencialLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="modalAvisoCredencialLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                    Aviso importante
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3 text-center">
                <p class="fs-6 text-secondary mb-0">
                    Únicamente podrás ingresar como lector, no podrás hacer solicitudes.
                </p>
            </div>
            <div class="modal-footer border-top-0 pt-0 justify-content-center">
                <button 
                    type="button" 
                    class="btn btn-roy rounded-3 px-5 py-2 fw-bold" 
                    data-bs-dismiss="modal"
                    onclick="mostrarScanner()">
                    Aceptar
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