const Calendario = {

    //==================================================
    // CONFIGURACIÓN
    //==================================================

    config: {

        bloqueados: []

    },


    //==================================================
    // CONFIGURACIÓN GENERAL
    //==================================================

    modo: "simple",

    onChange: null,


    //==================================================
    // ESTADO
    //==================================================

    estado: null,

    inicio: null,

    fin: null,


    //==================================================
    // INICIALIZAR
    //==================================================

    init(config = {}) {

        this.modo =
            config.modo ?? "simple";

        this.onChange =
            config.onChange ?? function () {};


        this.config.bloqueados =
            config.bloqueados ?? [];


        this.inicio =
            config.inicio ?? null;

        this.fin =
            config.fin ?? null;


        this.eventos();

        this.pintar();

        this.emitir();

    },


    //==================================================
    // EVENTOS
    //==================================================

    eventos() {

        const self = this;


        $(".celda-dia")
            .off("click.calendario")
            .on("click.calendario", function () {

                const celda =
                    $(this);


                if (
                    !celda.data("seleccionable")
                ) {

                    return;

                }


                self.seleccionar(
                    celda.data("fecha")
                );

            });

    },


    //==================================================
    // SELECCIONAR
    //==================================================

    seleccionar(fecha) {


        //================================================
        // MODO SIMPLE
        //================================================

        if (this.modo === "simple") {

            this.inicio = fecha;

            this.fin = fecha;

            this.pintar();

            this.emitir();

            return;

        }


        //================================================
        // PRIMER CLIC
        //================================================

        if (this.inicio === null) {

            this.inicio = fecha;

            this.fin = null;

            this.pintar();

            this.emitir();

            return;

        }


        //================================================
        // TERCER CLIC
        // NUEVA SELECCIÓN
        //================================================

        if (this.fin !== null) {

            this.inicio = fecha;

            this.fin = null;

            this.pintar();

            this.emitir();

            return;

        }


        //================================================
        // SEGUNDO CLIC
        //================================================

        let inicio =
            this.inicio;

        let fin =
            fecha;


        if (inicio > fin) {

            [
                inicio,
                fin
            ] = [
                fin,
                inicio
            ];

        }


        this.inicio =
            inicio;

        this.fin =
            fin;


        this.pintar();

        this.emitir();

    },


    //==================================================
    // PINTAR
    //==================================================

    pintar() {

        $(".celda-dia")
            .removeClass("seleccionado");


        if (!this.inicio) {

            return;

        }


        let inicio =
            this.inicio;

        let fin =
            this.fin ?? this.inicio;


        if (inicio > fin) {

            [
                inicio,
                fin
            ] = [
                fin,
                inicio
            ];

        }


        $(".celda-dia").each(function () {

            const fecha =
                $(this).data("fecha");


            if (
                fecha >= inicio &&
                fecha <= fin
            ) {

                $(this)
                    .addClass("seleccionado");

            }

        });

    },


    //==================================================
    // LIMPIAR
    //==================================================

    limpiar() {

        this.inicio = null;

        this.fin = null;


        $(".celda-dia")
            .removeClass("seleccionado");


        this.emitir();

    },


    //==================================================
    // EMITIR ESTADO
    //==================================================

    emitir(error = null) {

        let dias = 0;


        if (
            this.inicio &&
            this.fin
        ) {

            dias =
                Math.floor(
                    (
                        new Date(this.fin) -
                        new Date(this.inicio)
                    ) /
                    86400000
                ) + 1;

        }


        this.estado = {

            inicio:
                this.inicio,

            fin:
                this.fin,

            dias:
                dias,

            inicioTexto:
                this.inicio
                    ? this.formatear(this.inicio)
                    : "—",

            finTexto:
                this.fin
                    ? this.formatear(this.fin)
                    : "—",

            completo:
                this.inicio !== null &&
                this.fin !== null,

            error:
                error

        };


        this.onChange(
            this.estado
        );

    },


    //==================================================
    // FORMATEAR
    //==================================================

    formatear(fecha) {

        if (!fecha) {

            return "—";

        }


        const partes =
            fecha.split("-");


        return `${partes[2]}/${partes[1]}/${partes[0]}`;

    }

};