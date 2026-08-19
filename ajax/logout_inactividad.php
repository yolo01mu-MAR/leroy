<?php
    require_once('../includes/load.php');

    $session->logout();

    echo json_encode([
    'status' => 'ok'
    ]);
?>