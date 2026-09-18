<?php

require_once __DIR__ . '/../../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {

    /*
    |--------------------------------------------------------------------------
    | VALIDAR MÉTODO
    |--------------------------------------------------------------------------
    */

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS
    |--------------------------------------------------------------------------
    */

    $usuario_id = isset($_POST['usuario_id'])
        ? (int)$_POST['usuario_id']
        : 0;

    $password = trim($_POST['password'] ?? '');

    if ($usuario_id <= 0) {
        throw new Exception('Usuario no válido.');
    }

    if ($password === '') {
        throw new Exception('Ingrese una nueva contraseña.');
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAR LONGITUD
    |--------------------------------------------------------------------------
    */

    if (strlen($password) < 6) {
        throw new Exception(
            'La contraseña debe tener al menos 6 caracteres.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BUSCAR USUARIO
    |--------------------------------------------------------------------------
    */

    $usuario = find_by_id('users', $usuario_id);

    if (!$usuario) {
        throw new Exception('Usuario no encontrado.');
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAR NUEVA CONTRASEÑA
    |--------------------------------------------------------------------------
    */

    $nuevoHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    if (!$nuevoHash) {
        throw new Exception(
            'No fue posible generar la contraseña.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR CONTRASEÑA
    |--------------------------------------------------------------------------
    */

    global $db;

    $nuevoHashEscapado = $db->escape($nuevoHash);

    $sql = "
        UPDATE users
        SET password = '{$nuevoHashEscapado}'
        WHERE id = {$usuario_id}
        LIMIT 1
    ";

    if (!$db->query($sql)) {
        throw new Exception(
            'No fue posible actualizar la contraseña.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR ACTUALIZACIÓN
    |--------------------------------------------------------------------------
    */

    $usuarioActualizado = find_by_id('users', $usuario_id);

    if (
        !$usuarioActualizado ||
        empty($usuarioActualizado['password'])
    ) {
        throw new Exception(
            'No fue posible verificar el cambio de contraseña.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREAR SESIÓN KIOSCO
    |--------------------------------------------------------------------------
    */

    $_SESSION['kiosco']['autenticado'] = true;
    $_SESSION['kiosco']['empleado_id'] = $usuario_id;
    $_SESSION['kiosco']['ultimo_movimiento'] = time();


    /*
    |--------------------------------------------------------------------------
    | RESPUESTA
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        'ok' => true,
        'usuario_id' => $usuario_id,
        'mensaje' => 'Contraseña cambiada correctamente.'
    ]);

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);
}