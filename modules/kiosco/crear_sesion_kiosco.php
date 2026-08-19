<?php

require_once __DIR__ . '/../../app/bootstrap.php';

// COMPROBAR SI YA VIENE AUTENTICADO
$autenticado = !empty(
    $_SESSION['kiosco']['autenticado']
);


// OBTENER ID DEL EMPLEADO
if ($autenticado) {

    $id = (int)($_SESSION['kiosco']['empleado_id'] ?? 0);

} else {

    $id = (int)($_GET['id'] ?? 0);

}

// VALIDAR ID
if ($id <= 0) {

    $session->msg(
        'd',
        'No fue posible identificar al colaborador.'
    );

    redirect('../index.php');
}


// BUSCAR EMPLEADO
$empleado = get_datos_kiosco($id);

if (empty($empleado)) {

    $session->msg(
        'd',
        'Empleado no encontrado.'
    );

    redirect('../index.php');
}

$empleado = $empleado[0];


// RECUPERAR TRÁMITE
$tramite = $_SESSION['kiosco']['tramite'] ?? 'vacaciones';


// RECUPERAR MODO
$modo = strtoupper(
    $_GET['modo'] ?? 'CONSULTA'
);
if (!in_array($modo, ['CONSULTA', 'CREDENCIAL'])) {
    $modo = 'CONSULTA';
}

// CREAR SESIÓN DEL KIOSCO
$_SESSION['kiosco'] = [
    'empleado_id'          => $id,
    'departamento_id'      => $empleado['lugar_id'],
    'tramite'              => $tramite,
    'modo'                 => $modo,
    'autenticado'          => $autenticado,
    'solicitud_autorizada' => false,
    'ultimo_movimiento'    => time()

];


// RUTAS DE LOS TRÁMITES
$rutas = [
    'vacaciones'  => 'user_vacaciones.php',
    'tiempo'      => 'user_tiempo.php',
    'solicitudes' => 'user_solicitudes.php',
];

// Redireccion
if (isset($rutas[$tramite])) {
    redirect($rutas[$tramite]);
} else {
    redirect('../index.php');
}