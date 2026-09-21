<?php

require_once __DIR__ . '/../../../app/bootstrap.php';


/*
|--------------------------------------------------------------------------
| DATOS RECIBIDOS
|--------------------------------------------------------------------------
*/

$q = isset($_GET['q'])
    ? trim($_GET['q'])
    : '';

$semana = isset($_GET['semana'])
    ? (int)$_GET['semana']
    : 0;

$anio = isset($_GET['anio'])
    ? (int)$_GET['anio']
    : 0;


/*
|--------------------------------------------------------------------------
| VALIDACIONES
|--------------------------------------------------------------------------
*/

if ($semana < 1 || $semana > 53) {
    $semana = (int)date('W');
}

if ($anio < 2020 || $anio > 2100) {
    $anio = (int)date('o');
}


/*
|--------------------------------------------------------------------------
| ESCAPAR BÚSQUEDA
|--------------------------------------------------------------------------
*/

$q = $db->escape($q);


/*
|--------------------------------------------------------------------------
| ESTATUS
|--------------------------------------------------------------------------
*/

$estatusMap = [

    'APROBADA' => [
        'texto' => 'APROBADA',
        'class' => 'label-warning'
    ],

    'FINALIZADA' => [
        'texto' => 'FINALIZADA',
        'class' => 'label-success'
    ],

    'CANCELADA' => [
        'texto' => 'CANCELADA',
        'class' => 'label-danger'
    ],

    'DESCONOCIDO' => [
        'texto' => 'DESCONOCIDO',
        'class' => 'label-default'
    ]

];


/*
|--------------------------------------------------------------------------
| WHERE
|--------------------------------------------------------------------------
*/

$where = [];

$where[] = "v.semana = {$semana}";

$where[] = "v.anio = {$anio}";

$where[] = "
    v.estatus IN (
        'APROBADA',
        'FINALIZADA',
        'CANCELADA'
    )
";


/*
|--------------------------------------------------------------------------
| BUSQUEDA POR NOMBRE / NOMINA
|--------------------------------------------------------------------------
*/

if ($q !== '') {

    $where[] = "
        (
            v.usuario_id LIKE '%{$q}%'
            OR u.name LIKE '%{$q}%'
        )
    ";

}


/*
|--------------------------------------------------------------------------
| CONSULTA
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        v.usuario_id AS nomina,
        u.name AS nombre,
        v.fecha_solicitud,
        v.anio,
        v.semana,
        v.fecha_inicio AS inicio,
        v.fecha_fin AS fin,
        v.dias,
        v.estatus

    FROM vw_vacaciones v

    INNER JOIN users u
        ON u.id = v.usuario_id

    WHERE " . implode(" AND ", $where) . "

    ORDER BY
        v.usuario_id ASC

    LIMIT 50
";


$resultados = find_by_sql($sql);


/*
|--------------------------------------------------------------------------
| SIN RESULTADOS
|--------------------------------------------------------------------------
*/

if (empty($resultados)) {

    echo '
        <tr>
            <td
                colspan="7"
                class="text-center text-muted"
                style="padding:30px;">

                <span
                    class="glyphicon glyphicon-calendar"
                    style="font-size:28px; margin-bottom:8px;">
                </span>

                <br>

                <strong>
                    No se encontraron vacaciones.
                </strong>

                <br>

                <small>
                    Semana ' . $semana . ' del año ' . $anio . '
                </small>

            </td>
        </tr>
    ';

    exit;
}

?>


<?php foreach ($resultados as $emp): ?>

    <?php
        $estatus = strtoupper(
            trim(remove_junk($emp['estatus']))
        );
        $e = $estatusMap[$estatus] ?? $estatusMap['DESCONOCIDO'];
    ?>
    <tr>

        <!-- NOMINA -->
        <td class="text-center"><?php echo remove_junk($emp['nomina']); ?></td>
        <!-- NOMBRE -->
        <td><?php echo remove_junk($emp['nombre']); ?></td>
        <!-- FECHA SOLICITUD -->
        <td class="text-center"><?php echo !empty($emp['fecha_solicitud']) ? date('d/m/Y', strtotime($emp['fecha_solicitud'])) : '-'; ?></td>
        <!-- INICIO -->
        <td class="text-center"><?php echo !empty($emp['inicio']) ? date('d/m/Y', strtotime($emp['inicio'])) : '-';?></td>
        <!-- FIN -->
        <td class="text-center"><?php echo !empty($emp['fin']) ? date('d/m/Y', strtotime($emp['fin'])) : '-';?></td>
        <!-- DIAS -->
        <td class="text-center"><strong><?php echo remove_junk($emp['dias']);?></strong></td>
        <!-- ESTATUS -->
        <td class="text-center"><span class="label <?php echo $e['class']; ?>"> <?php echo $e['texto']; ?></span></td>
    </tr>

<?php endforeach; ?>