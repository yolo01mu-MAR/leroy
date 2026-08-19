<?php

require_once('../includes/load.php');

header('Content-Type: application/json');

$codigo = isset($_POST['codigo']) ? (int)$_POST['codigo'] : 0;

if($codigo <= 0){
    echo json_encode([
        'success' => false,
        'mensaje' => 'Código inválido.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| BUSCAR EMPLEADO
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
        WHERE u.id = '{$codigo}'
        LIMIT 1";

$result = $db->query($sql);

if(!$result || $db->num_rows($result) == 0){

    echo json_encode([
        'success' => false,
        'mensaje' => 'Empleado no encontrado.'
    ]);

    exit;
}

$usuario = $db->fetch_assoc($result);

echo json_encode([
    'success' => true,
    'usuario' => $usuario
]);

exit;