let html5QrCode = null;
let escaneando = false;

console.log("Lector cargado...");
const LectorQR = {

    iniciar: function (opciones) {

        const config = Object.assign({

            boton: "#btnLeer",
            reader: "reader",
            resultado: "#resultado",
            fps: 10,
            ancho: 250,
            alto: 120,
            onSuccess: function(){},
            onError: function(){}

        }, opciones);

        if (escaneando) {
            return;
        }

        escaneando = true;

        $(config.boton).prop("disabled", true);

        html5QrCode = new Html5Qrcode(config.reader);

        html5QrCode.start(

            {
                facingMode: "environment"
            },

            {
                fps: config.fps,
                qrbox: {
                    width: config.ancho,
                    height: config.alto
                }
            },

            function (decodedText) {

                html5QrCode.stop()
                .then(function(){
                    return html5QrCode.clear();
                })
                .then(function(){
                    html5QrCode = null;
                    escaneando=false;
                    $(config.boton).prop("disabled",false);
                    config.onSuccess(decodedText);
                });

            },

            function () {
                // Ignorar errores de lectura
            }

        ).catch(function (err) {

            escaneando = false;

            $(config.boton).prop("disabled", false);

            if (config.resultado) {

                $(config.resultado).html(
                    '<div class="alert alert-danger">' + err + '</div>'
                );

            }

            config.onError(err);

        });

    },

    detener:function(){

        if(html5QrCode){

            html5QrCode.stop()
            .then(function(){

                return html5QrCode.clear();

            })
            .finally(function(){

                html5QrCode=null;
                escaneando=false;

            });

        }

    },

    estaEscaneando: function(){

        return escaneando;

    }

};