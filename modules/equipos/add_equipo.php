<?php
require_once __DIR__ . '/../../app/bootstrap.php';
page_require_level(2);

if(isset($_POST['add_product'])){

    try{

        $db->query("START TRANSACTION");

        // =====================
        // DATOS GENERALES
        // =====================
        $tipo   = (int)$_POST['tipo_equipo'];
        $marca  = $db->escape($_POST['marca']);
        $modelo = $db->escape($_POST['modelo']);
        $anio   = (int)$_POST['anio'];
        $descripcion = $db->escape($_POST['descripcion']);
        $costo  = (float)$_POST['costo'];
        $estado = $db->escape($_POST['estado']);
        $fecha  = $_POST['fecha_alta'];
        $observaciones = $db->escape($_POST['observaciones']);
        $usuarioAsigno = (int)$_SESSION['user_id'];

        // =====================
        // INSERT EQUIPO
        // =====================
        $sql = "INSERT INTO equipo
                (tipo_equipo, marca, modelo, anio, descripcion, costo,
                 estado_funciona, observaciones, fecha_alta, usuarioAsigno)
                VALUES
                ('$tipo','$marca','$modelo','$anio','$descripcion','$costo',
                 '$estado','$observaciones','$fecha','$usuarioAsigno')";

        if(!$db->query($sql)){
            throw new Exception("Error al guardar el equipo.");
        }

        $id = $db->insert_id();

        // =====================
        // OBTENER PREFIJO
        // =====================
        $sqlPrefijo = "SELECT prefijo
                       FROM equipo_tipo
                       WHERE id = {$tipo}
                       LIMIT 1";

        $resPrefijo = $db->query($sqlPrefijo);

        if(!$resPrefijo){
            throw new Exception("No fue posible obtener el prefijo.");
        }

        $fila = $resPrefijo->fetch_assoc();

        if(!$fila){
            throw new Exception("El tipo de equipo no tiene prefijo.");
        }

        $codigo = 'ROY'.$fila['prefijo'].str_pad($id,3,'0',STR_PAD_LEFT);

        // =====================
        // ACTUALIZAR CÓDIGO
        // =====================
        $sqlCodigo = "UPDATE equipo
                      SET codigo_equipo='{$codigo}'
                      WHERE id={$id}";

        if(!$db->query($sqlCodigo)){
            throw new Exception("No fue posible generar el código del equipo.");
        }

        // =====================
        // ARRAY BASE
        // =====================
        $datos = [
            'id_equipo' => $id,
            'numero_serie' => $_POST['serie'] ?? null,
            'telefono' => $_POST['telefono'] ?? null,
            'IMEI' => $_POST['imei'] ?? null,
            'tamanio_pantalla' => $_POST['pantalla'] ?? null,
            'RAM' => $_POST['ram'] ?? null,
            'disco_duro' => $_POST['disco'] ?? null,
            'procesador' => $_POST['procesador'] ?? null,
            's_o' => $_POST['so'] ?? null,
            'entradas' => $_POST['puertos'] ?? null,
            'IP' => $_POST['ip'] ?? null,
            'MAC' => $_POST['mac'] ?? null,
            'plan_id' => $_POST['tipo_plan'] ?? null
        ];

        // =====================
        // CAMPOS POR TIPO
        // =====================
        $campos_por_tipo = [

            1 => ['id_equipo','numero_serie','telefono','IMEI','tamanio_pantalla','RAM','disco_duro','procesador','s_o','entradas','MAC','plan_id'],
            2 => ['id_equipo','numero_serie','RAM','disco_duro','procesador','s_o','entradas'],
            3 => ['id_equipo','tamanio_pantalla','entradas'],
            4 => ['id_equipo','numero_serie','tamanio_pantalla','RAM','disco_duro','procesador','s_o','entradas','MAC'],
            5 => ['id_equipo','numero_serie','IP'],
            6 => ['id_equipo','numero_serie','IP']

        ];

        $campos_validos = $campos_por_tipo[$tipo] ?? [];

        $columnas = [];
        $valores = [];

        foreach($datos as $col => $val){

            if(!in_array($col, $campos_validos)){
                continue;
            }

            $columnas[] = $col;

            if($val === null || $val === ''){
                $valores[] = "NULL";
            }else{
                $valores[] = "'" . $db->escape($val) . "'";
            }
        }

        // =====================
        // INSERT DETALLE
        // =====================
        $sqlDetalle = "INSERT INTO equipo_detalle (" . implode(",", $columnas) . ")
                       VALUES (" . implode(",", $valores) . ")";

        if(!$db->query($sqlDetalle)){
            throw new Exception("No fue posible guardar el detalle del equipo.");
        }

        // =====================
        // TODO CORRECTO
        // =====================
        $db->query("COMMIT");
        $session->msg('s',"Equipo agregado correctamente.");
        redirect('equipos_inventario.php', false);

    }catch(Exception $e){

        $db->query("ROLLBACK");
        $session->msg('d',$e->getMessage());
        redirect('add_equipo.php', false);

    }

}else{

    redirect('add_equipo.php', false);

}
?>