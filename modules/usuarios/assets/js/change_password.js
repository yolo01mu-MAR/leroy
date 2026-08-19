function togglePass(id, btn){
  const input = document.getElementById(id);
  const icon = btn.querySelector('span');

  if(input.type === "password"){
    input.type = "text";
    icon.classList.remove('glyphicon-eye-open');
    icon.classList.add('glyphicon-eye-close');
  } else {
    input.type = "password";
    icon.classList.remove('glyphicon-eye-close');
    icon.classList.add('glyphicon-eye-open');
  }
}