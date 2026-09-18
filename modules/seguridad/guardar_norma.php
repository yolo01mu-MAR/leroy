<?php

require_once __DIR__ . '/../../app/bootstrap.php';

page_require_level(12);

/*
|--------------------------------------------------------------------------
| VALIDAR POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('normas.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DATOS PRINCIPALES
|--------------------------------------------------------------------------
*/

$id_norma = (int)($_POST['id_norma'] ?? 0);

$codigo_norma     = trim($_POST['codigo_norma'] ?? '');
$nombre_norma     = trim($_POST['nombre_norma'] ?? '');
$id_clasificacion = (int)($_POST['id_clasificacion'] ?? 0);

$puntos_enviados = $_POST['puntos'] ?? [];


/*
|--------------------------------------------------------------------------
| VALIDACIONES
|--------------------------------------------------------------------------
*/

if ($codigo_norma === '') {

    $session->msg(
        "d=El código de la norma es obligatorio.&type=danger"
    );

    if ($id_norma > 0) {
        redirect('editar_normas.php?id=' . $id_norma);
    } else {
        redirect('add_norma.php');
    }

    exit;
}


if ($nombre_norma === '') {

    $session->msg(
        "d=El nombre de la norma es obligatorio.&type=danger"
    );

    if ($id_norma > 0) {
        redirect('editar_normas.php?id=' . $id_norma);
    } else {
        redirect('agregar_norma.php');
    }

    exit;
}


if ($id_clasificacion <= 0) {

    $session->msg(
        "d=Debes seleccionar una clasificación.&type=danger"
    );

    if ($id_norma > 0) {
        redirect('editar_normas.php?id=' . $id_norma);
    } else {
        redirect('add_norma.php');
    }

    exit;
}


/*
|--------------------------------------------------------------------------
| ESCAPAR DATOS
|--------------------------------------------------------------------------
*/

$codigo_norma_db = $db->escape($codigo_norma);
$nombre_norma_db = $db->escape($nombre_norma);


/*
|--------------------------------------------------------------------------
| DETERMINAR SI ES NUEVA O EDICIÓN
|--------------------------------------------------------------------------
*/

$es_nueva = ($id_norma <= 0);


/*
|--------------------------------------------------------------------------
| CREAR / ACTUALIZAR NORMA
|--------------------------------------------------------------------------
*/

if ($es_nueva) {

    /*
    |--------------------------------------------------------------------------
    | NUEVA NORMA
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO seguridad_normas (
            codigo_norma,
            nombre_norma,
            id_clasificacion,
            imagen
        )
        VALUES (
            '{$codigo_norma_db}',
            '{$nombre_norma_db}',
            {$id_clasificacion},
            'no_image.jpg'
        )
    ";

    if (!$db->query($sql)) {

        $session->msg(
            "d=Error al guardar la norma en la base de datos.&type=danger"
        );

        redirect('add_norma.php');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | OBTENER ID GENERADO
    |--------------------------------------------------------------------------
    */

    $id_norma = $db->insert_id();

    $imagen_actual = 'no_image.jpg';

} else {

    /*
    |--------------------------------------------------------------------------
    | EDITAR NORMA
    |--------------------------------------------------------------------------
    */

    $norma = find_by_id('seguridad_normas', $id_norma);

    if (!$norma) {

        $session->msg(
            "d=La norma que intentas editar no existe.&type=danger"
        );

        redirect('normas.php');
        exit;
    }

    $imagen_actual = !empty($norma['imagen'])
        ? $norma['imagen']
        : 'no_image.jpg';


    $sql = "
        UPDATE seguridad_normas
        SET
            codigo_norma = '{$codigo_norma_db}',
            nombre_norma = '{$nombre_norma_db}',
            id_clasificacion = {$id_clasificacion}
        WHERE id = {$id_norma}
        LIMIT 1
    ";

    if (!$db->query($sql)) {

        $session->msg(
            "d=Error al actualizar la norma.&type=danger"
        );

        redirect('editar_normas.php?id=' . $id_norma);
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| MANEJO DE IMAGEN
|--------------------------------------------------------------------------
*/

$ruta_imagen = $imagen_actual;

if (
    isset($_FILES['imagen']) &&
    $_FILES['imagen']['error'] === UPLOAD_ERR_OK
) {

    $file_tmp = $_FILES['imagen']['tmp_name'];

    $ext = strtolower(
        pathinfo(
            $_FILES['imagen']['name'],
            PATHINFO_EXTENSION
        )
    );

    /*
    |--------------------------------------------------------------------------
    | EXTENSIONES PERMITIDAS
    |--------------------------------------------------------------------------
    */

    $allowed = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'svg'
    ];

    if (!in_array($ext, $allowed, true)) {

        $session->msg(
            "d=El formato de imagen no está permitido.&type=danger"
        );

        if ($es_nueva) {
            redirect('add_norma.php');
        } else {
            redirect('editar_normas.php?id=' . $id_norma);
        }

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | NOMBRE DEL ARCHIVO
    |--------------------------------------------------------------------------
    */

    $nombre_archivo = $codigo_norma;

    // Espacios → guiones
    $nombre_archivo = preg_replace(
        '/\s+/',
        '-',
        $nombre_archivo
    );

    // Quitar caracteres no válidos
    $nombre_archivo = preg_replace(
        '/[^A-Za-z0-9._-]/',
        '',
        $nombre_archivo
    );


    if ($nombre_archivo === '') {
        $nombre_archivo = 'norma-' . $id_norma;
    }


    $nuevo_nombre = $nombre_archivo . '.' . $ext;


    /*
    |--------------------------------------------------------------------------
    | CARPETA
    |--------------------------------------------------------------------------
    */

    $carpeta_normas = __DIR__ . '/../../uploads/normas/';


    if (!is_dir($carpeta_normas)) {

        if (!mkdir($carpeta_normas, 0777, true)) {

            $session->msg(
                "d=No fue posible crear la carpeta de imágenes.&type=danger"
            );

            if ($es_nueva) {
                redirect('add_norma.php');
            } else {
                redirect('editar_normas.php?id=' . $id_norma);
            }

            exit;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DESTINO
    |--------------------------------------------------------------------------
    */

    $destino = $carpeta_normas . $nuevo_nombre;


    /*
    |--------------------------------------------------------------------------
    | MOVER IMAGEN
    |--------------------------------------------------------------------------
    */

    if (move_uploaded_file($file_tmp, $destino)) {

        /*
        |--------------------------------------------------------------------------
        | ELIMINAR IMAGEN ANTERIOR
        |--------------------------------------------------------------------------
        */

        if (!empty($imagen_actual)) {

            $imagen_anterior = basename($imagen_actual);

            $ruta_anterior = $carpeta_normas . $imagen_anterior;

            if (
                $imagen_anterior !== $nuevo_nombre &&
                file_exists($ruta_anterior)
            ) {
                unlink($ruta_anterior);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | GUARDAR NOMBRE EN BD
        |--------------------------------------------------------------------------
        */

        $ruta_imagen = $nuevo_nombre;

        $ruta_imagen_db = $db->escape($ruta_imagen);

        $sql_imagen = "
            UPDATE seguridad_normas
            SET imagen = '{$ruta_imagen_db}'
            WHERE id = {$id_norma}
            LIMIT 1
        ";

        $db->query($sql_imagen);
    }
}


/*
|--------------------------------------------------------------------------
| PROCESAR PUNTOS (UPSERT)
|--------------------------------------------------------------------------
|
| - UPDATE para puntos existentes (traen id > 0)
| - INSERT para puntos nuevos (sin id o id = 0)
| - DELETE solo de los puntos que el usuario quitó del formulario,
|   y solo si no tienen inspecciones asociadas. Si las tienen, se
|   dejan intactos y se avisa al usuario.
|
|--------------------------------------------------------------------------
*/

$puntos_no_eliminados = [];

if (!$es_nueva) {

    // IDs que actualmente existen en la BD para esta norma
    $ids_actuales_db = [];
    $res_actuales = $db->query("SELECT id FROM seguridad_normas_p WHERE id_norma = {$id_norma}");
    if ($res_actuales) {
        while ($row = $res_actuales->fetch_assoc()) {
            $ids_actuales_db[] = (int)$row['id'];
        }
    }

    // IDs que el usuario conservó en el formulario
    $ids_enviados = [];
    foreach ($puntos_enviados as $p) {
        $pid = (int)($p['id'] ?? 0);
        if ($pid > 0) {
            $ids_enviados[] = $pid;
        }
    }

    // Los que existían pero ya no vienen -> el usuario los quitó
    $ids_a_borrar = array_diff($ids_actuales_db, $ids_enviados);

    foreach ($ids_a_borrar as $id_borrar) {
        try {
            $db->query("DELETE FROM seguridad_normas_p WHERE id = {$id_borrar} LIMIT 1");
        } catch (mysqli_sql_exception $e) {
            // Tiene inspecciones asociadas: no se puede borrar
            $puntos_no_eliminados[] = $id_borrar;
        }
    }
}


/*
|--------------------------------------------------------------------------
| INSERTAR / ACTUALIZAR PUNTOS
|--------------------------------------------------------------------------
*/

foreach ($puntos_enviados as $p) {

    $p_id = (int)($p['id'] ?? 0);

    $p_no           = trim($p['no'] ?? '');
    $p_requisito    = trim($p['requisito'] ?? '');
    $p_comprobacion = trim($p['comprobacion'] ?? '');
    $p_evidencia    = trim($p['evidencia'] ?? '');

    if ($p_no === '' && $p_requisito === '' && $p_comprobacion === '' && $p_evidencia === '') {
        continue;
    }

    $p_no_db           = $db->escape($p_no);
    $p_requisito_db    = $db->escape($p_requisito);
    $p_comprobacion_db = $db->escape($p_comprobacion);
    $p_evidencia_db    = $db->escape($p_evidencia);

    if ($p_id > 0) {

        $sql_punto = "
            UPDATE seguridad_normas_p
            SET
                `no` = '{$p_no_db}',
                desc_requisito = '{$p_requisito_db}',
                tipo_comprobacion = '{$p_comprobacion_db}',
                desc_evidencia = '{$p_evidencia_db}'
            WHERE id = {$p_id}
            LIMIT 1
        ";

    } else {

        $sql_punto = "
            INSERT INTO seguridad_normas_p (
                id_norma,
                `no`,
                desc_requisito,
                tipo_comprobacion,
                desc_evidencia
            )
            VALUES (
                {$id_norma},
                '{$p_no_db}',
                '{$p_requisito_db}',
                '{$p_comprobacion_db}',
                '{$p_evidencia_db}'
            )
        ";
    }

    $db->query($sql_punto);
}

/*
|--------------------------------------------------------------------------
| MENSAJE FINAL
|--------------------------------------------------------------------------
*/
if ($es_nueva) {
    $session->msg("s=Norma agregada correctamente.&type=success");
} else {
    if (!empty($puntos_no_eliminados)) {
        $session->msg("d=Norma actualizada. Algunos requisitos no se eliminaron porque ya tienen inspecciones registradas.&type=warning");
    } else {
        $session->msg("s=Norma actualizada correctamente.&type=success");
    }
}


/*
|--------------------------------------------------------------------------
| REDIRECCIÓN
|--------------------------------------------------------------------------
*/

redirect('ver_norma.php?id=' . $id_norma);
exit;