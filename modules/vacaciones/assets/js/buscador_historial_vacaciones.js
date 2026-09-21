document.addEventListener("DOMContentLoaded", () => {

    const buscador = document.getElementById("buscador");
    const filtroSemana = document.getElementById("filtro_semana");
    const filtroAnio = document.getElementById("filtro_anio");
    const tabla = document.getElementById("tabla-resultados");

    let timeout = null;

    // Buscar
    function buscarHistorial() {

        const texto = buscador.value.trim();
        const semana = filtroSemana.value;
        const anio = filtroAnio.value;

        clearTimeout(timeout);

        timeout = setTimeout(() => {

            const params = new URLSearchParams();

            params.append("q", texto);
            params.append("semana", semana);
            params.append("anio", anio);

            // Mnesaje de buscando
            tabla.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center" style="padding:25px;">
                        <span class="glyphicon glyphicon-refresh glyphicon-spin"></span>
                        Buscando...
                    </td>
                </tr>
            `;

            fetch("ajax/buscar_x_semana.php?" + params.toString())

                .then(res => {

                    if (!res.ok) {
                        throw new Error("Error HTTP: " + res.status);
                    }

                    return res.text();

                })

                .then(html => {
                    tabla.innerHTML = html;
                })

                .catch(error => {

                    console.error(error);

                    tabla.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-danger" style="padding:25px;">
                                <span class="glyphicon glyphicon-warning-sign"></span>
                                <br>
                                Ocurrió un error al realizar la búsqueda.
                            </td>
                        </tr>
                    `;

                });

        }, 300);

    }

    // Eventos
    buscador.addEventListener("input", buscarHistorial);
    filtroSemana.addEventListener("input", buscarHistorial);
    filtroAnio.addEventListener("input", buscarHistorial);

    const btnLimpiar = document.getElementById("btn_limpiar_filtros");

    // Validar por semana
    filtroSemana.addEventListener("change", () => {

        let semana = parseInt(filtroSemana.value);

        if (isNaN(semana)) {
            filtroSemana.value = 1;
            semana = 1;
        }

        if (semana < 1) {
            filtroSemana.value = 1;
        }

        if (semana > 53) {
            filtroSemana.value = 53;
        }

        buscarHistorial();

    });

    // Validar año
    filtroAnio.addEventListener("change", () => {

        let anio = parseInt(filtroAnio.value);

        if (isNaN(anio)) {
            filtroAnio.value = new Date().getFullYear();
        }

        buscarHistorial();

    });
    
    btnLimpiar.addEventListener("click", () => {

        filtroSemana.value = semanaActual;
        filtroAnio.value = anioActual;
        buscador.value = "";

        buscarHistorial();

    });
});