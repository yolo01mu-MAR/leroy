<?php

require_once __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');


// =====================================================
// EVITAR CACHE
// =====================================================

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');


try {

    // =====================================================
    // VALIDAR MÉTODO
    // =====================================================

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        throw new Exception(
            'Método no permitido.'
        );
    }


    // =====================================================
    // DATOS RECIBIDOS
    // =====================================================

    $nomina   = trim($_POST['nomina'] ?? '');
    $password = trim($_POST['password'] ?? '');


    // =====================================================
    // VALIDAR NÓMINA
    // =====================================================

    if ($nomina === '') {

        throw new Exception(
            'Ingrese su número de nómina.'
        );
    }


    if (!ctype_digit($nomina)) {

        throw new Exception(
            'El número de nómina no es válido.'
        );
    }


    global $db;


    // =====================================================
    // BUSCAR USUARIO
    // =====================================================

    $nominaEscapada = $db->escape($nomina);

    $sql = "
        SELECT
            id,
            username,
            password,
            last_login
        FROM users
        WHERE username = '{$nominaEscapada}'
        LIMIT 1
    ";

    $resultado = $db->query($sql);


    if (
        !$resultado ||
        !$db->num_rows($resultado)
    ) {

        throw new Exception(
            'Número de nómina o contraseña incorrectos.'
        );
    }


    $usuario = $db->fetch_assoc($resultado);

    $usuario_id = (int)$usuario['id'];

    $lastLogin = $usuario['last_login'] ?? null;


    // =====================================================
    // SIN CONTRASEÑA
    // =====================================================
    //
    // Permite entrar solamente en modo CONSULTA.
    //
    // Si nunca ha iniciado sesión:
    // requiere establecer contraseña.
    //

    if ($password === '') {


        // -------------------------------------------------
        // PRIMER ACCESO
        // -------------------------------------------------

        if (empty($lastLogin)) {

            echo json_encode([
                'ok' => true,
                'requiere_cambio_password' => true,
                'usuario_id' => $usuario_id,
                'modo' => 'CAMBIO_PASSWORD',
                'mensaje' =>
                    'Es tu primer acceso. Debes establecer una contraseña antes de continuar.'
            ]);

            exit;
        }


        // -------------------------------------------------
        // CONSULTA
        // -------------------------------------------------

        echo json_encode([
            'ok' => true,
            'requiere_cambio_password' => false,
            'usuario_id' => $usuario_id,
            'modo' => 'CONSULTA',
            'mensaje' =>
                'Acceso de consulta autorizado.'
        ]);

        exit;
    }


    // =====================================================
    // CON CONTRASEÑA
    // =====================================================

    $usuarioAutenticado = authenticate(
        $nomina,
        $password
    );


    if (!$usuarioAutenticado) {

        throw new Exception(
            'Número de nómina o contraseña son incorrectos.'
        );
    }


    // =====================================================
    // VALIDAR QUE SEA EL MISMO USUARIO
    // =====================================================

    if (
        (int)$usuarioAutenticado['id'] !==
        $usuario_id
    ) {

        throw new Exception(
            'No fue posible validar al colaborador.'
        );
    }


    // =====================================================
    // PRIMER ACCESO
    // =====================================================

    if (empty($lastLogin)) {

        echo json_encode([
            'ok' => true,
            'requiere_cambio_password' => true,
            'usuario_id' => $usuario_id,
            'modo' => 'CAMBIO_PASSWORD',
            'mensaje' =>
                'Es tu primer acceso. Debes establecer una nueva contraseña.'
        ]);

        exit;
    }


    // =====================================================
    // AUTENTICAR KIOSCO
    // =====================================================

    $_SESSION['kiosco'] = [
        'empleado_id'          => $usuario_id,
        'autenticado'          => true,
        'solicitud_autorizada' => false,
        'ultimo_movimiento'    => time()
    ];


    // =====================================================
    // RESPUESTA
    // =====================================================

    echo json_encode([
        'ok' => true,
        'requiere_cambio_password' => false,
        'usuario_id' => $usuario_id,
        'modo' => 'AUTENTICADO',
        'mensaje' =>
            'Identidad validada correctamente.'
    ]);

    exit;


} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);

    exit;
}