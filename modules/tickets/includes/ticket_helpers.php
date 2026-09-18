<?php
    function crear_ticket($usuarioId, $categoriaId, $equipoId, $hardwareDetalle, $asunto, $descripcion){
        global $db;

        $equipoSql = is_null($equipoId) ? "NULL" : (int)$equipoId;
        $hardwareSql = is_null($hardwareDetalle) ? "NULL" : "'".$db->escape($hardwareDetalle)."'";

        $sql = "INSERT INTO ticket
        (
            usuario_id,
            equipo_id,
            hardware_detalle,
            categoria_id,
            asunto,
            descripcion,
            estatus_id,
            fecha_creacion,
            creado_por
        )
        VALUES
        (
            {$usuarioId},
            {$equipoSql},
            {$hardwareSql},
            {$categoriaId},
            '{$asunto}',
            '{$descripcion}',
            1,
            NOW(),
            {$usuarioId}
        )";

        if(!$db->query($sql)){
            return [
                'ok' => false,
                'mensaje' => 'No fue posible crear el ticket.'
            ];
        }

        // Obtener ID
        $idResult = find_by_sql("SELECT LAST_INSERT_ID() AS id");
        $nuevoId = (int)$idResult[0]['id'];

        // Obtener folio
        $datos = find_by_sql("
            SELECT folio
            FROM ticket
            WHERE id = {$nuevoId}
            LIMIT 1
        ");

        $folio = $datos[0]['folio'];

        // Registrar historial
        $sqlHist = "INSERT INTO ticket_historico
                    (
                        ticket_id,
                        fecha,
                        accion_id,
                        estatus_anterior,
                        estatus_nuevo,
                        usuario_movimiento
                    )
                    VALUES
                    (
                        {$nuevoId},
                        NOW(),
                        1,
                        NULL,
                        1,
                        {$usuarioId}
                    )";

        if($db->query($sqlHist)){

            $idHistorico = find_by_sql("
                SELECT LAST_INSERT_ID() AS id
            ");

            $idHistorico = (int)$idHistorico[0]['id'];

            $usuarios = get_usuarios_sistemas();

            foreach($usuarios as $usuario){

                crear_notificacion(
                    $nuevoId,
                    $usuario['id'],
                    'NUEVO_TICKET',
                    $idHistorico
                );

            }

        }

        return [
            'ok'     => true,
            'id'     => $nuevoId,
            'folio'  => $folio,
            'mensaje'=> 'Ticket creado correctamente.'
        ];
    }
    function cerrar_ticket($ticketId, $usuarioId){
        global $db;

        // Obtener estado actual
        $estado = find_by_sql("
            SELECT estatus_id
            FROM ticket
            WHERE id = {$ticketId}
            LIMIT 1
        ");

        if(empty($estado)){
            return [
                'ok' => false,
                'mensaje' => 'El ticket no existe.'
            ];
        }

        $estado = $estado[0];

        // Cambiar a CERRADO
        if(!$db->query("
            UPDATE ticket
            SET estatus_id = 5,
                fecha_cierre = NOW()
            WHERE id = {$ticketId}
        ")){
            return [
                'ok' => false,
                'mensaje' => 'No fue posible cerrar el ticket.'
            ];
        }

        // Registrar historial
        $db->query("
            INSERT INTO ticket_historico
            (
                ticket_id,
                fecha,
                accion_id,
                estatus_anterior,
                estatus_nuevo,
                usuario_movimiento
            )
            VALUES
            (
                {$ticketId},
                NOW(),
                7,
                {$estado['estatus_id']},
                5,
                {$usuarioId}
            )
        ");

        return [
            'ok' => true,
            'mensaje' => 'Gracias por confirmar la solución.'
        ];
    }
    function asignar_ticket($ticketId, $asignadoA, $prioridadId, $usuarioMovimiento){
        global $db;

        // Obtener datos actuales del ticket
        $sqlActual = "SELECT asignado_a, prioridad_id, estatus_id
                    FROM ticket
                    WHERE id = {$ticketId}
                    LIMIT 1";

        $actual = find_by_sql($sqlActual);

        if (empty($actual)) {
            return [
                'ok' => false,
                'mensaje' => 'El ticket no existe.'
            ];
        }

        $actual = $actual[0];

        // Nuevos valores
        $nuevoAsignado = ($asignadoA == '' || is_null($asignadoA)) ? NULL : (int)$asignadoA;
        $nuevaPrioridad = (int)$prioridadId;

        $estatusAnterior = (int)$actual['estatus_id'];
        $estatusNuevo    = $estatusAnterior;

        // Si es la primera asignación pasa automáticamente a EN_PROCESO
        if (empty($actual['asignado_a']) && !empty($nuevoAsignado)) {
            $estatusNuevo = 2; // EN_PROCESO
        }

        $valorAsignadoSql = is_null($nuevoAsignado) ? "NULL" : $nuevoAsignado;

        // Actualizar ticket
        $sqlUpdate = "UPDATE ticket
                    SET asignado_a = {$valorAsignadoSql},
                        prioridad_id = {$nuevaPrioridad},
                        estatus_id   = {$estatusNuevo}
                    WHERE id = {$ticketId}";

        if ($db->query($sqlUpdate)) {

            /* ===========================================
            ASIGNACIÓN / REASIGNACIÓN
            ============================================*/

            if ((int)$actual['asignado_a'] != (int)$nuevoAsignado) {

                // 3 = ASIGNADO
                // 4 = REASIGNADO
                $accion = empty($actual['asignado_a']) ? 3 : 4;

                $sqlHistorico = "INSERT INTO ticket_historico
                                (
                                    ticket_id,
                                    fecha,
                                    accion_id,
                                    estatus_anterior,
                                    estatus_nuevo,
                                    usuario_movimiento
                                )
                                VALUES
                                (
                                    {$ticketId},
                                    NOW(),
                                    {$accion},
                                    {$estatusAnterior},
                                    {$estatusNuevo},
                                    {$usuarioMovimiento}
                                )";

                if ($db->query($sqlHistorico)) {

                    $idHistorico = find_by_sql("
                        SELECT LAST_INSERT_ID() AS id
                    ");

                    $idHistorico = (int)$idHistorico[0]['id'];

                    crear_notificacion(
                        $ticketId,
                        $nuevoAsignado,
                        empty($actual['asignado_a']) ? 'ASIGNADO' : 'REASIGNADO',
                        $idHistorico
                    );

                }
            }

            /* ===========================================
            CAMBIO DE ESTADO
            ============================================*/

            if ($estatusAnterior != $estatusNuevo) {

                $sqlHistoricoEstado = "INSERT INTO ticket_historico
                                    (
                                        ticket_id,
                                        fecha,
                                        accion_id,
                                        estatus_anterior,
                                        estatus_nuevo,
                                        usuario_movimiento
                                    )
                                    VALUES
                                    (
                                        {$ticketId},
                                        NOW(),
                                        2,
                                        {$estatusAnterior},
                                        {$estatusNuevo},
                                        {$usuarioMovimiento}
                                    )";

                $db->query($sqlHistoricoEstado);
            }

            return [
                'ok' => true,
                'mensaje' => 'Ticket actualizado correctamente.'
            ];

        } else {

            return [
                'ok' => false,
                'mensaje' => 'No fue posible actualizar el ticket.'
            ];

        }

    }
    function agregar_seguimiento($ticketId, $usuarioId, $comentario, $estatusActual){
        
        global $db;
        $comentario = strtoupper($db->escape($comentario));

        if(trim($comentario) == ''){
            return [
                'ok' => false,
                'mensaje' => 'Escribe un comentario.'
            ];
        }

        if($comentario != ''){

            $sql = "INSERT INTO ticket_comentario
                    (
                        ticket_id,
                        usuario_id,
                        comentario,
                        tipo,
                        fecha,
                        requiere_respuesta,
                        respondido
                    )
                    VALUES
                    (
                        {$ticketId},
                        {$usuarioId},
                        '{$comentario}',
                        1,
                        NOW(),
                        0,
                        0
                    )";

            if($db->query($sql)){

                $comentarioId = $db->insert_id();

                $sqlHist = "INSERT INTO ticket_historico
                            (
                                ticket_id,
                                fecha,
                                accion_id,
                                estatus_anterior,
                                estatus_nuevo,
                                usuario_movimiento
                            )
                            VALUES
                            (
                                {$ticketId},
                                NOW(),
                                5,
                                {$estatusActual},
                                {$estatusActual},
                                {$usuarioId}
                            )";

                if($db->query($sqlHist)){

                    $historicoId = $db->insert_id();

                    // Obtener el dueño del ticket
                    $ticket = find_by_sql("
                        SELECT usuario_id
                        FROM ticket
                        WHERE id = {$ticketId}
                        LIMIT 1
                    ");

                    if(!empty($ticket)){

                        crear_notificacion(
                            $ticketId,
                            (int)$ticket[0]['usuario_id'],
                            'RESPUESTA_SISTEMAS',
                            $historicoId
                        );

                    }

                }

                return [
                    'ok' => true,
                    'mensaje' => 'Seguimiento registrado.',
                    'comentario_id' => $comentarioId,
                    'historicos' => [
                        $historicoId
                    ]
                ];
            }else{
                return [
                    'ok' => false,
                    'mensaje' => 'No fue posible registrar el seguimiento.'
                ];
            }
        }
    }
    function solicitar_respuesta($ticketId, $usuarioId, $comentario){

        global $db;

        $comentario = strtoupper($db->escape($comentario));

        if(trim($comentario) == ''){
            return [
                'ok' => false,
                'mensaje' => 'Escribe un comentario.'
            ];
        }

        // Obtener estado actual
        $sqlEstado = "SELECT estatus_id
                    FROM ticket
                    WHERE id = {$ticketId}
                    LIMIT 1";

        $estado = find_by_sql($sqlEstado);

        if(empty($estado)){
            return [
                'ok' => false,
                'mensaje' => 'El ticket no existe.'
            ];
        }

        $estado = $estado[0];

        // Guardar comentario
        $sql = "INSERT INTO ticket_comentario
                (
                    ticket_id,
                    usuario_id,
                    comentario,
                    tipo,
                    fecha,
                    requiere_respuesta,
                    respondido
                )
                VALUES
                (
                    {$ticketId},
                    {$usuarioId},
                    '{$comentario}',
                    1,
                    NOW(),
                    1,
                    0
                )";

        if(!$db->query($sql)){
            return [
                'ok' => false,
                'mensaje' => 'No fue posible registrar la solicitud.'
            ];
        }

        // ID del comentario
        $comentarioId = $db->insert_id();

        // Cambiar ticket a ESPERA_USUARIO
        $sqlUpdate = "UPDATE ticket
                    SET estatus_id = 3
                    WHERE id = {$ticketId}";

        $db->query($sqlUpdate);

        // Historial: comentario agregado
        $sqlHist = "INSERT INTO ticket_historico
                    (
                        ticket_id,
                        fecha,
                        accion_id,
                        estatus_anterior,
                        estatus_nuevo,
                        usuario_movimiento
                    )
                    VALUES
                    (
                        {$ticketId},
                        NOW(),
                        5,
                        {$estado['estatus_id']},
                        {$estado['estatus_id']},
                        {$usuarioId}
                    )";

        $db->query($sqlHist);
        $hist1 = $db->insert_id();

        // Historial: cambio de estado
        $sqlHistEstado = "INSERT INTO ticket_historico
                        (
                            ticket_id,
                            fecha,
                            accion_id,
                            estatus_anterior,
                            estatus_nuevo,
                            usuario_movimiento
                        )
                        VALUES
                        (
                            {$ticketId},
                            NOW(),
                            2,
                            {$estado['estatus_id']},
                            3,
                            {$usuarioId}
                        )";

        $db->query($sqlHistEstado);
        $hist2 = $db->insert_id();

        return [
            'ok' => true,
            'mensaje' => 'Se solicitó respuesta al usuario.',
            'comentario_id' => $comentarioId,
            'historicos' => [$hist1, $hist2]
        ];
    }
    function resolver_ticket($ticketId, $usuarioId, $comentario){

        global $db;

        $comentario = strtoupper($db->escape($comentario));

        if(trim($comentario) == ''){
            return [
                'ok' => false,
                'mensaje' => 'Escribe un comentario.'
            ];
        }

        // Obtener estado actual
        $sqlEstado = "SELECT estatus_id
                    FROM ticket
                    WHERE id = {$ticketId}
                    LIMIT 1";

        $estado = find_by_sql($sqlEstado);

        if(empty($estado)){
            return [
                'ok' => false,
                'mensaje' => 'El ticket no existe.'
            ];
        }

        $estado = $estado[0];

        // Guardar comentario
        $sql = "INSERT INTO ticket_comentario
                (
                    ticket_id,
                    usuario_id,
                    comentario,
                    tipo,
                    fecha,
                    requiere_respuesta,
                    respondido
                )
                VALUES
                (
                    {$ticketId},
                    {$usuarioId},
                    '{$comentario}',
                    1,
                    NOW(),
                    0,
                    0
                )";

        if(!$db->query($sql)){
            return [
                'ok' => false,
                'mensaje' => 'No fue posible resolver el ticket.'
            ];
        }

        // ID del comentario
        $comentarioId = $db->insert_id();

        // Cambiar estado a RESUELTO
        $sqlUpdate = "UPDATE ticket
                    SET estatus_id = 4
                    WHERE id = {$ticketId}";

        $db->query($sqlUpdate);

        // Histórico: comentario
        $sqlHist = "INSERT INTO ticket_historico
                    (
                        ticket_id,
                        fecha,
                        accion_id,
                        estatus_anterior,
                        estatus_nuevo,
                        usuario_movimiento
                    )
                    VALUES
                    (
                        {$ticketId},
                        NOW(),
                        5,
                        {$estado['estatus_id']},
                        {$estado['estatus_id']},
                        {$usuarioId}
                    )";

        $db->query($sqlHist);
        $hist1 = $db->insert_id();

        // Histórico: cambio de estado
        $sqlHistEstado = "INSERT INTO ticket_historico
                        (
                            ticket_id,
                            fecha,
                            accion_id,
                            estatus_anterior,
                            estatus_nuevo,
                            usuario_movimiento
                        )
                        VALUES
                        (
                            {$ticketId},
                            NOW(),
                            2,
                            {$estado['estatus_id']},
                            4,
                            {$usuarioId}
                        )";

        $db->query($sqlHistEstado);
        $hist2 = $db->insert_id();

        return [
            'ok' => true,
            'mensaje' => 'Ticket marcado como resuelto.',
            'comentario_id' => $comentarioId,
            'historicos' => [$hist1, $hist2]
        ];
    }
    function responder_solicitud($ticketId, $usuarioId, $comentario){
        global $db;

        $comentario = strtoupper($db->escape($comentario));

        if(trim($comentario) == ''){
            return [
                'ok' => false,
                'mensaje' => 'Escribe un comentario.'
            ];
        }

        // Obtener estado actual
        $sqlEstado = "SELECT estatus_id
                    FROM ticket
                    WHERE id = {$ticketId}
                    LIMIT 1";

        $estado = find_by_sql($sqlEstado);

        if(empty($estado)){
            return [
                'ok' => false,
                'mensaje' => 'El ticket no existe.'
            ];
        }

        $estado = $estado[0];

        // Guardar comentario
        $sql = "INSERT INTO ticket_comentario
                (
                    ticket_id,
                    usuario_id,
                    comentario,
                    requiere_respuesta,
                    respondido,
                    fecha
                )
                VALUES
                (
                    {$ticketId},
                    {$usuarioId},
                    '{$comentario}',
                    0,
                    0,
                    NOW()
                )";

        if(!$db->query($sql)){
            return [
                'ok' => false,
                'mensaje' => 'No fue posible enviar la respuesta.'
            ];
        }

        $comentarioId = $db->insert_id();

        $sqlPendiente = "SELECT id
                        FROM ticket_comentario
                        WHERE ticket_id = {$ticketId}
                        AND requiere_respuesta = 1
                        AND respondido = 0
                        LIMIT 1";

        $pendiente = find_by_sql($sqlPendiente);

        $comentarioRespondidoId = !empty($pendiente) ? (int)$pendiente[0]['id'] : 0;

        // Marcar solicitud anterior como respondida
        $sqlResponder = "UPDATE ticket_comentario
                        SET
                            requiere_respuesta = 0,
                            respondido = 1
                        WHERE ticket_id = {$ticketId}
                        AND requiere_respuesta = 1
                        AND respondido = 0";

        $db->query($sqlResponder);

        // Regresar el ticket a EN_PROCESO
        $sqlTicket = "UPDATE ticket
                    SET estatus_id = 2
                    WHERE id = {$ticketId}";

        $db->query($sqlTicket);

        // Histórico comentario
        $sqlHist = "INSERT INTO ticket_historico
                    (
                        ticket_id,
                        fecha,
                        accion_id,
                        estatus_anterior,
                        estatus_nuevo,
                        usuario_movimiento
                    )
                    VALUES
                    (
                        {$ticketId},
                        NOW(),
                        5,
                        {$estado['estatus_id']},
                        {$estado['estatus_id']},
                        {$usuarioId}
                    )";

        $db->query($sqlHist);

        $historicoComentarioId = $db->insert_id();

        // Histórico cambio de estado
        $sqlHistEstado = "INSERT INTO ticket_historico
                        (
                            ticket_id,
                            fecha,
                            accion_id,
                            estatus_anterior,
                            estatus_nuevo,
                            usuario_movimiento
                        )
                        VALUES
                        (
                            {$ticketId},
                            NOW(),
                            2,
                            {$estado['estatus_id']},
                            2,
                            {$usuarioId}
                        )";

        $db->query($sqlHistEstado);

        $historicoEstadoId = $db->insert_id();
        $historicoId = $db->insert_id();

        return [
            'ok' => true,
            'mensaje' => 'Respuesta enviada correctamente.',
            'comentario_id' => $comentarioId,
            'comentario_respondido_id' => $comentarioRespondidoId,
            'historicos' => [
                $historicoComentarioId,
                $historicoEstadoId
            ]
        ];
    }
    function reabrir_ticket($ticketId, $usuarioId, $comentario){
        global $db;

        $comentario = strtoupper($db->escape($comentario));

        if(trim($comentario) == ''){
            return [
                'ok' => false,
                'mensaje' => 'Escribe un comentario.'
            ];
        }

        // Obtener estado actual
        $estado = find_by_sql("
            SELECT estatus_id
            FROM ticket
            WHERE id = {$ticketId}
            LIMIT 1
        ");

        if(empty($estado)){
            return [
                'ok' => false,
                'mensaje' => 'El ticket no existe.'
            ];
        }

        $estado = $estado[0];

        // Guardar comentario
        $sqlComentario = "
            INSERT INTO ticket_comentario
            (
                ticket_id,
                usuario_id,
                comentario,
                requiere_respuesta,
                respondido,
                fecha
            )
            VALUES
            (
                {$ticketId},
                {$usuarioId},
                '{$comentario}',
                0,
                0,
                NOW()
            )
        ";

        if(!$db->query($sqlComentario)){
            return [
                'ok' => false,
                'mensaje' => 'No fue posible reabrir el ticket.'
            ];
        }

        // Regresar a EN_PROCESO
        $db->query("
            UPDATE ticket
            SET estatus_id = 2
            WHERE id = {$ticketId}
        ");

        // Historial comentario
        $db->query("
            INSERT INTO ticket_historico
            (
                ticket_id,
                fecha,
                accion_id,
                estatus_anterior,
                estatus_nuevo,
                usuario_movimiento
            )
            VALUES
            (
                {$ticketId},
                NOW(),
                5,
                {$estado['estatus_id']},
                {$estado['estatus_id']},
                {$usuarioId}
            )
        ");

        // Historial cambio de estado
        $db->query("
            INSERT INTO ticket_historico
            (
                ticket_id,
                fecha,
                accion_id,
                estatus_anterior,
                estatus_nuevo,
                usuario_movimiento
            )
            VALUES
            (
                {$ticketId},
                NOW(),
                2,
                {$estado['estatus_id']},
                2,
                {$usuarioId}
            )
        ");

        return [
            'ok' => true,
            'mensaje' => 'El ticket fue reabierto.'
        ];
    }
    function render_ticket_comentario($comentario, $ticketUsuarioId){

        if(empty($comentario)){
            return '';
        }

        ob_start();

        include(__DIR__.'/../templates/ticket_comentario.php');

        return ob_get_clean();
    }
    function get_ticket_comentario($comentarioId){
        $comentarioId = (int)$comentarioId;

        $sql = "SELECT
                    tc.*,
                    u.name
                FROM ticket_comentario tc
                INNER JOIN users u
                    ON u.id = tc.usuario_id
                WHERE tc.id = {$comentarioId}
                LIMIT 1";

        $resultado = find_by_sql($sql);

        if(empty($resultado)){
            return null;
        }

        return $resultado[0];
    }
    function render_ticket_historico($historico){

        if(empty($historico)){
            return '';
        }

        ob_start();

        include(__DIR__.'/../templates/ticket_historico.php');

        return ob_get_clean();
    }
    function get_ticket_historico_item($historicoId){

        $historicoId = (int)$historicoId;

        $sql = "SELECT
                    th.*,
                    ta.nombre_accion,
                    te1.nombre_estatus AS anterior,
                    te2.nombre_estatus AS nuevo,
                    u.name
                FROM ticket_historico th
                    INNER JOIN ticket_accion ta
                        ON ta.id = th.accion_id
                    LEFT JOIN ticket_estatus te1
                        ON te1.id = th.estatus_anterior
                    LEFT JOIN ticket_estatus te2
                        ON te2.id = th.estatus_nuevo
                    LEFT JOIN users u
                        ON u.id = th.usuario_movimiento
                WHERE th.id = {$historicoId}
                LIMIT 1";

        $resultado = find_by_sql($sql);

        if(empty($resultado)){
            return null;
        }

        return $resultado[0];
    }
    function crear_notificacion($ticketId, $usuarioId, $tipo, $historicoId = null){

        global $db;

        $ticketId     = (int)$ticketId;
        $usuarioId    = (int)$usuarioId;
        $historicoSql = is_null($historicoId) ? "NULL" : (int)$historicoId;

        $tipo = $db->escape($tipo);

        $sql = "INSERT INTO ticket_notificacion
                (
                    ticket_id,
                    usuario_id,
                    historico_id,
                    tipo
                )
                VALUES
                (
                    {$ticketId},
                    {$usuarioId},
                    {$historicoSql},
                    '{$tipo}'
                )";

        return $db->query($sql);
    }

    function get_ticket_comentarios_nuevos($ticketId, $ultimoComentario){

        global $db;

        $ticketId = (int)$ticketId;
        $ultimoComentario = (int)$ultimoComentario;

        $sql = "SELECT
                    tc.*,
                    u.name
                FROM ticket_comentario tc
                INNER JOIN users u
                    ON u.id = tc.usuario_id
                WHERE tc.ticket_id = {$ticketId}
                AND tc.id > {$ultimoComentario}
                ORDER BY tc.id ASC";

        return find_by_sql($sql);
    }


    // Notificaciones
    function get_usuarios_sistemas(){

        return find_by_sql("
            SELECT id
            FROM users
            WHERE user_level = 1
            AND statusLaboral_id = 1
        ");
    }
?>