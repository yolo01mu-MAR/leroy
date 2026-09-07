<?php

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../includes/vacaciones_helpers.php';
require_once __DIR__ . '/../includes/validar_kiosco.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $id = $_SESSION['kiosco']['empleado_id'] ?? null;

    if (!$id) {
        throw new Exception('Sesión inválida.');
    }


    //====================================================
    // MES SOLICITADO
    //====================================================

    $anio =
        isset($_GET['anio'])
            ? (int)$_GET['anio']
            : (int)date('Y');

    $mes =
        isset($_GET['mes'])
            ? (int)$_GET['mes']
            : (int)date('n');


    if ($mes < 1 || $mes > 12) {
        throw new Exception('Mes inválido.');
    }


    //====================================================
    // EMPLEADO
    //====================================================

    $empleado =
        get_datos_kiosco($id);

    $empleado =
        $empleado[0];


    //====================================================
    // NAVEGACIÓN
    //====================================================

    // MES ANTERIOR

    $mesAnterior =
        $mes - 1;

    $anioAnterior =
        $anio;

    if ($mesAnterior < 1) {

        $mesAnterior = 12;

        $anioAnterior--;

    }


    // MES SIGUIENTE

    $mesSiguiente =
        $mes + 1;

    $anioSiguiente =
        $anio;

    if ($mesSiguiente > 12) {

        $mesSiguiente = 1;

        $anioSiguiente++;

    }


    //====================================================
    // LÍMITE PARA MES ANTERIOR
    //====================================================

    $fechaMinima =
        strtotime(
            '+' . DIAS_ANTICIPACION . ' days'
        );

    $anioMinimo =
        (int)date(
            'Y',
            $fechaMinima
        );

    $mesMinimo =
        (int)date(
            'n',
            $fechaMinima
        );


    $permitirAnterior = true;


    if (
        $anioAnterior < $anioMinimo ||
        (
            $anioAnterior == $anioMinimo &&
            $mesAnterior < $mesMinimo
        )
    ) {

        $permitirAnterior = false;

    }


    //====================================================
    // DISPONIBILIDAD DEL MES ACTUAL
    //====================================================

    $primerDia =
        date(
            'Y-m-01',
            mktime(
                0,
                0,
                0,
                $mes,
                1,
                $anio
            )
        );


    $ultimoDia =
        date(
            'Y-m-t',
            mktime(
                0,
                0,
                0,
                $mes,
                1,
                $anio
            )
        );


    $ocupacion =
        get_disponibilidad_vacaciones(
            $empleado['lugar_id'],
            $primerDia,
            $ultimoDia
        );


    $disponibilidad =
        construir_disponibilidad_calendario(
            $ocupacion,
            $anio,
            $mes,
            CUPO_MAXIMO_VACACIONES
        );


    //====================================================
    // NOMBRE DEL MES
    //====================================================

    $meses = [

        1  => 'enero',
        2  => 'febrero',
        3  => 'marzo',
        4  => 'abril',
        5  => 'mayo',
        6  => 'junio',
        7  => 'julio',
        8  => 'agosto',
        9  => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'

    ];


    $nombre_mes =
        ucfirst(
            $meses[$mes]
        )
        . ' '
        . $anio
        . ' - '
        . $empleado['lugar'];


    //====================================================
    // FECHAS DEL CALENDARIO
    //====================================================

    $primerDiaMes =
        new DateTime(
            sprintf(
                '%04d-%02d-01',
                $anio,
                $mes
            )
        );


    $ultimoDiaMes =
        new DateTime(
            $primerDiaMes->format('Y-m-t')
        );


    //====================================================
    // SEMANA
    //====================================================

    // ISO:
    // 1 = lunes
    // 7 = domingo

    $diaSemanaInicio =
        (int)$primerDiaMes->format('N');


    $diaSemanaFin =
        (int)$ultimoDiaMes->format('N');


    //====================================================
    // PRIMER DÍA VISIBLE
    //====================================================

    $fechaInicioCalendario =
        clone $primerDiaMes;


    $fechaInicioCalendario->modify(
        '-' . ($diaSemanaInicio - 1) . ' days'
    );


    //====================================================
    // ÚLTIMO DÍA VISIBLE
    //====================================================

    $fechaFinCalendario =
        clone $ultimoDiaMes;


    $fechaFinCalendario->modify(
        '+' . (7 - $diaSemanaFin) . ' days'
    );


    //====================================================
    // GENERAR SOLAMENTE LAS CELDAS
    //====================================================

    ob_start();


    $fechaActual =
        clone $fechaInicioCalendario;


    while (
        $fechaActual <= $fechaFinCalendario
    ):


        $fecha =
            $fechaActual->format('Y-m-d');


        $esMesActual =
            $fechaActual->format('Y') == $anio
            &&
            $fechaActual->format('m') == sprintf(
                '%02d',
                $mes
            );


        //================================================
        // DÍA DE OTRO MES
        //================================================

        if (!$esMesActual):

            ?>

            <div
                class="celda-dia otro-mes"
                data-fecha="<?= $fecha ?>"
                data-estado="otro_mes"
                data-seleccionable="false"
            >

                <div class="dia-numero">

                    <?= $fechaActual->format('j') ?>

                </div>

            </div>

            <?php


        //================================================
        // DÍA DEL MES ACTUAL
        //================================================

        else:

            $info =
                $disponibilidad[$fecha]
                ?? [
                    'estado' =>
                        'no_laborable',

                    'ocupados' =>
                        0,

                    'libres' =>
                        CUPO_MAXIMO_VACACIONES
                ];


            $estado =
                $info['estado'];


            $ocupados =
                $info['ocupados'];


            $seleccionable =
                in_array(
                    $estado,
                    [
                        'disponible',
                        'uno'
                    ]
                );


            ?>

            <div
                class="celda-dia <?= $estado ?>"
                data-fecha="<?= $fecha ?>"
                data-estado="<?= $estado ?>"
                data-seleccionable="<?= $seleccionable ?>"
            >

                <div class="dia-numero">
                    <?= $fechaActual->format('j') ?>
                </div>

            </div>

            <?php

        endif;


        $fechaActual->modify('+1 day');


    endwhile;


    $html_grid =
        ob_get_clean();


    //====================================================
    // RESPUESTA
    //====================================================

    echo json_encode([

        'ok' =>
            true,

        'nombre_mes' =>
            $nombre_mes,

        'html_grid' =>
            $html_grid,

        'mes' =>
            $mes,

        'anio' =>
            $anio,

        'mesAnterior' =>
            $mesAnterior,

        'anioAnterior' =>
            $anioAnterior,

        'permitirAnterior' =>
            $permitirAnterior,

        'mesSiguiente' =>
            $mesSiguiente,

        'anioSiguiente' =>
            $anioSiguiente

    ]);

} catch (Exception $e) {

    echo json_encode([

        'ok' =>
            false,

        'mensaje' =>
            $e->getMessage()

    ]);

}