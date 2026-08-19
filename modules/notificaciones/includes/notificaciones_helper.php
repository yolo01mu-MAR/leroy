<?php

/**
 * Crea una nueva notificación.
 *
 * @param int         $usuario_id
 * @param int|null    $solicitud_id
 * @param string      $tipo
 * @param string      $titulo
 * @param string      $mensaje
 * @param string|null $url
 * @return int|false ID de la notificación o false si falla.
 */
function crear_notificacion(
    $usuario_id,
    $solicitud_id,
    $tipo,
    $titulo,
    $mensaje,
    $url = null
) {
    global $db;

    $usuario_id  = (int)$usuario_id;
    $solicitud_id = $solicitud_id !== null ? (int)$solicitud_id : null;

    if ($usuario_id <= 0) {
        return false;
    }

    $sql = "INSERT INTO notificaciones (
                usuario_id,
                solicitud_id,
                tipo,
                titulo,
                mensaje,
                url
            ) VALUES (
                '{$usuario_id}',
                " . ($solicitud_id !== null ? "'{$solicitud_id}'" : "NULL") . ",
                '" . $db->escape($tipo) . "',
                '" . $db->escape($titulo) . "',
                '" . $db->escape($mensaje) . "',
                " . ($url !== null
                    ? "'" . $db->escape($url) . "'"
                    : "NULL") . "
            )";

    if ($db->query($sql)) {
        return $db->insert_id();
    }

    return false;
}

/**
 * Obtiene las notificaciones de un usuario.
 *
 * @param int $usuario_id
 * @param int $limite
 * @return array
 */
function obtener_notificaciones_usuario($usuario_id, $limite = 20)
{
    global $db;

    $usuario_id = (int)$usuario_id;
    $limite = (int)$limite;

    if ($usuario_id <= 0) {
        return [];
    }

    if ($limite <= 0) {
        $limite = 20;
    }

    $sql = "SELECT
                id,
                usuario_id,
                solicitud_id,
                tipo,
                titulo,
                mensaje,
                url,
                leida,
                fecha_creacion,
                fecha_lectura
            FROM notificaciones
            WHERE usuario_id = '{$usuario_id}'
            ORDER BY fecha_creacion DESC
            LIMIT {$limite}";

    $resultado = $db->query($sql);

    if (!$resultado) {
        return [];
    }

    $notificaciones = [];

    while ($fila = $db->fetch_assoc($resultado)) {
        $notificaciones[] = $fila;
    }

    return $notificaciones;
}

/**
 * Cuenta las notificaciones no leídas.
 *
 * @param int $usuario_id
 * @return int
 */
function contar_notificaciones_no_leidas($usuario_id){
    global $db;

    $usuario_id = (int)$usuario_id;

    if ($usuario_id <= 0) {
        return 0;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM notificaciones
            WHERE usuario_id = '{$usuario_id}'
            AND vista = 0";

    $resultado = $db->query($sql);

    if (!$resultado) {
        return 0;
    }

    $fila = $db->fetch_assoc($resultado);

    return (int)$fila['total'];
}

/**
 * Marca como vistas todas las notificaciones
 * pendientes de un usuario.
 *
 * Esto NO las marca como leídas.
 *
 * @param int $usuario_id
 * @return bool
 */
function marcar_notificaciones_vistas($usuario_id){
    global $db;

    $usuario_id = (int)$usuario_id;

    if ($usuario_id <= 0) {
        return false;
    }

    $sql = "UPDATE notificaciones
            SET vista = 1
            WHERE usuario_id = '{$usuario_id}'
            AND vista = 0";

    return (bool)$db->query($sql);
}

/**
 * Marca una notificación como leída.
 *
 * @param int $notificacion_id
 * @param int $usuario_id
 * @return bool
 */
function marcar_notificacion_leida($notificacion_id, $usuario_id){
    global $db;

    $notificacion_id = (int)$notificacion_id;
    $usuario_id = (int)$usuario_id;

    if ($notificacion_id <= 0 || $usuario_id <= 0) {
        return false;
    }

    $sql = "UPDATE notificaciones
            SET
                vista = 1,
                leida = 1,
                fecha_lectura = NOW()
            WHERE id = '{$notificacion_id}'
            AND usuario_id = '{$usuario_id}'";

    return (bool)$db->query($sql);
}

/**
 * Marca todas las notificaciones de un usuario como leídas.
 *
 * @param int $usuario_id
 * @return bool
 */
function marcar_todas_notificaciones_leidas($usuario_id){
    global $db;

    $usuario_id = (int)$usuario_id;

    if ($usuario_id <= 0) {
        return false;
    }

    $sql = "UPDATE notificaciones
            SET
                leida = 1,
                fecha_lectura = NOW()
            WHERE usuario_id = '{$usuario_id}'
            AND leida = 0";

    return (bool)$db->query($sql);
}