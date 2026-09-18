<?php
  ob_start();

  require_once __DIR__ . '/app/bootstrap.php';

  if($session->isUserLoggedIn(true)) { redirect('home.php', false);}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Asistencia</title>
  <link rel="shortcut icon" href="libs/images/logo.ico" type="image/x-icon">
  <link rel="icon" href="libs/images/logo.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="libs/css/login.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
  <div class="login-container">
    <div class="logo-container">
      <img src="libs/images/leroy.png" alt="Logo LE ROY">
    </div>
    <h2>Bienvenido</h2>
     <?php echo display_msg($msg); ?>
    <form action="auth.php" method="POST">
      <div class="form-group">
        <label for="username">Usuario</label>
        <input type="text" name="username" class="form-control">
      </div>
      <div class="form-group">
        <label for="password">Contraseña</label>
        <div class="password-wrapper">
          <input type="password" id="newPass" name="password">

          <!-- OJO ABIERTO -->
          <span class="toggle-password eye-open" onclick="togglePass()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
          </span>

          <!-- OJO TACHADO -->
          <span class="toggle-password eye-close" onclick="togglePass()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
          </span>
        </div>
      </div>
      <button type="submit">Entrar</button>
      <div class="text-center mt-3">
        <p class="mb-1">¿Olvidaste tu contraseña?</p>

        <button
            type="button"
            class="btn btn-link"
            onclick="mostrarModalOlvidePassword()">
            Haz click aquí
        </button>
      </div>
    </form>
  </div>
  <!-- MODAL OLVIDÉ MI CONTRASEÑA -->
<div class="modal fade" id="modalOlvidePassword" tabindex="-1" aria-hidden="true">
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
<script src="<?= BASE_URL ?>/libs/js/recuperar_password.js"></script>
<script src="<?= BASE_URL ?>/libs/js/login.js"></script>
</body>
</html>
