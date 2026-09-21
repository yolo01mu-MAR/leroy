(function () {
    var NIVEL_EMPLEADO = 3; // Debe coincidir con la constante definida en PHP

    var selectNivel        = document.getElementById('selectNivel');
    var campoUsername      = document.getElementById('campoUsername');
    var campoPasswordGroup = document.getElementById('campoPasswordGroup');
    var notaEmpleado       = document.getElementById('notaAccesoEmpleado');
    var inputUsername      = document.getElementById('inputUsername');
    var inputPassword      = document.getElementById('inputPassword');

    var inputFoto   = document.getElementById('inputFoto');
    var previewFoto = document.getElementById('previewFoto');

    var inputName        = document.getElementById('inputName');
    var inputPuesto      = document.getElementById('inputPuesto');
    var lblNombreDisplay = document.getElementById('lblNombreDisplay');
    var lblPuestoDisplay = document.getElementById('lblPuestoDisplay');

    // Control dinámico según el nivel de usuario
    function actualizarVisibilidad() {
        var esEmpleado = parseInt(selectNivel.value, 10) === NIVEL_EMPLEADO;

        campoUsername.style.display      = esEmpleado ? 'none' : '';
        campoPasswordGroup.style.display = esEmpleado ? 'none' : '';
        notaEmpleado.style.display       = esEmpleado ? '' : 'none';

        // Solo exigimos username y password cuando NO es empleado
        inputUsername.required = !esEmpleado;
        if (inputPassword) {
            inputPassword.required = !esEmpleado;
        }
    }

    if (selectNivel) {
        selectNivel.addEventListener('change', actualizarVisibilidad);
        actualizarVisibilidad();
    }

    // Previsualización al cargar imagen
    if (inputFoto && previewFoto) {
        inputFoto.addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (evt) {
                    previewFoto.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Actualización de tarjeta lateral en tiempo real
    if (inputName && lblNombreDisplay) {
        inputName.addEventListener('input', function () {
            lblNombreDisplay.textContent = this.value.trim() !== '' ? this.value : 'Nuevo Usuario';
        });
    }

    if (inputPuesto && lblPuestoDisplay) {
        inputPuesto.addEventListener('input', function () {
            lblPuestoDisplay.textContent = this.value.trim() !== '' ? this.value : 'Puesto no especificado';
        });
    }
})();
document.addEventListener("DOMContentLoaded", () => {

    const nomina = document.getElementById("nomina");
    const iconoNomina = document.getElementById("iconoNomina");
    const resultadoNomina = document.getElementById("resultadoNomina");
    const formUsuario = document.getElementById("formUsuario");

    let timeoutNomina = null;
    let nominaExiste = false;

    if (!nomina) {
        return;
    }

    nomina.addEventListener("input", () => {

        clearTimeout(timeoutNomina);

        const valor = nomina.value.trim();

        // Limpiar resultado
        resultadoNomina.innerHTML = "";
        nominaExiste = false;

        // Si está vacío
        if (valor === "") {
            nomina.classList.remove("is-valid", "is-invalid");
            return;
        }

        // Esperar 300 ms antes de consultar
        timeoutNomina = setTimeout(() => {

            fetch("ajax/buscar_usuario_nomina.php?nomina=" + encodeURIComponent(valor))
                .then(response => response.json())
                .then(data => {

                    if (data.existe) {

                        nominaExiste = true;

                        nomina.classList.remove("is-valid");
                        nomina.classList.add("is-invalid");

                        iconoNomina.textContent = "✕";

                        resultadoNomina.innerHTML = `
                            <div class="text-danger" style="margin-top:5px;">
                                Esta nómina pertenece a <strong>${escapeHtml(data.nombre)}</strong>
                            </div>
                        `;

                    } else {

                        nominaExiste = false;

                        nomina.classList.remove("is-invalid");
                        nomina.classList.add("is-valid");

                        iconoNomina.textContent = "✓";

                        resultadoNomina.innerHTML = "";
                    }

                    if (valor === "") {

                        nomina.classList.remove("is-valid", "is-invalid");
                        iconoNomina.textContent = "";
                        resultadoNomina.innerHTML = "";

                        return;
                    }

                })
                .catch(error => {

                    console.error("Error verificando nómina:", error);

                    resultadoNomina.innerHTML = `
                        <div class="alert alert-warning" style="margin-bottom:0;">
                            No fue posible verificar la nómina.
                        </div>
                    `;

                });

        }, 300);
    });


    /*
    |--------------------------------------------------------------------------
    | Evitar guardar si la nómina ya existe
    |--------------------------------------------------------------------------
    */

    formUsuario.addEventListener("submit", (e) => {

        if (nominaExiste) {

            e.preventDefault();

            resultadoNomina.innerHTML = `
                <div class="alert alert-danger" style="margin-bottom:0;">
                    <strong>⚠ No se puede guardar.</strong><br>
                    La nómina ya pertenece a otro usuario.
                </div>
            `;

            nomina.focus();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Escapar HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(text) {

        const div = document.createElement("div");
        div.textContent = text;

        return div.innerHTML;
    }

});
