<?php
require_once 'vendor/autoload.php';
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
// DATOS
// =====================
$idReporte = (int)$_GET['id'];

// CONSULTA
$sql = "SELECT 
          r.id,
          m.nombre AS maquina,
          r.fecha,
          g.nombre AS grupo,
          u.name AS supervisor,
          r.operador_id,
          r.estado
        FROM reportes r
        INNER JOIN maquinas m ON r.maquina_id = m.id
        INNER JOIN grupos g ON r.turno_id = g.id
        INNER JOIN users u ON r.supervisor_id = u.id
        WHERE r.id = {$idReporte}";
        
$res = $db->query($sql);
$reporte = $res->fetch_assoc();

// =====================
// PRODUCCIÓN
// =====================
$produccion = [];

$sqlProd = "SELECT rp.*, tg.descripcion, tg.piezasXhora
            FROM registros_produccion rp
            JOIN tabla_gasas tg ON tg.ID = rp.producto_id
            WHERE rp.reporte_id = {$idReporte}";

$resProd = $db->query($sqlProd);

while($row = $resProd->fetch_assoc()){
  $produccion[] = $row;
}

// =====================
// FALLAS
// =====================
$fallas = [];

$sqlFallas = "SELECT rp.minutos, pg.nombre AS paro, sp.nombre AS subparo
              FROM registros_paros rp
              JOIN paro_general pg ON pg.id = rp.paro_id
              JOIN subparos sp ON sp.id = rp.subParos_id
              WHERE rp.reporte_id = {$idReporte}";

$resFallas = $db->query($sqlFallas);

while($row = $resFallas->fetch_assoc()){
  $fallas[] = $row;
}

$path = __DIR__ . '/libs/images/imagen_reporte.jpg';

$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);

$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

// =====================
// HTML
// =====================
$html = '
<style>
body { font-family: Arial; font-size: 11px; }

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 10px;
}

th, td {
  border: 1px solid black;
  padding: 4px;
  text-align: center;
}

th {
  background: #eee;
}

.info td {
  border: none;
  text-align: left;
}
</style>

<!-- LOGO -->
<div style="text-align:left;">
  <img src="'.$base64.'" width="150">
</div>

<h2 style="text-align:center;">REPORTE DE PRODUCCIÓN ACABADO GASAS</h2>
<h3 style="text-align:center;">'.$reporte['maquina'].'</h3>

<table class="info">
  <tr>
    <td><strong>Fecha:</strong> '.$reporte['fecha'].'</td>
    <td><strong>Turno:</strong> '.$reporte['grupo'].'</td>
  </tr>
  <tr>
    <td><strong>Supervisor:</strong> '.$reporte['supervisor'].'</td>
    <td><strong>Operador:</strong> '.$reporte['supervisor'].'</td>
  </tr>
</table>

<h4>PRODUCCION</h4>

<table>
  <tr>
    <th>Hora Inicio</th>
    <th>Hora Fin</th>
    <th>Producto</th>
    <th>Producción</th>
    <th>Teórico</th>
    <th>%</th>
  </tr>';

foreach($produccion as $p){

  $html .= '<tr>
    <td>'.$p['hora_inicio'].'</td>
    <td>'.$p['hora_fin'].'</td>
    <td>'.$p['descripcion'].'</td>
    <td>'.$p['produccion_real'].'</td>
    <td>'.$p['estandar_hora'].'</td>
    <td>'.number_format($p['porcentaje'],1).'%</td>
  </tr>';
}

$html .= '</table>';

$html .= '<h4>PAROS</h4>

<table>
<tr>
  <th>Minutos</th>
  <th>Causa</th>
  <th>Descripción</th>
</tr>';

foreach($fallas as $f){
  $html .= '<tr>
    <td>'.$f['minutos'].'</td>
    <td>'.$f['paro'].'</td>
    <td>'.$f['subparo'].'</td>
  </tr>';
}

$html .= '</table>';

$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

// LIMPIAR BUFFER (ANTES de imprimir)
if (ob_get_length()) {
    ob_end_clean();
}

// MOSTRAR PDF
$dompdf->stream("Reporte de Produccion.pdf", ["Attachment" => false]);
exit;