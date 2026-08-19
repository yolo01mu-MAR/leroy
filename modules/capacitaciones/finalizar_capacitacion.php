<?php

require_once __DIR__ . '/../../app/bootstrap.php';

page_require_level(5);

$id = (int)$_GET['id'];

$horaFin = date('H:i:s');

$sql = "UPDATE historico_capacitaciones SET hora_fin= '{$horaFin}', estatus='FINALIZADA' WHERE  id= '{$id}'";

if($db->query($sql)){

    $session->msg('s','Capacitación finalizada correctamente.');

}else{

    $session->msg('d','No fue posible finalizar la capacitación.');

}

redirect('capa_historico.php', false);