document.addEventListener('DOMContentLoaded', function () {
    var catCards             = document.querySelectorAll('.tk-cat-card');
    var inputCat             = document.getElementById('categoria_id');
    var camposEquipoPersonal = document.getElementById('camposEquipoPersonal');
    var camposImpresora      = document.getElementById('camposImpresora');
    var camposZebra          = document.getElementById('camposZebra');

    var hardwareOpciones     = document.getElementById('hardwareOpciones');
    var camposPeriferico     = document.getElementById('camposPeriferico');

    // Catálogo real de ticket_categoria
    var categoriaEquipoPersonal = 2;
    var categoriaImpresora      = 6;
    var categoriaZebra          = 7;

    catCards.forEach(function (card) {
        card.addEventListener('click', function () {
            catCards.forEach(function (c) { c.classList.remove('active'); });
            card.classList.add('active');

            var id = parseInt(card.getAttribute('data-id'));
            inputCat.value = id;

            hardwareOpciones.classList.remove("show");

            camposEquipoPersonal.classList.remove('show');
            camposImpresora.classList.remove('show');
            camposZebra.classList.remove('show');
            camposPeriferico.classList.remove('show');

            // Limpiar radios
            document.querySelectorAll("input[name='hardware_tipo']").forEach(function(r){
                r.checked = false;
            });

            // Limpiar selects
            document.getElementById("equipoPersonal").selectedIndex = 0;
            document.getElementById("hardwareDetalle").selectedIndex = 0;

            document.querySelector('#camposEquipoPersonal select').required = false;
            document.querySelector('#camposImpresora select').required = false;
            document.querySelector('#camposZebra select').required = false;
            document.querySelector('#camposPeriferico select').required = false;

            if(id === categoriaEquipoPersonal){
                hardwareOpciones.classList.add("show");
                document.querySelectorAll("input[name='hardware_tipo']").forEach(function(r){
                    r.checked = false;
                });
            }

            if(id === categoriaImpresora){
                camposImpresora.classList.add('show');
                document.querySelector('#camposImpresora select').required = true;
            }

            if(id === categoriaZebra){
                camposZebra.classList.add('show');
                document.querySelector('#camposZebra select').required = true;
            }
        });
    });

    document.getElementById('formNuevoTicket').addEventListener('submit', function (e) {
        if (!inputCat.value) {
            e.preventDefault();
            alert('Selecciona primero una categoría para tu ticket.');
        }
    });
    
    document.querySelectorAll("input[name='hardware_tipo']").forEach(function(radio){

        radio.addEventListener("change", function(){

            camposEquipoPersonal.classList.remove("show");
            camposPeriferico.classList.remove("show");

            document.querySelector("#camposEquipoPersonal select").required = false;
            document.querySelector("#camposPeriferico select").required = false;

            if(this.value === "equipo"){

                camposEquipoPersonal.classList.add("show");
                document.querySelector("#camposEquipoPersonal select").required = true;

            }else{

                camposPeriferico.classList.add("show");
                document.querySelector("#camposPeriferico select").required = true;

            }

        });

    });
});
