<?php
  $page_title = 'Agregar usuarios';
  require_once __DIR__ . '/../../app/bootstrap.php';
  page_require_level(1);

  $groups = find_all('user_groups');

  $tipo = $_GET['tipo'] ?? $_POST['tipo'] ?? 'usuario';

  // Nivel fijo según el tipo
  $nivel_fijo = null;

  switch ($tipo) {
    case 'admin':
      $nivel_fijo = 1; // ADMINISTRADOR
      break;

    case 'jefe':
      $nivel_fijo = 2; // JEFE DE CUADRILLA
      break;

    default:
      $nivel_fijo = null; // usuario normal
  }

  if (isset($_POST['add_user'])) {

    $req_fields = array('full-name','username','password','level');
    validate_fields($req_fields);

    if (empty($errors)) {

      $name       = remove_junk($db->escape($_POST['full-name']));
      $username   = remove_junk($db->escape($_POST['username']));
      $password   = sha1(remove_junk($db->escape($_POST['password'])));
      $user_level = (int)$db->escape($_POST['level']);

      $query  = "INSERT INTO users (name,username,password,user_level,status) ";
      $query .= "VALUES ('{$name}','{$username}','{$password}','{$user_level}','1')";

      if ($db->query($query)) {
        $session->msg('s', 'Cuenta creada correctamente');

        switch ($tipo) {
          case 'admin':
            redirect('admins.php');
            break;
          case 'jefe':
            redirect('jefes.php');
            break;
          default:
            redirect('users.php');
        }

      } else {
        $session->msg('d', 'No se pudo crear la cuenta.');
        redirect('add_user.php?tipo='.$tipo, false);
      }

    } else {
      $session->msg('d', $errors);
      redirect('add_user.php?tipo='.$tipo, false);
    }
  }
?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
  <?php echo display_msg($msg); ?>
  <div class="row">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Agregar usuario</span>
       </strong>
        <a href="<?php
          switch ($tipo) {
            case 'admin':
              echo 'adminUsers.php';
              break;
            case 'jefe':
              echo 'users.php';
              break;
            default:
              echo 'allUsers.php';
          }
        ?>" class="btn btn-danger btn-xs pull-right">
          <span class="glyphicon glyphicon-remove"></span> Cancelar
        </a>
      </div>
      <div class="panel-body">
        <div class="col-md-6">
          <form method="post" action="add_user.php">
            <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
            <div class="form-group">
              <label>Nombre</label>
              <input type="text" class="form-control" name="full-name" required>
            </div>
            <div class="form-group">
              <label>Usuario</label>
              <input type="text" class="form-control" name="username">
            </div>
            <div class="form-group">
              <label>Contraseña</label>
              <input type="password" class="form-control" name="password">
            </div>
            <div class="form-group">
              <label>Rol de usuario</label>
              <?php if ($nivel_fijo !== null): ?>
                <!-- Rol fijo -->
                <input type="hidden" name="level" value="<?php echo $nivel_fijo; ?>">
                <input type="text"
                      class="form-control"
                      value="<?php echo ($nivel_fijo == 1) ? 'ADMINISTRADOR' : 'JEFE DE CUADRILLA'; ?>"
                      disabled>
              <?php else: ?>
                <!-- Usuario normal -->
                <select class="form-control" name="level" required>
                  <?php foreach ($groups as $group): ?>
                    <?php if ($group['group_level'] == 3): ?>
                      <option value="<?php echo $group['group_level']; ?>">
                        <?php echo ucwords($group['group_name']); ?>
                      </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
              <?php endif; ?>
            </div>
            <button type="submit" name="add_user" class="btn btn-primary">
              Guardar
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
