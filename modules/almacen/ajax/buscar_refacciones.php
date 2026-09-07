<?php

require_once __DIR__ . '/../../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$resultados = [];

$termino = isset($_GET['q'])
    ? trim($_GET['q'])
    : '';

if ($termino === '' || strlen($termino) < 2) {
    echo json_encode($resultados);
    exit;
}

$termino = $db->escape($termino);

$sql = "SELECT
            p.id,
            d.des_gral,
            d.marca
        FROM almacen_producto p
        INNER JOIN almacen_producto_desc d
            ON d.id_producto = p.id
        WHERE
            CAST(p.id AS CHAR) LIKE '%{$termino}%'
            OR d.des_gral LIKE '%{$termino}%'
        ORDER BY p.id
        LIMIT 20";

$result = $db->query($sql);

if ($result) {

    while ($row = $db->fetch_assoc($result)) {

        $detalle = $row['des_gral'];

        if (!empty($row['marca'])) {
            $detalle .= ' · ' . $row['marca'];
        }

        $resultados[] = [
            'id'      => (int)$row['id'],
            'texto'   => (string)$row['id'],
            'detalle' => $detalle
        ];
    }
}

echo json_encode(
    $resultados,
    JSON_UNESCAPED_UNICODE
);