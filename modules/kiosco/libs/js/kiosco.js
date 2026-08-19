const Kiosco = {

    // Configuración
    tiempoSesion: 300,
    aviso: 60,

    // Estado
    segundos: 300,
    contador: 60,

    modalAbierto: false,

    // Objetos
    modalSesion: null,
    tiempoModal: null,
    timer: null,

    iniciar(){

        this.segundos = this.tiempoSesion;

        const reloj = document.getElementById("tiempoSesion");
        const caja = document.getElementById("relojSesion");

        this.timer = setInterval(()=>{

            this.segundos--;

            let minutos = Math.floor(this.segundos / 60);
            let segundos = this.segundos % 60;

            reloj.innerHTML =
                String(minutos).padStart(2,'0') +
                ":" +
                String(segundos).padStart(2,'0');

            // Cambiar color del reloj
            if(this.segundos <= 120){
                caja.style.background = "#ffc107";
                caja.style.color = "#000";
            }

            if(this.segundos <= 60){
                caja.style.background = "#dc3545";
                caja.style.color = "#fff";
            }

            // Mostrar aviso solamente una vez
            if(this.segundos === this.aviso && !this.modalAbierto){
                this.mostrarModal();
            }

            // Cerrar sesión automáticamente
            if(this.segundos <= 0){
                clearInterval(this.timer);
                this.cerrarSesion();
            }

        },1000);

        console.log("Temporizador iniciado");

    },

    mostrarModal(){

        this.modalAbierto = true;

        this.contador = 60;

        $("#contadorSesion").text(this.contador);

        this.modalSesion = new bootstrap.Modal(
            document.getElementById("modalSesion")
        );

        this.modalSesion.show();

        clearInterval(this.tiempoModal);

        this.tiempoModal = setInterval(()=>{

            this.contador--;

            $("#contadorSesion").text(this.contador);

            if(this.contador <= 0){

                this.cerrarSesion();

            }

        },1000);

    },

    renovarSesion(){

        $.post(
            "renovar_sesion.php",
            function(respuesta){

                if(!respuesta.ok){

                    Kiosco.cerrarSesion();
                    return;

                }

                // Reiniciar temporizador principal
                Kiosco.segundos = Kiosco.tiempoSesion;

                // Reiniciar estado del modal
                Kiosco.modalAbierto = false;

                // Detener contador del modal
                clearInterval(Kiosco.tiempoModal);

                // Cerrar modal
                Kiosco.modalSesion.hide();

                // Restaurar color del reloj
                const caja = document.getElementById("relojSesion");

                caja.style.background = "";
                caja.style.color = "";

            },
            "json"
        ).fail(function(){

            Swal.fire({
                icon: "error",
                title: "Sesión",
                text: "No fue posible renovar la sesión."
            });

            Kiosco.cerrarSesion();

        });

    },

    cerrarSesion(){

        window.location = "cerrar_kiosco.php";

    },

    validarPassword(){

        const btnValidar = document.getElementById("btnValidarPassword");
        const inputPassword = document.getElementById("passwordKiosco");
        const errorPassword = document.getElementById("errorPassword");

        const password = inputPassword.value.trim();

        errorPassword.classList.add("d-none");
        errorPassword.innerHTML = "";

        if(password === ""){

            errorPassword.innerHTML = "Ingresa tu contraseña.";
            errorPassword.classList.remove("d-none");

            inputPassword.focus();

            return;
        }

        btnValidar.disabled = true;
        btnValidar.innerHTML = "Validando...";

        const datos = new FormData();

        datos.append("password", password);

        fetch("validar_password_kiosco.php", {

            method: "POST",
            body: datos

        })
        .then(response => response.json())

        .then(data => {

            if(data.ok){

                window.location.href = "vacaciones_nueva.php";

                return;
            }

            errorPassword.innerHTML = data.mensaje;
            errorPassword.classList.remove("d-none");

            inputPassword.value = "";
            inputPassword.focus();

            btnValidar.disabled = false;
            btnValidar.innerHTML = "Continuar";

        })
        .catch(error => {

            console.error(error);

            errorPassword.innerHTML =
                "No fue posible validar la contraseña.";

            errorPassword.classList.remove("d-none");

            btnValidar.disabled = false;
            btnValidar.innerHTML = "Continuar";

        });

    }

};
