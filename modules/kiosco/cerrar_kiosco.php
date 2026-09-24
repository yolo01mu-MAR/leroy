<?php

require_once __DIR__ . '/../../app/bootstrap.php';


// =====================================================
// NO CACHE
// =====================================================

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');


// =====================================================
// LIMPIAR SESIÓN
// =====================================================

$_SESSION = [];


// =====================================================
// ELIMINAR COOKIE DE SESIÓN
// =====================================================

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


// =====================================================
// DESTRUIR SESIÓN
// =====================================================

session_destroy();


// =====================================================
// IR AL INICIO DEL KIOSCO
// =====================================================

header(
    'Location: ' .
    BASE_URL .
    '/modules/kiosco/identificar_colaborador.php'
);

exit;