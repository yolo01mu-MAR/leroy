<?php
require_once __DIR__ . '/../../../app/bootstrap.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
  // Si no hay texto, no regresamos nada
  // El JS se encargará de volver a la tabla normal
  exit;
}

$q = $db->escape($q);

$sql = "SELECT 
            vu.id AS nomina,
            vu.nombre,
            vu.puesto,
            vu.grupos,
            vu.departamento,
            vu.fecha_ingreso AS ingreso,
            vu.dias_otorgados AS otorgados,
            vu.dias_disfrutados AS disfrutados,
            vu.dias_pendientes AS pendientes,
            vu.dias_disponibles AS disponibles
          FROM vw_vacaciones_usuario vu
        WHERE vu.id LIKE '%{$q}%'
        OR vu.nombre LIKE '%{$q}%'
        ORDER BY vu.id ASC
        LIMIT 50";

$resultados = find_by_sql($sql);

if (empty($resultados)) {
  echo '
    <tr>
      <td colspan="8" class="text-center text-muted" style="padding:20px;">
        <strong>Sin resultados :(</strong><br>
      </td>
    </tr>
  ';
  exit;
}
?>
<?php foreach($resultados as $emp): ?>
  <tr>
      <td class="text-center"><?php echo remove_junk(ucwords($emp['nomina'])); ?></td>
      <!-- Nombre -->
        <td class="text-center">
          <?php echo remove_junk(ucwords($emp['nombre'])); ?>
          <br>
          <?php echo remove_junk(ucwords($emp['puesto'])); ?>
      </td>
      <td class="text-center">
          <?php echo remove_junk(ucwords($emp['grupos'])); ?>
          <br>
          <?php echo remove_junk(ucwords($emp['departamento'])); ?>
      </td>
      <!-- Ingreso -->
      <td class="text-center"><?php echo remove_junk(ucwords($emp['ingreso'])); ?></td>
      <!-- Periodo -->
      <td class="text-center"><?php echo remove_junk(ucwords($emp['otorgados'])); ?></td>
      <td class="text-center"><?php echo remove_junk(ucwords($emp['disfrutados'])); ?></td>
      <!-- Actualizaciones -->
      <td class="text-center"><?php echo remove_junk(ucwords($emp['pendientes'])); ?></td>
      <td class="text-center"><?php echo remove_junk(ucwords($emp['disponibles'])); ?></td>
  </tr>
<?php endforeach; ?>
