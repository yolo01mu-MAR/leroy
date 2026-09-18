$(document).on("click", ".btnSolicitar", function () {

    confirmarSolicitudTxt(
        $(this).data("id"),
        $(this).data("fecha")
    );

});