function limpiarFiltros() {
  window.location.href = 'asistenciaUser.php';
}
function verDetalle(id, semana, anio) {
  $('#contenidoDetalle').load(
    'ajax/detalle_asistencia.php?id=' + id + '&semana=' + semana + '&anio=' + anio,
    function() {
      $('#modalDetalleAsistencia').modal('show');
    }
  );
}
document.querySelectorAll('table tbody tr').forEach(tr => {
  tr.addEventListener('click', function () {
    document.querySelectorAll('table tbody tr')
      .forEach(f => f.classList.remove('fila-activa'));
    this.classList.add('fila-activa');
  });
});
document.querySelectorAll('.filtro').forEach(el => {
  el.addEventListener('change', bloquearFiltros);
  el.addEventListener('keyup', bloquearFiltros);
});

function bloquearFiltros() {
  let activo = this;

  document.querySelectorAll('.filtro').forEach(el => {
    if (el !== activo) {
      el.disabled = activo.value !== '';
    }
  });

  // Si el campo activo se limpia, desbloqueamos todo
  if (activo.value === '') {
    document.querySelectorAll('.filtro').forEach(el => {
      el.disabled = false;
    });
  }
}
