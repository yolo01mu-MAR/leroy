<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../modules/tickets/includes/ticket_helpers.php';

header('Content-Type: application/json; charset=utf-8');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    $nomina = trim($_POST['nomina'] ?? '');

    if ($nomina === '') {
        throw new Exception('Ingrese su número de nómina.');
    }

    if (!ctype_digit($nomina)) {
        throw new Exception('El número de nómina no es válido.');
    }

    global $db;

    $nominaEscapada = $db->escape($nomina);


    /*
    |--------------------------------------------------------------------------
    | BUSCAR COLABORADOR
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            username,
            name
        FROM users
        WHERE username = '{$nominaEscapada}'
        LIMIT 1
    ";

    $resultado = $db->query($sql);

    if (!$resultado || !$db->num_rows($resultado)) {

        throw new Exception(
            'No encontramos un colaborador con ese número de nómina.'
        );
    }

    $usuario = $db->fetch_assoc($resultado);

    $usuario_id = (int)$usuario['id'];


    /*
    |--------------------------------------------------------------------------
    | DATOS DEL TICKET
    |--------------------------------------------------------------------------
    */

    $categoria_id = 1; // SOPORTE

    $asunto = 'Restablecimiento de contraseña';

    $descripcion = "El colaborador solicitó el restablecimiento de su contraseña desde el Portal Le Roy.
    Número de nómina: {$nomina}
    Colaborador: {$usuario['name']}";

    $asunto = $db->escape($asunto);
    $descripcion = $db->escape($descripcion);


    /*
    |--------------------------------------------------------------------------
    | CREAR TICKET
    |--------------------------------------------------------------------------
    */

    $resultadoTicket = crear_ticket(
        $usuario_id,
        $categoria_id,
        null,
        null,
        $asunto,
        $descripcion
    );


    if (!$resultadoTicket['ok']) {

        throw new Exception(
            $resultadoTicket['mensaje'] ??
            'No fue posible crear la solicitud.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESPUESTA
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        'ok' => true,
        'mensaje' =>
            'Tu solicitud fue enviada correctamente a Sistemas.',
        'ticket_id' => $resultadoTicket['id'],
        'folio' => $resultadoTicket['folio']
    ]);

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);
}