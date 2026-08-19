<?php
  
$page_title = 'Reporte de Producción';
require_once __DIR__ . '/../../app/bootstrap.php';

page_require_level(4);

$idReporte = '';
$maquina_id = '';
$fechaReporte = '';
$turnoSeleccionado = '';
$supervisorSeleccionado = '';
$operadorSeleccionado = '';

$produccion_guardada = [];
$fallas_guardadas = [];

if(isset($_GET['id'])){

    $idReporte = (int)$_GET['id'];

    $sql = "SELECT r.*, 
              m.nombre as maquina_nombre
            FROM reportes r
            JOIN maquinas m ON m.id = r.maquina_id
            WHERE r.id = {$idReporte}";

    $resultado = $db->query($sql);

    if($row = $resultado->fetch_assoc()){

        $maquina_id = $row['maquina_nombre'];
        $fechaReporte = $row['fecha'];
        $turnoSeleccionado = $row['turno_id'];
        $supervisorSeleccionado = $row['supervisor_id'];
        $operadorSeleccionado = $row['operador_id'];
    }
    // PRODUCCIÓN
    $sqlProd = "SELECT 
                    rp.*,
                    tg.descripcion,
                    tg.piezasXhora
                FROM registros_produccion rp
                JOIN tabla_gasas tg ON tg.ID = rp.producto_id
                WHERE rp.reporte_id = {$idReporte}
                ORDER BY rp.hora_inicio ASC";

    $resultProd = $db->query($sqlProd);

    while($rowProd = $resultProd->fetch_assoc()){
        $produccion_guardada[] = $rowProd;
    }
    // Fallas
    $sqlFallas = "SELECT 
                      rp.id,
                      rp.reporte_id,
                      rp.paro_id,
                      rp.subParos_id,
                      rp.minutos,
                      pg.nombre AS paro_nombre,
                      sp.nombre AS subparo_nombre
                  FROM registros_paros rp
                  INNER JOIN paro_general pg ON pg.id = rp.paro_id
                  INNER JOIN subparos sp ON sp.id = rp.subParos_id
                  WHERE rp.reporte_id = {$idReporte}";

    $resultFallas = $db->query($sqlFallas);

    while($rowFalla = $resultFallas->fetch_assoc()){
        $fallas_guardadas[] = $rowFalla;
    }
}
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['autoguardado'])){

  $reporte_id = $_POST['reporte_id'];

  $turno = !empty($_POST['grupo']) ? (int)$_POST['grupo'] : null;
  $supervisor = !empty($_POST['supervisor']) ? (int)$_POST['supervisor'] : null;
  $operador = !empty($_POST['operador']) ? (int)$_POST['operador'] : null;

  $produccion = isset($_POST['produccion_json'])
      ? json_decode($_POST['produccion_json'], true)
      : [];

  $fallas = isset($_POST['fallas_json'])
      ? json_decode($_POST['fallas_json'], true)
      : [];

  $ok = autoguardar_reporte_completo(
      $reporte_id,
      $turno,
      $supervisor,
      $operador,
      $produccion,
      $fallas
  );

  echo $ok ? "AUTO OK" : "ERROR";
  exit();
}
$grupos = get_grupos();
$supervisor = get_supervisor();
$paros = get_paros();
$productosGasas = find_all('tabla_gasas');

?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading encabezado-reporte" 
          style="display:flex; align-items:center; justify-content:space-between;">

        <!-- IZQUIERDA: REGRESAR -->
        <a href="javascript:history.back()" class="btn btn-default btn-sm">
          <span class="glyphicon glyphicon-arrow-left"></span> Regresar
        </a>

        <!-- CENTRO: TITULO -->
        <strong style="position:absolute; left:50%; transform:translateX(-50%);">
          Reporte De Producción: <?= $maquina_id ?>
        </strong>

        <!-- DERECHA: IMPRIMIR -->
        <a href="imprimir_reporte_produccion.php?id=<?= $idReporte ?>" class="btn btn-primary no-print" target="_blank">
          🖨 Imprimir
        </a>

      </div>
      <div class="panel-body">
        <form method="post" id="formReporte">
          <input type="hidden" id="reporte_id" name="reporte_id" value="<?= $idReporte ?>">
          <!-- FILA 1: Fecha, Turno, Supervisor, Operador -->
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label for="fecha">Fecha</label> 
                <input type="date" name="Fecha" id="fecha" class="form-control" value="<?= $fechaReporte ?>" readonly>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="nombre">Turno</label>
                <select name="grupo" class="form-control">
                  <option value="">Elije una opción</option>
                  <?php foreach ($grupos as $g): ?>
                    <option value="<?= (int)$g['id'];?>"
                    <?= ($g['id'] == $turnoSeleccionado) ? 'selected' : ''; ?>>
                      <?= remove_junk($g['nombre']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="supervisor">Supervisor</label>
                <select name="supervisor" class="form-control">
                  <option value="">Elije una opción</option>
                  <?php foreach ($supervisor as $s): ?>
                    <option value="<?= (int)$s['id'];?>"
                      <?= ($s['id'] == $supervisorSeleccionado) ? 'selected' : ''; ?>>
                      <?= remove_junk($s['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="operador">Operador</label>
                <select name="operador" class="form-control">
                  <option value="">Elije una opción</option>
                  <?php foreach ($zonaTrabajo as $z): ?>
                    <option value="<?= (int)$z['id'];?>"
                      <?= ($s['id'] == $supervisorSeleccionado) ? 'selected' : ''; ?>>
                      <?= remove_junk($s['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <!-- TABLA 1: Se llenara en cuando el usuario elija un producto -->
            <div class="row">
              <div class="col-md-12">
                <table id="tablaProductos1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th class="text-center" style="width: 90px;">CODIGO</th>
                      <th class="text-center">DESCRIPCION</th>
                      <th class="text-center">TEORICO x Hr</th>
                      <th class="text-center">ACCION</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Aquí se llenarán los resultados -->
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div style="display:flex; justify-content:center; align-items:center;">
                  <label for="codigo" style="margin-right:10px; margin-bottom:0;">Código:</label>
                  <input list="listaCodigos"
                    name="codigo"
                    id="codigo"
                    class="form-control"
                    style="width:550px;"
                    placeholder="Escribe un código"
                    autocomplete="off">
                  <datalist id="listaCodigos">
                    <?php foreach ($productosGasas as $gasas): ?>
                      <option value="<?= (int)$gasas['ID'] . ' - ' . remove_junk($gasas['descripcion']); ?>">
                    <?php endforeach; ?>
                  </datalist>
                </div>
              </div>
            </div>
            <br>
            <!-- TABLA 2: Se hara la captura de la produccion de ese producto en ese lapso -->
            <div class="row">
              <div class="col-md-12">
                <table id="tablaProductos2" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th></th>
                      <th class="text-center" style="width: 90px;">Hora inicio</th>
                      <th class="text-center" style="width: 90px;">Hora Final</th>
                      <th class="text-center">Evento</th>
                      <th class="text-center">Cod. Material</th>
                      <th class="text-center">Produccion</th>
                      <th class="text-center">Teorico</th>
                      <th class="text-center">%</th>
                      <th class="text-center">Comprobacion</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Aquí se llenarán los resultados -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default" id="panelFallas" style="display:none;">
      <div class="panel-heading">
        <strong>Registro de Fallas</strong>
      </div>
    <div class="panel-body">
    <div class="row">
      <div class="col-md-12">
        <table id="tablaFallas" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th class="text-center" style="width: 90px;">Tiempo</th>
              <th class="text-center">Causa</th>
              <th class="text-center">descripcion</th>
            </tr>
          </thead>
          <tbody>
            <!-- Aquí se llenarán los resultados -->
          </tbody>
        </table>
       </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalFalla">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Registrar Paro</h4>
      </div>
      <div class="modal-body">
        <p id="mensajeParo"></p>
        <div class="form-group">
          <label>Minutos</label>
          <input type="number" id="minutosFalla_modal" class="form-control">
        </div>
        <div class="form-group">
          <label>Paro</label>
          <select name="paro_id_modal" id="paro_id_modal" class="form-control">
            <option value="">Elije una opción</option>
            <?php foreach ($paros as $p): ?>
              <option value="<?= (int)$p['id'];?>">
                <?= remove_junk($p['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Subparo</label>
          <select name="subparo_id_modal" id="subparo_id_modal" class="form-control">
            <option value="">Seleccione un subparo</option>
          </select>
        </div>
        <button class="btn btn-danger" onclick="registrarFallaModal()">
            Agregar
        </button>
      </div>
    </div>
  </div>
</div>
<script>
    const produccionGuardada = <?= json_encode($produccion_guardada ?? []); ?>;
    const fallasGuardadas = <?= json_encode($fallas_guardadas ?? []); ?>;
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>