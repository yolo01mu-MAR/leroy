
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>LE ROY</title>
        <link rel="shortcut icon" href="libs/images/logo.ico" type="image/x-icon">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="libs/css/kiosco.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>
    <body>
        <!-- NAVBAR -->
        <nav class="navbar navbar-expand-lg bg-white">
            <div class="container">
                <img src="libs/images/leroy.png" alt="Logo LE ROY" height="45">
                <button class="navbar-toggler"
                    data-bs-toggle="collapse"
                    data-bs-target="#menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="menu">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="almacen/alm_inventario.php">Almacen</a>
                        </li>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Tramites</a>
                            <div class="dropdown-menu fade-down m-0">
                                <a href="modules/kiosco/identificar_colaborador.php?op=vacaciones" class="dropdown-item">Vacaciones</a>
                                <a href="modules/kiosco/identificar_colaborador.php?op=tiempo" class="dropdown-item">Tiempo por tiempo</a>
                            </div>
                        </div>
                        <li class="nav-item">
                            <a class="btn btn-roy" href="login.php">Iniciar sesión</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- CARRUSEL -->
        <div id="principal" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="https://picsum.photos/1600/700?1" class="w-100">
                    <div class="carousel-caption">
                        <h1>Portal del Empleado</h1>
                        <p>Realiza tus solicitudes de RH desde un solo lugar.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://picsum.photos/1600/700?2" class="w-100">
                    <div class="carousel-caption">
                        <h1>Vacaciones</h1>
                        <p>Consulta tus días disponibles y envía solicitudes.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://picsum.photos/1600/700?3" class="w-100">
                    <div class="carousel-caption">
                        <h1>Tiempo por Tiempo</h1>
                        <p>Solicita permisos y consulta el estado de tus trámites.</p>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev"
                type="button"
                data-bs-target="#principal"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next"
                type="button"
                data-bs-target="#principal"
                data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>

        <!-- SERVICIOS -->
        <div class="container py-5">
            <h2 class="text-center mb-5">¿Qué deseas realizar?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <a href="modules/kiosco/identificar_colaborador.php?op=vacaciones" class="text-decoration-none text-dark">
                        <div class="card servicio text-center p-4 h-100">
                            <i class="bi bi-calendar2-check"></i>
                            <h4 class="mt-3">Vacaciones</h4>
                            <p>Solicita vacaciones y consulta tus días disponibles.</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="modules/kiosco/identificar_colaborador.php?op=tiempo" class="text-decoration-none text-dark">
                        <div class="card servicio text-center p-4 h-100">
                            <i class="bi bi-clock-history"></i>
                            <h4 class="mt-3">Tiempo por Tiempo</h4>
                            <p>Envía solicitudes de tiempo por tiempo.</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="modules/kiosco/identificar_colaborador.php?op=solicitudes" class="text-decoration-none text-dark">
                        <div class="card servicio text-center p-4">
                            <i class="bi bi-file-earmark-text"></i>
                            <h4 class="mt-3">Mis Solicitudes</h4>
                            <p>Consulta el estado de tus trámites.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- PIE DE PAGINA -->
        <footer class="py-4">
            <div class="container text-center">
                <b>LE ROY</b><br>
                Portal de Recursos Humanos
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>