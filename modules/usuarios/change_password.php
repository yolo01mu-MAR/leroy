<?php
  $page_title = 'Cambiar contraseña';
  require_once __DIR__ . '/../../app/bootstrap.php';
  // Checkin What level user has permission to view this page

  $scripts = [
    'change_password'
  ];

  $user = current_user(); 
  $current = current_user();

  if (isset($_GET['logout'])) {
    echo "<script>
      setTimeout(function() {
        window.location.href = 'index.php';
      }, 5000);
    </script>";
  }
  if(isset($_GET['cancel'])){
    $session->msg('d','Opción cancelada por el usuario.');
    redirect('edit_account.php', false);
  }
  if(isset($_POST['update'])){

    $req_fields = array('new-password','old-password','id' );
    validate_fields($req_fields);

    if(empty($errors)){

            if (!password_verify($_POST['old-password'], $current['password'])) {
                $session->msg('d', 'Tu antigua contraseña no coincide');
                redirect('change_password.php', false);
            }

            $id = (int)$_POST['id'];
            $new = password_hash($_POST['new-password'], PASSWORD_DEFAULT);
            $new = $db->escape($new);
            $sql = "UPDATE users SET password ='{$new}' WHERE id='{$db->escape($id)}'";
            $result = $db->query($sql);
                if($result && $db->affected_rows() === 1):
                  $session->logout();
                  $session->msg(
                    's',
                    'Contraseña actualizada correctamente. La sesión se cerrará en 5 segundos.'
                  );
                  redirect('change_password.php?logout=1', false);
                else:
                  $session->msg('d',' Lo siento, actualización falló.');
                  redirect('change_password.php', false);
                endif;
    } else {
      $session->msg("d", $errors);
      redirect('change_password.php',false);
    }
  }
?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<div style="width:100%; max-width:400px; margin:0 auto 10px; text-align:left;">
  <a href="change_password.php?cancel=1"
    class="btn btn-danger btn-sm">
    <span class="glyphicon glyphicon-arrow-left"></span> Cancelar
  </a>
</div>
<div class="login-page">
    <div class="text-center">
       <h3>Cambiar contraseña</h3>
    </div>
     <?php echo display_msg($msg); ?>
      <form method="post" action="change_password.php" class="clearfix">
        <div class="form-group">
          <label>Nueva contraseña</label>
          <div class="input-group">
            <input type="password" class="form-control" name="new-password" id="newPass">
            <span class="input-group-btn">
              <button type="button" class="btn btn-default" onclick="togglePass('newPass', this)">
                <span class="glyphicon glyphicon-eye-open"></span>
              </button>
            </span>
          </div>
        </div>
        <div class="form-group">
          <label>Antigua contraseña</label>
          <div class="input-group">
            <input type="password" class="form-control" name="old-password" id="oldPass">
            <span class="input-group-btn">
              <button type="button" class="btn btn-default" onclick="togglePass('oldPass', this)">
                <span class="glyphicon glyphicon-eye-open"></span>
              </button>
            </span>
          </div>
        </div>
<div class="form-group text-center">
  <input type="hidden" name="id" value="<?php echo (int)$user['id'];?>">
  <button type="submit" name="update" class="btn btn-info">
    <span class="glyphicon glyphicon-refresh"></span> Cambiar
  </button>
</div>

    </form>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
