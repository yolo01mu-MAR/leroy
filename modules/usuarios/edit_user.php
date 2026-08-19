<?php
  $page_title = 'Editar Usuario';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $scripts = [
    'edit_user'
  ];

  // Chequeo de nivel de permiso para ver esta página
  page_require_level(5);

  $return = $_GET['return'] ?? 'allUsers.php';

  $e_user = find_by_id('users', (int) $_GET['id']);
  if (!$e_user) {
    $session->msg("d", "Missing user id.");
    redirect($return);
  }

  $groups        = find_all('user_groups');
  $salarios      = find_all('salarios');
  $departamentos = find_all('departamento');  // catálogo general (gerencia), distinto de depPlantilla
  // $cuadrillas    = get_cuadrillas();          // se usa en la pestaña "Laboral", no puede quedar comentada

  $estatusMap = [
    1 => ['texto' => 'ACTIVO',   'class' => 'label-success'],
    2 => ['texto' => 'INACTIVO', 'class' => 'label-default'],
    3 => ['texto' => 'BAJA',     'class' => 'label-danger'],
  ];

  $estatusId    = (int) $e_user['statusLaboral_id'];
  $estatusTexto = $estatusMap[$estatusId]['texto'] ?? 'DESCONOCIDO';
  $estatusClass = $estatusMap[$estatusId]['class'] ?? 'label-default';

  // Nivel "empleado" (sin usuario/contraseña propios, entra por kiosco).
  // Ajusta este valor si tu id de nivel "empleado" es distinto de 3.
  const NIVEL_EMPLEADO = 3;

  // -----------------------------------------------------------------
  // Actualizar datos generales
  // -----------------------------------------------------------------
  if (isset($_POST['update'])) {

      $req_fields = array('name', 'level', 'statusLaboral_id');
      // 'username' ya no va en $req_fields a fuerzas: solo es obligatorio
      // si el nivel elegido no es "empleado". Se valida a mano abajo.
      validate_fields($req_fields);

      $nivelElegido = (int) ($_POST['level'] ?? 0);
      if ($nivelElegido !== NIVEL_EMPLEADO && trim($_POST['username'] ?? '') === '') {
          $errors[] = 'El usuario es obligatorio para roles distintos de empleado.';
      }

      if (empty($errors)) {

          $id               = (int) $e_user['id'];
          $name             = remove_junk($db->escape($_POST['name']));
          $username         = remove_junk($db->escape($_POST['username'] ?? ''));
          $level            = $nivelElegido;
          $status           = (int) $_POST['statusLaboral_id'];
          $fecha_nacimiento = remove_junk($db->escape($_POST['fecha_nacimiento']));
          $sexo             = ($_POST['sexo'] === 'F') ? 'F' : 'M';
          $email            = remove_junk($db->escape($_POST['email']));
          $curp             = remove_junk($db->escape($_POST['CURP']));
          $rfc              = remove_junk($db->escape($_POST['RFC']));
          $nss              = remove_junk($db->escape($_POST['NSS']));
          $puesto           = remove_junk($db->escape($_POST['puesto']));

          $departamento_id     = !empty($_POST['departamento_id']) ? (int) $_POST['departamento_id'] : null;
          $cuadrilla_id         = !empty($_POST['cuadrilla_id'])    ? (int) $_POST['cuadrilla_id']    : null;
          $salario_id           = !empty($_POST['salario_id'])      ? (int) $_POST['salario_id']      : null;
          $sql_departamento_id = $departamento_id === null ? 'NULL' : $departamento_id;
          $sql_cuadrilla_id    = $cuadrilla_id    === null ? 'NULL' : $cuadrilla_id;
          $sql_salario_id      = $salario_id      === null ? 'NULL' : $salario_id;

          $fecha_ingreso     = trim($_POST['fecha_ingreso'] ?? '');
          $sql_fecha_ingreso = $fecha_ingreso === '' ? 'NULL' : "'" . $db->escape($fecha_ingreso) . "'";

          $sql = "UPDATE users SET
                    name = '{$name}',
                    username = '{$username}',
                    user_level = '{$level}',
                    statusLaboral_id = '{$status}',
                    fecha_nacimiento = '{$fecha_nacimiento}',
                    sexo = '{$sexo}',
                    email = '{$email}',
                    CURP = '{$curp}',
                    RFC = '{$rfc}',
                    NSS = '{$nss}',
                    puesto = '{$puesto}',
                    departamento_id = {$sql_departamento_id},
                    cuadrilla_id = {$sql_cuadrilla_id},
                    salario_id = {$sql_salario_id},
                    fecha_ingreso = {$sql_fecha_ingreso}
                  WHERE id = '{$db->escape($id)}'";

          $result = $db->query($sql);

          if ($result) {
            $session->msg('s', "Cuenta actualizada.");
          } else {
            $session->msg('d', 'Lo siento, no se actualizaron los datos.');
          }
          redirect('edit_user.php?id=' . (int) $e_user['id'], false);

      } else {
          $session->msg("d", $errors);
          redirect('edit_user.php?id=' . (int) $e_user['id'], false);
      }
  }

  // -----------------------------------------------------------------
  // Actualizar contraseña (formulario aparte, sin cambios)
  // -----------------------------------------------------------------
  if (isset($_POST['update-pass'])) {
      $req_fields = array('password');
      validate_fields($req_fields);

      if (empty($errors)) {
          $id       = (int) $e_user['id'];
          $password = remove_junk($db->escape($_POST['password']));
          $h_pass   = sha1($password);

          $sql    = "UPDATE users SET password = '{$h_pass}' WHERE id = '{$db->escape($id)}'";
          $result = $db->query($sql);

          if ($result) {
              $session->msg('s', "Se ha actualizado la contraseña del usuario.");
          } else {
              $session->msg('d', 'No se pudo actualizar la contraseña de usuario.');
          }
          redirect('edit_user.php?id=' . (int) $e_user['id'], false);
      } else {
          $session->msg("d", $errors);
          redirect('edit_user.php?id=' . (int) $e_user['id'], false);
      }
  }
?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/edit_user.css">
<div class="row">
  <div class="col-md-12"> <?php echo display_msg($msg); ?> </div>
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          Actualiza cuenta de: <?php echo remove_junk(ucwords($e_user['name'])); ?>
        </strong>
      </div>
      <div class="panel-body">

        <!-- UN SOLO FORMULARIO: envuelve sidebar + pestañas -->
        <form method="post" action="edit_user.php?id=<?php echo (int) $e_user['id']; ?>" id="formUsuario">
          <div class="row detalle-empleado">

            <!-- SIDEBAR -->
            <div class="col-md-4">
              <div class="panel panel-default panel-perfil">
                <div class="panel-body text-center">

                  <?php
                    $foto = !empty($empleado['image'])
                        ? BASE_URL . '/uploads/users/' . $empleado['image']
                        : BASE_URL . '/uploads/users/no_image.jpg';

                        $tieneFoto = !empty($e_user['image']);
                  ?>

                  <div class="foto-perfil">
                    <img src="<?php echo $foto; ?>"
                      class="rounded-circle border border-3 border-warning mb-3"
                      width="130"
                      height="130">
                    <div class="foto-overlay">
                      <button type="button" class="btn btn-primary btn-sm btn-block"
                              data-toggle="modal" data-target="#modalFoto">
                        <i class="glyphicon glyphicon-camera"></i>
                        Cambiar foto
                      </button>
                      <?php if ($tieneFoto): ?>
                        <button type="button" class="btn btn-danger btn-sm btn-block" style="margin-top:8px;">
                          <i class="glyphicon glyphicon-trash"></i>
                          Eliminar
                        </button>
                      <?php endif; ?>
                    </div>
                  </div>

                  <h3 style="margin-top:20px;">
                    <?php echo remove_junk($e_user['name']); ?>
                  </h3>
                  <p class="text-muted">
                    <?php echo remove_junk($e_user['puesto']); ?>
                  </p>
                  <span class="label <?php echo $estatusClass; ?>">
                    <?php echo $estatusTexto; ?>
                  </span>

                  <hr>

                  <div class="form-group text-left">
                    <label>Nómina</label>
                    <input class="form-control" value="<?php echo (int) $e_user['id']; ?>" disabled>
                  </div>

                  <button type="submit" name="update" class="btn btn-roy btn-block">
                    <i class="glyphicon glyphicon-floppy-disk"></i>
                    Guardar cambios
                  </button>

                  <a href="<?php echo remove_junk($return); ?>" class="btn btn-default btn-block" style="margin-top:10px;">
                    <i class="glyphicon glyphicon-arrow-left"></i>
                    Regresar
                  </a>

                </div>
              </div>
            </div>

            <!-- INFORMACIÓN: pestañas -->
            <div class="col-md-8">

              <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#tab-personales" role="tab" data-toggle="tab">Personales</a></li>
                <li role="presentation"><a href="#tab-identificacion" role="tab" data-toggle="tab">Identificación</a></li>
                <li role="presentation"><a href="#tab-laboral" role="tab" data-toggle="tab">Laboral</a></li>
                <li role="presentation"><a href="#tab-acceso" role="tab" data-toggle="tab">Acceso</a></li>
              </ul>

              <div class="tab-content" style="padding:20px 5px;">

                <!-- PERSONALES -->
                <div role="tabpanel" class="tab-pane active" id="tab-personales">
                  <div class="row">
                    <div class="col-md-12 form-group">
                      <label class="control-label">Nombre completo</label>
                      <input type="text" class="form-control" name="name"
                             value="<?php echo remove_junk($e_user['name']); ?>" maxlength="50" required>
                    </div>

                    <div class="col-md-6 form-group" id="campoUsername">
                      <label class="control-label">Usuario</label>
                      <input type="text" class="form-control" name="username"
                             value="<?php echo remove_junk($e_user['username']); ?>" maxlength="50">
                    </div>

                    <div class="col-md-6 form-group">
                      <label class="control-label">Fecha de nacimiento</label>
                      <input type="text" class="form-control" name="fecha_nacimiento"
                             value="<?php echo remove_junk($e_user['fecha_nacimiento']); ?>" placeholder="DD/MM/AAAA">
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Sexo</label>
                      <select class="form-control" name="sexo">
                        <option value="M" <?php echo $e_user['sexo'] === 'M' ? 'selected' : ''; ?>>M</option>
                        <option value="F" <?php echo $e_user['sexo'] === 'F' ? 'selected' : ''; ?>>F</option>
                      </select>
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Email</label>
                      <input type="email" class="form-control" name="email"
                             value="<?php echo remove_junk($e_user['email']); ?>" maxlength="50">
                    </div>
                  </div>
                </div>

                <!-- IDENTIFICACIÓN -->
                <div role="tabpanel" class="tab-pane" id="tab-identificacion">
                  <div class="row">
                    <div class="col-md-4 form-group">
                      <label class="control-label">CURP</label>
                      <input type="text" class="form-control" name="CURP"
                             value="<?php echo remove_junk($e_user['CURP']); ?>" maxlength="50">
                    </div>
                    <div class="col-md-4 form-group">
                      <label class="control-label">RFC</label>
                      <input type="text" class="form-control" name="RFC"
                             value="<?php echo remove_junk($e_user['RFC']); ?>" maxlength="50">
                    </div>
                    <div class="col-md-4 form-group">
                      <label class="control-label">NSS</label>
                      <input type="text" class="form-control" name="NSS"
                             value="<?php echo remove_junk($e_user['NSS']); ?>" maxlength="50">
                    </div>
                  </div>
                </div>

                <!-- LABORAL -->
                <div role="tabpanel" class="tab-pane" id="tab-laboral">
                  <div class="row">
                    <div class="col-md-6 form-group">
                      <label class="control-label">Departamento</label>
                      <select class="form-control" name="departamento_id">
                        <option value="">— Sin asignar —</option>
                        <?php foreach ($departamentos as $dep): ?>
                          <option value="<?php echo (int) $dep['ID']; ?>"
                            <?php echo ((int) $dep['ID'] === (int) $e_user['departamento_id']) ? 'selected' : ''; ?>>
                            <?php echo remove_junk($dep['nombre']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Cuadrilla</label>
                      <select class="form-control" name="cuadrilla_id">
                        <option value="">— Sin asignar —</option>
                        <?php foreach ($cuadrillas as $cua): ?>
                          <option value="<?php echo (int) $cua['id']; ?>"
                            <?php echo ((int) $cua['id'] === (int) $e_user['cuadrilla_id']) ? 'selected' : ''; ?>>
                            <?php echo remove_junk($cua['etiqueta']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-12 form-group">
                      <label class="control-label">Puesto</label>
                      <input type="text" class="form-control" name="puesto"
                             value="<?php echo remove_junk($e_user['puesto']); ?>" maxlength="250">
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Fecha de ingreso</label>
                      <input type="date" class="form-control" name="fecha_ingreso"
                             value="<?php echo remove_junk($e_user['fecha_ingreso']); ?>">
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Salario</label>
                      <select class="form-control" name="salario_id">
                        <option value="">— Sin asignar —</option>
                        <?php foreach ($salarios as $sal): ?>
                          <option value="<?php echo (int) $sal['id']; ?>"
                            <?php echo ((int) $sal['id'] === (int) $e_user['salario_id']) ? 'selected' : ''; ?>>
                            <?php echo remove_junk($sal['codigo']); ?> —
                            $<?php echo number_format((float) $sal['salario'], 2); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="control-label">Estatus laboral</label>
                      <div class="estatus-laboral">
                        <?php foreach ($estatusMap as $idEst => $info): ?>
                          <label>
                            <input type="radio" name="statusLaboral_id" value="<?php echo $idEst; ?>"
                              <?php echo ($idEst === $estatusId) ? 'checked' : ''; ?>>
                            <span><?php echo $info['texto']; ?></span>
                          </label>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ACCESO -->
                <div role="tabpanel" class="tab-pane" id="tab-acceso">
                  <div class="row">
                    <div class="col-md-6 form-group">
                      <label class="control-label">Rol de usuario</label>
                      <select class="form-control" name="level" id="selectNivel">
                        <?php foreach ($groups as $group): ?>
                          <option value="<?php echo (int) $group['group_level']; ?>"
                            <?php echo ((int) $group['group_level'] === (int) $e_user['user_level']) ? 'selected' : ''; ?>>
                            <?php echo remove_junk(ucwords($group['group_name'])); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <p class="text-muted" id="notaAccesoEmpleado" style="margin-bottom:10px;">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    Los empleados no usan usuario ni contraseña: entran con su número de nómina en el kiosco.
                  </p>
                </div>

              </div>
            </div>

          </div>
        </form>
        <div class="panel panel-default" style="margin-top:20px;" id="panelPassword">
          <div class="panel-heading">
            <strong><span class="glyphicon glyphicon-lock"></span> Cambiar contraseña</strong>
          </div>
          <div class="panel-body">
            <form method="post" action="edit_user.php?id=<?php echo (int) $e_user['id']; ?>" class="form-inline">
              <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Nueva contraseña" required>
              </div>
              <button type="submit" name="update-pass" class="btn btn-info">
                Actualizar contraseña
              </button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalFoto">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="subir_foto.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <button class="close" data-dismiss="modal">&times;</button>
                    <h4>Cambiar fotografía</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="usuario" value="<?php echo (int) $e_user['id']; ?>">
                    <input type="file" name="foto" class="form-control" accept="image/*">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Subir fotografía</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>