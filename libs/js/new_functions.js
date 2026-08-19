// document.addEventListener("DOMContentLoaded", function () {
//   const buscador = document.getElementById("buscador");
//   const filas = document.querySelectorAll("#tabla-usuarios tbody tr");

//   buscador.addEventListener("keyup", function () {
//     const texto = this.value.toLowerCase();

//     filas.forEach(function (fila) {
//       const contenido = fila.textContent.toLowerCase();
//       fila.style.display = contenido.includes(texto) ? "" : "none";
//     });
//   });
// });
// document.addEventListener('DOMContentLoaded', function () {

//   const btn = document.getElementById('toggleSidebar');
//   const sidebar = document.querySelector('.sidebar');
//   const page = document.querySelector('.page');

//   if (!btn || !sidebar || !page) return;

//   btn.addEventListener('click', function (e) {
//     e.preventDefault();

//     sidebar.classList.toggle('collapsed');
//     page.classList.toggle('collapsed');
//     document.body.classList.toggle('sidebar-collapsed');
//   });

// });
document.addEventListener("DOMContentLoaded", function () {
  const alerts = document.querySelectorAll(".alert");

  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = "opacity 0.5s ease";
      alert.style.opacity = "0";

      setTimeout(() => {
        alert.remove();
      }, 500);
    }, 3000); // 3 segundos
  });
});
function fileSelected(input) {
  const fileName = document.getElementById('fileName');
  const btnRemove = document.getElementById('btnRemovePhoto');
  const msg = document.getElementById('photoMsg');

  if (input.files && input.files.length > 0) {
    fileName.textContent = 'Archivo seleccionado: ' + input.files[0].name;
    btnRemove.disabled = false;
    msg.innerHTML = '';
  }
}
function removePhoto() {
  const input = document.querySelector('input[name="file_upload"]');
  const fileName = document.getElementById('fileName');
  const btnRemove = document.getElementById('btnRemovePhoto');
  const msg = document.getElementById('photoMsg');

  input.value = '';
  fileName.textContent = '';
  btnRemove.disabled = true;

  msg.innerHTML = `
    <div class="alert alert-warning">
      <a href="#" class="close" onclick="this.parentElement.remove();return false;">&times;</a>
      Imagen eliminada correctamente.
    </div>
  `;

  setTimeout(() => {
    msg.innerHTML = '';
  }, 3000);
}
// document.getElementById('otraFechaCheck').addEventListener('change', function() {
//   const inputFecha = document.getElementById('fecha');

//   if (this.checked) {
//       inputFecha.removeAttribute('readonly');
//   } else {
//       inputFecha.setAttribute('readonly', true);
      
//       // Opcional: regresar a la fecha actual cuando se desmarque
//       const hoy = new Date().toLocaleDateString('en-CA', { timeZone: 'America/Mexico_City' });
//       inputFecha.value = hoy;
//   }
// });
