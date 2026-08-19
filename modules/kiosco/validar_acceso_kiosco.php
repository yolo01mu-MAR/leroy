<?php

require_once __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    $nomina   = trim($_POST['nomina'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($nomina === '') {
        throw new Exception('Ingrese su número de nómina.');
    }

    if ($password === '') {
        throw new Exception('Ingrese su contraseña.');
    }

    // Validar que la nómina sea numérica
    if (!ctype_digit($nomina)) {
        throw new Exception('El número de nómina no es válido.');
    }

    $usuario = authenticate($nomina, $password);

    if (!$usuario) {
        throw new Exception(
            'Número de nómina o contraseña incorrectos.'
        );
    }

    if ((int)$usuario['id'] !== (int)$nomina) {
        throw new Exception(
            'No fue posible validar al colaborador.'
        );
    }

    $_SESSION['kiosco']['autenticado'] = true;
    $_SESSION['kiosco']['empleado_id'] = (int)$usuario['id'];
    $_SESSION['kiosco']['ultimo_movimiento'] = time();

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Identidad validada correctamente.'
    ]);

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);
}