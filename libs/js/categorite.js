$(document).ready(function(){

    $('#paro_id').change(function(){

        console.log("Cambio detectado");
        var paro_id = $(this).val();

        if(paro_id != ''){

            $.ajax({
                url: 'ajax/subparos.php',
                method: 'POST',
                data: {paro_id: paro_id},
                success: function(data){
                    $('#subparo_id').html('<option value="">Seleccione un subparo</option>' + data);
                }
            });

        } else {
            $('#subparo_id').html('<option value="">Seleccione un subparo</option>');
        }

    });

});