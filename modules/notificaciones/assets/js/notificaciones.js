(function () {

    'use strict';


    // =====================================================
    // CONFIGURACIÓN
    // =====================================================
    const CONFIG = {

        urlObtener:
            window.BASE_URL +
            '/modules/notificaciones/ajax/obtener_notificaciones.php',

        urlMarcarVistas:
            window.BASE_URL +
            '/modules/notificaciones/ajax/marcar_vistas.php',

        urlMarcarLeida:
            window.BASE_URL +
            '/modules/notificaciones/ajax/marcar_leida.php',

        intervalo: 30000
    };


    // =====================================================
    // INICIALIZAR
    // =====================================================

    function inicializarNotificaciones() {

        const boton =
            document.getElementById('notificacionesBoton');

        const panel =
            document.getElementById('notificacionesPanel');

        const overlay =
            document.getElementById('notificacionesOverlay');

        const cerrar =
            document.getElementById('notificacionesCerrar');


        if (!boton || !panel) {
            console.error(
                'No se encontraron los elementos de notificaciones.'
            );

            return;
        }


        // ==========================================
        // CARGAR NOTIFICACIONES
        // ==========================================

        cargarNotificaciones();

        setInterval(
            cargarNotificaciones,
            CONFIG.intervalo
        );


        // ==========================================
        // ABRIR / CERRAR
        // ==========================================

        boton.addEventListener('click', function (event) {

            event.preventDefault();
            event.stopPropagation();


            const abierto =
                panel.classList.contains('abierto');


            if (abierto) {

                cerrarPanel();

            } else {

                abrirPanel();

            }

        });


        // ==========================================
        // BOTÓN X
        // ==========================================

        if (cerrar) {

            cerrar.addEventListener(
                'click',
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    cerrarPanel();

                }
            );

        }


        // ==========================================
        // OVERLAY
        // ==========================================

        if (overlay) {

            overlay.addEventListener(
                'click',
                function () {

                    cerrarPanel();

                }
            );

        }


        // ==========================================
        // CERRAR CON ESC
        // ==========================================

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape' &&
                    panel.classList.contains('abierto')
                ) {

                    cerrarPanel();

                }

            }
        );


        // ==========================================
        // CLIC EN NOTIFICACIÓN
        // ==========================================

        document.addEventListener(
            'click',
            async function (event) {

                const item =
                    event.target.closest(
                        '.notificacion-item'
                    );


                if (!item) {
                    return;
                }


                event.preventDefault();
                event.stopPropagation();


                const id =
                    item.dataset.id;

                const url =
                    item.dataset.url;


                const marcada =
                    await marcarNotificacionLeida(id);


                if (marcada) {

                    item.classList.remove(
                        'no-leida'
                    );


                    if (url) {

                        window.location.href =
                            url;

                    }

                }

            }
        );



        // ==========================================
        // FUNCIONES DEL PANEL
        // ==========================================

        function abrirPanel() {

            panel.classList.add('abierto');

            if (overlay) {
                overlay.classList.add('abierto');
            }

            // Al abrir:
            // las nuevas pasan a vistas.
            marcarNotificacionesVistas();

        }


        function cerrarPanel() {

            panel.classList.remove('abierto');

            if (overlay) {
                overlay.classList.remove('abierto');
            }

        }

    }


    // =====================================================
    // OBTENER NOTIFICACIONES
    // =====================================================

    async function cargarNotificaciones() {

        try {

            const respuesta =
                await fetch(CONFIG.urlObtener, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

            if (!respuesta.ok) {
                throw new Error(
                    'Error HTTP: ' + respuesta.status
                );
            }

            const datos =
                await respuesta.json();

            if (!datos.ok) {
                console.error(
                    'Error de notificaciones:',
                    datos.mensaje
                );

                return;
            }

            actualizarContador(
                datos.total_no_leidas
            );

            renderizarNotificaciones(
                datos.notificaciones
            );

        } catch (error) {

            console.error(
                'No se pudieron cargar las notificaciones:',
                error
            );

        }

    }

    async function marcarNotificacionesVistas() {

        try {

            const respuesta = await fetch(
                CONFIG.urlMarcarVistas,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (!respuesta.ok) {
                throw new Error(
                    'Error HTTP: ' + respuesta.status
                );
            }

            const datos = await respuesta.json();

            if (!datos.ok) {
                console.error(
                    'No se pudieron marcar como vistas.'
                );

                return;
            }

            // El contador debe quedar inmediatamente en 0
            actualizarContador(0);

        } catch (error) {

            console.error(
                'Error al marcar notificaciones como vistas:',
                error
            );

        }
    }

    async function marcarNotificacionLeida(id) {

        try {

            const formulario = new FormData();

            formulario.append('id', id);

            const respuesta = await fetch(
                CONFIG.urlMarcarLeida,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formulario
                }
            );

            if (!respuesta.ok) {
                throw new Error(
                    'Error HTTP: ' + respuesta.status
                );
            }

            const datos = await respuesta.json();

            if (!datos.ok) {
                console.error(
                    'No se pudo marcar la notificación.'
                );

                return false;
            }

            return true;

        } catch (error) {

            console.error(
                'Error al marcar notificación:',
                error
            );

            return false;
        }
    }


    // =====================================================
    // CONTADOR
    // =====================================================

    function actualizarContador(total) {

        const contador =
            document.getElementById(
                'notificacionesContador'
            );

        if (!contador) {
            return;
        }

        total = parseInt(total, 10) || 0;

        if (total <= 0) {

            contador.textContent = '';

            contador.classList.remove(
                'visible'
            );

            return;
        }

        contador.textContent =
            total > 99 ? '99+' : total;

        contador.classList.add(
            'visible'
        );
    }


    // =====================================================
    // RENDERIZAR
    // =====================================================

    function renderizarNotificaciones(
        notificaciones
    ) {

        const lista =
            document.getElementById(
                'notificacionesLista'
            );

        if (!lista) {
            return;
        }

        if (
            !Array.isArray(notificaciones) ||
            notificaciones.length === 0
        ) {

            lista.innerHTML = `
                <div class="notificaciones-vacio">
                    <i class="bi bi-bell-slash"></i>
                    <p>No tienes notificaciones.</p>
                </div>
            `;

            return;
        }


        lista.innerHTML =
            notificaciones
                .map(crearNotificacionHTML)
                .join('');
    }


    // =====================================================
    // CREAR HTML DE NOTIFICACIÓN
    // =====================================================

    function crearNotificacionHTML(
        notificacion
    ) {

        const clase =
            parseInt(notificacion.leida, 10) === 0
                ? 'no-leida'
                : '';

        const id =
            escaparHTML(notificacion.id);

        const titulo =
            escaparHTML(notificacion.titulo);

        const mensaje =
            escaparHTML(notificacion.mensaje);

        const fecha =
            formatearFecha(
                notificacion.fecha_creacion
            );

        return `
            <div
                class="notificacion-item ${clase}"
                data-id="${id}"
                data-url="${
                    escaparHTML(
                        notificacion.url || ''
                    )
                }"
            >

                <div class="notificacion-icono">
                    <i class="bi bi-bell"></i>
                </div>

                <div class="notificacion-contenido">

                    <p class="notificacion-titulo">
                        ${titulo}
                    </p>

                    <p class="notificacion-mensaje">
                        ${mensaje}
                    </p>

                    <span class="notificacion-fecha">
                        ${fecha}
                    </span>

                </div>

            </div>
        `;
    }


    // =====================================================
    // FECHA
    // =====================================================

    function formatearFecha(
        fecha
    ) {

        if (!fecha) {
            return '';
        }

        const fechaObjeto =
            new Date(
                fecha.replace(' ', 'T')
            );

        if (
            Number.isNaN(
                fechaObjeto.getTime()
            )
        ) {
            return fecha;
        }

        const ahora =
            new Date();

        const diferencia =
            Math.floor(
                (ahora - fechaObjeto) / 1000
            );


        if (diferencia < 60) {
            return 'Hace unos segundos';
        }

        if (diferencia < 3600) {

            const minutos =
                Math.floor(
                    diferencia / 60
                );

            return 'Hace ' +
                minutos +
                (minutos === 1
                    ? ' minuto'
                    : ' minutos');
        }

        if (diferencia < 86400) {

            const horas =
                Math.floor(
                    diferencia / 3600
                );

            return 'Hace ' +
                horas +
                (horas === 1
                    ? ' hora'
                    : ' horas');
        }

        if (diferencia < 604800) {

            const dias =
                Math.floor(
                    diferencia / 86400
                );

            return 'Hace ' +
                dias +
                (dias === 1
                    ? ' día'
                    : ' días');
        }

        return fechaObjeto.toLocaleDateString(
            'es-MX',
            {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }
        );
    }


    // =====================================================
    // ESCAPAR HTML
    // =====================================================

    function escaparHTML(valor) {

        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }


    // =====================================================
    // ARRANCAR
    // =====================================================

    document.addEventListener(
        'DOMContentLoaded',
        inicializarNotificaciones
    );

})();