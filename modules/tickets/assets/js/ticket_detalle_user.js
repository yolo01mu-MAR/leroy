document.addEventListener('DOMContentLoaded', function () {

    // ===========================
    // Panel Reabrir Ticket
    // ===========================

    var btnNo = document.getElementById('btnNoResuelto');
    var panel = document.getElementById('panelReabrir');

    if (btnNo && panel) {
        btnNo.addEventListener('click', function () {
            panel.style.display =
                panel.style.display === 'none' ? 'block' : 'none';
        });
    }

    // ===========================
    // Responder Ticket (AJAX)
    // ===========================

    const form = document.getElementById('formSeguimiento');

    if (!form) {
        return;
    }

    form.addEventListener('submit', function (e) {

        e.preventDefault();

        const ticketId = document.getElementById('ticket_id').value;
        const comentario = document.getElementById('comentarioSeguimiento').value.trim();

        if (comentario === '') {
            alert('Escribe un comentario.');
            return;
        }

        const datos = new FormData();

        datos.append('accion', 'responder');
        datos.append('ticket_id', ticketId);
        datos.append('comentario', comentario);

        fetch('ajax/ticket.php', {
            method: 'POST',
            body: datos
        })
        .then(respuesta => respuesta.json())
        .then(resultado => {

            if (!resultado.ok) {
                alert(resultado.mensaje);
                return;
            }

            const conversacion = document.getElementById('ticketConversacion');

            conversacion.insertAdjacentHTML(
                'beforeend',
                resultado.html
            );

            const historial = document.getElementById('ticketHistorico');

            if (historial) {
                historial.insertAdjacentHTML(
                    'afterbegin',
                    resultado.historial
                );
            }

            const badge = document.getElementById('badgeEstatus');

            if (badge && resultado.estado) {

                badge.className = "tk-status " + resultado.estado.class;
                badge.textContent = resultado.estado.nombre;

            }

            // ===== QUITAR "REQUIERE RESPUESTA" =====
            console.log(resultado);
            if (resultado.comentario_respondido_id) {

                const comentario = document.getElementById(
                    'comentario-' + resultado.comentario_respondido_id
                );

                if (comentario) {

                    const etiqueta = comentario.querySelector('.tk-requiere-respuesta');

                    if (etiqueta) {
                        etiqueta.remove();
                    }

                }

            }

            // Ocultar panel de acciones
            const form = document.getElementById('formSeguimiento');

            if (form) {
                form.closest('.tk-card').style.display = 'none';
            }

            const txtComentario = document.getElementById('comentarioSeguimiento');
            txtComentario.value = '';
            txtComentario.focus();

            conversacion.lastElementChild?.scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });

        })
        .catch(error => {

            console.error(error);

        });

    });

});