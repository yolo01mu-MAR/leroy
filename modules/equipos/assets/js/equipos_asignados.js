$(document).on('click', '.abrir-modal-entrega', function(){

  var id = $(this).data('id');
  var id_equipo = $(this).data('equipo');

  $('#entrega_id').val(id);
  $('#equipo_id').val(id_equipo);

  $('#modalEntrega').modal('show');

});