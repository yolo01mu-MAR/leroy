<?php
  
  $page_title = 'Lista de categorías';
  require_once __DIR__ . '/../../app/bootstrap.php';

  page_require_level(4);

  $idReporte = '';

  if(isset($_GET['id'])){

    $idReporte = (int)$_GET['id'];

    $sql = "SELECT r.*, 
              m.nombre as maquina_nombre
            FROM reportes r
            JOIN maquinas m ON m.id = r.maquina_id
            WHERE r.id = {$idReporte}
    ";

    $resultado = $db->query($sql);

    if($row = $resultado->fetch_assoc()){

        $maquina_id = $row['maquina_nombre'];
        $fechaReporte = $row['fecha'];
        $turnoSeleccionado = $row['turno_id'];
        $supervisorSeleccionado = $row['supervisor_id'];
        $operadorSeleccionado = $row['operador_id'];
    }

    $produccion_guardada = [];

    $sqlProd = "SELECT rp.*, 
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
    $fallas_guardadas = [];

    $sqlFallas = "SELECT 
                    rp.id,
                    rp.reporte_id,
                    pg.nombre,
                    sp.nombre,
                    rp.minutos
                  FROM registros_paros rp
                    INNER JOIN paro_general pg ON rp.paro_id = pg.id
                    INNER JOIN subparos sp ON rp.subParos_id = sp.id
                  WHERE rp.reporte_id = {$idReporte}";

    $resultFallas = $db->query($sqlFallas);

    while($rowFalla = $resultFallas->fetch_assoc()){
        $fallas_guardadas[] = $rowFalla;
    }
  }  

  $grupos = get_grupos();
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
      <div class="panel-heading encabezado-reporte">
        <strong>Reporte De Producción: <?= $maquina_id ?></strong>
        <div class="control-guardado">
          <!-- BOTÓN GUARDAR (solo manual) -->
          <button id="btnGuardar" class="btn-guardar" onclick="guardarManual()">
            💾
          </button>
          <!-- SWITCH AUTOGUARDADO -->
          <label class="switch">
            <input type="checkbox" id="autoSwitch" checked onchange="toggleAuto()">
            <span class="slider"></span>
          </label>
          <span class="texto-auto">Autoguardado</span>
          <span id="estadoMini" class="estado-mini">
            Sin cambios
          </span>

        </div>
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
                      <?= (isset($_GET['grupo']) && $_GET['grupo'] == $g['id']) ? 'selected' : '' ?>>
                      <?= remove_junk($g['nombre']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="zona">Supervisor</label>
                <select name="zona" class="form-control">
                  <option value="">Elije una opción</option>
                  <?php foreach ($zonaTrabajo as $z): ?>
                    <option value="<?= (int)$z['id'];?>"
                      <?= (isset($_GET['zona']) && $_GET['zona'] == $z['id']) ? 'selected' : '' ?>>
                      <?= remove_junk($z['zona']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="zona">Operador</label>
                <select name="zona" class="form-control">
                  <option value="">Elije una opción</option>
                  <?php foreach ($zonaTrabajo as $z): ?>
                    <option value="<?= (int)$z['id'];?>"
                      <?= (isset($_GET['zona']) && $_GET['zona'] == $z['id']) ? 'selected' : '' ?>>
                      <?= remove_junk($z['zona']); ?>
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
        <div class="form-inline" style="margin-bottom:10px;">
          <!-- Minutos de produccion -->
          <input type="number" id="minutosFalla" class="form-control" placeholder="Minutos">
          <!-- Paros -->
          <select name="paro_id" id="paro_id" class="form-control">
            <option value="">Elije una opción</option>
              <?php foreach ($paros as $p): ?>
                <option value="<?= (int)$p['id'];?>"
                  <?= (isset($_GET['paros']) && $_GET['paros'] == $p['id']) ? 'selected' : '' ?>>
                  <?= remove_junk($p['nombre']); ?>
                </option>
              <?php endforeach; ?>
          </select>
          <!-- Sub Paros -->
          <select name="subparo_id" id="subparo_id" class="form-control">
            <option value="">Seleccione un subparo</option>
          </select>
          <button type="button" class="btn btn-danger" onclick="registrarFalla()">Agregar</button>
        </div>
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
<script>
    const produccionGuardada = <?= json_encode($produccion_guardada ?? []); ?>;
    const fallasGuardadas = <?= json_encode($fallas_guardadas ?? []); ?>;
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>