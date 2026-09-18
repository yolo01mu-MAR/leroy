document.addEventListener("DOMContentLoaded", () => {
  
  const buscador   = document.getElementById("buscador");
  const tabla      = document.getElementById("tabla-resultados");
  const paginacion = document.getElementById("paginacion");

  const tablaOriginal = tabla.innerHTML; 
  let timeout = null;

  buscador.addEventListener("keyup", () => {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
      const texto = buscador.value.trim();

      if (texto === '') {
        tabla.innerHTML = tablaOriginal;
        paginacion.style.display = 'block';
        return;
      }

      paginacion.style.display = 'none';

      fetch("ajax/buscar_usuario.php?q=" + encodeURIComponent(texto))
        .then(res => res.text())
        .then(html => {
          tabla.innerHTML = html;
        });

    }, 300);
  });
});
