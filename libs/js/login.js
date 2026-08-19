  setTimeout(function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    });
  }, 3500); // 3 segundos

  function togglePass(id, iconWrapper) {
    const input = document.getElementById(id);
    const icon = iconWrapper.querySelector('i');

    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove('glyphicon-eye-open');
      icon.classList.add('glyphicon-eye-close');
    } else {
      input.type = "password";
      icon.classList.remove('glyphicon-eye-close');
      icon.classList.add('glyphicon-eye-open');
    }
  }
  const passInput = document.getElementById("newPass");
  const toggleIcon = document.querySelector(".toggle-password");

  passInput.addEventListener("input", () => {
    if (passInput.value.length > 0) {
      toggleIcon.style.display = "block";
    } else {
      toggleIcon.style.display = "none";
      passInput.type = "password"; // por seguridad
      toggleIcon.querySelector("i").className = "glyphicon glyphicon-eye-open";
    }
  });
  function togglePass(){
  const input = document.getElementById('newPass');
  const eyeOpen = document.querySelector('.eye-open');
  const eyeClose = document.querySelector('.eye-close');

  if(input.type === "password"){
    input.type = "text";
    eyeOpen.style.display = "none";
    eyeClose.style.display = "block";
  } else {
    input.type = "password";
    eyeOpen.style.display = "block";
    eyeClose.style.display = "none";
  }
}
