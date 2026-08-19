<?php
require_once __DIR__ . '/../../app/bootstrap.php';

if(!isset($_POST['id'])){
  die("Datos incompletos");
}

$id = (int)$_POST['idEntrega'];
$idEquipo = (int)$_POST['equipo_id'];
$condiciones = $db->escape($_POST['condiciones']);
$observaciones = $db->escape($_POST['observaciones']);
$usuario = (int)$_SESSION['user_id'];
$fecha = date('Y-m-d H:i:s');

// =============================
// 1. ACTUALIZAR ENTREGA
// =============================
$sql1 = "UPDATE equipo_entrega 
         SET estado = 'FINALIZADO',
             fecha_devolucion = '{$fecha}',
             condiciones_entrega = '{$condiciones}',
             observaciones = '{$observaciones}'
         WHERE id = {$id}";

// =============================
// 2. ACTUALIZAR EQUIPO
// =============================
$sql2 = "UPDATE equipo 
         SET observaciones = '{$observaciones}',
             asignado = 0
         WHERE id = {$idEquipo}";

// Ejecutar ambas
if($db->query($sql1) && $db->query($sql2)){

  $_SESSION['msg']['s'] = "Entrega registrada correctamente";

} else {
  $_SESSION['msg']['d'] = "Error al procesar la entrega";
}

header('Location: equipos_asignados.php');
exit;