<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/includes/correo_helper.php';


$html = generar_correo_vacaciones([

    'titulo' =>
        'Nueva solicitud de vacaciones',

    'nombre_colaborador' =>
        'JUAN PÉREZ',

    'mensaje' =>
        'La solicitud de vacaciones requiere tu atención.',

    'fecha_inicio' =>
        '25/08/2026',

    'fecha_fin' =>
        '27/08/2026',

    'dias' =>
        3,

    'estado' =>
        'Pendiente de revisión',

    'estado_color' =>
        'azul',

    'url' =>
        BASE_URL .
        '/modules/solicitudes/solicitudes_vacaciones.php?id=123&estatus=PENDIENTE_RH',

    'texto_boton' =>
        'Ver solicitud'

]);


/*
|--------------------------------------------------------------------------
| MOSTRAR PREVISUALIZACIÓN
|--------------------------------------------------------------------------
*/

echo $html;