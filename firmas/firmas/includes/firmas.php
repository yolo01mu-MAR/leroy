<?php

class FirmaTipo
{
    public const EMPLEADO = 'EMPLEADO';
    public const JEFE     = 'JEFE';
    public const RH       = 'RH';
}

class FirmaModulo
{
    public const VACACIONES   = 'vacaciones';
    public const TXT          = 'txt';
    public const PERMISOS     = 'permisos';
    public const EQUIPO       = 'equipo';
    public const CAPACITACION = 'capacitacion';
}

function guardar_firma($datos){
    $validacion = validar_firma($datos);

    if ($validacion !== true) {
        return [
            'ok' => false,
            'mensaje' => $validacion
        ];
    }

    $archivo = guardar_imagen_firma($datos);

    if ($archivo === false) {
        return [
            'ok' => false,
            'mensaje' => 'No fue posible guardar la imagen.'
        ];
    }
    if (!guardar_registro_firma($datos, $archivo)) {
        if (file_exists($archivo['ruta_fisica'])) {
            unlink($archivo['ruta_fisica']);
        }
        return [
            'ok' => false,
            'mensaje' => 'No fue posible registrar la firma.'
        ];
    }
    return [
        'ok' => true,
        'mensaje' => 'Firma registrada correctamente.',
        'archivo' => $archivo['ruta_bd']
    ];
}
function validar_firma($datos){
    $campos = [
        'modulo',
        'registro_id',
        'tipo',
        'usuario_id',
        'firma'
    ];

    foreach ($campos as $campo) {

        if (!isset($datos[$campo])) {
            return "Falta el campo: {$campo}";
        }

        if (trim($datos[$campo]) === '') {
            return "El campo {$campo} está vacío.";
        }

    }

    if (strpos($datos['firma'], 'data:image/png;base64,') !== 0) {
        return 'La firma recibida no es válida.';
    }

    return true;
}
function guardar_imagen_firma($datos){
    // Quitar encabezado Base64
    $base64 = preg_replace(
        '#^data:image/\w+;base64,#i',
        '',
        $datos['firma']
    );

    $imagen = base64_decode($base64);

    if ($imagen === false) {
        return false;
    }

    $anio = date('Y');
    $mes  = date('m');

    $rutas = obtener_rutas_firma($datos['modulo'], $anio, $mes);

    $directorio = $rutas['fisica'];

    if (!is_dir($directorio)) {
        mkdir($directorio, 0775, true);
    }

    $nombreArchivo = sprintf(
        "%s_%d_%s.png",
        $datos['modulo'],
        $datos['registro_id'],
        $datos['tipo']
    );

    $rutaCompleta = $directorio . DS . $nombreArchivo;

    if (!file_put_contents($rutaCompleta, $imagen)) {
        return false;
    }

    return [
        'archivo'      => $nombreArchivo,

        // Ruta pública para BD
        'ruta_bd' => $rutas['web'].'/'.$nombreArchivo,
        // Ruta física para uso interno
        'ruta_fisica'  => $rutaCompleta
    ];
}
function guardar_registro_firma($datos, $archivo){
    global $db;

    $modulo      = $db->escape($datos['modulo']);
    $registroId  = (int)$datos['registro_id'];
    $tipo        = $db->escape($datos['tipo']);
    $usuarioId   = (int)$datos['usuario_id'];
    $rutaArchivo = $db->escape($archivo['ruta_bd']);

    $sql = "INSERT INTO firmas (

                modulo,
                registro_id,
                tipo,
                usuario_id,
                archivo

            ) VALUES (

                '{$modulo}',
                {$registroId},
                '{$tipo}',
                {$usuarioId},
                '{$rutaArchivo}'

            )

            ON DUPLICATE KEY UPDATE

                archivo    = VALUES(archivo),
                usuario_id = VALUES(usuario_id),
                estatus    = 'ACTIVA'";

    return $db->query($sql);
}
function obtener_firma($modulo, $registroId, $tipo){
    global $db;

    $modulo = $db->escape($modulo);
    $registroId = (int)$registroId;
    $tipo = $db->escape($tipo);

    $sql = "SELECT
                f.*,
                u.name AS usuario_nombre,
                u.image AS usuario_imagen
            FROM firmas f
                INNER JOIN users u ON u.id = f.usuario_id
            WHERE
                f.modulo = '{$modulo}'
            AND f.registro_id = {$registroId}
            AND f.tipo = '{$tipo}'
            AND f.estatus = 'ACTIVA'
            LIMIT 1";

    $resultado = $db->query($sql);

    if (!$resultado || $db->num_rows($resultado) === 0) {
        return null;
    }

    return $db->fetch_assoc($resultado);
}
function existe_firma($modulo, $registroId, $tipo){
    return obtener_firma($modulo, $registroId, $tipo) !== false;
}
function obtener_tipos_firma($modulo){
    switch ($modulo) {

        case FirmaModulo::VACACIONES:
            return [
                FirmaTipo::EMPLEADO,
                FirmaTipo::JEFE,
                FirmaTipo::RH
            ];

        case FirmaModulo::EQUIPO:
            return [
                FirmaTipo::EMPLEADO,
                FirmaTipo::JEFE
            ];

        case FirmaModulo::CAPACITACION:
            return [
                FirmaTipo::EMPLEADO
            ];

        case FirmaModulo::TXT:
            return [
                FirmaTipo::EMPLEADO,
                FirmaTipo::JEFE,
                FirmaTipo::RH
            ];

        default:
            return [];
    }
}
function obtener_firmas($modulo, $registroId){
    global $db;

    $firmas = [];

    foreach (obtener_tipos_firma($modulo) as $tipo) {

        $firmas[$tipo] = [
            'firmada'   => false,
            'nombre'    => null,
            'fecha'     => null,
            'archivo'   => null,
            'usuario_id'=> null
        ];

    }

    $modulo     = $db->escape($modulo);
    $registroId = (int)$registroId;

    $sql = "SELECT
                f.*,
                u.name AS usuario_nombre,
                u.image AS usuario_imagen
            FROM firmas f
            INNER JOIN users u ON u.id = f.usuario_id
            WHERE
                f.modulo = '{$modulo}'
            AND f.registro_id = {$registroId}
            AND f.tipo = '{$tipo}'
            AND f.estatus = 'ACTIVA'
            LIMIT 1;";

    $resultado = $db->query($sql);

     if (!$resultado) {
        return $firmas;
    }

    while ($fila = $db->fetch_assoc($resultado)) {

        $firmas[$fila['tipo']] = [
            'firmada'    => true,
            'nombre'     => $fila['usuario_nombre'],
            'fecha'      => $fila['created_at'],
            'archivo'    => $fila['archivo'],
            'usuario_id' => $fila['usuario_id']
        ];

    }

    return $firmas;
}
function firma_pendiente($firmas, $tipo){
    return empty($firmas[$tipo]['firmada']);
}
function render_firma(array $config){
    $requeridos = [
        'modulo',
        'registro_id',
        'tipo',
        'usuario_id'
    ];

    foreach ($requeridos as $campo) {

        if (!array_key_exists($campo, $config)) {
            throw new InvalidArgumentException(
                "Falta el parámetro '{$campo}' en render_firma()."
            );
        }

    }

    $config = array_merge([
            'titulo'      => '',
            'readonly'    => false,
            'width'       => 400,
            'height'      => 180,
            'btnGuardar'  => 'Firmar',
            'btnLimpiar'  => 'Limpiar',
            'mostrarInfo' => true
        ], $config);

    $firma = obtener_firma(
        $config['modulo'],
        $config['registro_id'],
        $config['tipo']
    );

    // Identificador único para esta instancia
    $uid = 'firma_' . md5(
        $config['modulo'] . '_' .
        $config['registro_id'] . '_' .
        $config['tipo']
    );

    ob_start();

    include(__DIR__ . '../../templates/firmas.php');

    return ob_get_clean();
}
function render_firma_item($firma, $tipo){
    ob_start();
    include(__DIR__.'/../templates/card_firma_item.php');
    return ob_get_clean();
}
function obtener_rutas_firma($modulo, $anio = null, $mes = null){
    $anio ??= date('Y');
    $mes  ??= date('m');

    return [

        'fisica' => UPLOADS_PATH
                    . DS . 'firmas'
                    . DS . $modulo
                    . DS . $anio
                    . DS . $mes,

        'web' => UPLOADS_URL
                    . "/firmas/$modulo/$anio/$mes"

    ];
}