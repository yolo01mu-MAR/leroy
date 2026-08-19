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
