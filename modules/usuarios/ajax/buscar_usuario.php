<?php
require_once __DIR__ . '/../../../app/bootstrap.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
  // Si no hay texto, no regresamos nada
  // El JS se encargará de volver a la tabla normal
  exit;
}

$q = $db->escape($q);

$estatusMap = [
  1 => ['texto' => 'ACTIVO',       'class' => 'label-success'],
  2 => ['texto' => 'INACTIVO',     'class' => 'label-default'],
  3 => ['texto' => 'BAJA',         'class' => 'label-danger'],
  4 => ['texto' => 'INCAPACIDAD',  'class' => 'label-warning'],
  5 => ['texto' => 'VACACIONES',   'class' => 'label-info'],
];

$sql = "SELECT 
              v.id,
              v.nombre,
              v.puesto,
              v.departamentos,
              v.grupos,
              v.dep_cuadrilla,
              v.tipo,
              ug.group_name  AS rol,
              v.statusLaboral_id
            FROM vw_usuarios_completos v
              LEFT JOIN grupos g       ON v.grupos = g.id
              LEFT JOIN user_groups ug ON v.nivel = ug.group_level
         	WHERE v.id LIKE '%{$q}%'
          		OR v.nombre LIKE '%{$q}%'
            ORDER BY v.id ASC
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

foreach ($resultados as $emp):
?>
<tr>
  <td class="text-center"><?php echo (int)$emp['id']; ?></td>
  <td>
    <a href="detalle_empleado.php?id=<?php echo (int)$emp['id']; ?>">
      <?php echo remove_junk(ucwords($emp['nombre']));?>
    </a>
  </td>
  <td class="text-center"><?php echo remove_junk($emp['puesto']); ?></td>
  <td class="text-center"><?php echo remove_junk($emp['dep_cuadrilla']); ?></td>
  <td class="text-center"><?php echo !empty($emp['grupo']) ? remove_junk($emp['grupo']) : '—'; ?></td>
  <td class="text-center">
    <?php echo !empty($emp['rol']) ? remove_junk($emp['rol']) : 'Usuario'; ?>
  </td>
  <?php
    $estatusId = (int)$emp['statusLaboral_id'];
    if (isset($estatusMap[$estatusId])) {
      $e = $estatusMap[$estatusId];
      echo "<td class='text-center'>
              <span class='label {$e['class']}'>{$e['texto']}</span>
            </td>";
    } else {
      echo "<td class='text-center'>
              <span class='label label-default'>DESCONOCIDO</span>
            </td>";
    }
  ?>
  <td class="text-center">
    <div class="btn-group">
      <a href="edit_user.php?id=<?php echo (int)$emp['id']; ?>" class="btn btn-xs btn-warning">
        <i class="glyphicon glyphicon-pencil"></i>
      </a>
      <a href="delete_user.php?id=<?php echo (int)$emp['id']; ?>" class="btn btn-xs btn-danger">
        <i class="glyphicon glyphicon-remove"></i>
      </a>
    </div>
  </td>
</tr>
<?php endforeach; ?>
