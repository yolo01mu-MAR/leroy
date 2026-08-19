$(document).on('click', '.ver-detalles', function(e){
    console.log("Diste Click");
    
    e.preventDefault();

    var id = $(this).data('id');

    $('#modalDetalles').modal('show');
    $('#contenido-detalles').html('Cargando...');

    $.ajax({
      url: 'ajax/detalle_equipo.php',
      type: 'POST',
      data: { id: id },
      success: function(response){
        $('#contenido-detalles').html(response);
      },
      error: function(){
        $('#contenido-detalles').html('Error al cargar los datos');
      }
    });
});