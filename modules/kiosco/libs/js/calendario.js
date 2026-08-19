const Calendario = {

    //==============================
    // Configuración
    //==============================
    config:{

        bloqueados:[
            "lleno",
            "bloqueado",
            "pasado",
            "no_laborable"
        ]

    },

    //==============================
    // Configuración
    //==============================
    modo: "simple",
    onChange: null,

    //==============================
    // Estado actual
    //==============================
    estado: null,

    //==============================
    // Estado
    //==============================
    inicio: null,
    fin: null,

    //==============================
    // Inicializar
    //==============================
    init(config = {}){

        this.modo = config.modo ?? "simple";
        this.onChange = config.onChange ?? function(){};

        this.inicio = null;
        this.fin = null;

        this.eventos();

    },

    //==============================
    // Eventos
    //==============================
    eventos(){

        const self = this;

        $(".celda-dia")
            .off("click.calendario")
            .on("click.calendario", function(){

                const celda = $(this);

                if(!celda.data("seleccionable")){
                    return;
                }

                self.seleccionar(
                    celda.data("fecha")
                );

            });

    },

    //==============================
    // Selección
    //==============================
    seleccionar(fecha){

        // -----------------------------
        // MODO SIMPLE
        // -----------------------------
        if(this.modo === "simple"){

            this.inicio = fecha;
            this.fin = fecha;

            this.pintar();
            this.emitir();

            return;

        }

        // -----------------------------
        // MODO RANGO
        // -----------------------------

        // Primer clic
        if(this.inicio === null){

            this.inicio = fecha;
            this.fin = null;

        }

        // Segundo clic
        else if(this.fin === null){

            this.fin = fecha;

            if(this.inicio > this.fin){
                [this.inicio,this.fin] = [this.fin,this.inicio];
            }

            if(!this.validarRango(this.inicio,this.fin)){

                this.limpiar();
                this.emitir("rango");

                return;

            }

        }

        // Tercer clic
        else{

            this.limpiar();

            this.inicio = fecha;
            this.fin = null;

        }

        this.pintar();

        this.emitir();

    },

    //==============================
    // Pintar selección
    //==============================
    pintar(){

        $(".celda-dia")
            .removeClass("seleccionado");

        if(!this.inicio){
            return;
        }

        let inicio = this.inicio;
        let fin = this.fin ?? this.inicio;

        if(inicio > fin){
            [inicio, fin] = [fin, inicio];
        }

        $(".celda-dia").each(function(){

            const fecha = $(this).data("fecha");

            if(fecha >= inicio && fecha <= fin){

                $(this).addClass("seleccionado");

            }

        });

    },

    //==============================
    // Limpiar selección
    //==============================
    limpiar(){

        this.inicio = null;
        this.fin = null;

        $(".celda-dia")
            .removeClass("seleccionado");

        this.emitir();

    },

    //==============================
    // Emitir cambios
    //==============================
    emitir(error = null){

        let dias = 0;

        if(this.inicio && this.fin){

            dias =
                Math.floor(
                    (new Date(this.fin) - new Date(this.inicio)) / 86400000
                ) + 1;

        }

        this.estado = {

            inicio: this.inicio,

            fin: this.fin,

            dias: dias,

            inicioTexto: this.inicio
                ? this.formatear(this.inicio)
                : "—",

            finTexto: this.fin
                ? this.formatear(this.fin)
                : "—",

            completo:
                this.inicio !== null &&
                this.fin !== null,

            error: error

        };

        this.onChange(this.estado);

    },

    //==============================
    // Formatear fecha
    //==============================
    formatear(fecha){

        if(!fecha){
            return "—";
        }

        const p = fecha.split("-");

        return `${p[2]}/${p[1]}/${p[0]}`;

    },

    //==============================
    // Validar rango
    //==============================
    validarRango(inicio, fin){

        let valido = true;

        const bloqueados = this.config.bloqueados;

        $(".celda-dia").each(function(){

            const fecha = $(this).data("fecha");

            if(fecha >= inicio && fecha <= fin){

                const estado = $(this).data("estado");

                if(bloqueados.includes(estado)){

                    valido = false;

                    return false;

                }

            }

        });

        return valido;

    },

};