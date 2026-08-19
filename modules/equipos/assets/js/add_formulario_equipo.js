document.getElementById("tipo_equipo").addEventListener("change", function() {

    let tipo = this.value;
    let divDepartamento = document.getElementById("div_departamento");
    const tipoPlan = document.getElementById("tipo_plan");

    // Reiniciar el plan siempre
    tipoPlan.disabled = true;
    tipoPlan.required = false;
    tipoPlan.selectedIndex = 0;

    // Ocultar por defecto
    divDepartamento.style.display = "none";
    document.getElementById("departamento").required = false;
    document.getElementById("departamento").value = "";

    // TODOS LOS CAMPOS
    let campos = [
        "imei","telefono","pantalla","ram","disco","procesador",
        "so","ip","mac","serie","puertos"
    ];

    // DESACTIVAR TODO
    campos.forEach(c => {
        let el = document.getElementById(c);
        if(el){
            el.disabled = true;
            el.required = false;
        }
    });

    // ACTIVAR SEGÚN TIPO
    switch(tipo){
        
        // CELULAR
        case "1":
            tipoPlan.disabled = false;
            tipoPlan.required = true;
            activar(["imei","telefono","pantalla","ram","disco","procesador","so","mac","serie","puertos"]);
        break;

        // PC
        case "2":
            document.getElementById("tipo_plan").disabled = true;
            activar(["ram","disco","procesador","so","serie","puertos"]);
        break;

        // MONITOR
        case "3":
            document.getElementById("tipo_plan").disabled = true;
            activar(["serie", "pantalla","puertos"]);
        break;

        // LAPTOP
        case "4":
            document.getElementById("tipo_plan").disabled = true;
            activar(["pantalla","ram","disco","procesador","so","mac","serie","puertos"]);
        break;

        // IMPRESORA
        case "5":
            document.getElementById("tipo_plan").disabled = true;
            activar(["ip","serie"]);

            divDepartamento.style.display = "flex"; // o "block"
            document.getElementById("departamento").required = true;
        break;

        // ZEBRA
        case "6":
            document.getElementById("tipo_plan").disabled = true;
            activar(["ip","serie"]);
        break;
    }

});

// FUNCIÓN
function activar(lista){
    lista.forEach(c => {
        let el = document.getElementById(c);
        if(el){
            el.disabled = false;
            el.required = true;
        }
    });
}