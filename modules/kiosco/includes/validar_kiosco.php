<?php

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (empty($_SESSION['kiosco']['empleado_id'])) {
    redirect(BASE_URL . '/index.php');
    exit;
}

$tiempoMaximo = 300;

if (
    (time() - $_SESSION['kiosco']['ultimo_movimiento']) > $tiempoMaximo
) {

    unset($_SESSION['kiosco']);

    redirect(BASE_URL . '/index.php');
    exit;
}

$_SESSION['kiosco']['ultimo_movimiento'] = time();

function kiosco_requiere_credencial()
{
    if (
        empty($_SESSION['kiosco']) ||
        $_SESSION['kiosco']['modo'] !== 'CREDENCIAL'
    ) {

        redirect(BASE_URL . '/index.php');
        exit;

    }
}