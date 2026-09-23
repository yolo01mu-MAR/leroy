<?php

require_once __DIR__ . '/../../app/bootstrap.php';

use Dompdf\Dompdf;
use Dompdf\Options;


// ========================================
// FILTROS
// ========================================

$semana = isset($_GET['semana'])
    ? (int) $_GET['semana']
    : (int) date('W');

$anio = isset($_GET['anio'])
    ? (int) $_GET['anio']
    : (int) date('o');

$buscar = isset($_GET['buscar'])
    ? trim($_GET['buscar'])
    : '';


// ========================================
// OBTENER INFORMACIÓN
// ========================================

$empleadosSaldos = find_all_solicitudes_aprobadas($semana, $anio);


// ========================================
// FILTRAR POR EMPLEADO
// ========================================

if ($buscar !== '') {

    $buscarNormalizado = mb_strtolower($buscar, 'UTF-8');

    $empleadosSaldos = array_filter(
        $empleadosSaldos,
        function ($emp) use ($buscarNormalizado) {

            $nomina = mb_strtolower(
                $emp['nomina'] ?? '',
                'UTF-8'
            );

            $nombre = mb_strtolower(
                $emp['nombre'] ?? '',
                'UTF-8'
            );

            return
                strpos($nomina, $buscarNormalizado) !== false ||
                strpos($nombre, $buscarNormalizado) !== false;
        }
    );
}


// ========================================
// HTML DEL REPORTE
// ========================================

$html = '
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<style>

    @page {
        margin: 35px 40px;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #1f2933;
    }

    .encabezado {
        text-align: center;
        margin-bottom: 25px;
    }

    .titulo {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .subtitulo {
        font-size: 11px;
        color: #52606d;
    }

    .informacion {
        width: 100%;
        margin-bottom: 20px;
    }

    .informacion td {
        padding: 5px;
    }

    .etiqueta {
        font-weight: bold;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background-color: #f3f4f6;
        border: 1px solid #d1d5db;
        padding: 7px 5px;
        text-align: center;
        font-size: 9px;
    }

    td {
        border: 1px solid #e5e7eb;
        padding: 7px 5px;
        font-size: 9px;
    }

    .center {
        text-align: center;
    }

    .estado {
        font-weight: bold;
    }

    .pie {
        margin-top: 25px;
        text-align: right;
        font-size: 8px;
        color: #6b7280;
    }

</style>

</head>

<body>

<div class="encabezado">

    <div class="titulo">
        Historial de Vacaciones
    </div>

    <div class="subtitulo">
        Reporte de solicitudes de vacaciones
    </div>

</div>


<table class="informacion">

    <tr>
        <td>
            <span class="etiqueta">Semana:</span>
            ' . htmlspecialchars($semana) . '
        </td>

        <td>
            <span class="etiqueta">Año:</span>
            ' . htmlspecialchars($anio) . '
        </td>

        <td>
            <span class="etiqueta">Empleado:</span>
            ' . (
                $buscar !== ''
                    ? htmlspecialchars($buscar)
                    : 'Todos'
            ) . '
        </td>
    </tr>

</table>


<table>

<thead>

<tr>

    <th>Nómina</th>
    <th>Nombre</th>
    <th>Fecha de solicitud</th>
    <th>Periodo inicio</th>
    <th>Periodo fin</th>
    <th>Días</th>
    <th>Estatus</th>

</tr>

</thead>

<tbody>
';


// ========================================
// FILAS
// ========================================

if (!empty($empleadosSaldos)) {

    foreach ($empleadosSaldos as $emp) {

        $nomina = htmlspecialchars(
            $emp['nomina'] ?? ''
        );

        $nombre = htmlspecialchars(
            $emp['nombre'] ?? ''
        );

        $fechaSolicitud = htmlspecialchars(
            $emp['fecha_solicitud'] ?? ''
        );

        $inicio = htmlspecialchars(
            $emp['inicio'] ?? ''
        );

        $fin = htmlspecialchars(
            $emp['fin'] ?? ''
        );

        $dias = htmlspecialchars(
            $emp['dias'] ?? ''
        );

        $estatus = strtoupper(
            $emp['estatus'] ?? 'DESCONOCIDO'
        );

        $html .= '
        <tr>

            <td class="center">
                ' . $nomina . '
            </td>

            <td>
                ' . $nombre . '
            </td>

            <td class="center">
                ' . $fechaSolicitud . '
            </td>

            <td class="center">
                ' . $inicio . '
            </td>

            <td class="center">
                ' . $fin . '
            </td>

            <td class="center">
                ' . $dias . '
            </td>

            <td class="center estado">
                ' . htmlspecialchars($estatus) . '
            </td>

        </tr>';
    }

} else {

    $html .= '
    <tr>

        <td colspan="7" class="center">
            No se encontraron registros.
        </td>

    </tr>';
}


$html .= '

</tbody>

</table>


<div class="pie">
    Reporte generado el ' . date('d/m/Y H:i') . '
</div>

</body>
</html>
';


// ========================================
// GENERAR PDF
// ========================================

$options = new Options();

$options->set(
    'isRemoteEnabled',
    true
);

$options->set(
    'defaultFont',
    'DejaVu Sans'
);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper(
    'A4',
    'landscape'
);

$dompdf->render();

$nombreArchivo =
    'reporte_vacaciones_' .
    $semana . '_' .
    $anio .
    '.pdf';

$dompdf->stream(
    $nombreArchivo,
    [
        'Attachment' => false
    ]
);