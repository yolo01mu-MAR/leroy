<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../../../includes/helpers.php';

$id     = (int)$_GET['id'];
$semana = $_GET['semana'];
$anio   = (int)$_GET['anio'];

$infoSemana = obtenerSemanaISO($anio, $semana);

$lunes   = $infoSemana['lunes'];
$domingo = $infoSemana['domingo'];

$diasSemana = [];
for ($i = 0; $i < 7; $i++) {
  $fecha = date('Y-m-d', strtotime("$lunes +$i day"));
  $diasSemana[$fecha] = [
    'fecha' => $fecha,
    'estado' => 0
  ];
}

/* ===============================
   DATOS DEL EMPLEADO
================================ */
$emp = get_empleado($id);

/* ===============================
   REGISTROS DE LA SEMANA
================================ */
$rows = get_asistencia_semana($id, $anio, $semana);

foreach ($rows as $r) {
  $fecha = $r['fecha'];
  if (isset($diasSemana[$fecha])) {
    $diasSemana[$fecha]['estado'] = (int)$r['asistencia'];
  }
}

// ===============================
// MAPA DE ESTADOS
// ===============================
$estados = [
  0 => ['Sin registro', 'estado-none'],
  1 => ['Asistió', 'estado-ok'],
  2 => ['Falta injustificada', 'estado-bad'],
  3 => ['Tiempo por tiempo', 'estado-warn'],
  4 => ['Falta justificada', 'estado-info'],
];

// Nombres de días
$diasNombres = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

/* ===============================
   RANGO DE FECHAS
================================ */
$inicio = $rows[0]['fecha'] ?? '';
$fin    = end($rows)['fecha'] ?? '';
?>
<!-- ===== ENCABEZADO ===== -->
<div class="text-center">
  <h3><strong><?= $emp['name']; ?></strong><br></h3>
  <p>
    Puesto: <?= $emp['puesto']; ?> |
    Grupo: <?= $emp['grupo']; ?> |
    Zona: <?= $emp['zona']; ?> <br>
    <strong> Semana: <?= $emp['tipo_semana']; ?><br></strong>
  </p>
</div>
<hr>
<!-- ===== TABLA ===== -->
<div class="text-center">
    <h4>
    <strong>
            Semana <?= $semana ?>
            · <?= fecha_es($lunes, 'dd MMMM'); ?>
            al <?= fecha_es($domingo, 'dd MMMM yyyy'); ?>
          </strong>
    </h4>
    <div class="row text-center calendario-semana">
      <?php $i = 0; foreach ($diasSemana as $dia): ?>
        <div class="dia-col">
          <div class="dia-card <?= $estados[$dia['estado']][1]; ?>">
            <div class="dia-nombre"><?= $diasNombres[$i]; ?></div>
            <div class="dia-fecha"><?= date('d', strtotime($dia['fecha'])); ?></div>
            <div class="dia-estado"><?= $estados[$dia['estado']][0]; ?></div>
          </div>
        </div>
      <?php $i++; endforeach; ?>
    </div>
</div>