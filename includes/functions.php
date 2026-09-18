<?php
 $errors = array();

 /*--------------------------------------------------------------*/
 /* Function for Remove escapes special
 /* characters in a string for use in an SQL statement
 /*--------------------------------------------------------------*/
  function real_escape($str){
    global $con;
    $escape = mysqli_real_escape_string($con,$str);
    return $escape;
  }
  /*--------------------------------------------------------------*/
  /* Function for Remove html characters
  /*--------------------------------------------------------------*/
  function remove_junk($str){
    $str = nl2br($str);
    $str = htmlspecialchars(strip_tags($str, ENT_QUOTES));
    return $str;
  }
  /*--------------------------------------------------------------*/
  /* Function for Uppercase first character
  /*--------------------------------------------------------------*/
  function first_character($str){
    $val = str_replace('-'," ",$str);
    $val = ucfirst($val);
    return $val;
  }
  /*--------------------------------------------------------------*/
  /* Function for Checking input fields not empty
  /*--------------------------------------------------------------*/
  function validate_fields($var){
    global $errors;
    foreach ($var as $field) {
      $val = remove_junk($_POST[$field]);
      if(isset($val) && $val==''){
        $errors = $field ." No puede estar en blanco.";
        return $errors;
      }
    }
  }
  /*--------------------------------------------------------------*/
  /* Function for Display Session Message
    Ex echo displayt_msg($message);
  /*--------------------------------------------------------------*/
  function display_msg($msg =''){
    $output = array();
    if(!empty($msg)) {
        foreach ($msg as $key => $value) {
          $output  = "<div class=\"alert alert-{$key}\">";
          $output .= "<a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>";
          $output .= remove_junk(first_character($value));
          $output .= "</div>";
        }
        return $output;
    } else {
      return "" ;
    }
  }
/*--------------------------------------------------------------*/
/* Function for redirect
/*--------------------------------------------------------------*/
function redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
      header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}
/*--------------------------------------------------------------*/
/* Function for find out total saleing price, buying price and profit
/*--------------------------------------------------------------*/
function total_price($totals){
   $sum = 0;
   $sub = 0;
   foreach($totals as $total ){
     $sum += $total['total_saleing_price'];
     $sub += $total['total_buying_price'];
     $profit = $sum - $sub;
   }
   return array($sum,$profit);
}
/*--------------------------------------------------------------*/
/* Function for Readable date time
/*--------------------------------------------------------------*/
function read_date($str){
     if($str)
      return date('d/m/Y g:i:s a', strtotime($str));
     else
      return null;
  }
/*--------------------------------------------------------------*/
/* Function for  Readable Make date time
/*--------------------------------------------------------------*/
function make_date(){
  return strftime("%Y-%m-%d %H:%M:%S", time());
}
/*--------------------------------------------------------------*/
/* Function for  Readable date time
/*--------------------------------------------------------------*/
function count_id(){
  static $count = 1;
  return $count++;
}
/*--------------------------------------------------------------*/
/* Function for Creting random string
/*--------------------------------------------------------------*/
function randString($length = 5)
{
  $str='';
  $cha = "0123456789abcdefghijklmnopqrstuvwxyz";

  for($x=0; $x<$length; $x++)
   $str .= $cha[mt_rand(0,strlen($cha))];
  return $str;
}
/*--------------------------------------------------------------*/
/* Function para verificar la rotacion de turnos
/*--------------------------------------------------------------*/
function verificar_rotacion_turno() {
  global $db;

  $hoy = date("Y-m-d");
  $hoy = $db->escape($hoy);

  $sql = "SELECT * 
          FROM rotaciones_turno 
          WHERE fecha = '{$hoy}' 
          AND aplicado = 0";

  $cambios = find_by_sql($sql);

  if(!empty($cambios)){

      foreach($cambios as $cambio){

          $g1 = $db->escape($cambio['grupo_1']);
          $g2 = $db->escape($cambio['grupo_2']);

          // Intercambiar turnos
          $sql_update = "UPDATE grupos
                         SET turno_actual =
                           CASE
                             WHEN turno_actual = 'MAÑANA' THEN 'NOCHE'
                             ELSE 'MAÑANA'
                           END
                         WHERE nombre IN ('{$g1}','{$g2}')";

          $db->query($sql_update);

          // Marcar como aplicado
          $sql_aplicar = "UPDATE rotaciones_turno
                          SET aplicado = 1,
                              fecha_aplicado = NOW()
                          WHERE id = {$cambio['id']}";

          $db->query($sql_aplicar);
      }

      return true; // hubo cambio
  }

  return false; // no hubo cambio
}

// =========================================================
// COLORES POR CLASIFICACIÓN SEGURIDAD E HIGIENE
// =========================================================
function color_clasificacion($clasificacion){
    
    $clas = strtolower(trim($clasificacion));

    $mapa = [
        'seguridad' => [
            'bg'     => '#eff6ff',
            'color'  => '#2563eb',
            'border' => '#bfdbfe',
            'icon'   => 'glyphicon-warning-sign'
        ],

        'organizacion' => [
            'bg'     => '#f5f3ff',
            'color'  => '#7c3aed',
            'border' => '#ddd6fe',
            'icon'   => 'glyphicon-cog'
        ],

        'organización' => [
            'bg'     => '#f5f3ff',
            'color'  => '#7c3aed',
            'border' => '#ddd6fe',
            'icon'   => 'glyphicon-cog'
        ],

        'salud' => [
            'bg'     => '#ecfdf5',
            'color'  => '#059669',
            'border' => '#a7f3d0',
            'icon'   => 'glyphicon-heart'
        ]
    ];

    return isset($mapa[$clas])
        ? $mapa[$clas]
        : [
            'bg'     => '#f8fafc',
            'color'  => '#64748b',
            'border' => '#e2e8f0',
            'icon'   => 'glyphicon-tag'
        ];
}
?>
