<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>LE ROY</title>
        <link rel="shortcut icon" href="libs/images/logo.ico" type="image/x-icon">

        <link rel="manifest" href="manifest.json">

        <meta name="theme-color" content="#212529">

        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="LE ROY">


        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        
        <link rel="stylesheet" href="libs/css/index.css">
    </head>
    <body>
        <!-- NAVBAR -->
        <nav class="navbar navbar-expand-lg bg-white sticky-top py-3 border-bottom py-2 custom-navbar">
            <div class="container">

                <!-- LOGO -->
                <img 
                    src="libs/images/logo.png" 
                    alt="Logo LE ROY" 
                    height="45"
                    draggable="false">
                
                <button 
                    class="navbar-toggler border-0 shadow-none"
                    data-bs-toggle="collapse"
                    data-bs-target="#menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="menu">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                        <li class="nav-item">
                            <a 
                                class="nav-link px-3" 
                                href="almacen/alm_inventario.php">
                                <i class="bi bi-box-seam me-1"></i> Almacén
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle px-3" data-bs-toggle="dropdown">
                                <i class="bi bi-file-earmark-text me-1"></i>Realizar Trámites
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                                <li>
                                    <a 
                                        href="modules/kiosco/identificar_colaborador.php?op=vacaciones" 
                                        class="dropdown-item py-2">
                                        <!-- <i class="bi bi-umbrella me-2 text-warning"></i>  -->
                                        Solicitar Vacaciones
                                    </a>
                                </li>
                                <li>
                                    <a 
                                        href="modules/kiosco/identificar_colaborador.php?op=tiempo" 
                                        class="dropdown-item py-2">
                                        <!-- <i class="bi bi-clock-history me-2 text-warning"></i>  -->
                                        Solicitar Tiempo por tiempo
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-roy shadow-sm w-100" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                            </a>
                        </li>
                        
                    </ul>
                </div>
            </div>
        </nav>
        <!-- CARRUSEL -->
        <div id="principal" class="carousel slide custom-carousel" data-bs-ride="carousel">

            <div class="carousel-indicators">
                <button type="button" data-bs-target="#principal" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#principal" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#principal" data-bs-slide-to="2"></button>
            </div>

            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="carousel-overlay"></div>
                    <img src="https://picsum.photos/1600/700?1"class="d-block w-100 object-fit-cover">
                    <div class="carousel-caption">
                        <h1 class="display-4 fw-bold">Portal del Empleado</h1>
                        <p class="fs-5 text-light opacity-90">Realiza tus solicitudes de RH desde un solo lugar.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://picsum.photos/1600/700?2" class="d-block w-100 object-fit-cover">
                    <div class="carousel-caption">
                        <h1 class="display-4 fw-bold">Vacaciones</h1>
                        <p class="fs-5 text-light opacity-90">Consulta tus días disponibles y envía solicitudes.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://picsum.photos/1600/700?3" class="d-block w-100 object-fit-cover">
                    <div class="carousel-caption">
                        <h1 class="display-4 fw-bold">Tiempo por Tiempo</h1>
                        <p class="fs-5 text-light opacity-90">Solicita permisos y consulta el estado de tus trámites.</p>
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

        <main class="py-5 custom-bg">

            <!-- SERVICIOS -->
            <div class="container py-4">

                <div class="text-center mb-5">
                    <h2 class="fw-bold display-6 mb-2">¿Qué deseas realizar hoy?</h2>
                    <p class="text-muted fs-6">Selecciona una opción para comenzar tu trámite</p>
                </div>
                
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
        </main>

        <!-- PIE DE PAGINA -->
        <footer class="custom-footer py-4 border-top">
        <div class="container text-center">
            <h5 class="fw-bold mb-1">LE ROY</h5>
            <p class="small mb-0 opacity-75">Portal de Recursos Humanos.</p>
        </div>
    </footer>
        <script type="text/javascript" src="libs/js/pwa.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>