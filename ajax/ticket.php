<?php

require_once('../includes/load.php');
require_once('../includes/ticket_helpers.php');

page_require_level(5);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'ok' => false,
        'mensaje' => 'Método no permitido.'
    ]);

    exit;
}

$usuario = current_user();

if (!$usuario) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Sesión no válida.'
    ]);
    exit;
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {

    case 'responder':

        $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $comentario = trim($_POST['comentario'] ?? '');

        if ($ticketId <= 0 || $comentario == '') {

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Información incompleta.'
            ]);

            exit;
        }

        $resultado = responder_solicitud(
            $ticketId,
            $usuario['id'],
            $comentario
        );

        if(!$resultado['ok']){
            echo json_encode($resultado);
            exit;
        }

        $comentario = get_ticket_comentario($resultado['comentario_id']);

        $ticket = get_detalle_ticket($ticketId);
        $ticket = $ticket[0];

        $statusClass = '';

        switch ((int)$ticket['estatus_id']) {

            case 1: $statusClass = 'abierto'; break;
            case 2: $statusClass = 'proceso'; break;
            case 3: $statusClass = 'espera'; break;
            case 4: $statusClass = 'resuelto'; break;
            case 5: $statusClass = 'cerrado'; break;
            case 6: $statusClass = 'cancelado'; break;

        }

        $htmlComentario = render_ticket_comentario(
            $comentario,
            $ticket['usuario_id']
        );

        $htmlHistorico = '';

        foreach ($resultado['historicos'] as $idHistorico) {

            $historico = get_ticket_historico($idHistorico);
            $htmlHistorico .= render_ticket_historico($historico);

        }

        echo json_encode([
            'ok' => true,
            'mensaje' => $resultado['mensaje'],
            'html' => $htmlComentario,
            'historial' => $htmlHistorico,

            'comentario_respondido_id' => $resultado['comentario_respondido_id'],

            'estado' => [
                'nombre' => strtoupper($ticket['nombre_estatus']),
                'class'  => $statusClass
            ]
        ]);
        
        exit;
    case 'seguimiento':

        $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $comentario = trim($_POST['comentario'] ?? '');

        if ($ticketId <= 0 || $comentario == '') {

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Información incompleta.'
            ]);

            exit;
        }

        $ticket = get_detalle_ticket($ticketId);

        if (empty($ticket)) {

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ticket no encontrado.'
            ]);

            exit;
        }

        $ticket = $ticket[0];

        $resultado = agregar_seguimiento(
            $ticketId,
            $usuario['id'],
            $comentario,
            $ticket['estatus_id']
        );

        if(!$resultado['ok']){
            echo json_encode($resultado);
            exit;
        }

        $comentario = get_ticket_comentario($resultado['comentario_id']);

        $ticket = get_detalle_ticket($ticketId);
        $ticket = $ticket[0];

        $statusClass = '';

        switch ((int)$ticket['estatus_id']) {

            case 1: $statusClass = 'abierto'; break;
            case 2: $statusClass = 'proceso'; break;
            case 3: $statusClass = 'espera'; break;
            case 4: $statusClass = 'resuelto'; break;
            case 5: $statusClass = 'cerrado'; break;
            case 6: $statusClass = 'cancelado'; break;

        }

        $htmlComentario = render_ticket_comentario(
            $comentario,
            $ticket['usuario_id']
        );

        $htmlHistorico = '';

        foreach ($resultado['historicos'] as $idHistorico) {

            $historico = get_ticket_historico_item($idHistorico);
            $htmlHistorico .= render_ticket_historico($historico);

        }

        echo json_encode([
            'ok' => true,
            'mensaje' => $resultado['mensaje'],
            'html' => $htmlComentario,
            'historial' => $htmlHistorico,

            'estado' => [
                'texto' => strtoupper($ticket['nombre_estatus']),
                'clase' => $statusClass
            ]
        ]);

        exit;
    case 'solicitar':

        $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $comentario = trim($_POST['comentario'] ?? '');

        if ($ticketId <= 0 || $comentario == '') {

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Información incompleta.'
            ]);

            exit;
        }

        $ticket = get_detalle_ticket($ticketId);

        if (empty($ticket)) {

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ticket no encontrado.'
            ]);

            exit;
        }

        $ticket = $ticket[0];

        $resultado = solicitar_respuesta(
            $ticketId,
            $usuario['id'],
            $comentario
        );

        if(!$resultado['ok']){
            echo json_encode($resultado);
            exit;
        }

        $comentario = get_ticket_comentario($resultado['comentario_id']);

        $ticket = get_detalle_ticket($ticketId);
        $ticket = $ticket[0];

        $statusClass = '';

        switch ((int)$ticket['estatus_id']) {

            case 1: $statusClass = 'abierto'; break;
            case 2: $statusClass = 'proceso'; break;
            case 3: $statusClass = 'espera'; break;
            case 4: $statusClass = 'resuelto'; break;
            case 5: $statusClass = 'cerrado'; break;
            case 6: $statusClass = 'cancelado'; break;

        }

        $htmlComentario = render_ticket_comentario(
            $comentario,
            $ticket['usuario_id']
        );

        $htmlHistorico = '';

        foreach ($resultado['historicos'] as $idHistorico) {

            $historico = get_ticket_historico_item($idHistorico);
            $htmlHistorico .= render_ticket_historico($historico);

        }

        echo json_encode([
            'ok' => true,
            'mensaje' => $resultado['mensaje'],
            'html' => $htmlComentario,
            'historial' => $htmlHistorico,
            'estado' => [
                'texto' => strtoupper($ticket['nombre_estatus']),
                'clase' => $statusClass
            ]
        ]);

        exit;
    case 'resolver':

        $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $comentario = trim($_POST['comentario'] ?? '');

        if ($ticketId <= 0 || $comentario == '') {

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Información incompleta.'
            ]);

            exit;
        }

        $ticket = get_detalle_ticket($ticketId);

        if (empty($ticket)) {

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ticket no encontrado.'
            ]);

            exit;
        }

        $ticket = $ticket[0];

        $resultado = resolver_ticket(
            $ticketId,
            $usuario['id'],
            $comentario
        );

        if(!$resultado['ok']){
            echo json_encode($resultado);
            exit;
        }

        $comentario = get_ticket_comentario($resultado['comentario_id']);

        $ticket = get_detalle_ticket($ticketId);
        $ticket = $ticket[0];

        $statusClass = '';

        switch ((int)$ticket['estatus_id']) {

            case 1: $statusClass = 'abierto'; break;
            case 2: $statusClass = 'proceso'; break;
            case 3: $statusClass = 'espera'; break;
            case 4: $statusClass = 'resuelto'; break;
            case 5: $statusClass = 'cerrado'; break;
            case 6: $statusClass = 'cancelado'; break;

        }

        $htmlComentario = render_ticket_comentario(
            $comentario,
            $ticket['usuario_id']
        );

        $htmlHistorico = '';

        foreach ($resultado['historicos'] as $idHistorico) {

            $historico = get_ticket_historico_item($idHistorico);
            $htmlHistorico .= render_ticket_historico($historico);

        }

        echo json_encode([
            'ok' => true,
            'mensaje' => $resultado['mensaje'],
            'html' => $htmlComentario,
            'historial' => $htmlHistorico,
            'estado' => [
                'texto' => strtoupper($ticket['nombre_estatus']),
                'clase' => $statusClass
            ]
        ]);

        exit;
    default:

        echo json_encode([
            'ok' => false,
            'mensaje' => 'Acción no válida.'
        ]);
        exit;
}