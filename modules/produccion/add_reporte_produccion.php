<?php
    require_once __DIR__ . '/../../app/bootstrap.php';

    if(!isset($_POST['fechaReporte']) || !isset($_POST['maquina_id'])){
        header("Location: reportes.php");
        exit();
    }

    $fecha = $db->escape($_POST['fechaReporte']);
    $maquina_id = (int)$_POST['maquina_id'];

    $sql = "INSERT INTO reportes
            (maquina_id, fecha, estado, created_at)
            VALUES
            ({$maquina_id}, '{$fecha}', 'ACTIVO', NOW())";

    if($db->query($sql)){

        $nuevoId = $db->insert_id();

        // REDIRECCIÓN AUTOMÁTICA
        header("Location: edit_reporte_produccion.php?id=".$nuevoId);
        exit();

    } else {

        die("Error al crear reporte");

    }
?>