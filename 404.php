<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada | Le Roy</title>
    <!-- FontAwesome para iconos modernos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/libs/css/404.css?v=1.3">
</head>
<body>

    <div class="error-card">
        <!-- Logo de la empresa -->
        <div class="brand-logo">
            <img src="/asistenciaLEROY_NUEVO/libs/images/logo.png" alt="Le Roy" onerror="this.style.display='none'"> 
            <!-- Si no encuentra la imagen del logo, simplemente no rompe el diseño -->
        </div>

        <!-- Código 404 animado -->
        <div class="error-number">
            4<span class="animated-zero">0</span>4
        </div>

        <h1 class="error-title">¡Ups! Esta ruta necesita asistencia</h1>
        
        <p class="error-msg">
            Parece que el enlace al que intentas acceder no existe o fue movido. Estamos trabajando para mantener la plataforma en perfectas condiciones.
        </p>

        <!-- Acciones -->
        <div class="actions-wrapper">
            <a href="/asistenciaLEROY_NUEVO/home.php" 
                class="btn-back" 
                onclick="if (window.history.length > 1) { window.history.back(); return false; }">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Regresar a la página anterior</span>
            </a>
        </div>
    </div>

</body>
</html>