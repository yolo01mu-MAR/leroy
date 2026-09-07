<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../includes/barcode/autoload.php';
use Picqer\Barcode\BarcodeGeneratorPNG;

/*
|--------------------------------------------------------------------------
| ID USUARIO
|--------------------------------------------------------------------------
*/

$id = (int)$_GET['id'];

if($id <= 0){
    die('ID inválido');
}

/*
|--------------------------------------------------------------------------
| CONSULTA
|--------------------------------------------------------------------------
*/

$sql = "SELECT 
            u.id,
            u.name as nombre,
            u.puesto,
            u.fecha_ingreso,
            u.RFC,
            u.NSS,
            u.image,
            u.statusLaboral_id,
            c.ID AS cuadrilla,
            g.nombre AS grupo,
            dp.nombre AS zonaTrabajo,
            d.zona AS departamento

        FROM users u

        LEFT JOIN cuadrilla c ON c.ID = u.cuadrilla_id
        LEFT JOIN grupos g ON c.grupo_id = g.id
        LEFT JOIN departamento_plantilla dp ON dp.id = c.depPlantilla_id
        LEFT JOIN departamento d ON d.ID = dp.departamento_id

        WHERE u.id = '{$id}'
        LIMIT 1";

/*
|--------------------------------------------------------------------------
| EJECUTAR QUERY
|--------------------------------------------------------------------------
*/

$emp = find_by_sql($sql);

if(empty($emp)){
    die('Usuario no encontrado');
}

$emp = $emp[0];

/*
|--------------------------------------------------------------------------
| DATOS
|--------------------------------------------------------------------------
*/

$nombre       = $emp['nombre'];
$nomina       = $emp['id'];
$departamento = $emp['departamento'];
$puesto       = $emp['puesto'];
$RFC          = $emp['RFC'];
$NSS          = $emp['NSS'];
 
$fecha = date('d/m/Y');

/*
|--------------------------------------------------------------------------
| GENERAR BARCODE
|--------------------------------------------------------------------------
*/

$generator = new BarcodeGeneratorPNG();

$barcode = $generator->getBarcode(
    $nomina,
    $generator::TYPE_CODE_128
);

$carpetaTmp = __DIR__ . '/../../tmp';

if (!is_dir($carpetaTmp)) {
    mkdir($carpetaTmp, 0777, true);
}

$barcodeFile = $carpetaTmp . '/barcode_' . $id . '.png';

file_put_contents(
    $barcodeFile,
    $barcode
);

/*
|--------------------------------------------------------------------------
| FOTO
|--------------------------------------------------------------------------
*/

$foto = __DIR__ . '/../../uploads/users/' . $emp['image'];

if (!file_exists($foto)) {
    $foto = __DIR__ . '/../../uploads/users/no_image.jpg';
}

if (!file_exists($foto)) {
    die('La foto no existe');
}

/*
|--------------------------------------------------------------------------
| ARCHIVOS
|--------------------------------------------------------------------------
*/


$template = __DIR__ . '/plantilla.pptx';

if (!file_exists($template)) {
    die('No existe plantilla.pptx');
}

/*
|--------------------------------------------------------------------------
| CREAR TEMPORAL
|--------------------------------------------------------------------------
*/

if (!is_dir($carpetaTmp)) {
    mkdir($carpetaTmp, 0777, true);
}

$salida = $carpetaTmp . '/credencial_' . $id . '.pptx';

/*
|--------------------------------------------------------------------------
| COPIAR PLANTILLA
|--------------------------------------------------------------------------
*/

if(!copy($template, $salida)){
    die('No se pudo copiar la plantilla');
}

/*
|--------------------------------------------------------------------------
| ABRIR PPTX
|--------------------------------------------------------------------------
*/

$zip = new ZipArchive;

if($zip->open($salida) === TRUE){

    /*
    |--------------------------------------------------------------------------
    | SLIDES
    |--------------------------------------------------------------------------
    */

    $slides = [
        'ppt/slides/slide1.xml',
        'ppt/slides/slide2.xml'
    ];

    foreach($slides as $slide){

        $contenido = $zip->getFromName($slide);

        if($contenido){

            /*
            |--------------------------------------------------------------------------
            | PLACEHOLDERS
            |--------------------------------------------------------------------------
            */

            $buscar = [
                '{{NOMBRE}}',
                '{{NOMINA}}',
                '{{DEPARTAMENTO}}',
                '{{FECHA}}',
                '{{RFC}}',
                '{{NSS}}'
            ];

            $reemplazar = [
                htmlspecialchars($nombre),
                htmlspecialchars($nomina),
                htmlspecialchars($departamento),
                htmlspecialchars($fecha),
                htmlspecialchars($RFC),
                htmlspecialchars($NSS)
            ];

            $contenido = str_replace(
                $buscar,
                $reemplazar,
                $contenido
            );

            /*
            |--------------------------------------------------------------------------
            | GUARDAR SLIDE
            |--------------------------------------------------------------------------
            */

            $zip->deleteName($slide);

            $zip->addFromString(
                $slide,
                $contenido
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FOTO
    |--------------------------------------------------------------------------
    |
    | image3.jpg debe existir previamente
    | dentro de la plantilla PPTX
    |
    */

    $imagenPPT = 'ppt/media/image3.jpg';

    /*
    |--------------------------------------------------------------------------
    | BARCODE
    |--------------------------------------------------------------------------
    */

    $barcodePPT = 'ppt/media/image4.png';

    if($zip->locateName($barcodePPT) !== false){

        $zip->deleteName($barcodePPT);

        $zip->addFile(
            $barcodeFile,
            $barcodePPT
        );

    }else{

        die('No existe image4.png en la plantilla');
    }

    if($zip->locateName($imagenPPT) !== false){

        $zip->deleteName($imagenPPT);

        $zip->addFile(
            $foto,
            $imagenPPT
        );

    }else{

        die('No existe image3.jpg en la plantilla');
    }

    $zip->close();

}else{

    die('No se pudo abrir el PPTX');
}

/*
|--------------------------------------------------------------------------
| DESCARGAR
|--------------------------------------------------------------------------
*/

header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');

header(
    'Content-Disposition: attachment; filename="credencial_'.$id.'.pptx"'
);

header('Content-Length: ' . filesize($salida));

readfile($salida);

/*
|--------------------------------------------------------------------------
| ELIMINAR TEMPORAL
|--------------------------------------------------------------------------
*/

unlink($salida);

if(file_exists($barcodeFile)){
    unlink($barcodeFile);
}

exit;