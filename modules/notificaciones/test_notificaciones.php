<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/includes/notificaciones_helper.php';
require_once __DIR__ . '/../correo/includes/correo_helper.php';


$destinatarios =
    notificar_por_responsabilidad(
        'NOMINA',
        999,
        'PRUEBA',
        'Prueba de notificación',
        'Esta es una prueba del sistema de notificaciones.'
    );


echo '<pre>';

print_r($destinatarios);

echo '</pre>';

$resultadosCorreo =
    enviar_correo_a_usuarios(
        $destinatarios,
        'Prueba de correo - LE ROY',
        '
            <h2>Prueba de notificación</h2>

            <p>
                Esta es una prueba del sistema
                de notificaciones de Asistencia LE ROY.
            </p>

            <p>
                Si recibiste este correo,
                la distribución por responsabilidad
                está funcionando correctamente.
            </p>
        '
    );


echo '<hr>';

echo '<h3>Resultado de correos</h3>';

print_r($resultadosCorreo);