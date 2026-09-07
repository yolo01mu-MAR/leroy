<?php

require_once __DIR__ . '/../app/bootstrap.php';

// =====================================================
// CERRAR COMPLETAMENTE LA SESIÓN
// =====================================================

$_SESSION = [];

// Eliminar cookie de sesión
if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destruir sesión
session_destroy();

// Respuesta AJAX
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'ok' => true
]);

exit;