<?php

require_once('../includes/load.php');

$id_usuario = (int)$_POST['codigo'];

/*
|--------------------------------------------------------------------------
| BUSCAR USUARIO
|--------------------------------------------------------------------------
*/

$sql = "SELECT 
            u.id,
            u.name AS nombre,
            u.puesto,
            u.fecha_ingreso,
            u.image,
            u.statusLaboral_id,
            c.ID AS cuadrilla,
            c.usuario_id,
            jefe.name AS encargado,
            g.nombre AS grupo,
            dp.nombre AS zonaTrabajo,
            d.zona AS departamento
        FROM users u
        LEFT JOIN cuadrilla c ON c.ID = u.cuadrilla_id
        LEFT JOIN users jefe ON jefe.id = c.usuario_id
        LEFT JOIN grupos g ON c.grupo_id = g.id
        LEFT JOIN departamento_plantilla dp ON dp.id = c.depPlantilla_id
        LEFT JOIN departamento d ON d.ID = dp.departamento_id
        WHERE u.id = '{$id_usuario}'
        LIMIT 1";

$result = $db->query($sql);

if($db->num_rows($result) == 0){

    echo '
    <div class="alert alert-danger">
        Usuario no encontrado
    </div>';

    exit;
}

$usuario = $db->fetch_assoc($result);