<?php

require_once __DIR__ . '/../../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$nomina = isset($_GET['nomina'])
    ? trim($_GET['nomina'])
    : '';

if ($nomina === '') {
    echo json_encode([
        'existe' => false
    ]);
    exit;
}

$nomina = $db->escape($nomina);

$sql = "SELECT 
            id,
            name
        FROM users
        WHERE id = '{$nomina}'
        LIMIT 1";

$resultados = find_by_sql($sql);

if (!empty($resultados)) {

    $usuario = $resultados[0];

    echo json_encode([
        'existe' => true,
        'id'     => $usuario['id'],
        'nombre' => $usuario['name']
    ]);

    exit;
}

echo json_encode([
    'existe' => false
]);