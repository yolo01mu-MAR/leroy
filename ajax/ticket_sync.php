<?php

require_once('../includes/load.php');
require_once('../includes/ticket_helpers.php');

header('Content-Type: application/json');

$ticketId = isset($_POST['ticket']) ? (int)$_POST['ticket'] : 0;
$ultimoComentario = isset($_POST['ultimoComentario']) ? (int)$_POST['ultimoComentario'] : 0;

$comentarios = get_ticket_comentarios_nuevos(
    $ticketId,
    $ultimoComentario
);

echo json_encode([
    'ok' => true,
    'comentarios' => $comentarios
]);