<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/includes/notificaciones_helper.php';

$usuario_id = 14566;

// Crear notificación de prueba
$id = crear_notificacion(
    $usuario_id,
    null,
    'PRUEBA',
    'Notificación de prueba',
    'Esta es una notificación de prueba del sistema LE ROY.',
    null
);

echo "<h3>Crear notificación</h3>";

if ($id) {
    echo "✅ Notificación creada. ID: " . $id;
} else {
    echo "❌ No se pudo crear la notificación.";
}


// Contar no leídas
$total = contar_notificaciones_no_leidas($usuario_id);

echo "<hr>";
echo "<h3>Notificaciones no leídas</h3>";
echo "Total: " . $total;


// Obtener notificaciones
$notificaciones = obtener_notificaciones_usuario($usuario_id);

echo "<hr>";
echo "<h3>Notificaciones</h3>";

foreach ($notificaciones as $notificacion) {

    echo "<div style='
        border:1px solid #ddd;
        padding:10px;
        margin-bottom:10px;
    '>";

    echo "<strong>" . htmlspecialchars($notificacion['titulo']) . "</strong><br>";

    echo htmlspecialchars($notificacion['mensaje']) . "<br>";

    echo "<small>";
    echo "ID: " . $notificacion['id'];
    echo " | Leída: " . $notificacion['leida'];
    echo "</small>";

    echo "</div>";
}

// echo "<hr>";
// echo "<h3>Marcar notificación como leída</h3>";

// $marcada = marcar_notificacion_leida($id, $usuario_id);

// if ($marcada) {
//     echo "✅ Notificación marcada como leída.";
// } else {
//     echo "❌ No se pudo marcar como leída.";
// }


// // ========================================
// // VOLVER A CONTAR NO LEÍDAS
// // ========================================

// $total = contar_notificaciones_no_leidas($usuario_id);

// echo "<br>";
// echo "Notificaciones no leídas ahora: " . $total;