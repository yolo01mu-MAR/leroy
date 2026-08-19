function actualizarReloj() {
    const opciones = {
      timeZone: 'America/Mexico_City',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: 'numeric',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    };

    const ahora = new Date();
    const fecha = ahora.toLocaleString('es-MX', opciones);
    document.getElementById('reloj').textContent = fecha;
}
// Ejecutar al cargar
actualizarReloj();

// Actualizar cada segundo
setInterval(actualizarReloj, 1000);