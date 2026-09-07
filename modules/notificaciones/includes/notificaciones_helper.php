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
    $mensaje
) {
    global $db;

    $usuario_id   = (int)$usuario_id;
    $solicitud_id = $solicitud_id !== null
        ? (int)$solicitud_id
        : null;

    if ($usuario_id <= 0) {
        return false;
    }

    $sql = "INSERT INTO notificaciones (
                usuario_id,
                solicitud_id,
                tipo,
                titulo,
                mensaje
            ) VALUES (
                '{$usuario_id}',
                " . (
                    $solicitud_id !== null
                        ? "'{$solicitud_id}'"
                        : "NULL"
                ) . ",
                '" . $db->escape($tipo) . "',
                '" . $db->escape($titulo) . "',
                '" . $db->escape($mensaje) . "'
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
function obtener_notificaciones_usuario($usuario_id, $limite = 10){
    global $db;

    $usuario_id = (int)$usuario_id;
    $limite = (int)$limite;

    if ($usuario_id <= 0) {
        return [];
    }

    if ($limite <= 0) {
        $limite = 10;
    }

    $sql = "SELECT
                id,
                usuario_id,
                solicitud_id,
                tipo,
                titulo,
                mensaje,
                fecha_creacion
            FROM notificaciones
            WHERE usuario_id = {$usuario_id}
            ORDER BY fecha_creacion DESC
            LIMIT {$limite}";

    return find_by_sql($sql);
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
            WHERE usuario_id = {$usuario_id}";

    $resultado = find_by_sql($sql);

    return (int)($resultado[0]['total'] ?? 0);
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

/**
 * Elimina una notificación del usuario.
 *
 * @param int $notificacion_id
 * @param int $usuario_id
 *
 * @return bool
 */
function eliminar_notificacion($notificacion_id, $usuario_id){
    global $db;

    $notificacion_id = (int)$notificacion_id;
    $usuario_id = (int)$usuario_id;

    if ($notificacion_id <= 0 || $usuario_id <= 0) {
        return false;
    }

    $sql = "DELETE FROM notificaciones
            WHERE id = '{$notificacion_id}'
            AND usuario_id = '{$usuario_id}'";

    return (bool)$db->query($sql);
}

function obtener_usuarios_por_nivel($nivel){
    global $db;

    $nivel = (int)$nivel;

    if ($nivel <= 0) {
        return [];
    }

    $sql = "SELECT
                id,
                name,
                email
            FROM users
            WHERE user_level = {$nivel}
              AND statusLaboral_id = 1
            ORDER BY id";

    return find_by_sql($sql);
}
function notificar_por_nivel($nivel, $solicitud_id, $tipo, $titulo, $mensaje) {
    
    $usuarios = obtener_usuarios_por_nivel($nivel);

    $destinatarios = [];

    foreach ($usuarios as $usuario) {

        $notificacion_id = crear_notificacion(
            $usuario['id'],
            $solicitud_id,
            $tipo,
            $titulo,
            $mensaje
        );

        if ($notificacion_id) {

            $destinatarios[] = [
                'id'    => (int)$usuario['id'],
                'nombre'=> $usuario['name'],
                'email' => trim($usuario['email'] ?? '')
            ];

        }
    }

    return $destinatarios;
}
function obtener_usuarios_por_responsabilidad($responsabilidad){
    global $db;

    $responsabilidad = $db->escape(
        trim($responsabilidad)
    );

    if(empty($responsabilidad)){
        return [];
    }

    $sql = "
        SELECT
            u.id,
            u.name,
            u.email

        FROM users u

        INNER JOIN user_responsabilidad ur
            ON ur.usuario_id = u.id

        INNER JOIN responsabilidades r
            ON r.id = ur.responsabilidad_id

        WHERE r.nombre = '{$responsabilidad}'
          AND r.activo = 1
          AND u.statusLaboral_id = 1

        ORDER BY u.name ASC
    ";

    return find_by_sql($sql);
}
function notificar_por_responsabilidad(
    $responsabilidad,
    $solicitud_id,
    $tipo,
    $titulo,
    $mensaje
){

    $usuarios =
        obtener_usuarios_por_responsabilidad(
            $responsabilidad
        );

    $destinatarios = [];

    foreach($usuarios as $usuario){

        $notificacion_id =
            crear_notificacion(
                (int)$usuario['id'],
                $solicitud_id,
                $tipo,
                $titulo,
                $mensaje
            );

        if($notificacion_id){

            $destinatarios[] = [
                'id' =>
                    (int)$usuario['id'],

                'nombre' =>
                    $usuario['name'],

                'email' =>
                    trim($usuario['email'] ?? '')
            ];

        }

    }

    return $destinatarios;
}