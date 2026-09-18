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
    'VIGENTE'    => ['texto' => 'VIGENTE',  'class' => 'label-success'],
    'FINALIZADO' => ['texto' => 'INACTIVO', 'class' => 'label-default'],
];
$sql = "SELECT 
            vs.usuario_id AS nomina,
            u.`name` AS nombre,
            u.fecha_ingreso AS ingreso,
            vs.periodo_inicio AS inicio,
            vs.periodo_fin AS fin,
            vs.estatus,
            vs.fecha_generacion as creates,
            vs.updated_at AS updates
        FROM vacaciones_saldo vs
            INNER JOIN users u ON vs.usuario_id = u.id
        WHERE vs.usuario_id LIKE '%{$q}%'
        OR u.name LIKE '%{$q}%'
        ORDER BY vs.usuario_id ASC
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
        <td class="text-center"><?php echo remove_junk(ucwords($emp['nombre'])); ?></td>
        <!-- Ingreso -->
        <td class="text-center"><?php echo remove_junk(ucwords($emp['ingreso'])); ?></td>
        <!-- Periodo -->
        <td class="text-center"><?php echo remove_junk(ucwords($emp['inicio'])); ?></td>
        <td class="text-center"><?php echo remove_junk(ucwords($emp['fin'])); ?></td>
        <!-- Estado -->
        <?php
        $estatusId = remove_junk(ucwords($emp['estatus']));
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
        <!-- Actualizaciones -->
        <td class="text-center"><?php echo remove_junk(ucwords($emp['creates'])); ?></td>
        <td class="text-center"><?php echo remove_junk(ucwords($emp['updates'])); ?></td>
    </tr>
<?php endforeach; ?>
