<?php
  $page_title = 'Asistencia semanal';

  require_once __DIR__ . '/../../app/bootstrap.php';
  require_once __DIR__ . '/includes/asistencia_helpers.php';

  page_require_level(5);
  require_permiso('asistencias.semanal');

  $hayFiltros = false;
  foreach (['nomina','nombre','grupo','nave','departamento','depPlantilla','jefe'] as $f) {
    if (!empty($_GET[$f])) {
      $hayFiltros = true;
      break;
    }
  }

  $asistencia = find_asistencia_filtrada();

  $departamento = get_departamento();
  $depPlantilla = get_dep_plantilla();
  $naves = get_nave();
  $grupos = get_grupos();
  $jefeCuadrilla = get_jefes_cuadrilla();

  $infoSemana = obtenerSemanaISO();

  $semana  = $infoSemana['semana'];
  $anio    = $infoSemana['anio'];
  $domingo = $infoSemana['domingo'];
  $sabado  = $infoSemana['sabado'];

  $estadoAsistencia = [
    1 => ['icon' => 'glyphicon-ok',    'class' => 'estado-ok',    'title' => 'Asistió'],
    2 => ['icon' => 'glyphicon-remove','class' => 'estado-bad',   'title' => 'Falta injustificada'],
    3 => ['icon' => 'glyphicon-time',  'class' => 'estado-txt',   'title' => 'Tiempo por tiempo'],
    4 => ['icon' => 'glyphicon-ok',    'class' => 'estado-just',  'title' => 'Falta justificada'],
    0 => ['icon' => 'glyphicon-minus', 'class' => 'estado-none',  'title' => 'Sin registro'],
  ];

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
        <div class="panel-heading">
          <strong>Filtrar por:</strong>
        </div>
        <div class="panel-body">
          <form method="get">
            <!-- FILA 1: Empleado -->
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="nomina">Nómina</label>
                  <input type="text" name="nomina" class="form-control filtro"
                        placeholder="Ej. 19003" <?= $hayFiltros ? 'disabled' : '' ?>>
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-group">
                  <label for="nombre">Nombre del empleado</label>
                  <input type="text" name="nombre" class="form-control filtro"
                        placeholder="Ej. Eliel Flores" <?= $hayFiltros ? 'disabled' : '' ?>>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="grupo">Grupo</label>
                  <select name="grupo" class="form-control filtro" <?= $hayFiltros ? 'disabled' : '' ?>>
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
            </div>
            <!-- FILA 2: Organización -->
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="nave">Nave</label>
                  <select name="nave" class="form-control filtro" <?= $hayFiltros ? 'disabled' : '' ?>>
                    <option value="">Elije una opción</option>
                    <?php foreach ($naves as $n): ?>
                      <option value="<?= (int)$n['ID'];?>"
                        <?= (isset($_GET['nave']) && $_GET['nave'] == $n['ID']) ? 'selected' : '' ?>>
                        <?= remove_junk($n['nombre']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="departamento">Departamento</label>
                  <select name="departamento" class="form-control filtro" <?= $hayFiltros ? 'disabled' : '' ?>>
                    <option value="">Elije una opción</option>
                    <?php foreach ($departamento as $d): ?>
                      <option value="<?= (int)$d['id'];?>"
                        <?= (isset($_GET['departamento']) && $_GET['departamento'] == $d['id']) ? 'selected' : '' ?>>
                        <?= remove_junk($d['zona']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="depPlantila">Zona de trabajo</label>
                  <select name="depPlantilla" class="form-control filtro" <?= $hayFiltros ? 'disabled' : '' ?>>
                    <option value="">Elije una opción</option>
                    <?php foreach ($depPlantilla as $dp): ?>
                      <option value="<?= (int)$dp['id'];?>"
                        <?= (isset($_GET['depPlantilla']) && $_GET['depPlantilla'] == $dp['id']) ? 'selected' : '' ?>>
                        <?= remove_junk($dp['nombre']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="jefe">Jefe de cuadrilla</label>
                  <select name="jefe" class="form-control filtro" <?= $hayFiltros ? 'disabled' : '' ?>>
                    <option value="">Elije una opción</option>
                    <?php foreach ($jefeCuadrilla as $jC): ?>
                      <option value="<?= (int)$jC['id']; ?>"
                        <?= (isset($_GET['jefe']) && $_GET['jefe'] == $jC['id']) ? 'selected' : '' ?>>
                        <?= remove_junk($jC['name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <!-- FILA 3: Botones -->
            <div class="row">
              <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-roy" <?= $hayFiltros ? 'disabled' : '' ?>>
                  <i class="glyphicon glyphicon-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-default" onclick="limpiarFiltros()">
                  Limpiar
                </button>
              </div>
            </div>
          </form>
        </div>
        <hr class="hr-compact">
        <div class="titulo-semana">
          <strong>
            Semana <?= $semana ?>
            · <?= fecha_es($domingo, 'dd MMMM'); ?>
            al <?= fecha_es($sabado, 'dd MMMM yyyy'); ?>
          </strong>
        </div>    
        <?php if ($resumen = resumen_filtros_asistencia()): ?>
          <div class="filtro-resumen">
            Filtrado por: <?= $resumen; ?>
          </div>
        <?php endif; ?>
        <div class="panel-body">
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th class="text-center">Nómina</th>
                <th class="text-center">Nombre</th>
                <th class="text-center">Grupo</th>
                <th class="text-center">Asistencias</th>
                <th class="text-center">Faltas Injust.</th>
                <th class="text-center">Faltas Just.</th>
                <th class="text-center">T x T</th>
                <th class="text-center">Dias registrados</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($asistencia as $row): ?>
                <tr onclick="verDetalle(<?= (int)$row['id_empleado']; ?>, '<?= $row['semana_iso']; ?>', <?= (int)$row['anio']; ?>)" style="cursor:pointer;">
                  <td class="text-center"><?= (int)$row['id_empleado']; ?></td>
                  <td><?= remove_junk($row['nombre']); ?></td>
                  <td class="text-center"><?= $row['grupo']; ?></td>
                  <td class="text-center">
                    <span class="badge-num <?= $row['dias_asistencia'] > 0 ? 'bg-ok' : 'bg-none' ?>">
                      <?= $row['dias_asistencia']; ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <span class="badge-num <?= $row['dias_falta_injustificada'] > 0 ? 'bg-bad' : 'bg-none' ?>">
                      <?= $row['dias_falta_injustificada']; ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <span class="badge-num <?= $row['dias_falta_justificada'] > 0 ? 'bg-just' : 'bg-none' ?>">
                      <?= $row['dias_falta_justificada']; ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <span class="badge-num <?= $row['dias_tiempo_por_tiempo'] > 0 ? 'bg-txt' : 'bg-none' ?>">
                      <?= $row['dias_tiempo_por_tiempo']; ?>
                    </span>
                  </td>
                  <td class="text-center"><?= $row['dias_registrados']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="modal fade" id="modalDetalleAsistencia" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">

                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">Detalle de asistencia semanal del Empleado: <?php $row["nombre"] ?></h4>
                </div>

                <div class="modal-body" id="contenidoDetalle">
                  <p class="text-center">Cargando información...</p>
                </div>

                <div class="modal-footer">
                  <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/js/asistenciaUsuarioSemanal.js"></script>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
