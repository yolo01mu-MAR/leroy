<?php
require_once('../includes/load.php');

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id <= 0){
    echo json_encode(['success' => false]);
    exit;
}

$sql = "SELECT ID, descripcion, factor, piezasXminuto, piezasXhora, paqXminuto, paqXhora
        FROM tabla_gasas
        WHERE ID = {$id}
        LIMIT 1";

$resultado = find_by_sql($sql);

if(empty($resultado)){
    echo json_encode(['success' => false]);
    exit;
}

$producto = $resultado[0];

echo json_encode([
    'success' => true,
    'id' => (int)$producto['ID'],
    'descripcion' => remove_junk($producto['descripcion']),
    'factor' => (float)$producto['factor'],
    'piezasXhora' => (float)$producto['piezasXhora'],
    'piezasXminuto' => (float)$producto['piezasXminuto']
]);
