let filaActiva = null;
let filaModal = null;
let tiempoPendienteGlobal = 0;
const inputCodigo = document.getElementById('codigo');
const lista = document.getElementById('listaCodigos');
let cambioCodigoDetectado = false;

document.getElementById("codigo").addEventListener("keydown", function(e){

    if(e.key === "Enter"){

        e.preventDefault();
        this.dispatchEvent(new Event("input"));

    }

});
inputCodigo.addEventListener('input', function() {

    let valor = this.value;
    let opcionValida = Array.from(lista.options).find(option => option.value === valor);

    if(!opcionValida) return;

    let id = valor.split(' - ')[0];
    if(!id) return;

    fetch('ajax/buscar_producto.php?id=' + id)
        .then(response => response.json())
        .then(data => {

            if(!data.success) return;

            // TABLA 1 (solo productos únicos)
            let existe = false;

            document.querySelectorAll("#tablaProductos1 tbody tr").forEach(row => {
                let codigo = row.children[0].innerText.trim();
                if(codigo === String(data.id)){
                    existe = true;
                }
            });
            if(!existe){
                let fila1 = `
                    <tr>
                        <td class="text-center codigo">${data.id}</td>
                        <td class="text-center">${data.descripcion}</td>
                        <td class="text-center">${data.piezasXhora}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-xs eliminarProducto">
                                <i class="glyphicon glyphicon-trash"></i>
                            </button>
                        </td>
                    </tr>
                    `;
                document.querySelector('#tablaProductos1 tbody').insertAdjacentHTML('beforeend', fila1);
            }else{

                // resaltar producto existente
                document.querySelectorAll("#tablaProductos1 tbody tr").forEach(row => {
            
                    let codigo = row.children[0].innerText.trim();
            
                    if(codigo === String(data.id)){
                        row.style.background = "#fff3cd";
            
                        setTimeout(()=>{
                            row.style.background = "";
                        },1500);
                    }
            
                });
            
            }

            //TABLA 2
            let filaId = Date.now();

            let fila2 = `
                <tr data-descripcion="${data.descripcion}" data-fila-id="fila_${Date.now()}">
                    <td class="text-center">
                        <button type="button" class="btn btn-success btn-xs continuarFila">
                            <i class="glyphicon glyphicon-plus"></i>
                        </button>
                    </td>
                    <td><input type="time" class="form-control horaInicio"></td>
                    <td><input type="time" class="form-control horaFinal"></td>
                    <td class="text-center evento">PRODUCCION</td>
                    <td class="text-center codigo">${data.id}</td>
                    <td><input type="number" class="form-control produccion"></td>
                    <td class="text-center teorico" data-piezas-hora="${data.piezasXhora}">0</td>
                    <td class="text-center porcentaje">0%</td>
                    <td class="text-center tiempoDeudor"></td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-xs eliminarProduccion">
                            <i class="glyphicon glyphicon-trash"></i>
                        </button>
                    </td>
                </tr>
            `;     

            document
                .querySelector('#tablaProductos2 tbody')
                .insertAdjacentHTML('beforeend', fila2);

                // Obtener la última fila insertada
                let ultimaFila = document.querySelector('#tablaProductos2 tbody tr:last-child');

                // Inicializar Flatpickr en esa fila
                flatpickr(ultimaFila.querySelector('.horaInicio'), {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true
                });

                flatpickr(ultimaFila.querySelector('.horaFinal'), {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true
                });

            inputCodigo.value = '';
            inputCodigo.focus();

        });

});
document.addEventListener("click", function(e){

    if(e.target.closest(".eliminarProducto")){

        console.log("CLICK DETECTADO");

        let fila = e.target.closest("tr");
        let codigo = fila.children[0].innerText.trim();

        fila.remove();

        document.querySelectorAll("#tablaProductos2 tbody tr").forEach(row => {

            let codigoTabla2 = row.children[4].innerText.trim();

            if(codigoTabla2 === codigo){
                row.remove();
            }

        });

        generarTablaProductos();

    }

});
document.addEventListener("click", function(e){

    if(e.target.closest(".eliminarProduccion")){

        let fila = e.target.closest("tr");

        // eliminar solo si es editable
        if(!fila.querySelector(".horaInicio")) return;

        fila.remove();

        generarTablaProductos();

    }

});
document.addEventListener('change', function(e){

    if(e.target.classList.contains('horaInicio') || 
       e.target.classList.contains('horaFinal')){

        validarContinuidad();

        let fila = e.target.closest('tr');

        let inicio = fila.querySelector('.horaInicio').value;
        let fin = fila.querySelector('.horaFinal').value;

        if(inicio && fin){

            let i = new Date(`1970-01-01T${inicio}`);
            let f = new Date(`1970-01-01T${fin}`);

            if(f < i ){
                f.setDate(f.getDate()+1);
            }

            let minutos = (f - i) / 60000;

            if(minutos > 0){

                let piezasHora = parseFloat(
                    fila.querySelector('.teorico').dataset.piezasHora
                );
            
                let horasTrabajadas = minutos / 60;
            
                let teoricoCalculado = (piezasHora * horasTrabajadas).toFixed(0);
            
                fila.querySelector('.teorico').textContent = teoricoCalculado;
            
                calcularTodo(fila, horasTrabajadas);
            }
            
        }
    }
});
document.addEventListener('input', function(e){

    if(e.target.classList.contains('produccion')){

        let fila = e.target.closest('tr');
         calcularTodo(fila);

        let prodElem = fila.querySelector('.produccion');

        let produccion = 0;

        if(prodElem){
            produccion = (prodElem.tagName === "INPUT")
                ? parseFloat(prodElem.value)
                : parseFloat(prodElem.innerText);
        }

        produccion = produccion || 0;
        let teoricoElem = fila.querySelector('.teorico');

        let teorico = 0;

        if(teoricoElem){
            teorico = parseFloat(teoricoElem.innerText) || 0;
        }

        let porcentaje = teorico > 0
            ? ((produccion / teorico) * 100).toFixed(2)
            : 0;

        fila.querySelector('.porcentaje').textContent = porcentaje + '%';

        if(porcentaje >= 100){
            fila.querySelector('.comprobacion').innerHTML =
                '<span class="label label-success">OK</span>';
        } else {
            fila.querySelector('.comprobacion').innerHTML =
                '<span class="label label-danger">BAJO</span>';
        }
    }
});
function calcularTodo(fila, horasTrabajadas = null){

    let prodElem = fila.querySelector('.produccion');
    let produccion = (prodElem?.tagName === "INPUT")
        ? parseFloat(prodElem.value)
        : parseFloat(prodElem?.innerText);

    produccion = produccion || 0;

    // TEORICO
    let teoricoElem = fila.querySelector('.teorico');
    let teorico = parseFloat(teoricoElem?.innerText) || 0;

    // PIEZAS HORA
    let piezasHora = parseFloat(
        teoricoElem?.dataset.piezasHora
    ) || 0;

    // HORAS
    if(horasTrabajadas === null){

        let inicioElem = fila.querySelector('.horaInicio');
        let finElem = fila.querySelector('.horaFinal');

        let inicio = (inicioElem?.tagName === "INPUT")
            ? inicioElem.value
            : inicioElem?.innerText;

        let fin = (finElem?.tagName === "INPUT")
            ? finElem.value
            : finElem?.innerText;

        if(inicio && fin){
            let i = new Date(`1970-01-01T${inicio}`);
            let f = new Date(`1970-01-01T${fin}`);

            if(f < i){
                f.setDate(f.getDate()+1);
            }

            let minutos = (f - i) / 60000;
            horasTrabajadas = minutos > 0 ? minutos / 60 : 0;
        } else {
            horasTrabajadas = 0;
        }
    }

    // PORCENTAJE
    let porcentaje = teorico > 0
        ? ((produccion / teorico) * 100).toFixed(2)
        : 0;

    fila.querySelector('.porcentaje').textContent = porcentaje + '%';

    // TIEMPOS
    let minutosTrabajados = horasTrabajadas * 60;

    let minutosGanados = piezasHora > 0
        ? (produccion / piezasHora) * 60
        : 0;

    let minutosPerdidos = minutosTrabajados - minutosGanados;

    if(minutosPerdidos < 0){
        minutosPerdidos = 0;
    }

    // GUARDAR SIEMPRE
    fila.dataset.tiempoPendiente = minutosPerdidos;

    // TEXTO
    let texto = minutosPerdidos.toFixed(0) + ' min';

    let celda = fila.querySelector('.tiempoDeudor');

    celda.style.fontWeight = "bold";
    celda.textContent = texto;
    celda.style.color = minutosPerdidos > 0 ? 'red' : 'green';

    filaActiva = fila;

    recalcularTiempoPendienteTotal();

}
function validarHuecos(){

    let filas = [];

    document.querySelectorAll("#tablaProductos2 tbody tr").forEach(row => {

        let inicio = row.querySelector(".horaInicio")?.value 
                  || row.querySelector(".horaInicio")?.innerText;

        let fin = row.querySelector(".horaFinal")?.value 
               || row.querySelector(".horaFinal")?.innerText;

        if(inicio && fin){
            filas.push({inicio, fin});
        }

    });

    filas.sort((a,b)=> a.inicio.localeCompare(b.inicio));

    let totalHuecos = 0;

    for(let i=1;i<filas.length;i++){

        let finAnterior = convertirMinutos(filas[i-1].fin);
        let inicioActual = convertirMinutos(filas[i].inicio);

        let diff = inicioActual - finAnterior;

        if(diff > 0){
            totalHuecos += diff;
        }
    }

    return totalHuecos;
}
function validarTiempoPerdido(){

    let total = 0;

    document.querySelectorAll("#tablaProductos2 tbody tr").forEach(row => {

        let texto = row.querySelector('.tiempoDeudor')?.innerText || "0";
        let minutos = parseFloat(texto) || 0;

        total += minutos;

    });

    return total;
}
function totalFallas(){
    return obtenerMinutosFallas();
}
function validarAntesDeGuardar(){

    let huecos = validarHuecos();
    let perdidos = validarTiempoPerdido();
    let fallas = totalFallas();

    let totalDebe = huecos + perdidos;

    if(totalDebe > fallas){

        let pendiente = totalDebe - fallas;

        abrirModalParo("","", pendiente);
        return false;
    }

    return true;
}
function generarTablaProductos(){

    let productos = {};

    document.querySelectorAll("#tablaProductos2 tbody tr").forEach(row => {

        let codigo = row.children[4].innerText.trim();

        let descripcion = row.dataset.descripcion || "";

        let teoricoCelda = row.querySelector(".teorico");

        let teorico = 0;

        if(teoricoCelda){

            teorico =
                parseFloat(teoricoCelda.dataset.piezasHora) ||
                parseFloat(teoricoCelda.innerText) ||
                0;

        }

        if(!productos[codigo]){

            productos[codigo] = {
                descripcion: descripcion,
                teorico: teorico
            };

        }

    });

    let tbody = document.querySelector("#tablaProductos1 tbody");

    tbody.innerHTML = "";
    
    Object.keys(productos).forEach(codigo => {

        let fila = `
            <tr>
                <td class="text-center">${codigo}</td>
                <td class="text-center">${productos[codigo].descripcion}</td>
                <td class="text-center">${productos[codigo].teorico}</td>
            </tr>
        `;

        tbody.insertAdjacentHTML("beforeend", fila);

    });

}
function agregarFalla(minutos, paroId, paroNombre, subparoId, subparoNombre){

    if(!filaActiva){

        let filaId = null;

        if(parseInt(subparoId) !== 43){ // NO es cambio de código
            filaId = filaActiva.dataset.filaId;
        }

        // SOLO permitir cambio de código (43)
        if(parseInt(subparoId) === 43){

            ajustarSiguienteFila(minutos);

            let filaFalla = `
                <tr data-fila-id="${filaId}" data-paro-id="${paroId}" data-subparo-id="${subparoId}">
                    <td class="text-center">${minutos}</td>
                    <td class="text-center">${paroNombre}</td>
                    <td class="text-center">${subparoNombre}</td>
                </tr>
            `;

            document.querySelector('#tablaFallas tbody').insertAdjacentHTML('beforeend', filaFalla);

            // RECALCULAR TODAS LAS FILAS
            calcularTodo(filaActiva);

            // habilitar buscador
            let buscador = document.getElementById("codigo");
            buscador.disabled = false;
            buscador.focus();

            return; // IMPORTANTE: salir aquí
        }

        alert("No hay fila activa.");
        return;
    }

    let tiempoActual = parseFloat(filaActiva.dataset.tiempoPendiente) || 0;

    // RESTAR FALLA AL TIEMPO REAL
    tiempoActual -= minutos;

    if(tiempoActual < 0){
        tiempoActual = 0;
    }

    // ACTUALIZAR DATASET (CLAVE)
    filaActiva.dataset.tiempoPendiente = tiempoActual;

    let celda = filaActiva.querySelector('.tiempoDeudor');
    
    celda.textContent = tiempoActual.toFixed(0) + ' min';
    celda.style.color = tiempoActual > 0 ? 'red' : 'green';

    let filaId = filaActiva ? filaActiva.dataset.filaId : '';

    let filaFalla = `
        <tr data-paro-id="${paroId}" data-subparo-id="${subparoId}" data-fila-id="${filaId}">
            <td class="text-center">${minutos}</td>
            <td class="text-center">${paroNombre}</td>
            <td class="text-center">${subparoNombre}</td>
        </tr>
    `;

    document
    .querySelector('#tablaFallas tbody')
    .insertAdjacentHTML('beforeend', filaFalla);

    // PRIMERO insertas, LUEGO recalculas
    document.querySelectorAll("#tablaProductos2 tbody tr").forEach(fila => {
        calcularTodo(fila);
    });

    recalcularTiempoPendienteTotal();
    actualizarTiempoRestante();

    if(!filaActiva){

        let filas = document.querySelectorAll("#tablaProductos2 tbody tr");

        for(let i = filas.length - 1; i >= 0; i--){

            let inicio = filas[i].querySelector(".horaInicio")?.value;
            let fin = filas[i].querySelector(".horaFinal")?.value;

            if(inicio && fin){
                filaActiva = filas[i];
                break;
            }
        }
        function ajustarSiguienteFila(minutos){

            let filas = document.querySelectorAll("#tablaProductos2 tbody tr");

            for(let i = 1; i < filas.length; i++){

                let anterior = filas[i-1];
                let actual = filas[i];

                let finAnterior = anterior.querySelector(".horaFinal")?.value;
                let inicioActual = actual.querySelector(".horaInicio")?.value;

                if(finAnterior && inicioActual){

                    let nuevaHora = sumarMinutos(finAnterior, minutos);

                    actual.querySelector(".horaInicio").value = nuevaHora;

                    // opcional: también mover la hora final
                    let finActual = actual.querySelector(".horaFinal")?.value;

                    if(finActual){
                        actual.querySelector(".horaFinal").value = sumarMinutos(finActual, minutos);
                    }

                break; // solo afecta la siguiente

                }
            }
        }

    }

}
function actualizarTiempoRestante(){

    let btnAgregar = document.getElementById('btnAgregarFalla');
    let btnGuardar = document.getElementById('btnGuardarReporte');
    let btn = document.getElementById("btnFinalizar");

    if(!btn) return;

    let tiempo = parseFloat(tiempoPendienteGlobal) || 0;

    console.log("Tiempo pendiente:", tiempo);

    if(tiempo <= 0.5){

        tiempoPendienteGlobal = 0;

        btn.disabled = false;

        if(btnAgregar){
            btnAgregar.disabled = true;
        }

        if(btnGuardar){
            btnGuardar.disabled = false;
        }

    } else {

        btn.disabled = true;

        if(btnAgregar){
            btnAgregar.disabled = false;
        }

        if(btnGuardar){
            btnGuardar.disabled = true;
        }
    }
}
function registrarFalla(){

    let minutos = 0;
    let paroId = "";
    let paroNombre = "";
    let subparoId = "";
    let subparoNombre = "";

    // detectar si el modal está abierto
    if($("#modalFalla").hasClass("in")){

        minutos = parseFloat(document.getElementById("minutosFalla_modal").value) || 0;

        let paroSelect = document.getElementById("paro_id_modal");
        paroId = paroSelect.value;
        paroNombre = paroSelect.options[paroSelect.selectedIndex]?.text || "";

        let subparoSelect = document.getElementById("subparo_id_modal");
        subparoId = subparoSelect.value;
        subparoNombre = subparoSelect.options[subparoSelect.selectedIndex]?.text || "";

    }else{

        minutos = parseFloat(document.getElementById("minutosFalla").value) || 0;

        let paroSelect = document.getElementById("paro_id");
        paroId = paroSelect.value;
        paroNombre = paroSelect.options[paroSelect.selectedIndex]?.text || "";

        let subparoSelect = document.getElementById("subparo_id");
        subparoId = subparoSelect.value;
        subparoNombre = subparoSelect.options[subparoSelect.selectedIndex]?.text || "";

    }

    if(minutos <= 0 || paroId === "" || subparoId === ""){
        alert("Completa todos los datos.");
        return;
    }

    agregarFalla(minutos, paroId, paroNombre, subparoId, subparoNombre);

    if(subparoId == 43){ // CAMBIO DE CODIGO

        let buscador = document.getElementById("codigo");

        buscador.disabled = false;
        buscador.value = "";
        buscador.focus();

    }

    // cerrar modal si se usó
    $("#modalFalla").modal("hide");

}
function guardarManual(){
    autoguardar();
}
function autoguardar(tipo = "auto"){

    if(!validarAntesDeGuardar()){
        return;
    }

    cambiarEstado("guardando");

    let form = document.getElementById("formReporte");
    let datos = new FormData(form);

    datos.append("grupo", document.querySelector('[name="grupo"]').value);
    datos.append("supervisor", document.querySelector('[name="supervisor"]').value);
    datos.append("operador", document.querySelector('[name="operador"]').value);
    datos.append("autoguardado", "1");

    let reporteId = document.getElementById("reporte_id").value;
    datos.append("reporte_id", reporteId);

    let produccion = [];

    document.querySelectorAll("#tablaProductos2 tbody tr").forEach(row => {

        let celdas = row.children;

        let horaInicio = "";
        let horaFin = "";
        let evento = "";
        let codigo = "";
        let prod = "";
        let teorico = "";
        let porcentaje = "";

        // HORA INICIO
        let inicioInput = celdas[1].querySelector("input");
        horaInicio = inicioInput ? inicioInput.value : celdas[1].innerText.trim();

        // HORA FIN
        let finInput = celdas[2].querySelector("input");
        horaFin = finInput ? finInput.value : celdas[2].innerText.trim();

        // EVENTO
        evento = celdas[3].innerText.trim();

        // CODIGO
        codigo = celdas[4].innerText.trim();

        // PRODUCCION
        let prodInput = celdas[5].querySelector("input");
        prod = prodInput ? prodInput.value : celdas[5].innerText.trim();

        // TEORICO
        teorico = celdas[6].innerText.trim();

        // PORCENTAJE
        porcentaje = celdas[7].innerText.replace("%","").trim();

        if(horaInicio && horaFin){

            produccion.push({
                fila_id: row.dataset.id,
                hora_inicio: horaInicio,
                hora_fin: horaFin,
                evento: evento,
                codigo: codigo,
                produccion: prod,
                teorico: teorico,
                porcentaje: porcentaje
            });

        }

    });

    datos.append("produccion_json", JSON.stringify(produccion));

    // ===== FALLAS =====
    let fallas = [];

    document.querySelectorAll("#tablaFallas tbody tr").forEach(row => {

        fallas.push({
            fila_id: row.dataset.filaId || null,
            minutos: row.children[0]?.innerText.trim() || '',
            paro_id: row.dataset.paroId || '',
            subparo_id: row.dataset.subparoId || '',
            descripcion: row.children[2]?.innerText.trim() || ''
        });

    });

    datos.append("fallas_json", JSON.stringify(fallas));

    fetch(window.location.pathname, {
        method:"POST",
        body: datos
    })
    .then(r => r.text())
    .then(data => {
        if(data.includes("AUTO OK")){
            // Guardado correcto
            cambiarEstado("ok");
            setTimeout(()=>{
                cambiarEstado("idle");
            },2000);
        } else {
            cambiarEstado("error");
        }
    })
    .catch(error => {
        console.error(error);
        cambiarEstado("error");
    });
}

function actualizarEstado(tipo, mensaje){

    const estado = document.getElementById("estadoGuardado");
    const dot = estado.querySelector(".estado-dot");
    const texto = estado.querySelector(".estado-texto");

    dot.className = "estado-dot"; // reset

    switch(tipo){
        case "guardando":
            dot.classList.add("naranja");
            break;
        case "auto":
            dot.classList.add("verde");
            break;
        case "manual":
            dot.classList.add("azul");
            break;
        case "error":
            dot.classList.add("rojo");
            break;
        case "inactivo":
            dot.classList.add("gris");
            break;
    }

    texto.innerText = mensaje;
}

let autoActivo = true;
let intervaloAuto = setInterval(() => autoguardar("auto"), 300000);

function toggleAuto(){

    const switchAuto = document.getElementById("autoSwitch");

    if(switchAuto.checked){

        autoActivo = true;

        intervaloAuto = setInterval(() => {
            autoguardar("auto");
        },300000);

    }else{

        autoActivo = false;
        clearInterval(intervaloAuto);

    }
}
function recalcularTiempoPendienteTotal(){

    let total = 0;

    document.querySelectorAll("#tablaProductos2 tbody tr").forEach(row => {

        let minutos = parseFloat(row.dataset.tiempoPendiente) || 0;

        total += minutos;

    });

    let minutosFallas = obtenerMinutosFallas();

    // AQUÍ SÍ restas fallas (para el botón)
    tiempoPendienteGlobal = total - minutosFallas;

    if(tiempoPendienteGlobal < 0){
        tiempoPendienteGlobal = 0;
    }

    let panel = document.getElementById("panelFallas");

    if(panel){
        panel.style.display = "block";
    }

}
function validarContinuidad(){

    let filas = document.querySelectorAll("#tablaProductos2 tbody tr");
    
    for(let i=1;i<filas.length;i++){
    
        let anterior = filas[i-1];
        let actual = filas[i];
    
        let finAnterior = anterior.querySelector(".horaFinal")?.value;
        let inicioActual = actual.querySelector(".horaInicio")?.value;
    
        if(finAnterior && inicioActual && finAnterior !== inicioActual){
    
            actual.querySelector(".horaInicio").value = finAnterior;
    
        }
    
    }
    
}

$(document).on('change', '#paro_id, #paro_id_modal', function(){

    let paro_id = $(this).val();

    // Detectar qué select de subparos corresponde
    let subparoSelect;

    if(this.id === "paro_id_modal"){
        subparoSelect = $("#subparo_id_modal");
    }else{
        subparoSelect = $("#subparo_id");
    }

    if(!paro_id){
        subparoSelect.html('<option value="">Seleccione un subparo</option>');
        return;
    }

    $.ajax({
        url: 'ajax/subparos.php',
        type: 'POST',
        data: { paro_id: paro_id },
        success: function(data){
            subparoSelect.html(
                '<option value="">Seleccione un subparo</option>' + data
            );
        },
        error: function(xhr){
            console.error("Error cargando subparos:", xhr.responseText);
        }
    });

});
document.addEventListener("DOMContentLoaded", function(){

    // ===== PANEL FALLAS SIEMPRE VISIBLE =====
    let panel = document.getElementById("panelFallas");
    if(panel){
        panel.style.display = "block";
    }

    // ===== CARGAR PRODUCCIÓN GUARDADA =====
    if(typeof produccionGuardada !== "undefined" && produccionGuardada.length > 0){

        let tbody = document.querySelector("#tablaProductos2 tbody");

        produccionGuardada.forEach(p => {

            let fila = `
            <tr data-id="${p.id}" data-descripcion="${p.descripcion}">
                <td></td>
                <td class="text-center horaInicio">${p.hora_inicio}</td>
                <td class="text-center horaFinal">${p.hora_fin}</td>
                <td class="text-center evento">${p.evento}</td>
                <td class="text-center codigo">${p.producto_id}</td>
                <td class="text-center produccion">${p.produccion_real}</td>
                <td class="text-center teorico" data-piezas-hora="${p.piezasXhora}">${p.estandar_hora}</td>
                <td class="text-center porcentaje">${p.porcentaje}%</td>
                <td class="text-center tiempoDeudor"></td>
            </tr>`;

            tbody.insertAdjacentHTML("beforeend", fila);

        });

        // recalcular cada fila
        document.querySelectorAll("#tablaProductos2 tbody tr").forEach(fila => {

            let inicio = fila.querySelector(".horaInicio")?.innerText.trim();
            let fin = fila.querySelector(".horaFinal")?.innerText.trim();

            if(inicio && fin){

                let i = new Date(`1970-01-01T${inicio}`);
                let f = new Date(`1970-01-01T${fin}`);

                if(f < i){
                    f.setDate(f.getDate()+1);
                }

                let minutos = (f - i) / 60000;
                let horas = minutos / 60;

                calcularTodo(fila, horas);
            }

        });
    }

    // ===== CARGAR FALLAS GUARDADAS =====
    if(typeof fallasGuardadas !== "undefined" && fallasGuardadas.length > 0){

        let tbody = document.querySelector("#tablaFallas tbody");

        fallasGuardadas.forEach(f => {

            let fila = `
                <tr data-paro-id="${f.paro_id}" data-subparo-id="${f.subParos_id}">
                    <td class="text-center">${f.minutos}</td>
                    <td class="text-center">${f.paro_nombre}</td>
                    <td class="text-center">${f.subparo_nombre}</td>
                </tr>`;

            tbody.insertAdjacentHTML("beforeend", fila);

        });
    }

    // ===== BLOQUEAR CAMPOS SI YA HAY DATOS =====
    if(typeof produccionGuardada !== "undefined" && produccionGuardada.length > 0){
        document.querySelector('[name="grupo"]').disabled = true;
        document.querySelector('[name="supervisor"]').disabled = true;
        document.querySelector('[name="operador"]').disabled = true;
    }

    // ===== INICIALIZACIONES =====
    recalcularTiempoPendienteTotal();
    actualizarTiempoRestante();
    generarTablaProductos();
    validarContinuidad();

});
function cambiarEstado(tipo){

    const dot = document.getElementById("estadoDot");

    dot.className = "estado-dot";

    if(tipo === "guardando"){
        dot.classList.add("naranja");
    }
    if(tipo === "ok"){
        dot.classList.add("verde");
    }
    if(tipo === "error"){
        dot.classList.add("rojo");
    }
    if(tipo === "idle"){
        dot.classList.add("gris");
    }

}
document.addEventListener("click", function(e){

    if(e.target.closest(".continuarFila")){
 
        let fila = e.target.closest("tr");
    
        let botonActual = fila.querySelector(".continuarFila");
   
        document.getElementById("codigo").disabled = true;
    
        if(botonActual){
            botonActual.style.display = "none";
        }
    
        let horaFin = fila.querySelector(".horaFinal").value;
    
        if(!horaFin){
            alert("Primero captura la hora final.");
            return;
        }
    
        let codigo = fila.children[4].innerText;
        let descripcion = fila.dataset.descripcion;
        let piezasHora = fila.querySelector(".teorico").dataset.piezasHora;
        let eventoActual = fila.querySelector(".evento").value;
    
        let nuevaFila = `
            <tr data-descripcion="${descripcion}" data-fila-id="fila_${Date.now()}">
                <td class="text-center">
                    <button type="button" class="btn btn-success btn-xs continuarFila">
                        <i class="glyphicon glyphicon-plus"></i>
                    </button>
                </td>
                <td><input type="time" class="form-control horaInicio" value="${horaFin}"></td>
                <td><input type="time" class="form-control horaFinal"></td>
                <td class="text-center evento">PRODUCCION</td>
                <td class="text-center codigo">${codigo}</td>
                <td><input type="number" class="form-control produccion"></td>
                <td class="text-center teorico" data-piezas-hora="${piezasHora}">0</td>
                <td class="text-center porcentaje">-</td>
                <td class="text-center tiempoDeudor"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-xs eliminarProduccion">
                        <i class="glyphicon glyphicon-trash"></i>
                    </button>
                </td>
            </tr>
            `;
    
        fila.insertAdjacentHTML("afterend", nuevaFila);

        let nueva = fila.nextElementSibling;

        flatpickr(nueva.querySelector('.horaInicio'), {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });

        flatpickr(nueva.querySelector('.horaFinal'), {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });
    
    }
    
});

function convertirMinutos(hora){

    let partes = hora.split(":");

    let h = parseInt(partes[0]) || 0;
    let m = parseInt(partes[1]) || 0;

    return (h * 60) + m;

}
function ajustarSiguienteFila(minutos){

    let filas = document.querySelectorAll("#tablaProductos2 tbody tr");

    for(let i = 1; i < filas.length; i++){

        let anterior = filas[i-1];
        let actual = filas[i];

        let finAnterior = anterior.querySelector(".horaFinal")?.value 
                        || anterior.querySelector(".horaFinal")?.innerText;

        let inicioActual = actual.querySelector(".horaInicio")?.value;

        if(finAnterior && inicioActual){

            let nuevaHora = sumarMinutos(finAnterior, minutos);

            actual.querySelector(".horaInicio").value = nuevaHora;

            let finActual = actual.querySelector(".horaFinal")?.value;

            if(finActual){
                actual.querySelector(".horaFinal").value = sumarMinutos(finActual, minutos);
            }

            break;
        }
    }
}
function sumarMinutos(hora, minutos){

    let partes = hora.split(":");

    let h = parseInt(partes[0]) || 0;
    let m = parseInt(partes[1]) || 0;

    let total = (h * 60) + m + minutos;

    let nuevaH = Math.floor(total / 60);
    let nuevaM = total % 60;

    return String(nuevaH).padStart(2,'0') + ":" + String(nuevaM).padStart(2,'0');
}
function registrarFallaModal(){

    let filaId = null;

    if(filaModal){
        filaId = filaModal.dataset.filaId;
    }

    let minutos = parseFloat(document.getElementById("minutosFalla_modal").value) || 0;

    let paroSelect = document.getElementById("paro_id_modal");
    let paroId = paroSelect.value;
    let paroNombre = paroSelect.options[paroSelect.selectedIndex]?.text || "";

    let subparoSelect = document.getElementById("subparo_id_modal");
    let subparoId = subparoSelect.value;
    let subparoNombre = subparoSelect.options[subparoSelect.selectedIndex]?.text || "";

    if(minutos <= 0 || paroId === "" || subparoId === ""){
        alert("Completa todos los datos.");
        return;
    }

    // evitar duplicados de cambio de código
    if(parseInt(subparoId) === 43){

        let yaExiste = false;

        document.querySelectorAll("#tablaFallas tbody tr").forEach(row => {
            if(parseInt(row.dataset.subparoId) === 43){
                yaExiste = true;
            }
        });

        if(yaExiste){
            return;
        }
    }

    filaId = filaModal ? filaModal.dataset.filaId : '';

    let filaFalla = `
        <tr data-paro-id="${paroId}" data-subparo-id="${subparoId}" data-fila-id="${filaId}">
            <td class="text-center">${minutos}</td>
            <td class="text-center">${paroNombre}</td>
            <td class="text-center">${subparoNombre}</td>
        </tr>
    `;

    huecoJustificado = true;

    document.querySelector('#tablaFallas tbody').insertAdjacentHTML('beforeend', filaFalla);
        $("#modalFalla").modal("hide");
        autoguardar();
}

    function abrirModalParo(inicio, fin, minutos){

        filaModal = filaActiva; 
        let minutosInput = document.getElementById("minutosFalla_modal");

        minutosInput.value = minutos;
        minutosInput.readOnly = true;

        document.getElementById("mensajeParo").innerText =
            `Se detectó una falta de tiempo de ${minutos} minutos (${inicio} - ${fin})`;

        $("#modalFalla").modal("show");

}
function obtenerMinutosFallas(){

    let total = 0;

    document.querySelectorAll("#tablaFallas tbody tr").forEach(row => {

        let minutos = parseFloat(row.children[0].innerText) || 0;
        total += minutos;

    });

    return total;
}