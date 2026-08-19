<?php
require_once __DIR__ . '/../../../app/bootstrap.php';

if(isset($_POST['id'])){
  $id = (int)$_POST['id'];

  $sql = "SELECT
            e.id,
            e.codigo_equipo,
            et.nombre AS Tipo,
            e.marca,
            e.modelo,
            e.anio,
            e.descripcion,
            e.costo,
            e.estado_funciona,
            e.observaciones,
            e.fecha_alta,
            ed.numero_serie,
            ed.telefono,
            ed.IMEI,
            ed.tamanio_pantalla,
            ed.disco_duro,
            ed.RAM,
            ed.procesador,
            ed.s_o,
            ed.entradas,
            ed.IP,
            ed.MAC,
            d.zona AS departamento,
            ee.id_usuario AS asignado,
            u.name AS usuario
          FROM equipo e
            LEFT JOIN equipo_tipo et ON e.tipo_equipo = et.id
            LEFT JOIN equipo_detalle ed ON e.id = ed.id_equipo
            LEFT JOIN equipo_plan ep ON ed.plan_id = ep.id
            LEFT JOIN equipo_entrega ee ON e.id = ee.id_equipo
            LEFT JOIN equipo_ubicacion eu ON eu.id_equipo = e.id
            LEFT JOIN departamento d ON eu.id_departamento = d.ID
            LEFT JOIN users u ON ee.id_usuario = u.id
          WHERE e.id = '{$id}' LIMIT 1";

  $result = $db->query($sql);

  if($row = $result->fetch_assoc()){

    $tipo = strtolower($row['Tipo']);

    // Estado bonito
    $estado = $row['estado_funciona'] === "SI" 
      ? "<span class='label label-success'>Funciona</span>"
      : "<span class='label label-danger'>No funciona</span>";

    echo "<div class='container-fluid'>";

    // =====================
    // DATOS GENERALES
    // =====================
    echo "
    <div class='panel panel-primary'>
      <div class='panel-heading'><strong>Datos Generales</strong></div>
      <div class='panel-body'>
        <div class='row'>

          <div class='col-md-4'><label>Código</label><div class='well well-sm'>{$row['codigo_equipo']}</div></div>
          <div class='col-md-4'><label>Tipo</label><div class='well well-sm'>{$row['Tipo']}</div></div>
          <div class='col-md-4'><label>Marca</label><div class='well well-sm'>{$row['marca']}</div></div>

          <div class='col-md-4'><label>Modelo</label><div class='well well-sm'>{$row['modelo']}</div></div>
          <div class='col-md-4'><label>Año</label><div class='well well-sm'>{$row['anio']}</div></div>
          <div class='col-md-4'><label>Costo</label><div class='well well-sm'>$ {$row['costo']}</div></div>

          ".(!empty($row['descripcion']) ? "<div class='col-md-6'><label>Descripción</label><div class='well well-sm'>{$row['descripcion']}</div></div>" : "")."
          ".(!empty($row['observaciones']) ? "<div class='col-md-6'><label>Observaciones</label><div class='well well-sm'>{$row['observaciones']}</div></div>" : "")."

        </div>
      </div>
    </div>
    ";

    // =====================
    // TELÉFONO
    // =====================
    if($tipo == 'celular'){
      echo "
      <div class='panel panel-success'>
        <div class='panel-heading'><strong>Información del Teléfono</strong></div>
        <div class='panel-body'>
          <div class='row'>

            ".(!empty($row['numero_serie']) ? "<div class='col-md-4'><label>No. Serie</label><div class='well well-sm'>{$row['numero_serie']}</div></div>" : "")."
            ".(!empty($row['IMEI']) ? "<div class='col-md-4'><label>IMEI</label><div class='well well-sm'>{$row['IMEI']}</div></div>" : "")."
            ".(!empty($row['telefono']) ? "<div class='col-md-4'><label>Teléfono</label><div class='well well-sm'>{$row['telefono']}</div></div>" : "")."
            ".(!empty($row['tamanio_pantalla']) ? "<div class='col-md-4'><label>Pantalla</label><div class='well well-sm'>{$row['tamanio_pantalla']}</div></div>" : "")."
            ".(!empty($row['procesador']) ? "<div class='col-md-4'><label>Procesador</label><div class='well well-sm'>{$row['procesador']}</div></div>" : "")."
            ".(!empty($row['s_o']) ? "<div class='col-md-4'><label>Sistema Operativo</label><div class='well well-sm'>{$row['s_o']}</div></div>" : "")."
            ".(!empty($row['disco_duro']) ? "<div class='col-md-4'><label>Tamaño de Disco</label><div class='well well-sm'>{$row['disco_duro']}</div></div>" : "")."
            ".(!empty($row['RAM']) ? "<div class='col-md-4'><label>Memoria RAM</label><div class='well well-sm'>{$row['RAM']}</div></div>" : "")."

          </div>
        </div>
      </div>
      ";
    }

    // =====================
    // COMPUTADORA / LAPTOP / IMPRESORA / ZEBRA
    // =====================
    if($tipo == 'laptop' || $tipo == 'computadora' || $tipo == 'pc' || $tipo == 'impresora' || $tipo == 'zebra'){
      echo "
      <div class='panel panel-info'>
        <div class='panel-heading'><strong>Información del Dispositivo</strong></div>
        <div class='panel-body'>
          <div class='row'>
            ".(!empty($row['numero_serie']) ? "<div class='col-md-4'><label>No. Serie</label><div class='well well-sm'>{$row['numero_serie']}</div></div>" : "")."
            ".(!empty($row['s_o']) ? "<div class='col-md-4'><label>Sistema Operativo</label><div class='well well-sm'>{$row['s_o']}</div></div>" : "")."
            ".(!empty($row['procesador']) ? "<div class='col-md-4'><label>Procesador</label><div class='well well-sm'>{$row['procesador']}</div></div>" : "")."
            ".(!empty($row['disco_duro']) ? "<div class='col-md-4'><label>Disco Duro</label><div class='well well-sm'>{$row['disco_duro']}</div></div>" : "")."
            ".(!empty($row['RAM']) ? "<div class='col-md-4'><label>Memoria RAM</label><div class='well well-sm'>{$row['RAM']}</div></div>" : "")."
          </div>
        </div>
      </div>
      ";
    }

    // =====================
    // RED
    // =====================
    if(!empty($row['IP']) || !empty($row['MAC'])){
      echo "
      <div class='panel panel-warning'>
        <div class='panel-heading'><strong>Red</strong></div>
        <div class='panel-body'>
          <div class='row'>

            ".(!empty($row['IP']) ? "<div class='col-md-6'><label>IP</label><div class='well well-sm'>{$row['IP']}</div></div>" : "")."
            ".(!empty($row['MAC']) ? "<div class='col-md-6'><label>MAC (WIFI)</label><div class='well well-sm'>{$row['MAC']}</div></div>" : "")."

          </div>
        </div>
      </div>
      ";
    }

    // =====================
    // ESTADO
    // =====================
    echo "
    <div class='panel panel-default'>
      <div class='panel-heading'><strong>Estado y Control</strong></div>
      <div class='panel-body'>
        <div class='row'>
          <div class='col-md-4'><label>Estado</label><div class='well well-sm'>{$estado}</div></div>";
          
          if (empty($row['asignado'])){
            if($tipo == 'impresora' || $tipo == 'zebra'){
              echo "<div class='col-md-4'><label>Asignado a</label><div class='well well-sm'>".$row['departamento']."</div></div>";
            }else{
              echo "<div class='col-md-4'><label>Asignado a</label><div class='well well-sm'>Sin Asignar</div></div>";
            }
          } else {
            echo "<div class='col-md-4'><label>Asignado a</label><div class='well well-sm'>".$row['asignado']." - ".$row['usuario']."</div></div>";
          }

          echo "
          <div class='col-md-4'><label>Fecha Alta</label><div class='well well-sm'>{$row['fecha_alta']}</div></div>
        </div>
      </div>
    </div>
    ";

    echo "</div>";

  } else {
    echo "<div class='alert alert-danger'>No se encontraron datos</div>";
  }
}
?>