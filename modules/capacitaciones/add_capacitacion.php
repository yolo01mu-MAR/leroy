<?php    
    require_once __DIR__ . '/../../app/bootstrap.php';

    if(isset($_POST['guardar'])){

        $nombre     = remove_junk($db->escape($_POST['nombre']));
        $instructor = remove_junk($db->escape($_POST['instructor']));
        $ubicacion  = remove_junk($db->escape($_POST['ubicacion']));
        $fecha      = $_POST['fecha'];
        $hora = date('H:i:s');

        if(empty($nombre) || empty($instructor) || empty($fecha)){
            $session->msg('d','Complete todos los campos obligatorios');
            redirect('capa_new.php', false);
        }
        $sql = "INSERT INTO historico_capacitaciones (nombre,instructor,ubicacion,fecha,hora_inicio) 
                VALUES ('{$nombre}','{$instructor}','{$ubicacion}','{$fecha}','{$hora}')";

        if($db->query($sql)){

            $id_capacitacion = $db->insert_id();

            redirect("capa_registro_asistencia.php?id={$id_capacitacion}", false);

        } else {

            $session->msg('d','Error al crear capacitación');
            redirect('capa_new.php', false);

        }
    }
?>