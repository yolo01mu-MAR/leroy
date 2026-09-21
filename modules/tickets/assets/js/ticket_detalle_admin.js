$(function () {

    iniciarEventos();

});

let botonAccion = '';
let ultimoComentario = 0;
let sincronizando = false;

function iniciarEventos(){

    detectarBoton();
    enviarFormulario();
    iniciarGestion();

    actualizarUltimoComentario();
    iniciarSincronizacion();

}
function iniciarGestion(){

    $('#formGestion').on('submit', function(e){

        e.preventDefault();
        console.log('Guardar gestión');

    });

}
function iniciarSincronizacion(){

    setInterval(function(){

        sincronizarTicket();

    },5000);

}
function actualizarUltimoComentario(){

    let ultimo = $('#ticketConversacion .tk-clearfix:last');

    if(ultimo.length){
        ultimoComentario = parseInt(ultimo.data('comentario')) || 0;
    }

}
function detectarBoton(){

    $('#formSeguimiento button[type="submit"]').on('click', function(){
        botonAccion = $(this).attr('name');
    });

}
function obtenerAccion(){

    switch(botonAccion){

        case 'btnSeguimiento':
            return 'seguimiento';

        case 'btnSolicitar':
            return 'solicitar';

        case 'btnResolver':
            return 'resolver';

        default:
            return null;

    }

}
function enviarAjax(formulario, accion){

    let datos = new FormData(formulario);

    datos.append('accion', accion);
    datos.append('ticket_id', $('#formSeguimiento').data('ticket'));

    $.ajax({

        url: 'ajax/ticket.php',
        type: 'POST',
        data: datos,
        processData: false,
        contentType: false,
        dataType: 'json',

        success: function(respuesta){

            procesarRespuesta(respuesta);

        },

        error: function(xhr){

            console.error(xhr);

        }

    });

}
function procesarRespuesta(respuesta){

    if(!respuesta.ok){

        alert(respuesta.mensaje);
        return;

    }

    agregarComentario(respuesta.html);
    agregarHistorico(respuesta.historial);
    actualizarEstado(respuesta.estado);
    limpiarFormulario();

}
function agregarComentario(html){

    $('#ticketConversacion').append(html);

}
function agregarHistorico(html){

    $('#ticketHistorial').append(html);

}
function limpiarFormulario(){

    $('#comentarioSeguimiento')
        .val('')
        .focus();

}
function actualizarEstado(estado){

    if(!estado){
        return;
    }

    $('.tk-status')
        .removeClass('abierto proceso espera resuelto cerrado cancelado')
        .addClass(estado.clase)
        .text(estado.texto);

}
function enviarFormulario(){

    $('#formSeguimiento').on('submit', function(e){

        e.preventDefault();

        let accion = obtenerAccion();

        if(accion == null){
            return;
        }

        if(accion === 'resolver'){
            if(!confirm('¿Confirmas que el problema quedó resuelto?')){
                return;
            }
        }

        enviarAjax(this, accion);

    });

}
function sincronizarTicket(){

    if(sincronizando){
        return;
    }

    sincronizando = true;

    $.ajax({

        url:'ajax/ticket_sync.php',
        type:'POST',

        data:{
            ticket:$('#formSeguimiento').data('ticket'),
            ultimoComentario:ultimoComentario
        },

        dataType:'json',

        success:function(respuesta){

            console.log(respuesta);

        },

        complete:function(){

            sincronizando = false;

        }

    });

}