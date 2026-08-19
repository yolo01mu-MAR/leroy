<?php

require_once __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json');

try {

    // Verificar sesión del kiosco
    if (
        empty($_SESSION['kiosco']) ||
        empty($_SESSION['kiosco']['empleado_id'])
    ) {
        throw new Exception('Sesión de kiosco no válida.');
    }

    $usuario_id = (int)$_SESSION['kiosco']['empleado_id'];
    $password = trim($_POST['password'] ?? '');

    if ($password === '') {
        throw new Exception('Ingresa tu contraseña.');
    }

    // Validar contraseña
    if (!validar_password_kiosco($usuario_id, $password)) {
        throw new Exception('Contraseña incorrecta.');
    }

    // Usuario ya autenticado
    $_SESSION['kiosco']['autenticado'] = true;

    // Actualizar actividad
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