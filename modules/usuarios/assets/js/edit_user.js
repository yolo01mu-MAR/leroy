(function () {
    var NIVEL_EMPLEADO = 3; // debe coincidir con la constante PHP de arriba

    var selectNivel     = document.getElementById('selectNivel');
    var campoUsername   = document.getElementById('campoUsername');
    var panelPassword    = document.getElementById('panelPassword');
    var notaEmpleado     = document.getElementById('notaAccesoEmpleado');
    var inputUsername    = campoUsername.querySelector('input[name="username"]');

    function actualizarVisibilidad() {
        var esEmpleado = parseInt(selectNivel.value, 10) === NIVEL_EMPLEADO;

        campoUsername.style.display = esEmpleado ? 'none' : '';
        panelPassword.style.display  = esEmpleado ? 'none' : '';
        notaEmpleado.style.display   = esEmpleado ? '' : 'none';

        // required solo cuando el campo es visible/obligatorio
        inputUsername.required = !esEmpleado;
    }

    selectNivel.addEventListener('change', actualizarVisibilidad);
    actualizarVisibilidad(); // estado inicial al cargar la página
})();