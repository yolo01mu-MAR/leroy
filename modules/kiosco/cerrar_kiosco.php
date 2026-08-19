<?php

    require_once __DIR__ . '/../../app/bootstrap.php';

    // Eliminar únicamente la sesión del kiosco
    unset($_SESSION['kiosco']);

    // Regenerar el ID de sesión
    session_regenerate_id(true);

    // Regresar al inicio
    redirect(BASE_URL . '/index.php');

?>