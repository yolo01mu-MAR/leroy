<?php

require_once('../includes/load.php');

$codigo = (int)$_POST['codigo'];
$capacitacion_id = (int)$_POST['capacitacion_id'];

/*
|--------------------------------------------------------------------------
| BUSCAR USUARIO
|--------------------------------------------------------------------------
*/

$sql = "SELECT id,name
        FROM users
        WHERE id = '{$codigo}'
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

/*
|--------------------------------------------------------------------------
| VALIDAR DUPLICADO
|--------------------------------------------------------------------------
*/

$sql = "SELECT id_usuario
        FROM registro_capacitaciones
        WHERE id_capacitacion = '{$capacitacion_id}'
        AND id_usuario = '{$usuario['id']}'
        LIMIT 1";

$existe = $db->query($sql);

if($db->num_rows($existe) > 0){

    echo '
    <div class="alert alert-warning">
        <strong>'.$usuario['name'].'</strong><br>
        Ya estaba registrado
    </div>';

    exit;
}

/*
|--------------------------------------------------------------------------
| REGISTRAR
|--------------------------------------------------------------------------
*/

$sql = "INSERT INTO registro_capacitaciones(id_capacitacion,id_usuario)
        VALUES ('{$capacitacion_id}','{$usuario['id']}')";

if($db->query($sql)){

    echo '
    <div class="alert alert-success">
        <strong>✓ '.$usuario['name'].'</strong><br>
        Registrado correctamente
    </div>';

}else{

    echo '
    <div class="alert alert-danger">
        Error al registrar asistencia
    </div>';
}