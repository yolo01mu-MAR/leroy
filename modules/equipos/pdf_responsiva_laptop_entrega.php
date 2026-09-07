<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/bootstrap.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// =====================
// CONFIGURACIÓN DOMPDF
// =====================
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
// =====================
// ID USUARIO
// =====================
$idUsuario = 0;

if (isset($_GET['id'])) {
    $idUsuario = (int)$_GET['id'];
}

if ($idUsuario <= 0) {
    die("ID no válido");
}

if($idUsuario <= 0){
    die("ID no válido");
}
// =====================
// DATOS EQUIPO
// =====================
$sqlEquipo = "SELECT 
                ee.id_equipo,
                ee.fecha_entrega,
                et.nombre AS tipo_equipo,
                e.costo,
                e.marca,
                e.modelo,
                ed.procesador,
                ed.numero_serie,
                u.name AS usuario,
                u.puesto,
                d.zona AS departamento,
                u2.name AS asigno
              FROM equipo_entrega ee
                INNER JOIN users u ON ee.id_usuario = u.id
                LEFT JOIN departamento d ON u.departamento_id = d.id
                INNER JOIN equipo e ON ee.id_equipo = e.id
                INNER JOIN equipo_detalle ed ON e.id = ed.id_equipo
                INNER JOIN equipo_tipo et ON e.tipo_equipo = et.id
                INNER JOIN users u2 ON e.usuarioAsigno = u2.id
              WHERE ee.id_usuario = {$idUsuario}
              AND et.nombre = 'LAPTOP'
            ";

function fecha_espanol($fecha){
    $meses = [
        1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio',
        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
    ];

    $timestamp = strtotime($fecha);

    $dia = date('d', $timestamp);
    $mes = $meses[(int)date('m', $timestamp)];
    $anio = date('Y', $timestamp);

    return "$dia de $mes de $anio";
}

$resEquipo = $db->query($sqlEquipo);
$equipo = $resEquipo->fetch_assoc();

// =====================
// LOGO BASE64
// =====================
$path = BASE_PATH . '/libs/images/Laboratorios.jpg';
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

$fechaTexto = fecha_espanol($equipo['fecha_entrega']);

// =====================
// HTML
// =====================
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13px;
    color: #000;
    margin: 30px 40px;
}

.header {
    width: 100%;
    position: relative;
}

.logo {
    float: left;
}

.header-info {
    float: right;
    text-align: right;
    font-size: 20px;
}
.codigo {
    font-size: 20px;
    font-weight: bold;
    font-style: italic;
}

.fecha {
    font-size: 11px;
}

.clear {
    clear: both;
}

.title {
    text-align: center;
    font-weight: bold;
    font-style: italic;
    font-size: 20px; 
    color: #6e6e6e; 
    margin-top: 20px;
    margin-bottom: 20px;
}

.bold { font-weight: bold; }
.italic { font-style: italic; }

p {
    margin: 8px 0;
    line-height: 1.4;
    text-align: justify;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

td {
    border: 1px solid #000;
    padding: 6px;
    font-size: 12px;
}

.table-title {
    text-align: center;
    font-weight: bold;
    background: #f2f2f2;
}

.signatures {
    margin-top: 40px;
}

.signatures td {
    border: none;
    text-align: center;
}

.line {
    margin-top: 50px;
    border-top: 1px solid #000;
    width: 80%;
    margin-left: auto;
    margin-right: auto;
}

.footer {
    text-align: center;
    font-size: 11px;
    margin-top: 40px;
}

</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div class="logo">
        <img src="'.$base64.'" width="350">
    </div>

    <div class="header-info">
        <div class="codigo">DM-F-001</div>
        <div class="fecha">'.$fechaTexto.'</div>
    </div>
</div>

<div class="clear"></div>

<!-- TITULO -->
<div class="title">
    CARTA RESPONSIVA DE EQUIPO DE CÓMPUTO
</div>

<!-- DATOS -->
<p class="bold">Recibe: '.$equipo['usuario'].'</p>
<p class="bold">Entrega: '.$equipo['asigno'].'</p>

<br>
<p>
A continuación, encontrará las características del equipo de cómputo
que a partir de este momento está a su responsabilidad.
</p>

<br>

<!-- UBICACION -->
<table>
<tr>
    <td colspan="2" class="table-title">Ubicación de la Computadora</td>
</tr>
<tr>
    <td>Departamento: '.$equipo['departamento'].'</td>
    <td>Puesto: '.$equipo['puesto'].'</td>
</tr>
</table>

<br>
<br>

<!-- DATOS GENERALES -->
<table>
<tr>
    <td colspan="2" class="table-title">Datos Generales de la Computadora</td>
</tr>
<tr>
    <td>
        Marca: '.$equipo['marca'].' '.$equipo['modelo'].' '.$equipo['procesador'].'
    </td>
    <td>
        No. Serie: '.$equipo['numero_serie'].'
    </td>
</tr>
</table>

<br>
<br>

<!-- TEXTO -->
<p>
<span class="bold">'.$equipo['usuario'].'</span>, como usuario de este equipo de cómputo y 
<span class="bold">'.$equipo['asigno'].'</span>, firman esta Carta Responsiva y aceptan el contenido de esta 
para fines de uso en labores de trabajo que correspondan a Laboratorios Le Roy, S.A. de C.V., 
y a su vez ratifican haber leído y comprendido el Reglamento de uso del Equipo de Computo 
<span class="bold italic">DM-I-001</span>, que les fue entregado previamente.
</p>

<br>
<br>
<br>

<!-- FIRMAS -->
<table class="signatures">
<tr>
    <td class="italic">Recibo el equipo y estoy de acuerdo.</td>
    <td></td>
    <td class="italic">Hace entrega del equipo.</td>
</tr>
<tr>
    <td><div class="line"></div></td>
    <td></td>
    <td><div class="line"></div></td>
</tr>
<tr>
    <td class="bold italic">'.$equipo['usuario'].'</td>
    <td></td>
    <td class="bold italic">'.$equipo['asigno'].'</td>
</tr>
</table>

<br>
<br>
<br>
<br>
<br>

<!-- FOOTER -->
<div class="footer">
Página 1 de 1.
</div>

</body>
</html>
';

// =====================
// GENERAR PDF
// =====================
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

if (ob_get_length()) {
    ob_end_clean();
}

$dompdf->stream("Responsiva Laptop.pdf", ["Attachment" => false]);
exit;