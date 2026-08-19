<?php
require_once('../includes/load.php');

if(isset($_POST['paro_id'])){

    $paro_id = (int)$_POST['paro_id'];

    $sql = "SELECT id, nombre FROM subparos WHERE paro_id = {$paro_id}";
    $result = $db->query($sql);

    while($row = $result->fetch_assoc()){
        echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
    }
}
?>