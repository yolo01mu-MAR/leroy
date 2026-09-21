<?php
  $page_title = 'Agregar Usuario';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $scripts = [
    'add_user'
  ];

  // Chequeo de nivel de permiso para ver esta página
  page_require_level(5);

  $return = $_GET['return'] ?? 'allUsers.php';

  $groups        = find_all('user_groups');
  $salarios      = find_all('salarios');
  $departamentos = find_all('departamento');
  // $cuadrillas    = get_cuadrillas(); // Descomentar si usas la función de cuadrillas

  $estatusMap = [
    1 => ['texto' => 'ACTIVO',   'class' => 'label-success'],
    2 => ['texto' => 'INACTIVO', 'class' => 'label-default'],
    3 => ['texto' => 'BAJA',     'class' => 'label-danger'],
  ];

  // Valores por defecto para el nuevo usuario
  $estatusId    = 1; 
  $estatusTexto = $estatusMap[$estatusId]['texto'];
  $estatusClass = $estatusMap[$estatusId]['class'];

  // -----------------------------------------------------------------
  // Guardar Nuevo Usuario
  // -----------------------------------------------------------------
  if (isset($_POST['add_user'])) {

      // 'username' ya no es obligatorio por defecto
      $req_fields = array('name', 'level', 'statusLaboral_id');
      validate_fields($req_fields);

      $nivelElegido = (int) ($_POST['level'] ?? 0);
      $usernameVal  = trim($_POST['username'] ?? '');

      // Solo si NO es empleado, exigimos usuario y contraseña
      if ($nivelElegido !== NIVEL_EMPLEADO) {
          if (empty($usernameVal)) {
              $errors[] = 'El nombre de usuario es obligatorio para roles distintos de empleado.';
          }
          if (empty($_POST['password'])) {
              $errors[] = 'La contraseña es obligatoria para roles distintos de empleado.';
          }
      }

      if (empty($errors)) {

          $name             = remove_junk($db->escape($_POST['name']));
          $username         = remove_junk($db->escape($usernameVal));
          $password         = !empty($_POST['password']) ? sha1($_POST['password']) : sha1('123456'); // contraseña por defecto si se requiere
          $level            = $nivelElegido;
          $status           = (int) $_POST['statusLaboral_id'];
          $fecha_nacimiento = remove_junk($db->escape($_POST['fecha_nacimiento'] ?? ''));
          $sexo             = ($_POST['sexo'] === 'F') ? 'F' : 'M';
          $email            = remove_junk($db->escape($_POST['email'] ?? ''));
          $curp             = remove_junk($db->escape($_POST['CURP'] ?? ''));
          $rfc              = remove_junk($db->escape($_POST['RFC'] ?? ''));
          $nss              = remove_junk($db->escape($_POST['NSS'] ?? ''));
          $puesto           = remove_junk($db->escape($_POST['puesto'] ?? ''));

          $departamento_id     = !empty($_POST['departamento_id']) ? (int) $_POST['departamento_id'] : "NULL";
          $cuadrilla_id         = !empty($_POST['cuadrilla_id'])    ? (int) $_POST['cuadrilla_id']    : "NULL";
          $salario_id           = !empty($_POST['salario_id'])      ? (int) $_POST['salario_id']      : "NULL";

          $fecha_ingreso     = trim($_POST['fecha_ingreso'] ?? '');
          $sql_fecha_ingreso = $fecha_ingreso === '' ? "NULL" : "'" . $db->escape($fecha_ingreso) . "'";

          // Manejo del archivo de foto de perfil
          $image_name = 'no_image.jpg';
          if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
              $file_tmp  = $_FILES['user_image']['tmp_name'];
              $file_name = $_FILES['user_image']['name'];
              $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
              $allowed   = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

              if (in_array($ext, $allowed)) {
                  $image_name = uniqid('usr_') . '.' . $ext;
                  $upload_dir = BASE_PATH . '/uploads/users/';
                  if (!is_dir($upload_dir)) {
                      mkdir($upload_dir, 0777, true);
                  }
                  move_uploaded_file($file_tmp, $upload_dir . $image_name);
              }
          }

          $sql  = "INSERT INTO users (";
          $sql .= " name, username, password, user_level, statusLaboral_id, fecha_nacimiento, sexo, email, CURP, RFC, NSS, puesto, departamento_id, cuadrilla_id, salario_id, fecha_ingreso, image";
          $sql .= ") VALUES (";
          $sql .= " '{$name}', '{$username}', '{$password}', '{$level}', '{$status}', '{$fecha_nacimiento}', '{$sexo}', '{$email}', '{$curp}', '{$rfc}', '{$nss}', '{$puesto}', {$departamento_id}, {$cuadrilla_id}, {$salario_id}, {$sql_fecha_ingreso}, '{$image_name}'";
          $sql .= ")";

          if ($db->query($sql)) {
              $session->msg('s', "Usuario creado exitosamente.");
              redirect('add_user.php', false);
          } else {
              $session->msg('d', 'Lo siento, falló el registro del usuario.');
              redirect('add_user.php', false);
          }

      } else {
          $session->msg("d", $errors);
          redirect('add_user.php', false);
      }
  }
?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/add_user.css">

<div class="row">
  <div class="col-md-12"> <?php echo display_msg($msg); ?> </div>
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-user"></span>
          Agregar Nuevo Usuario
        </strong>
      </div>
      <div class="panel-body">

        <!-- FORMULARIO GENERAL -->
        <form method="post" action="add_user.php" id="formUsuario" enctype="multipart/form-data">
          <div class="row detalle-empleado">

            <!-- SIDEBAR -->
            <div class="col-md-4">
              <div class="panel panel-default panel-perfil">
                <div class="panel-body text-center">

                  <div class="foto-perfil">
                    <img id="previewFoto" src="<?php echo BASE_URL . '/uploads/users/no_image.jpg'; ?>"
                          class="rounded-circle border border-3 border-warning mb-3"
                          width="130"
                          height="130"
                          alt="Foto de Perfil">
                    
                    <div class="foto-overlay">
                      <!-- Input oculta para subir imagen -->
                      <input type="file" name="user_image" id="inputFoto" accept="image/*" style="display: none;">
                      <button type="button" class="btn btn-primary btn-sm btn-block" onclick="document.getElementById('inputFoto').click();">
                        <i class="glyphicon glyphicon-camera"></i>
                        Cargar imagen
                      </button>
                    </div>
                  </div>

                  <h3 id="lblNombreDisplay" style="margin-top:20px;">
                    Nuevo Usuario
                  </h3>
                  <p class="text-muted" id="lblPuestoDisplay">
                    Puesto no especificado
                  </p>
                  <span class="label <?php echo $estatusClass; ?>" id="lblEstatusDisplay">
                    <?php echo $estatusTexto; ?>
                  </span>

                  <hr>

                  <div class="form-group text-left">
                      <label for="nomina">Nómina</label>

                      <div class="nomina-wrapper">
                          <input 
                              type="text"
                              class="form-control"
                              name="nomina"
                              id="nomina"
                              autocomplete="off"
                              inputmode="numeric"
                              maxlength="20"
                          >

                          <span id="iconoNomina" class="nomina-icon"></span>
                      </div>

                      <div id="resultadoNomina"></div>
                  </div>

                  <button type="submit" name="add_user" class="btn btn-roy btn-block">
                    <i class="glyphicon glyphicon-floppy-disk"></i>
                    Guardar Usuario
                  </button>

                  <a href="<?php echo remove_junk($return); ?>" class="btn btn-default btn-block" style="margin-top:10px;">
                    <i class="glyphicon glyphicon-arrow-left"></i>
                    Regresar
                  </a>

                </div>
              </div>
            </div>

            <!-- INFORMACIÓN: PESTAÑAS -->
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
                      <input type="text" class="form-control" name="name" id="inputName" maxlength="50" required placeholder="Nombre completo">
                    </div>

                    <div class="col-md-6 form-group" id="campoUsername">
                      <label class="control-label">Usuario</label>
                      <!-- Campo usuario NO obligatorio por defecto -->
                      <input type="text" class="form-control" name="username" id="inputUsername" maxlength="50" placeholder="Nombre de usuario">
                    </div>

                    <div class="col-md-6 form-group">
                      <label class="control-label">Fecha de nacimiento</label>
                      <input type="text" class="form-control" name="fecha_nacimiento" placeholder="DD/MM/AAAA">
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Sexo</label>
                      <select class="form-control" name="sexo">
                        <option value="M">M</option>
                        <option value="F">F</option>
                      </select>
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Email</label>
                      <input type="email" class="form-control" name="email" maxlength="50" placeholder="correo@ejemplo.com">
                    </div>
                  </div>
                </div>

                <!-- IDENTIFICACIÓN -->
                <div role="tabpanel" class="tab-pane" id="tab-identificacion">
                  <div class="row">
                    <div class="col-md-4 form-group">
                      <label class="control-label">CURP</label>
                      <input type="text" class="form-control" name="CURP" maxlength="50">
                    </div>
                    <div class="col-md-4 form-group">
                      <label class="control-label">RFC</label>
                      <input type="text" class="form-control" name="RFC" maxlength="50">
                    </div>
                    <div class="col-md-4 form-group">
                      <label class="control-label">NSS</label>
                      <input type="text" class="form-control" name="NSS" maxlength="50">
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
                          <option value="<?php echo (int) $dep['ID']; ?>">
                            <?php echo remove_junk($dep['nombre']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Cuadrilla</label>
                      <select class="form-control" name="cuadrilla_id">
                        <option value="">— Sin asignar —</option>
                        <?php if (isset($cuadrillas)): foreach ($cuadrillas as $cua): ?>
                          <option value="<?php echo (int) $cua['id']; ?>">
                            <?php echo remove_junk($cua['etiqueta']); ?>
                          </option>
                        <?php endforeach; endif; ?>
                      </select>
                    </div>
                    <div class="col-md-12 form-group">
                      <label class="control-label">Puesto</label>
                      <input type="text" class="form-control" name="puesto" id="inputPuesto" maxlength="250" placeholder="Puesto u ocupación">
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Fecha de ingreso</label>
                      <input type="date" class="form-control" name="fecha_ingreso">
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="control-label">Salario</label>
                      <select class="form-control" name="salario_id">
                        <option value="">— Sin asignar —</option>
                        <?php foreach ($salarios as $sal): ?>
                          <option value="<?php echo (int) $sal['id']; ?>">
                            <?php echo remove_junk($sal['codigo']); ?> — $<?php echo number_format((float) $sal['salario'], 2); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group col-md-12">
                      <label class="control-label">Estatus laboral</label>
                      <div class="estatus-laboral">
                        <?php foreach ($estatusMap as $idEst => $info): ?>
                          <label style="margin-right:15px;">
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
                          <option value="<?php echo (int) $group['group_level']; ?>">
                            <?php echo remove_junk(ucwords($group['group_name'])); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="col-md-6 form-group" id="campoPasswordGroup">
                      <label class="control-label">Contraseña</label>
                      <input type="password" class="form-control" name="password" id="inputPassword" placeholder="Contraseña">
                    </div>
                  </div>
                  <p class="text-muted" id="notaAccesoEmpleado" style="margin-bottom:10px; display:none;">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    Los empleados no usan usuario ni contraseña: entran con su número de nómina en el kiosco.
                  </p>
                </div>

              </div>
            </div>

          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>