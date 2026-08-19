$("#btnLeer").on("click", function () {
    LectorQR.iniciar({

        boton:"#btnLeer",
        reader:"reader",

        onSuccess:function(codigo){
            registrarAsistencia(codigo);
        }

    });

});
function registrarAsistencia(codigo){

    let capacitacion = $('#capacitacion_id').val();
    $.ajax({
        url:'ajax/registrar_asistencia.php',
        type:'POST',

        data:{
            codigo:codigo,
            capacitacion_id:capacitacion
        },
        success:function(response){
            $('#resultado').html(response);
                setTimeout(function(){
                    location.reload();
                },1500);
        },
        error:function(xhr){
            $('#resultado').html(
                '<div class="alert alert-danger">' +
                'Error ' + xhr.status +
                '</div>'
            );
            $('#btnLeer').prop('disabled', false);
                escaneando = false;
        }
    });
}