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
                e.costo,
                ed.telefono,
                ed.IMEI,
                e.marca,
                e.modelo,
                et.nombre AS tipo_equipo,
                u.name AS usuario,
                u.puesto,
                d.zona AS departamento,
                ep.nombre AS plan,
                ep.minutos,
                ep.internet,
                ep.seguro_de_equipo,
                ep.llamadas_incluidas,
                ep.llamadas_internacional,
                u2.name AS asigno
              FROM equipo_entrega ee
                INNER JOIN users u ON ee.id_usuario = u.id
                LEFT JOIN departamento d ON u.departamento_id = d.id
                INNER JOIN equipo e ON ee.id_equipo = e.id
                INNER JOIN equipo_detalle ed ON e.id = ed.id_equipo
                INNER JOIN equipo_tipo et ON e.tipo_equipo = et.id
                INNER JOIN equipo_plan ep ON ed.plan_id = ep.id
                INNER JOIN users u2 ON e.usuarioAsigno = u2.id
              WHERE ee.id_usuario = {$idUsuario} AND et.nombre = 'CELULAR'";

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
$path = __DIR__ . '/libs/images/Laboratorios.jpg';
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
                font-size: 11px;
                margin: 25px 35px 25px 35px;
                line-height: 1.2;
            }
            strong {
                font-weight: bold;
            }
            .label {
                font-weight: bold;
                font-size: 11px;
            }
            h5 {
                font-size: 11px;
                font-weight: bold;
                margin: 10px 0 5px 0;
            }
            .fecha {
                text-align: right;
                font-size: 10px;
                margin-bottom: 5px;
                text-align: right;
                font-size: 11px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            td, th {
                border: 1px solid #000;
                padding: 5px;
            }
            .sin-borde td {
                border: none;
            }
            .center {
                text-align: center;
            }
            .right {
                text-align: right;
            }
            .firma {
                text-align: center;
                padding-top: 40px;
            }
            table.plan th, table.plan td {
                text-align: center;
            }
            table.condiciones td {
                padding: 8px;
            }
            .numero {
                width: 60px;
                text-align: center;
                font-weight: bold;
            }
            <span style="margin-left:80px;">
        </style>
    </head>
    <body>
        <!-- LOGO -->
        <div style="text-align:left;">
        <img src="'.$base64.'" width="650">
        </div>

        <div class="fecha">
        México, CDMX. A '.$fechaTexto.'
        </div>

        <table style="width:100%; border-collapse:collapse; font-size:11px; margin-top:5px;">
            <tr>
                <!-- IZQUIERDA -->
                <td style="width:33%; border:none; vertical-align:top;">
                    <span class="label">No. DE EQUIPO:</span>
                    <span style="margin-left:12px; font-size:13px;">
                        <strong>'.$equipo['id_equipo'].'</strong>
                    </span>
                </td>
                <!-- CENTRO -->
                <td style="width:34%; border:none; text-align:center; vertical-align:top; font-size:11px;">
                    <div>
                        <span class="label">COSTO EQUIPO</span>
                        <span style="margin-left:8px;">$</span>
                        <span style="margin-left:8px;">'.number_format($equipo['costo'],2).'</span>
                    </div>
                    <div style="margin-top:1px;">
                        <span class="label">COSTO CARGADOR</span>
                        <span style="margin-left:8px;">$</span>
                        <span style="margin-left:8px;">1,500.00</span>
                    </div>
                </td>
                <!-- DERECHA -->
                <td style="width:33%; border:none; text-align:right; vertical-align:top;">
                    <span class="label">No. TELEFONICO:</span>
                    <span style="margin-left:12px;">
                        <strong>'.$equipo['telefono'].'</strong>
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border:none; padding-top:5px;">
                    <span class="label">No. DE IMEI:</span>

                    <span style="margin-left:15px;">
                        <strong>'.$equipo['IMEI'].'</strong>
                    </span>

                    <span class="label"></span>

                    <span style="margin-left:40px;">
                        <strong>'.$equipo['marca'].' '.$equipo['modelo'].'</strong>
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border:none; padding-top:3px;">
                    <span class="label">NOMBRE DEL USUARIO / RESPONSABLE:</span>
                    <span style="margin-left:15px;">
                        <strong>'.$equipo['usuario'].'</strong>
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border:none; padding-top:2px;">
                    <span class="label">DEPARTAMENTO:</span>
                    <span style="margin-left:15px;">
                        <strong>'.$equipo['departamento'].'</strong>
                    </span>
                </td>
            </tr>
        </table>

        <h5>CARACTERÍSTICAS DEL PLAN CONTRATADO PARA EL EQUIPO '.$equipo['plan'].' QUE LE FUE ASIGNADO Y EL CUAL QUEDA BAJO SU RESPONSABILIDAD:</h5>

        <table class="plan">
            <tr>
                <th rowspan="3">PLAN<br>CONTRATADO</th>
                <th colspan="5">SERVICIOS CONTRATADOS</th>
            </tr>
            <tr>
                <th>MINUTOS</th>
                <th>INTERNET</th>
                <th>SEGURO DE EQUIPO</th>
                <th>LLAMADAS INCLUIDAS A CELULAR</th>
                <th>LARGA DISTANCIA NACIONAL E INTERNACIONAL (USA Y CANADA)</th>
            </tr>
            <tr>
                <td>- 1 -</td>
                <td>- 2 -</td>
                <td>- 3 -</td>
                <td>- 4 -</td>
                <td>- 5 -</td>
            </tr>
            <tr>
                <th>'.$equipo['plan'].'</th>
                <td>'.$equipo['minutos'].'</td>
                <td>'.$equipo['internet'].'</td>
                <td>'.($equipo['seguro_de_equipo'] ? 'SI' : 'NO').'</td>
                <td>'.($equipo['llamadas_incluidas'] ? 'SI' : 'NO').'</td>
                <td>'.($equipo['llamadas_internacional'] ? 'SI' : 'NO').'</td>
            </tr>
        </table>

        <table class="condiciones">
            <tr>
                <td class="numero">- 1 -</td>
                <td>
                    CUENTA CON TIEMPO ILIMITADO PARA REALIZAR LLAMADAS E INTERNET TOTAL '.$equipo['internet'].'; 1GB PARA TEAMS
                </td>
            </tr>
            <tr>
                <td class="numero">- 2 -</td>
                <td>
                    CUENTA CON MINUTOS ILIMITADOS AL MES PARA REALIZAR LLAMADAS TELEFONICAS.
                    AL CONSUMIR EL TOTAL DE GB DISPONIBLE, EL SERVICIO DE INTERNET SERA RESTRINGIDO.
                </td>
            </tr>
            <tr>
                <td class="numero">- 3 -</td>
                <td>
                    ESTOY DE ACUERDO QUE EN CASO DE NO DEVOLVER EL EQUIPO Y CARGADOR EN BUENAS CONDICIONES
                    PAGARE LA CANTIDAD CORRESPONDIENTE.
                </td>
            </tr>
            <tr>
                <td class="numero">- 4 -</td>
                <td>
                    EL EQUIPO ES UNA HERRAMIENTA DE TRABAJO, DEBE SER ATENDIDO.
                    AL TERCER REPORTE SERA RETIRADO.
                </td>
            </tr>
        </table>
        <br>
        <br>
        <p>
            EL PERIODO MENSUAL DE NUESTRA CUENTA SON LOS DIAS 30 DE CADA MES, FAVOR DE CONSIDERAR ESTAS FECHAS PARA
            LOS ABONOS DE INTERNET.
        </p>
        <br>
        <table class="sin-borde">
            <tr>
                <td class="firma">
                    <strong>ENTREGA</strong>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    ___________________________<br>
                    '.$equipo['asigno'].'<br>        
                </td>
                <td class="firma">
                    <strong>RECIBE</strong>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    ___________________________<br>
                    '.$equipo['usuario'].'<br>
                </td>
            </tr>
        </table>
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

$dompdf->stream("Responsiva Celular.pdf", ["Attachment" => false]);
exit;