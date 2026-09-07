let tiempoInactivo;
const LIMITE = 60 * 60 * 1000; // 1 hora

function reiniciarTiempo() {
  clearTimeout(tiempoInactivo);
  tiempoInactivo = setTimeout(cerrarSesion, LIMITE);
}

function cerrarSesion() {
    fetch(BASE_URL + '/ajax/logout_inactividad.php')
        .then(() => {
            Swal.fire({
                icon: 'warning',
                title: 'Sesión cerrada',
                text: 'Tu sesión se cerró por inactividad.',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                window.location.href =
                    BASE_URL + "/index.php";
            });
        })
        .catch(() => {

            window.location.href =
                BASE_URL + "/index.php";

        });
}
// eventos que cuentan como actividad
['click', 'mousemove', 'keydown', 'scroll', 'touchstart']
  .forEach(evento => document.addEventListener(evento, reiniciarTiempo));

// iniciar contador
reiniciarTiempo();
