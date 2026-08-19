<?php
  $page_title = 'Dashboard - Inventario';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(11);

  // ---------------------------------------------------------------
  // DATOS FIJOS PARA PRUEBA DE INTERFAZ (sin consultas a BD)
  // Reemplaza estos arrays por las funciones de modelo cuando
  // conectes la vista a datos reales.
  // ---------------------------------------------------------------

  $total_productos = 128;
  $entradas_hoy    = 14;
  $salidas_hoy     = 9;
  $alertas_stock   = 5;

  $recien_agregados = [
    ['name' => 'Filtro de aceite FA-220', 'quantity' => 40, 'date' => '2026-08-11'],
    ['name' => 'Bomba de agua BA-11',     'quantity' => 12, 'date' => '2026-08-11'],
    ['name' => 'Banda dentada BD-45',     'quantity' => 25, 'date' => '2026-08-10'],
    ['name' => 'Sensor de oxígeno SO-07', 'quantity' => 18, 'date' => '2026-08-10'],
    ['name' => 'Kit de frenos KF-330',    'quantity' => 8,  'date' => '2026-08-09'],
    ['name' => 'Amortiguador AM-90',      'quantity' => 16, 'date' => '2026-08-09'],
  ];

  $recien_entrados = [
    ['name' => 'Filtro de aire FA-101', 'quantity' => 30, 'note' => 'Compra a proveedor',    'date' => '2026-08-11'],
    ['name' => 'Bujía BJ-15',           'quantity' => 60, 'note' => 'Reabastecimiento',       'date' => '2026-08-11'],
    ['name' => 'Radiador RD-500',       'quantity' => 5,  'note' => 'Compra a proveedor',     'date' => '2026-08-10'],
    ['name' => 'Batería BT-12V',        'quantity' => 10, 'note' => 'Devolución de cliente',  'date' => '2026-08-10'],
    ['name' => 'Aceite sintético 5W30', 'quantity' => 48, 'note' => 'Reabastecimiento',       'date' => '2026-08-09'],
    ['name' => 'Llanta LL-205/55',      'quantity' => 8,  'note' => 'Compra a proveedor',     'date' => '2026-08-09'],
  ];

  $recien_salidos = [
    ['name' => 'Pastillas de freno PF-88', 'quantity' => 4,  'note' => 'Venta mostrador',  'date' => '2026-08-11'],
    ['name' => 'Filtro de aceite FA-220',  'quantity' => 6,  'note' => 'Orden de servicio', 'date' => '2026-08-11'],
    ['name' => 'Bujía BJ-15',              'quantity' => 12, 'note' => 'Venta mostrador',  'date' => '2026-08-10'],
    ['name' => 'Aceite sintético 5W30',    'quantity' => 9,  'note' => 'Orden de servicio', 'date' => '2026-08-10'],
    ['name' => 'Banda dentada BD-45',      'quantity' => 3,  'note' => 'Venta mostrador',  'date' => '2026-08-09'],
    ['name' => 'Amortiguador AM-90',       'quantity' => 2,  'note' => 'Orden de servicio', 'date' => '2026-08-09'],
  ];

  $mas_solicitados = [
    ['name' => 'Aceite sintético 5W30',    'total_salidas' => 54],
    ['name' => 'Filtro de aceite FA-220',  'total_salidas' => 41],
    ['name' => 'Bujía BJ-15',              'total_salidas' => 33],
    ['name' => 'Pastillas de freno PF-88', 'total_salidas' => 27],
    ['name' => 'Filtro de aire FA-101',    'total_salidas' => 19],
    ['name' => 'Banda dentada BD-45',      'total_salidas' => 12],
  ];

  // Para la barra de "más solicitados": normaliza contra el valor más alto
  $max_solicitudes = 1;
  foreach ($mas_solicitados as $ms) {
    if ((int)$ms['total_salidas'] > $max_solicitudes) {
      $max_solicitudes = (int)$ms['total_salidas'];
    }
  }
?>
<style>
  /* ---- Dashboard minimalista ---- */
  .kpi-row{ margin-bottom:24px; }
  .kpi-card{
    background:#fff; border:1px solid #eef0f2; border-radius:8px;
    padding:18px 20px; display:flex; align-items:center; gap:14px;
    transition:.15s ease;
  }
  .kpi-card:hover{ box-shadow:0 2px 10px rgba(0,0,0,.06); }
  .kpi-icon{
    width:44px; height:44px; border-radius:8px; flex:0 0 44px;
    display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff;
  }
  .kpi-icon.i-blue{ background:#4f5fd6; }
  .kpi-icon.i-green{ background:#5cb85c; }
  .kpi-icon.i-red{ background:#d9534f; }
  .kpi-icon.i-amber{ background:#f0ad4e; }
  .kpi-value{ font-size:22px; font-weight:700; color:#2c3542; line-height:1.1; }
  .kpi-label{ font-size:12px; color:#9aa2af; text-transform:uppercase; letter-spacing:.03em; }

  .dash-card{
    background:#fff; border:1px solid #eef0f2; border-radius:8px;
    padding:20px; margin-bottom:24px;
  }
  .dash-card-title{
    font-size:14px; font-weight:700; color:#2c3542; margin-bottom:16px;
    display:flex; align-items:center; gap:8px;
  }
  .dash-card-title .glyphicon{ color:#9aa2af; margin-right:6px; font-size:13px; }

  .dash-list{ list-style:none; margin:0; padding:0; }
  .dash-list li{
    display:flex; align-items:center; gap:12px;
    padding:10px 0; border-bottom:1px solid #f4f5f7;
  }
  .dash-list li:last-child{ border-bottom:none; }
  .dash-avatar{
    width:36px; height:36px; border-radius:50%; flex:0 0 36px;
    display:flex; align-items:center; justify-content:center;
    font-size:14px; color:#fff; background:#c8cdd6;
  }
  .dash-avatar.a-blue{ background:#4f5fd6; }
  .dash-avatar.a-green{ background:#5cb85c; }
  .dash-avatar.a-red{ background:#d9534f; }
  .dash-avatar.a-purple{ background:#9b59b6; }

  .dash-item-name{ font-size:13px; font-weight:600; color:#2c3542; }
  .dash-item-meta{ font-size:11px; color:#9aa2af; }
  .dash-item-right{ margin-left:auto; text-align:right; font-size:12px; color:#6b7280; white-space:nowrap; }

  .dash-empty{ text-align:center; color:#9aa2af; font-size:12px; padding:20px 0; }

  .rank-bar-wrap{ width:100%; background:#f4f5f7; border-radius:4px; height:6px; margin-top:4px; }
  .rank-bar{ height:6px; border-radius:4px; background:#9b59b6; }
</style>
<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<!-- KPIs -->
<div class="row kpi-row">
  <div class="col-md-3 col-sm-6">
    <div class="kpi-card">
      <div class="kpi-icon i-blue"><span class="glyphicon glyphicon-th-large"></span></div>
      <div>
        <div class="kpi-value"><?php echo (int)$total_productos; ?></div>
        <div class="kpi-label">Productos totales</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="kpi-card">
      <div class="kpi-icon i-green"><span class="glyphicon glyphicon-arrow-down"></span></div>
      <div>
        <div class="kpi-value"><?php echo (int)$entradas_hoy; ?></div>
        <div class="kpi-label">Entradas hoy</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="kpi-card">
      <div class="kpi-icon i-red"><span class="glyphicon glyphicon-arrow-up"></span></div>
      <div>
        <div class="kpi-value"><?php echo (int)$salidas_hoy; ?></div>
        <div class="kpi-label">Salidas hoy</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="kpi-card">
      <div class="kpi-icon i-amber"><span class="glyphicon glyphicon-warning-sign"></span></div>
      <div>
        <div class="kpi-value"><?php echo (int)$alertas_stock; ?></div>
        <div class="kpi-label">Alertas de stock</div>
      </div>
    </div>
  </div>
</div>

<!-- Widgets -->
<div class="row">

  <!-- Recién agregados
  <div class="col-md-6">
    <div class="dash-card">
      <div class="dash-card-title">
        <span class="glyphicon glyphicon-plus-sign"></span> Recién agregados
      </div>
      <ul class="dash-list">
        <?php foreach ($recien_agregados as $p): ?>
          <li>
            <div class="dash-avatar a-blue"><span class="glyphicon glyphicon-tag"></span></div>
            <div>
              <div class="dash-item-name"><?php echo $p['name']; ?></div>
              <div class="dash-item-meta">Stock inicial: <?php echo (int)$p['quantity']; ?></div>
            </div>
            <div class="dash-item-right"><?php echo $p['date']; ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div> -->

  <!-- Recién entrados -->
  <div class="col-md-6">
    <div class="dash-card">
      <div class="dash-card-title">
        <span class="glyphicon glyphicon-log-in"></span> Recién entrados
      </div>
      <ul class="dash-list">
        <?php foreach ($recien_entrados as $e): ?>
          <li>
            <div class="dash-avatar a-green"><span class="glyphicon glyphicon-arrow-down"></span></div>
            <div>
              <div class="dash-item-name"><?php echo $e['name']; ?></div>
              <div class="dash-item-meta">+<?php echo (int)$e['quantity']; ?> unidades · <?php echo $e['note']; ?></div>
            </div>
            <div class="dash-item-right"><?php echo $e['date']; ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Recién salidos -->
  <div class="col-md-6">
    <div class="dash-card">
      <div class="dash-card-title">
        <span class="glyphicon glyphicon-log-out"></span> Recién salidos
      </div>
      <ul class="dash-list">
        <?php foreach ($recien_salidos as $s): ?>
          <li>
            <div class="dash-avatar a-red"><span class="glyphicon glyphicon-arrow-up"></span></div>
            <div>
              <div class="dash-item-name"><?php echo $s['name']; ?></div>
              <div class="dash-item-meta">-<?php echo (int)$s['quantity']; ?> unidades · <?php echo $s['note']; ?></div>
            </div>
            <div class="dash-item-right"><?php echo $s['date']; ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>