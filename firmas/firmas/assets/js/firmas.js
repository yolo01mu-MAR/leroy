class FirmaElectronica {

    constructor(config) {

        this.canvas = document.getElementById(config.canvas);

        if (!this.canvas) {
            throw new Error(`No existe el canvas: ${config.canvas}`);
        }

        // Ajustar tamaño real del canvas
        this.canvas.width = this.canvas.offsetWidth;
        this.canvas.height = config.height ?? 250;
        this.signaturePad = new SignaturePad(this.canvas);
        this.modulo = config.modulo;
        this.registroId = config.registroId;
        this.tipo = config.tipo;
        this.usuarioId = config.usuarioId;
        this.onSuccess = config.onSuccess ?? null;
        this.onError = config.onError ?? null;
    }
    limpiar() {
        this.signaturePad.clear();
    }
    tieneFirma() {
        return !this.signaturePad.isEmpty();
    }
    obtenerDatos() {
        return {
            modulo: this.modulo,
            registro_id: this.registroId,
            tipo: this.tipo,
            usuario_id: this.usuarioId,
            firma: this.signaturePad.toDataURL("image/png")
        };
    }
    guardar() {
        if (!this.tieneFirma()) {
            if (this.onError) {
                this.onError({
                    mensaje: "Debe capturar una firma."
                });
            }
            return;
        }
        const datos = this.obtenerDatos();

        const debug = document.getElementById("debugDatos");

        if(debug){

            debug.textContent = JSON.stringify(datos, null, 4);

        }
        $.ajax({
            url: "ajax/guardar_firma.php",
            type: "POST",
            data:datos,
            dataType: "json",
            success:(respuesta)=>{

                const debug=document.getElementById("debugRespuesta");

                if(debug){

                    debug.textContent=JSON.stringify(respuesta,null,4);

                }

                if(this.onSuccess){

                    this.onSuccess(respuesta);

                }

            },
            error:(xhr)=>{

                const debug=document.getElementById("debugRespuesta");

                if(debug){

                    debug.textContent=xhr.responseText;

                }

                if(this.onError){

                    this.onError(xhr);

                }

            }
        });
    }
    obtenerBase64() {

        if (this.signaturePad.isEmpty()) {
            return null;
        }

        return this.signaturePad.toDataURL("image/png");

    }
}

const FirmaTipo = Object.freeze({

    EMPLEADO: "EMPLEADO",
    JEFE: "JEFE",
    RH: "RH"

});

const FirmaModulo = Object.freeze({

    VACACIONES: "vacaciones",
    TXT: "txt",
    EQUIPO: "equipo",
    PERMISOS: "permisos",
    CAPACITACION: "capacitacion"

});
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".firma-componente").forEach(function (contenedor) {

        if (contenedor.dataset.firmada === "1") {
            return;
        }

        const canvas = contenedor.querySelector(".firma-canvas");

        if (!canvas) {
            return;
        }

        const firma = new FirmaElectronica({

            canvas: canvas.id,
            modulo: contenedor.dataset.modulo,
            registroId: contenedor.dataset.registro,
            tipo: contenedor.dataset.tipo,
            usuarioId: contenedor.dataset.usuario,
            height: contenedor.dataset.height,

            onSuccess: function () {
                location.reload();
            }

        });

        contenedor
            .querySelector(".btn-limpiar")
            .addEventListener("click", function () {
                firma.limpiar();
            });

        contenedor
            .querySelector(".btn-firmar")
            .addEventListener("click", function () {
                firma.guardar();
            });

    });

});
