<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';
require_once __DIR__ . '/../../app/bootstrap.php';
require_once('includes/sql.php');

$id_capacitacion = (int)$_GET['id'];

$capacitacion = find_datos_capacitacion($id_capacitacion);
$capacitacion = $capacitacion[0];
$participantes = find_participantes_capacitacion($id_capacitacion);

$porPagina = 27;

$paginas = array_chunk($participantes, $porPagina);

$html = '';

use Dompdf\Dompdf;
use Dompdf\Options;

foreach ($paginas as $indice => $pagina) {

    $filas = '';

    $contador = 1;

    foreach ($pagina as $empleado) {

        $filas .= "
        <tr>
            <td class='num'>{$contador}</td>
            <td class='text-center'>{$empleado['nomina']}</td>
            <td>{$empleado['nombre']}</td>
            <td>{$empleado['puesto']}</td>
            <td class='text-center'>{$empleado['departamento']}</td>
        </tr>";

        $contador++;
    }

    while ($contador <= 27) {

        $filas .= "
        <tr>
            <td class='num'>{$contador}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>";

        $contador++;
    }

    $horario = $capacitacion['hora_inicio'] . ' - ' . $capacitacion['hora_fin'];
    $fecha = date('d/m/Y', strtotime($capacitacion['fecha']));

// ── HTML del documento ─────────────────────────────────────────────────────
$html .= <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<!-- <title>Lista de Asistencia</title> -->
<style>

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000;
    padding: 18px 22px;
    background: #fff;
}

/* ── ENCABEZADO ── */
table.header-tbl {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
}

table.header-tbl td {
    vertical-align: middle;
    padding: 0;
}

.td-logo {
    width: 175px;
}

.td-logo img {
    width: 160px;
}

table.info-tbl {
    border-collapse: collapse;
    margin-left: 16px;
}

table.info-tbl td {
    font-size: 11px;
    padding: 1px 12px 1px 0;
    vertical-align: middle;
}

table.info-tbl td.bold {
    font-weight: bold;
}

/* ── CURSO / TEMA ── */
.curso {
    border: 1.5px solid #333;
    padding: 5px 8px;
    font-weight: bold;
    font-size: 11px;
    margin-bottom: 8px;
}

/* ── FECHA / DURACIÓN / HORARIO ── */
table.datos-tbl {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
}

table.datos-tbl td {
    padding: 0 6px 0 0;
    font-size: 11px;
    white-space: nowrap;
    vertical-align: middle;
}

table.datos-tbl strong{
    display:inline-block;
    height:26px;
    line-height:33px;
    vertical-align:middle;
    font-weight:bold;
}

.datos-box{
    display:inline-block;
    border:1px solid #333;
    height:24px;
    line-height:24px;
    margin-left:6px;
    vertical-align:middle;
    text-align:center;
    font-size:11px;
}

.box-fecha    { width: 160px; }
.box-duracion { width: 120px; }
.box-horario  { width: 130px; }

/* ── TABLA PRINCIPAL ── */
table.lista {
    width: 100%;
    border-collapse: collapse;
}

.lista th {
    background-color: #234f84;
    color: #ffffff;
    border: 1px solid #555;
    padding: 3px;
    font-size: 9px;
    font-weight: normal;
    text-align: center;
    vertical-align: middle;
    line-height: 1.15;
}

.lista td {
    border: 1px solid #555;
    height: 23px;
    font-size: 10px;
    padding: 1px 3px;
    vertical-align: middle;
}

.lista td.num {
    text-align: center;
}

/* ── PIE ── */
.footer {
    margin-top: 18px;
    font-size: 11px;
}

.footer-row {
    margin-bottom: 14px;
}

.linea {
    display: inline-block;
    border-bottom: 1px solid #333;
    vertical-align: bottom;
    height: 14px;
}

.l-nomina    { width:  95px; }
.l-instructor{ width: 290px; }
.l-firma     { width: 110px; }
.l-puesto    { width: 290px; }
.l-otro      { width: 290px; }

.version-row {
    margin-top: 8px;
    overflow: hidden;
}

.version-left  { float: left;  font-size: 11px; }
.version-right { float: right; font-size: 11px; }

.page-break{
    page-break-after: always;
}

.text-center{
    text-align: center;
}

</style>
</head>
<body>

<!-- ══ ENCABEZADO ══ -->
<table class="header-tbl">
    <tr>
        <td class="td-logo">
            <img src="libs/images/img_asistencia.jpg" alt="Le Roy">
        </td>
        <td>
            <table class="info-tbl">
                <tr>
                    <td>Código</td>
                    <td class="bold">RH-F-020</td>
                </tr>
                <tr>
                    <td>Área</td>
                    <td class="bold">Recursos Humanos</td>
                </tr>
                <tr>
                    <td>Tipo de documento</td>
                    <td class="bold">Registro</td>
                </tr>
                <tr>
                    <td>Título</td>
                    <td class="bold">Lista de Asistencia</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ══ CURSO / TEMA ══ -->
<div class="curso">CURSO / TEMA: {$capacitacion['capacitacion']}</div>

<!-- ══ FECHA / DURACIÓN / HORARIO ══ -->
<table class="datos-tbl">
    <tr>
        <td class="dato">
            <strong>FECHA:</strong>
            <span class="datos-box box-fecha">$fecha</span>
        </td>

        <td class="dato">
            <strong>DURACIÓN HRS.:</strong>
            <span class="datos-box box-duracion">{$capacitacion['duracion']}</span>
        </td>

        <td class="dato">
            <strong>HORARIO:</strong>
            <span class="datos-box box-horario">{$horario}</span>
        </td>
    </tr>
</table>

<!-- ══ TABLA DE ASISTENCIA ══ -->
<table class="lista">
    <thead>
        <tr>
            <th style="width:4.5%;">Núm.<br>Consecutivo</th>
            <th style="width:9%;">Núm. Nómina</th>
            <th style="width:38%;">Nombre del participante:<br>(nombre(s), apellido paterno y materno)</th>
            <th style="width:18%;">Puesto</th>
            <th style="width:14%;">Departamento al que<br>pertenece</th>
        </tr>
    </thead>
    <tbody>
        {$filas}
    </tbody>
</table>

<!-- ══ PIE ══ -->
<div class="footer">
    <div class="footer-row">
        <strong>NÓMINA:</strong>
        <span class="linea l-nomina"></span>
        &nbsp;&nbsp;
        <strong>NOMBRE DEL INSTRUCTOR:</strong>
        <span class="linea l-instructor">{$capacitacion['instructor']}</span>
        &nbsp;&nbsp;
        <strong>FIRMA:</strong>
        <span class="linea l-firma"></span>
    </div>
    <div class="footer-row">
        <strong>PUESTO:</strong>
        <span class="linea l-puesto"></span>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <strong>OTRO:</strong>
        <span class="linea l-otro"></span>
    </div>
    <div class="version-row">
        <span class="version-left">Versión 6</span>
        <span class="version-right">Página 1 de 1</span>
    </div>
</div>
</body>
</html>
HTML;
    if ($indice < count($paginas) - 1) {

        $html .= '<div class="page-break"></div>';

    }

}

// ── Configurar y renderizar con Dompdf ────────────────────────────────────
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('chroot', __DIR__);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('Letter', 'portrait');
$dompdf->render();

// 'Attachment' => false  → abre en el navegador
// 'Attachment' => true   → fuerza descarga
$dompdf->stream('lista asistencia.pdf', ['Attachment' => false]);
