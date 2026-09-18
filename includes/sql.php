<?php
  //require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql){
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id){
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table,$id){
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
/*--------------------------------------------------------------*/
/* Function para cambiar el status del usuario a baja - no eliminar
/*--------------------------------------------------------------*/
function baja_by_id($table,$id){
  global $db;
  if(tableExists($table))
   {
    $sql  = "UPDATE ".$db->escape($table);
    $sql .= " SET statusLaboral_id = 3";
    $sql .= " WHERE id = ". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/
function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login form.
  /*--------------------------------------------------------------*/
  function authenticate($username = '', $password = ''){
      global $db;

      $username = $db->escape($username);

      $sql = sprintf("
          SELECT
               u.id,
              u.username,
              u.password,
              u.user_level,
              g.group_name,
              g.group_level,
              g.group_status
          FROM users u
          INNER JOIN user_groups g
              ON g.id = u.user_level
          WHERE u.username = '%s'
          LIMIT 1
      ", $username);

      $result = $db->query($sql);

      if (!$db->num_rows($result)) {
          return false;
      }

      $user = $db->fetch_assoc($result);

      if ($user['group_status'] != 1) {
          return false;
      }

      $hash = $user['password'];

      // NUEVO MÉTODO
      if (password_verify($password, $hash)) {

          if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {

              $nuevoHash = password_hash($password, PASSWORD_DEFAULT);

              $id = (int)$user['id'];

              $db->query("
                  UPDATE users
                  SET password='{$nuevoHash}'
                  WHERE id='{$id}'
              ");
          }

          return $user;
      }

      // COMPATIBILIDAD CON SHA1 cuando se borren los nuevos inicios de sesion se elimina
      if (sha1($password) === $hash) {

          $nuevoHash = password_hash($password, PASSWORD_DEFAULT);

          $id = (int)$user['id'];

          $db->query("
              UPDATE users
              SET password='{$nuevoHash}'
              WHERE id='{$id}'
          ");

          return $user;
      }

      return false;
  }
  function validar_password_kiosco($usuario_id, $password){

    global $db;

    $usuario_id = (int)$usuario_id;

    if ($usuario_id <= 0 || empty($password)) {
        return false;
    }

    // Obtener el username usando el ID del colaborador
    $sql = "SELECT username
            FROM users
            WHERE id = '{$usuario_id}'
            LIMIT 1";

    $resultado = $db->query($sql);

    if (!$resultado || !$db->num_rows($resultado)) {
        return false;
    }

    $usuario = $db->fetch_assoc($resultado);

    if (empty($usuario['username'])) {
        return false;
    }

    // Reutilizar la autenticación existente
    $autenticado = authenticate(
        $usuario['username'],
        $password
    );

    if (!$autenticado) {
        return false;
    }

    // Confirmar que corresponde al mismo usuario
    if ((int)$autenticado['id'] !== $usuario_id) {
        return false;
    }

    return true;
  }
  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*--------------------------------------------------------------*/
  function current_user(){
      static $current_user;
      global $db;
      if(!$current_user){
         if(isset($_SESSION['user_id'])):
             $user_id = intval($_SESSION['user_id']);
             $current_user = find_by_id('users',$user_id);
        endif;
      }
    return $current_user;
  }

  /*--------------------------------------------------------------*/
  /*  Revisa que el usuario tenga permisos para ver la pagina
  /*--------------------------------------------------------------*/
  function tiene_permiso($clave){

      global $db;

      // VERIFICAR SESIÓN
      if (!isset($_SESSION['user_id'])) {
          return false;
      }

      $usuario_id = (int)$_SESSION['user_id'];

      // OBTENER USUARIO
      $usuario = find_by_id(
          'users',
          $usuario_id
      );

      if (!$usuario) {
          return false;
      }

      // user_level = ID del grupo
      $grupo_id = (int)$usuario['user_level'];

      if ($grupo_id <= 0) {
          return false;
      }

      $clave = $db->escape($clave);

      // =====================================================
      // REVISAR PERMISO
      // =====================================================

      $sql = "
          SELECT mp.vista_id

          FROM menu_permisos mp

          INNER JOIN menu_vistas mv
              ON mv.id = mp.vista_id

          WHERE mp.grupo_id = {$grupo_id}

            AND mv.clave = '{$clave}'

            AND mv.activo = 1

          LIMIT 1
      ";

      $resultado = $db->query($sql);

      if (!$resultado) {
          return false;
      }

      return $db->num_rows($resultado) > 0;
  }
  
  /*--------------------------------------------------------------*/
  /*  Revisa que el usuario realmente necesite acceso
  /*--------------------------------------------------------------*/
  function require_permiso($clave){

    global $session;

    // =====================================================
    // VERIFICAR SESIÓN
    // =====================================================

    if (!$session->isUserLoggedIn(true)) {

        $session->msg(
            'd',
            'Por favor iniciar sesión...'
        );

        header(
            'Location: ' .
            BASE_URL .
            '/index.php'
        );

        exit;
    }


    // =====================================================
    // VERIFICAR PERMISO
    // =====================================================

    if (!tiene_permiso($clave)) {

        $session->msg(
            'd',
            '¡Lo siento! No tienes permiso para ver esta página.'
        );

        header(
            'Location: ' .
            BASE_URL .
            '/home.php'
        );

        exit;
    }


    return true;
  }

  function obtener_menu_usuario(){

      global $db;

      $usuario = current_user();

      if(!$usuario){
          return [];
      }

      $grupo_id = (int)$usuario['user_level'];

      if($grupo_id <= 0){
          return [];
      }

      $sql = "
          SELECT
              mm.id AS modulo_id,
              mm.nombre AS modulo,
              mm.icono,

              mv.id AS vista_id,
              mv.clave,
              mv.nombre AS vista,
              mv.ruta,
              mv.orden AS vista_orden,

              mm.orden AS modulo_orden

          FROM menu_permisos mp

          INNER JOIN menu_vistas mv
              ON mv.id = mp.vista_id

          INNER JOIN menu_modulos mm
              ON mm.id = mv.modulo_id

          WHERE mp.grupo_id = {$grupo_id}

            AND mm.activo = 1
            AND mv.activo = 1

          ORDER BY
              mm.orden ASC,
              mv.orden ASC
      ";

      $resultado = $db->query($sql);

      if(!$resultado){
          return [];
      }

      $menu = [];

      while($fila = $db->fetch_assoc($resultado)){

          $modulo = $fila['modulo'];

          if(!isset($menu[$modulo])){

              $menu[$modulo] = [
                  'icono' => $fila['icono'],
                  'items' => []
              ];
          }

          $menu[$modulo]['items'][] = [

              'id'     => $fila['vista_id'],
              'clave'  => $fila['clave'],
              'nombre' => $fila['vista'],
              'ruta'   => $fila['ruta'],
              'orden'  => $fila['vista_orden']

          ];
      }

      return $menu;
  }

  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/
  function updateLastLogIn($user_id)
  {
      global $db;
      $date = date('Y-m-d H:i:s');
      $sql = "UPDATE users 
              SET last_login = '{$date}' 
              WHERE id = '{$user_id}' 
              LIMIT 1";
      $result = $db->query($sql);
      return ($result && $db->affected_rows() === 1);
  }
  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
  function find_by_nombreZonaTrabajo($val)
  {
    global $db;
    $sql = "SELECT zona FROM departamento WHERE zona = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
  function find_by_groupLevel($level){
      global $db;

      $sql = "SELECT *
              FROM user_groups
              WHERE id = '{$db->escape($level)}'
              LIMIT 1";

      $result = $db->query($sql);

      if($db->num_rows($result)){
          return $db->fetch_assoc($result);
      }

      return false;
  }
  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
  // function page_require_level($require_level){
  //   global $session;

  //   if (!$session->isUserLoggedIn(true)) {
  //     $session->msg('d','Por favor iniciar sesión...');
  //     redirect('index.php', false);
  //   }

  //   if(!isset($_SESSION['group_level'])){
  //     $session->msg('d','Nivel de acceso no definido.');
  //     redirect('home.php', false);
  //   }

  //   $user_level = (int)$_SESSION['group_level'];

  //   $current_user = current_user();
  //   $login_level = find_by_groupLevel($current_user['user_level']);

  //   if(!$login_level){
  //     $session->msg('d','No se encontró el grupo del usuario.');
  //     redirect('home.php', false);
  //   }

  //   if($login_level['group_status'] == '0'){
  //     $session->msg('d','Este nivel de usuario está inactivo!');
  //     redirect('home.php', false);
  //   }

  //   // VALIDACIÓN DE PERMISOS
  //   if($user_level <= (int)$require_level){
  //     return true;
  //   }

  //   $session->msg("d", "¡Lo siento! No tienes permiso para ver la página.");
  //   redirect('home.php', false);
  // }
  function page_require_level($require_level){

    global $session;

    // VERIFICAR SESIÓN
    if (!$session->isUserLoggedIn(true)) {

        $session->msg(
            'd',
            'Por favor iniciar sesión...'
        );

      header('Location: ' . BASE_URL . '/home.php');
      exit;
    }

    // OBTENER USUARIO ACTUAL
    $current_user = current_user();

    if (!$current_user) {

      $session->msg(
        'd',
        'No fue posible obtener los datos del usuario.'
      );

      header('Location: ' . BASE_URL . '/home.php');
      exit;
    }

    // USER_LEVEL AHORA REPRESENTA EL ID DEL GRUPO
    if (
      !isset($current_user['user_level']) ||
      empty($current_user['user_level'])
    ) {

      $session->msg(
          'd',
          'Grupo de usuario no definido.'
      );

      header('Location: ' . BASE_URL . '/home.php');
      exit;
    }

    $grupo_id = (int)$current_user['user_level'];

    // BUSCAR GRUPO POR ID
    $grupo = find_by_id(
        'user_groups',
        $grupo_id
    );

    if (!$grupo) {

      $session->msg(
          'd',
          'No se encontró el grupo del usuario.'
      );

      header('Location: ' . BASE_URL . '/home.php');
      exit;
    }

    // VERIFICAR GRUPO ACTIVO
    if ((int)$grupo['group_status'] === 0) {

      $session->msg(
          'd',
          'Este grupo de usuario está inactivo.'
      );

      header('Location: ' . BASE_URL . '/home.php');
      exit;
    }

    // OBTENER NIVEL REAL DEL GRUPO
    $group_level = (int)$grupo['group_level'];

    // VALIDAR NIVEL
    if ($group_level <= (int)$require_level) {

      return true;
    }

    // SIN PERMISO
    $session->msg(
      'd',
      '¡Lo siento! No tienes permiso para ver la página.'
    );

    header('Location: ' . BASE_URL . '/home.php');
    exit;
  }
   /*--------------------------------------------------------------*/
   /* Function for Finding all product name
   /* JOIN with categorie  and media database table
   /*--------------------------------------------------------------*/
  function join_product_table(){
     global $db;
     $sql  =" SELECT p.id,p.name,p.quantity,p.buy_price,p.sale_price,p.media_id,p.date,c.name";
    $sql  .=" AS categorie,m.file_name AS image";
    $sql  .=" FROM products p";
    $sql  .=" LEFT JOIN categories c ON c.id = p.categorie_id";
    $sql  .=" LEFT JOIN media m ON m.id = p.media_id";
    $sql  .=" ORDER BY p.id ASC";
    return find_by_sql($sql);

   }
  /*--------------------------------------------------------------*/
  /* Function for Finding all product name
  /* Request coming from ajax.php for auto suggest
  /*--------------------------------------------------------------*/

   function find_product_by_title($product_name){
     global $db;
     $p_name = remove_junk($db->escape($product_name));
     $sql = "SELECT name FROM products WHERE name like '%$p_name%' LIMIT 5";
     $result = find_by_sql($sql);
     return $result;
   }

  /*--------------------------------------------------------------*/
  /* Function for Finding all product info by product title
  /* Request coming from ajax.php
  /*--------------------------------------------------------------*/
  function find_all_product_info_by_title($title){
    global $db;
    $sql  = "SELECT * FROM products ";
    $sql .= " WHERE name ='{$title}'";
    $sql .=" LIMIT 1";
    return find_by_sql($sql);
  }

  /*--------------------------------------------------------------*/
  /* Function for Update product quantity
  /*--------------------------------------------------------------*/
  function update_product_qty($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE products SET quantity=quantity -'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);

  }
  /*--------------------------------------------------------------*/
  /* Function for Display Recent product Added
  /*--------------------------------------------------------------*/
 function find_recent_product_added($limit){
   global $db;
   $sql   = " SELECT p.id,p.name,p.sale_price,p.media_id,c.name AS categorie,";
   $sql  .= "m.file_name AS image FROM products p";
   $sql  .= " LEFT JOIN categories c ON c.id = p.categorie_id";
   $sql  .= " LEFT JOIN media m ON m.id = p.media_id";
   $sql  .= " ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);
   return find_by_sql($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for Find Highest saleing Product
 /*--------------------------------------------------------------*/
 function find_higest_saleing_product($limit){
   global $db;
   $sql  = "SELECT p.name, COUNT(s.product_id) AS totalSold, SUM(s.qty) AS totalQty";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON p.id = s.product_id ";
   $sql .= " GROUP BY s.product_id";
   $sql .= " ORDER BY SUM(s.qty) DESC LIMIT ".$db->escape((int)$limit);
   return $db->query($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for find all sales
 /*--------------------------------------------------------------*/
 function find_all_sale(){
   global $db;
   $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " ORDER BY s.date DESC";
   return find_by_sql($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for Display Recent sale
 /*--------------------------------------------------------------*/
function find_recent_sale_added($limit){
  global $db;
  $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " ORDER BY s.date DESC LIMIT ".$db->escape((int)$limit);
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate sales report by two dates
/*--------------------------------------------------------------*/
function find_sale_by_dates($start_date,$end_date){
  global $db;
  $start_date  = date("Y-m-d", strtotime($start_date));
  $end_date    = date("Y-m-d", strtotime($end_date));
  $sql  = "SELECT s.date, p.name,p.sale_price,p.buy_price,";
  $sql .= "COUNT(s.product_id) AS total_records,";
  $sql .= "SUM(s.qty) AS total_sales,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price,";
  $sql .= "SUM(p.buy_price * s.qty) AS total_buying_price ";
  $sql .= "FROM sales s ";
  $sql .= "LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE s.date BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " GROUP BY DATE(s.date),p.name";
  $sql .= " ORDER BY DATE(s.date) DESC";
  return $db->query($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Daily sales report
/*--------------------------------------------------------------*/
function  dailySales($year,$month){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y-%m' ) = '{$year}-{$month}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%e' ),s.product_id";
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Monthly sales report
/*--------------------------------------------------------------*/
function  monthlySales($year){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y' ) = '{$year}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%c' ),s.product_id";
  $sql .= " ORDER BY date_format(s.date, '%c' ) ASC";
  return find_by_sql($sql);
}

/* Funciones nuevas */

/*--------------------------------------------------------------*/
/* Datos del jefe: nombre, nave y zona
/*--------------------------------------------------------------*/
function find_jefe_planilla($jefe_id){
  global $db;

  $jefe_id = (int)$jefe_id;

  $sql = "SELECT
            u.id        AS jefe_id,
            u.name      AS encargado,
            n.nombre    AS nave,
            d.zona     AS departamento,
            dp.nombre AS departamento_plantilla,
            g.nombre    AS grupoAcargo
          FROM cuadrilla c
          INNER JOIN users u ON c.usuario_id = u.id
          INNER JOIN departamento_plantilla dp ON c.depPlantilla_id = dp.id
          INNER JOIN departamento d ON dp.departamento_id = d.ID
          INNER JOIN naves n ON d.nave_id= n.id
          INNER JOIN grupos g ON c.grupo_id = g.id
          WHERE c.usuario_id = {$jefe_id}
            AND u.user_level = 2
            AND u.statusLaboral_id = 1
          LIMIT 1";

  $result = find_by_sql($sql);
  return $result[0] ?? null;
}
/*--------------------------------------------------------------*/
/* Usuarios a cargo del jefe (id, nombre, puesto)
/*--------------------------------------------------------------*/
function find_planilla_by_jefe($jefe_id){
  global $db;

  $jefe_id = (int)$jefe_id;

  $sql = "SELECT
            u.id,
            u.name,
            u.puesto,
            g.nombre            AS grupo,
            dp.nombre             AS departamento_plantilla,
            u.statusLaboral_id
          FROM users u
            INNER JOIN cuadrilla c ON u.cuadrilla_id = c.ID
            INNER JOIN grupos g ON c.grupo_id = g.id
            INNER JOIN departamento_plantilla dp ON c.depPlantilla_id = dp.id
            INNER JOIN departamento d ON dp.departamento_id = d.ID
          WHERE c.usuario_id = {$jefe_id}
          ORDER BY u.id ASC";


  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Funciones para mostrar y paginizar empleados (ADMINISTRADORES)
/*--------------------------------------------------------------*/
//Funcion para contar la vista de los empleados 
function count_view_user_admins(){
    global $db;

    $sql = "SELECT COUNT(*) AS total FROM users WHERE user_level = 1";
    $result = $db->query($sql);
    $row = $db->fetch_assoc($result);

    return (int)$row['total'];
}
//Funcion para paginar la vista de empleados
function find_view_user_admins_paginated($limit, $offset){
    global $db;

    $limit  = (int)$limit;
    $offset = (int)$offset;

    $sql = "SELECT u.id,u.name,u.username,g.group_name,u.statusLaboral_id,
            u.last_login
          FROM users u
            LEFT JOIN user_groups g ON g.id = u.user_level
          where u.user_level = 1
          ORDER BY u.name ASC
          LIMIT {$limit} OFFSET {$offset}";

    return find_by_sql($sql);
} 

/*--------------------------------------------------------------*/
/* Funciones para mostrar y paginizar empleados (users)
/*--------------------------------------------------------------*/

//Funcion para contar la vista de los empleados 
function count_view_user_cuadrilla(){
    global $db;

    $sql = "SELECT 
                COUNT(DISTINCT u.id) AS total
            FROM users u
            INNER JOIN cuadrilla c ON c.usuario_id = u.id
            WHERE u.user_level = 2";
    $result = $db->query($sql);
    $row = $db->fetch_assoc($result);

    return (int)$row['total'];
}
//Funcion para paginar la vista de empleados
function find_view_user_cuadrilla_paginated($limit, $offset){
    global $db;

    $limit  = (int)$limit;
    $offset = (int)$offset;

    $sql = "SELECT 
              u.id,
              u.name,
              u.puesto,
              u.user_level,
              u.statusLaboral_id,
              g.group_name
            FROM users u
            LEFT JOIN user_groups g 
              ON g.id = u.user_level
            WHERE u.user_level = 2
            AND EXISTS (
              SELECT 1
              FROM cuadrilla c
              WHERE c.usuario_id = u.id
            )
            ORDER BY u.id ASC
          LIMIT {$limit} OFFSET {$offset}";

    return find_by_sql($sql);
}  

/*--------------------------------------------------------------*/
/* Funciones para mostrar y paginizar empleados (allUsers)
/*--------------------------------------------------------------*/

//Funcion para contar la vista de los empleados
function count_view_empleados(){
    global $db;

    $sql = "SELECT COUNT(*) AS total FROM vw_usuarios_completos";
    $result = $db->query($sql);
    $row = $db->fetch_assoc($result);

    return (int)$row['total'];
}
//Funcion para paginar la vista de empleados
function find_view_empleados_paginated($limit, $offset){
    global $db;

    $limit  = (int)$limit;
    $offset = (int)$offset;

    $sql = "SELECT 
                v.id,
                v.nombre,
                v.puesto,
                v.departamentos,
                v.grupos,
                v.dep_cuadrilla,
                v.tipo,
                ug.group_name AS rol,
                v.statusLaboral_id
            FROM vw_usuarios_completos v
            LEFT JOIN grupos g       ON v.grupos = g.id
            LEFT JOIN user_groups ug ON v.nivel = ug.id
            WHERE v.nivel != 2
            ORDER BY v.statusLaboral_id ASC, v.id ASC
            LIMIT {$limit} OFFSET {$offset}";

    return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Funciones para mostrar y paginizar Zona De Trabajo
/*--------------------------------------------------------------*/

//Funcion para contar la tabla de zona de trabajo
function count_view_departamento(){
    global $db;

    $sql = "SELECT COUNT(*) AS total FROM departamento_plantilla";
    $result = $db->query($sql);
    $row = $db->fetch_assoc($result);

    return (int)$row['total'];
}
//Funcion para paginar la zona de trabajo
function find_view_zona_paginated($limit, $offset){
  global $db;

  $limit  = (int)$limit;
  $offset = (int)$offset;

  $sql = "SELECT 
            dp.ID,
            n.nombre AS nave,
            d.zona,
            dp.nombre
          FROM departamento_plantilla dp
          	INNER JOIN departamento d ON dp.departamento_id = d.ID
            INNER JOIN naves n ON d.nave_id = n.ID
          ORDER BY n.ID ASC
          LIMIT {$limit} OFFSET {$offset}";

  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Funciones para mostrar y paginizar La Asistencia de la semana
/*--------------------------------------------------------------*/
function get_asistencia_semana($id, $anio, $semana) {
  
  $sql = "SELECT DATE(fecha) AS fecha, asistencia
          FROM asistencia
          WHERE id_empleado = {$id}
            AND YEAR(fecha) = {$anio}
            AND DATE_FORMAT(fecha, '%v') = '{$semana}'
          ORDER BY fecha";
  return find_by_sql($sql);

}
// function find_asistencia_semana_actual() {
        
//   global $db;

//   $semana = date('W');
//   $anio   = date('Y');

//   $where = [];

//   if (!empty($_GET['departamento'])) {
//     $where[] = "v.nomDepartamento = " . (int)$_GET['zona'];
//     echo $where;
//   }

//   if (!empty($_GET['depPlantilla'])) {
//     $where[] = "v.zona = " . (int)$_GET['nombre'];
//     echo $where;
//   }

//   if (!empty($_GET['nave'])) {
//     $where[] = "v.nave = " . (int)$_GET['nave'];
//   }

//   if (!empty($_GET['grupo'])) {
//     $where[] = "v.grupo = " . (int)$_GET['grupo'];
//   }

//   if (!empty($_GET['nomina'])) {
//     $where[] = "v.id_empleado = " . (int)$_GET['nomina'];
//   }

//   if (!empty($_GET['nombre'])) {
//     $nombre = remove_junk($_GET['nombre']);
//     $where[] = "v.nombre LIKE '%{$nombre}%'";
//   }

//   $sql = "SELECT v.*
//           FROM vw_asistencia_semanal v
//           WHERE v.semana_iso = {$semana}
//             AND v.anio = {$anio}
//             AND v.estatus = 1";

//   if (!empty($where)) {
//     $sql .= " AND " . implode(" AND ", $where);
//   }

//   $sql .= " ORDER BY v.nombre ASC";


//   return find_by_sql($sql);
// }
function get_empleado($id) {
  
  $sql = "SELECT 
            u.name, 
            u.puesto, 
            g.nombre AS grupo, 
            dp.nombre AS zona, 
            g.tipo_semana
          FROM users u
            INNER JOIN cuadrilla c ON u.cuadrilla_id = c.ID
            INNER JOIN departamento_plantilla dp ON dp.id = c.depPlantilla_id
            INNER JOIN grupos g ON c.grupo_id = g.id
          WHERE u.id = {$id}
          LIMIT 1";

  $res = find_by_sql($sql);
  return $res[0] ?? null;

}
function get_departamento() {
  
  $sql = "SELECT id, zona FROM departamento ORDER BY id ASC";

  return find_by_sql($sql);
}
function get_dep_plantilla() {
  
  $sql = "SELECT id, nombre FROM departamento_plantilla ORDER BY id ASC";

  return find_by_sql($sql);
}
function get_nave() {
  
  $sql = "SELECT ID, nombre FROM naves ORDER BY ID ASC";

  return find_by_sql($sql);
}
function get_supervisor() {
  
  $sql = "SELECT * FROM users WHERE user_level = 2 ORDER BY id ASC";

  return find_by_sql($sql);
}
function get_grupos() {
  
  $sql = "SELECT * FROM grupos ORDER BY id ASC";

  return find_by_sql($sql);
}
function get_paros() {
  
  $sql = "SELECT * FROM paro_general ORDER BY id ASC";

  return find_by_sql($sql);
}
function get_jefes_cuadrilla() {
  
  $sql = "SELECT 
            u.* 
          FROM users u 
            WHERE user_level = 2 
            AND EXISTS (
              SELECT 1
              FROM cuadrilla c
              WHERE c.usuario_id = u.id
            ) 
          ORDER BY id asc";

  return find_by_sql($sql);
}
function find_asistencia_filtrada() {
  global $db;

  // Semana actual
  $info   = obtenerSemanaISO();
  $semana = (int)$info['semana'];
  $anio   = (int)$info['anio'];

  $where = [];

  // Filtros opcionales
  if (!empty($_GET['nomina'])) {
    $where[] = "v.id_empleado = " . (int)$_GET['nomina'];
  }

  if (!empty($_GET['nombre'])) {
    $nombre = $db->escape($_GET['nombre']);
    $where[] = "v.nombre LIKE '%{$nombre}%'";
  }

  if (!empty($_GET['departamento'])) {
    $where[] = "v.idDepartamento = " . (int)$_GET['departamento'];
  }

  if (!empty($_GET['depPlantilla'])) {
    $where[] = "v.Zona_id = " . (int)$_GET['depPlantilla'];
  }

  if (!empty($_GET['nave'])) {
    $where[] = "v.nave = " . (int)$_GET['nave'];
  }

  if (!empty($_GET['grupo'])) {
    $where[] = "v.idGrupo = " . (int)$_GET['grupo'];
  }

  if (!empty($_GET['jefe'])) {
    $where[] = "c.usuario_id = " . (int)$_GET['jefe'];
  }

  // SQL base
  $sql = "SELECT v.*
          FROM vw_asistencia_semanal v
          INNER JOIN cuadrilla c ON v.id_cuadrilla = c.ID
          WHERE v.semana_iso = {$semana}
            AND v.anio = {$anio}
            AND v.estatus = 1";

  // Aplicar filtros si existen
  if (!empty($where)) {
    $sql .= " AND " . implode(" AND ", $where);
  }

  $sql .= " ORDER BY v.nombre ASC";

  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Funciones usadas para Saldo y periodo de vacaciones Vacaciones
/*--------------------------------------------------------------*/
function get_periodo_vacaciones(){
  
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
          INNER JOIN users u ON vs.usuario_id = u.id";

  return find_by_sql($sql);

}
function get_saldo_vacaciones(){
  
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
          ORDER BY vu.id ASC ";

  return find_by_sql($sql);

}
function update_saldo_vacaciones(){

}

/*--------------------------------------------------------------*/
/* Funciones usadas en reporte de produccion
/*--------------------------------------------------------------*/
function generar_reporte($maquina_id, $fechaReporte){
  global $db;

  $maquina_id = $db->escape($maquina_id);
  $fechaReporte = $db->escape($fechaReporte);

  $query = "INSERT INTO reportes (maquina_id, fecha)
            VALUES ('{$maquina_id}','{$fechaReporte}')";

  return $db->query($query);
}
function autoguardar_reporte_completo($reporte_id, $turno, $supervisor, $operador, $produccion, $fallas){
  global $db;

  $db->query("START TRANSACTION");

  try {

      // Actualizar cabecera
      if(!actualizar_reporte($reporte_id, $turno, $supervisor, $operador)){
          throw new Exception("Error en actualizar_reporte(): " . $db->error);
      }

      // Guardar producción
      $map = guardar_produccion($reporte_id, $produccion);

      if($map === false){
          throw new Exception("Error en guardar_produccion(): " . $db->error);
      }

      // Guardar fallas
      if(!guardar_fallas($reporte_id, $fallas, $map)){
          throw new Exception("Error en guardar_fallas(): " . $db->error);
      }

      $db->query("COMMIT");
      return true;

  } catch (Exception $e) {

      $db->query("ROLLBACK");

      // Mostrar error completo
      echo "<pre>";
      echo "ERROR EN AUTOGUARDADO:\n";
      echo $e->getMessage();
      echo "\n\nMYSQL ERROR:\n";
      echo $db->error;
      echo "</pre>";

      exit();
  }
}
function actualizar_reporte($reporte_id, $turno, $supervisor, $operador){
  global $db;

  $reporte_id = (int)$reporte_id;

  $turno_sql = !empty($turno) ? (int)$turno : "NULL";
  $supervisor_sql = !empty($supervisor) ? (int)$supervisor : "NULL";
  $operador_sql = !empty($operador) ? (int)$operador : "NULL";

  $sql = "UPDATE reportes 
          SET turno_id = $turno_sql,
              supervisor_id = $supervisor_sql,
              operador_id = $operador_sql,
              update_at = NOW()
          WHERE id = $reporte_id";

  return $db->query($sql);
}
function guardar_produccion($reporte_id, $produccion){
  global $db;
  
  $map = [];

  $reporte_id = $db->escape($reporte_id);
  $db->query("DELETE FROM registros_produccion WHERE reporte_id = '$reporte_id'");
  
  $produccion = json_decode($_POST['produccion_json'], true);

  foreach($produccion as $p){

      $hora_inicio = $db->escape($p['hora_inicio']);
      $hora_fin    = $db->escape($p['hora_fin']);
      $evento      = $db->escape($p['evento']);
      $codigo      = $db->escape($p['codigo']);
      $produccionR = $db->escape($p['produccion']);
      $teorico     = $db->escape($p['teorico']);
      $porcentaje  = $db->escape($p['porcentaje']);

      $sql = "INSERT INTO registros_produccion
              (reporte_id,hora_inicio,hora_fin,evento,producto_id,produccion_real,estandar_hora,porcentaje)
              VALUES
              ('$reporte_id','$hora_inicio','$hora_fin','$evento','$codigo','$produccionR','$teorico','$porcentaje')";

      echo($sql);
      if(!$db->query($sql)){
        die($db->error);
      }

        $produccion_id = $db->insert_id;
        $map[$p['fila_id']] = $produccion_id;
    }

  return $map;
}
function guardar_fallas($reporte_id, $fallas){
  global $db;

  $reporte_id = (int)$reporte_id;

  $db->query("DELETE FROM registros_paros WHERE reporte_id = $reporte_id");

  if(empty($fallas)){
      return true;
  }

  foreach($fallas as $f){

    $paro_id = isset($f['paro_id']) ? (int)$f['paro_id'] : 0;
    $subparo_id = isset($f['subparo_id']) ? (int)$f['subparo_id'] : 0;
    $minutos = $db->escape($f['minutos']);

    if(!empty($f['fila_id']) && isset($map[$f['fila_id']])){
      $produccion_id = $map[$f['fila_id']];
    }else{
      $produccion_id = "NULL"; // cambio de código
    }

    $sql = "INSERT INTO registros_paros
            (reporte_id, produccion_id, paro_id, subParos_id, minutos)
            VALUES
            ($reporte_id, $produccion_id, $paro_id, $subparo_id, '$minutos')";

    if(!$db->query($sql)){
        die($db->error);
    }

  }

  return true;
}
function obtener_reportes_activos(){
  global $db;

  $sql = "SELECT 
            r.id,
            g.nombre AS grupo,
            m.nombre AS maquina,
            r.fecha AS fecha
          FROM reportes r
          left JOIN maquinas m ON r.maquina_id = m.id 
          left JOIN grupos g ON r.turno_id = g.id
            WHERE estado = 'ACTIVO'
          ORDER BY r.id DESC";

  return $db->query($sql);
}
function obtener_reportes(){
  global $db;

  $sql = "SELECT 
            r.id,
            r.fecha,
            g.nombre AS grupo,
            m.nombre AS maquina,
            u.name AS supervisor,
            uu.name AS operador,
            r.estado
          FROM reportes r
            INNER JOIN grupos g ON r.turno_id = g.id
            INNER JOIN maquinas m ON r.maquina_id = m.id
            INNER JOIN users u ON r.supervisor_id = u.id
            LEFT JOIN users uu ON r.operador_id = uu.id
          ORDER BY r.id DESC";

  return $db->query($sql);
}
/*--------------------------------------------------------------*/
/* Funciones usadas en registros de produccion
/*--------------------------------------------------------------*/
function count_view_registros_produccion(){
  global $db;

  $sql = "SELECT COUNT(*) AS total FROM vw_registros_produccion";
  $result = $db->query($sql);
  $row = $db->fetch_assoc($result);

  return (int)$row['total'];
}
function find_view_registros_produccion($limite, $offset, $tipo){
  global $db;

  if($tipo == 'dia'){
    $sql = "SELECT 
              fecha,
              TURNO,
              MAQUINA,
              producto_id,
              descripcion,
              SUM(produccion_real) AS produccion_real,
              piezasXhora,
              (SUM(produccion_real) / piezasXhora) * 100 AS porcentaje
            FROM vw_registros_produccion
            GROUP BY fecha, TURNO, MAQUINA, producto_id
            ORDER BY fecha DESC
            LIMIT {$limite} OFFSET {$offset}";
  }elseif($tipo == 'semana'){
    $sql = "SELECT 
              YEAR(fecha) AS anio,
              WEEK(fecha) AS semana,
              TURNO,
              MAQUINA,
              producto_id,
              descripcion,
              SUM(produccion_real) AS produccion_real,
              piezasXhora,
              (SUM(produccion_real) / piezasXhora) * 100 AS porcentaje
            FROM vw_registros_produccion
            GROUP BY anio, semana, TURNO, MAQUINA, producto_id
            ORDER BY anio DESC, semana DESC
            LIMIT {$limite} OFFSET {$offset}";
  }elseif($tipo == 'mes'){
    $sql = "SELECT 
              YEAR(fecha) AS anio,
              MONTH(fecha) AS mes,
              TURNO,
              MAQUINA,
              producto_id,
              descripcion,
              SUM(produccion_real) AS produccion_real,
              piezasXhora,
              (SUM(produccion_real) / piezasXhora) * 100 AS porcentaje
            FROM vw_registros_produccion
            GROUP BY anio, mes, TURNO, MAQUINA, producto_id
            ORDER BY anio DESC, mes DESC
            LIMIT {$limite} OFFSET {$offset}";
  }

  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Funciones usadas en detalle de empleado
/*--------------------------------------------------------------*/

function view_detalle_empleado($id){
  global $db;

  $sql = "SELECT 
            u.id,
            u.name as nombre,
            u.puesto,
            u.fecha_ingreso,
            u.CURP,
            u.RFC,
            u.NSS,
            u.image,
            u.statusLaboral_id,
            c.ID AS cuadrilla,
            g.nombre AS grupo,
            dp.nombre AS zonaTrabajo,
            d.zona AS departamento
          FROM users u
            LEFT JOIN cuadrilla c ON c.ID = u.cuadrilla_id
            LEFT JOIN grupos g ON c.grupo_id = g.id
            LEFT JOIN departamento_plantilla dp ON dp.id = c.depPlantilla_id
            LEFT JOIN departamento d ON d.ID = dp.departamento_id
          WHERE u.id = '{$id}'
          LIMIT 1";

  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Funciones usadas en registros de paros
/*--------------------------------------------------------------*/
function count_view_registros_paros(){
  global $db;

  $sql = "SELECT COUNT(*) AS total FROM vw_registros_paros";
  $result = $db->query($sql);
  $row = $db->fetch_assoc($result);

  return (int)$row['total'];
}
function find_view_registros_paros($limit, $offset){
  global $db;

  $limit  = (int)$limit;
  $offset = (int)$offset;

  $sql = "SELECT 
            *
          FROM vw_registros_paros
          ORDER BY fecha ASC
          LIMIT {$limit} OFFSET {$offset}";

  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Funciones usadas en agregar un equipo al inventario
/*--------------------------------------------------------------*/
function get_equipo_tipo() {
  
  $sql = "SELECT * FROM equipo_tipo ORDER BY id ASC";

  return find_by_sql($sql);
}
function get_equipo_plan() {
  
  $sql = "SELECT id,nombre FROM equipo_plan ORDER BY id ASC";

  return find_by_sql($sql);
}
function generarCodigoEquipo($db, $tipo, $id){
    $res = $db->query("SELECT prefijo FROM equipo_tipo WHERE id = $tipo");
    $prefijo = $res->fetch_assoc()['prefijo'];

    return 'ROY' . $prefijo . str_pad($id, 3, '0', STR_PAD_LEFT);
}
/*--------------------------------------------------------------*/
/* Funciones usadas en el inventario
/*--------------------------------------------------------------*/
function count_view_equipos(){
  global $db;

  $sql = "SELECT COUNT(*) AS total FROM equipo";
  $result = $db->query($sql);
  $row = $db->fetch_assoc($result);

  return (int)$row['total'];
}
function find_view_equipos(){
  global $db;

  $sql = "SELECT e.id, e.codigo_equipo, e.marca, e.modelo, et.nombre AS tipo 
          FROM equipo e
          INNER JOIN equipo_tipo et ON e.tipo_equipo = et.id
          ORDER BY e.id ASC";

  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Funciones usadas la vista de asignacion de equipos
/*--------------------------------------------------------------*/
function count_view_equipos_asignados(){
  global $db;

  $sql = "SELECT COUNT(*) AS total 
          FROM equipo_entrega ee
            INNER JOIN equipo e ON ee.id_equipo = e.id
          WHERE e.tipo_equipo = 1 OR e.tipo_equipo = 4";
  $result = $db->query($sql);
  $row = $db->fetch_assoc($result);

  return (int)$row['total'];
}
function find_view_equipos_asignados($limit, $offset){
  global $db;

  $limit  = (int)$limit;
  $offset = (int)$offset;

  $sql = "SELECT 
            ee.id AS idEntrega,
            e.id AS equipo_id,
            e.codigo_equipo,
            et.nombre AS tipo,
            e.modelo,
            ee.id_usuario AS nomina,
            u.`name` AS usuario,
            ee.estado
          FROM equipo_entrega ee
            INNER JOIN equipo e ON ee.id_equipo = e.id
            INNER JOIN equipo_tipo et ON e.tipo_equipo = et.id
            INNER JOIN equipo_detalle ed ON e.id = ed.id_equipo
            INNER JOIN users u ON ee.id_usuario = u.id
         	WHERE et.id = 1 OR et.id = 4
          ORDER BY e.id ASC
            LIMIT {$limit} OFFSET {$offset}";

  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Funciones usadas la vista registro capacitacion
/*--------------------------------------------------------------*/
function find_asistencia_capacitacion($id_capacitacion) {
  global $db;

  $sql = "SELECT 
            hc.id,
            u.id AS nomina,
            u.name,
            u.puesto,
            d.zona
          FROM registro_capacitaciones rc
            INNER JOIN historico_capacitaciones hc ON rc.id_capacitacion = hc.id
            INNER JOIN users u ON rc.id_usuario = u.id
            LEFT JOIN cuadrilla c ON c.ID = u.cuadrilla_id
            LEFT JOIN grupos g ON c.grupo_id = g.id
            LEFT JOIN departamento_plantilla dp ON dp.id = c.depPlantilla_id
            LEFT JOIN departamento d ON d.ID = dp.departamento_id
          WHERE hc.id = {$id_capacitacion}
          ORDER BY hc.fecha DESC";

  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Funciones usadas la vista de historico de capacitacion
/*--------------------------------------------------------------*/
function count_view_capacitaciones(){
  global $db;

  $sql = "SELECT COUNT(*) AS total 
          FROM historico_capacitaciones";
  $result = $db->query($sql);
  $row = $db->fetch_assoc($result);

  return (int)$row['total'];
}
function find_view_capacitaciones_paginated($limit, $offset){
    global $db;

    $limit  = (int)$limit;
    $offset = (int)$offset;

    $sql = "SELECT
              id,
              nombre,
              instructor,
              fecha,
              estatus
            FROM historico_capacitaciones
            WHERE estatus = 'FINALIZADA'
            ORDER BY fecha DESC
            LIMIT {$limit}
            OFFSET {$offset}";

    return find_by_sql($sql);
}
function find_capacitacion_activa(){
    global $db;

    $sql = "SELECT
              id,
              nombre,
              instructor,
              fecha,
              estatus
            FROM historico_capacitaciones
            WHERE estatus = 'ACTIVA'
            ORDER BY fecha DESC";

    return find_by_sql($sql);

}
/*--------------------------------------------------------------*/
/* Funciones usadas para lista de asistencia PDF
/*--------------------------------------------------------------*/
function find_datos_capacitacion($id_capacitacion){
    global $db;

    $sql = "SELECT 
              id,
              nombre AS capacitacion,
              fecha,
              TIMEDIFF(hora_fin, hora_inicio) AS duracion,
              instructor,
              hora_inicio,
              hora_fin
            FROM historico_capacitaciones
            WHERE id = {$id_capacitacion}";

    return find_by_sql($sql);
}

function find_participantes_capacitacion($id_capacitacion){
    global $db;

    $sql = "SELECT 
                hc.id AS id_capacitacion,
                u.id AS nomina,
                u.name AS nombre,
                u.puesto,
                d.zona AS departamento
            FROM registro_capacitaciones rc
              INNER JOIN historico_capacitaciones hc ON rc.id_capacitacion = hc.id
              INNER JOIN users u ON rc.id_usuario = u.id
              INNER JOIN cuadrilla c ON u.cuadrilla_id = c.ID
              INNER JOIN departamento_plantilla dp ON c.depPlantilla_id = dp.id
              INNER JOIN departamento d ON dp.departamento_id = d.ID
            WHERE rc.id_capacitacion = {$id_capacitacion}";

    return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Funciones para la vista de almacen inventario
/*--------------------------------------------------------------*/

//Funcion para contar los productos del inventario
function count_inventario(){
    global $db;

    $sql = "SELECT COUNT(*) AS total FROM almacen_producto";
    $result = $db->query($sql);
    $row = $db->fetch_assoc($result);

    return (int)$row['total'];
}
//Funcion para paginar la vista de empleados
function find_productos_paginated($limit, $offset){
    global $db;

    $limit  = (int)$limit;
    $offset = (int)$offset;

    $sql = "SELECT 
              p.id,
              pd.id_media,
              coalesce(m.file_name,'no_media.jpg') AS foto,
              pd.des_gral,
              pf.denominacion AS categoria,
              p.stock_total AS stock,
              p.fecha_registro AS fecha
            FROM almacen_producto p
            INNER JOIN almacen_producto_desc pd ON pd.id_producto = p.id
            LEFT JOIN media m ON m.id = pd.id_media
            left JOIN almacen_producto_familia pf ON pf.id = pd.id_familia
            left JOIN almacen_producto_seccion ps ON ps.id = p.id_seccion
            ORDER BY p.id ASC
            LIMIT {$limit} OFFSET {$offset}";

    return find_by_sql($sql);
}
function find_producto($id){
    global $db;

    $sql = "SELECT 
              p.id,
              pd.id_media,
              m.file_name AS foto,
              pd.nombre,
              pd.des_gral,
              pd.des_detallada,
              pt.nombre AS categoria,
              p.stock_total AS stock,
              p.fecha_registro AS fecha
            FROM producto p
              INNER JOIN producto_desc pd ON pd.id_producto = p.id
              INNER JOIN media m ON m.id = pd.id_media
              INNER JOIN producto_tipo pt ON pt.id = pd.id_tipo
              INNER JOIN producto_seccion ps ON ps.id = p.id_seccion
            WHERE p.id = {$id}";

    return find_by_sql($sql);
}
function find_categorias(){
    global $db;

    $sql = "SELECT * from almacen_producto_familia";

    return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Funciones para la mesa de ayuda
/*--------------------------------------------------------------*/


// Funcion para el historial de TODOS los tickets
function get_historial_tickets(){
    global $db;

    $sql = "SELECT
                  t.id,
                  t.folio,
                  t.asunto,
                  tc.nombre_categoria AS categoria,
                  te.nombre_estatus AS estatus,
                  t.fecha_creacion,
                  t.asignado_a AS asignado_id,
                  IFNULL(asig.name,'Sin asignar') AS asignado
              FROM ticket t
              INNER JOIN ticket_categoria tc ON tc.id = t.categoria_id
              INNER JOIN ticket_estatus te ON te.id = t.estatus_id
              LEFT JOIN users asig ON asig.id = t.asignado_a
              ORDER BY
                  CASE
                      WHEN t.estatus_id IN (1,2,3) THEN 0
                      ELSE 1
                  END, t.fecha_creacion DESC";

    return find_by_sql($sql);
}
function get_ticket_historico($id){
    global $db;

    $sql = "SELECT
                    th.*,
                    ta.nombre_accion,
                    te1.nombre_estatus AS anterior,
                    te2.nombre_estatus AS nuevo,
                    u.name
                FROM ticket_historico th
                    INNER JOIN ticket_accion ta ON ta.id = th.accion_id
                    LEFT JOIN ticket_estatus te1 ON te1.id=th.estatus_anterior
                    LEFT JOIN ticket_estatus te2 ON te2.id=th.estatus_nuevo
                    LEFT JOIN users u ON u.id=th.usuario_movimiento
                WHERE th.ticket_id = {$id}
                ORDER BY th.fecha ASC";

    return find_by_sql($sql);
}
function get_ticket_historico_cierre($id){
    global $db;

    $user_id = $id;

    $sql = "SELECT
              th.*,
              ta.nombre_accion,
              te1.nombre_estatus AS anterior,
              te2.nombre_estatus AS nuevo,
              u.name
            FROM ticket_historico th
              INNER JOIN ticket_accion ta ON ta.id = th.accion_id
              LEFT JOIN ticket_estatus te1 ON te1.id=th.estatus_anterior
              LEFT JOIN ticket_estatus te2 ON te2.id=th.estatus_nuevo
              LEFT JOIN users u ON u.id=th.usuario_movimiento
            WHERE th.ticket_id = 1 AND th.accion_id IN (1,3,7)
            ORDER BY th.fecha ASC;";

    return find_by_sql($sql);
}
function get_ticket_comentarios($id){
    global $db;

    $sql = "SELECT
              tc.*,
              u.name
            FROM ticket_comentario tc
              INNER JOIN users u ON u.id=tc.usuario_id
            WHERE tc.ticket_id = {$id}
              ORDER BY tc.fecha ASC";

    return find_by_sql($sql);
}
function get_historial_admin($id){
    global $db;

    $user_id = $id;

    $sql = "SELECT
                t.id,
                t.folio,
                t.asunto,
                tc.nombre_categoria AS categoria,
                te.nombre_estatus AS estatus,
                t.fecha_creacion,
                t.asignado_a AS asignado_id,
                IFNULL(asig.name,'Sin asignar') AS asignado
            FROM ticket t
            INNER JOIN ticket_categoria tc
                ON tc.id = t.categoria_id
            INNER JOIN ticket_estatus te
                ON te.id = t.estatus_id
            LEFT JOIN users asig
                ON asig.id = t.asignado_a
            WHERE t.asignado_a = {$user_id} AND asig.id = {$user_id}
            ORDER BY
                CASE
                    WHEN t.estatus_id IN (1,2,3) THEN 0
                    ELSE 1
                END, t.fecha_creacion DESC";

    return find_by_sql($sql);
}
function get_historial_user($id){
    global $db;

    $user_id = $id;

    $sql = "SELECT
              t.id,
              t.folio,
              t.asunto,
              tc.nombre_categoria AS categoria,
              te.nombre_estatus AS estatus,
              t.fecha_creacion,
              IFNULL(asig.name,'Sin asignar') AS asignado
          FROM ticket t
          INNER JOIN ticket_categoria tc
              ON tc.id = t.categoria_id
          INNER JOIN ticket_estatus te
              ON te.id = t.estatus_id
          LEFT JOIN users asig
              ON asig.id = t.asignado_a
          WHERE t.usuario_id = {$user_id}
          ORDER BY
              CASE
                  WHEN t.estatus_id IN (1,2,3) THEN 0
                  ELSE 1
              END,
              t.fecha_creacion DESC";

    return find_by_sql($sql);
}
function get_detalle_ticket($id){
    global $db;

    $sql = "SELECT
              t.*,
              u.name AS empleado,
              IFNULL(eq.codigo_equipo,'N/A') AS codigo_equipo,
              eq.marca,
              eq.modelo,
              ed.numero_serie AS serie,
              tc.nombre_categoria,
              tp.nombre_prioridad,
              te.nombre_estatus,
              asig.name AS responsable,
              d.zona AS departamento
          FROM ticket t
              INNER JOIN users u ON u.id = t.usuario_id
              LEFT JOIN equipo eq ON eq.id = t.equipo_id
              LEFT JOIN equipo_detalle ed ON ed.id_equipo = eq.id
              INNER JOIN ticket_categoria tc ON tc.id = t.categoria_id
              LEFT JOIN ticket_prioridad tp ON tp.id = t.prioridad_id
              INNER JOIN ticket_estatus te ON te.id = t.estatus_id
              LEFT JOIN users asig ON asig.id = t.asignado_a
              LEFT JOIN equipo_ubicacion eu ON eu.id_equipo = t.equipo_id
              LEFT JOIN departamento d ON d.ID = eu.id_departamento
            WHERE t.id={$id}
            LIMIT 1";

    return find_by_sql($sql);
}
function get_equipos_asignados_usuarios($id){
    global $db;

    $sql = "SELECT 
              e.id, 
              e.codigo_equipo, 
              e.marca, 
              e.modelo,
              et.nombre AS tipo
            FROM equipo e
              INNER JOIN equipo_entrega ee ON ee.id_equipo = e.id
              INNER JOIN users u ON ee.id_usuario = u.id
              INNER JOIN equipo_tipo et ON et.id = e.tipo_equipo
            WHERE u.id = {$id}
            ORDER BY e.codigo_equipo ASC ";

    return find_by_sql($sql);
}
function get_ticket_agentes(){
    global $db;

    $sql = "SELECT id, name FROM users WHERE user_level = 1 ORDER BY name ASC";

    return find_by_sql($sql);
}
function get_ticket_categoria(){
    global $db;

    $sql = "SELECT id, nombre_categoria FROM ticket_categoria ORDER BY id ASC";

    return find_by_sql($sql);
}
function get_ticket_impresoras(){
    global $db;

    $sql = "SELECT
                        e.id,
                        e.codigo_equipo,
                        e.marca,
                        e.modelo,
                        et.nombre AS tipo,
                        d.zona AS departamento
                    FROM equipo_ubicacion eu 
                        INNER JOIN departamento d ON d.ID = eu.id_departamento
                        INNER JOIN equipo e ON e.id = eu.id_equipo
                        INNER JOIN equipo_tipo et ON et.id = e.tipo_equipo
                        WHERE et.id = 5";

    return find_by_sql($sql);
}
function get_ticket_zebras(){
    global $db;

    $sql = "SELECT
                    e.id,
                    e.codigo_equipo,
                    e.marca,
                    e.modelo,
                    et.nombre AS tipo,
                    d.zona AS departamento
                FROM equipo_ubicacion eu 
                    INNER JOIN departamento d ON d.ID = eu.id_departamento
                    INNER JOIN equipo e ON e.id = eu.id_equipo
                    INNER JOIN equipo_tipo et ON et.id = e.tipo_equipo
                    WHERE et.id = 6";

    return find_by_sql($sql);
}
function get_ticket_prioridades(){
    global $db;

    $sql = "SELECT id, nombre_prioridad FROM ticket_prioridad ORDER BY id ASC";

    return find_by_sql($sql);
}
function find_view_equipos_historial($limit, $offset){
  global $db;

  $limit  = (int)$limit;
  $offset = (int)$offset;

  $sql = "SELECT 
            e.id, 
            e.codigo_equipo, 
            e.marca, e.modelo, 
            et.nombre AS tipo,
            COUNT(t.id) AS total_tickets,
            SUM(CASE WHEN t.estatus_id NOT IN (5,6) THEN 1 ELSE 0 END) AS tickets_abiertos,
            MAX(t.fecha_creacion) AS ultimo_reporte 
          FROM equipo e
            LEFT JOIN equipo_tipo et ON e.tipo_equipo = et.id
            LEFT JOIN ticket t ON t.equipo_id = e.id
          GROUP BY e.id, e.codigo_equipo, e.marca, e.modelo, et.nombre
          ORDER BY total_tickets DESC, e.codigo_equipo ASC
          LIMIT {$limit} OFFSET {$offset}";

  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Funciones para el kiosco
/*--------------------------------------------------------------*/
function get_datos_kiosco($id){
  global $db;

  $sql = "SELECT
            *
          FROM vw_vacaciones_usuario 
          WHERE id = '{$id}' AND estatus = 'VIGENTE'
          LIMIT 1";

  return find_by_sql($sql);
}
function get_solicitudes_vacaciones_usuario($id){
  global $db;

  $sql = "SELECT 
            v.fecha_solicitud,
            v.fecha_inicio,
            v.fecha_fin,
            v.dias,
            v.estatus
          FROM vacaciones v
          WHERE v.usuario_id = {$id}";

  return find_by_sql($sql);
}
function get_disponibilidad_vacaciones($departamento, $fechaInicio, $fechaFin){

    global $db;

    $departamento = (int)$departamento;

    $sql = "SELECT
                v.fecha_inicio,
                v.fecha_fin
            FROM vacaciones v
              INNER JOIN users u ON u.id = v.usuario_id
              INNER JOIN cuadrilla c ON c.ID = u.cuadrilla_id
              INNER JOIN departamento_plantilla dp ON dp.id = c.depPlantilla_id
            WHERE dp.id = {$departamento}
              AND v.estatus IN ('PENDIENTE_RH','APROBADA')
              AND (
                    v.fecha_inicio <= '{$fechaFin}'
                AND v.fecha_fin    >= '{$fechaInicio}'
              )";

    return find_by_sql($sql);
}

// Funciones usadas para el modulo de normas de seguridad e higiene
function find_all_normas(){
  
  global $db;
  
  $sql = "SELECT
            sn.ID,
            sc.nombre AS clasificacion,
            sn.codigo_norma,
            sn.nombre_norma,
            sn.imagen,
            COUNT(snp.id) AS puntos
          FROM seguridad_normas sn
          INNER JOIN seguridad_clasificacion sc ON sc.id = sn.id_clasificacion
          LEFT JOIN seguridad_normas_p snp ON snp.id_norma = sn.id
          GROUP BY
            sn.ID,
            sc.nombre,
            sn.codigo_norma,
            sn.nombre_norma,
            sn.imagen
          ORDER BY 
            sn.ID ASC";

  return find_by_sql($sql);
}

function get_coordinador_seguridad() {

    $sql = "SELECT * FROM users WHERE user_level = 12 ORDER BY id ASC";

    return find_by_sql($sql);

}

function get_norma_clasificaciones($id_norma){
  global $db;

  $sql = "SELECT 
              sn.id, 
              sn.id_clasificacion, 
              sc.nombre AS clasificacion,
              sn.codigo_norma, 
              sn.nombre_norma, 
              sn.imagen
          FROM seguridad_normas sn
          INNER JOIN seguridad_clasificacion sc ON sc.id = sn.id_clasificacion
          WHERE sn.id = 1
          LIMIT 1";

  return find_by_sql($sql);
}

function get_puntos_norma($id_norma){
  global $db;

  $sql = "SELECT 
              snp.id,
              snp.`no` AS punto,
              snp.desc_requisito AS requisito,
              snp.tipo_comprobacion AS comprobacion,
              snp.desc_evidencia AS evidencia
          FROM seguridad_normas_p snp
          WHERE snp.id_norma = {$id_norma}
          ORDER BY snp.`no` ASC";

  return find_by_sql($sql);
}

function find_clasificaciones_normas(){
  
  global $db;
  
  $sql = "SELECT id, nombre FROM seguridad_clasificacion ORDER BY nombre ASC";

  return find_by_sql($sql);
}

// Funciones para el historial de vacaciones
function find_all_solicitudes_aprobadas(){
  
  global $db;
  
  $sql = "SELECT
            v.usuario_id AS nomina,
            u.name AS nombre,
            v.fecha_solicitud,
            v.fecha_inicio AS inicio,
            v.fecha_fin AS fin,
            v.dias,
            v.estatus
          FROM vacaciones v
            INNER JOIN users u ON u.id = v.usuario_id
          WHERE v.estatus IN 
          ('APROBADA',
          'FINALIZADA',
          'CANCELADA')
          ORDER BY v.fecha_solicitud DESC";

  return find_by_sql($sql);
}
?>
