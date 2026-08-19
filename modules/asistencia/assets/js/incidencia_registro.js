$("#btnLeer").on("click", function () {

    LectorQR.iniciar({

        boton:"#btnLeer",
        reader:"reader",

        success:function(res){

            if(!res.success){

                Swal.fire({
                    icon: "error",
                    title: "Empleado no encontrado",
                    text: res.mensaje
                });

                return;
            }

            cargarEmpleado(res.usuario);

        }

    });

});
function cargarEmpleado(usuario){

    $("#nomina").text(usuario.id);
    $("#nombre").text(usuario.nombre);
    $("#puesto").text(usuario.puesto);
    $("#departamento").text(usuario.departamento);
    $("#area").text(usuario.zonaTrabajo);
    $("#grupo").text(usuario.grupo);
    $("#encargado").text(usuario.encargado);

    $("#btnLeer").slideUp(300);
    $("#reader").slideUp(300);
    $("#formIncidencia").slideDown(300);

}