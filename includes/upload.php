<?php

class  Media {

  public $imageInfo;
  public $fileName;
  public $fileType;
  public $fileTempPath;

  const GENERAL  = 'general';
  const USERS    = 'users';
  const PRODUCTS = 'products';
  const ALMACEN  = 'almacen';
  const TICKETS  = 'tickets';
  const EQUIPOS  = 'equipos';

  /* Ruta donde se almacenará el archivo */
  public $uploadPath;

  /* Módulo al que pertenece el archivo */
  public $module = 'general';

  /* Id del registro relacionado */
  public $referenceId = null;

  /* Usuario que realiza la carga */
  public $userId = null;

  private $paths = [
    self::GENERAL  => UPLOADS_PATH . DS . 'general',
    self::USERS    => UPLOADS_PATH . DS . 'users',
    self::PRODUCTS => UPLOADS_PATH . DS . 'products',
    self::ALMACEN  => UPLOADS_PATH . DS . 'almacen',
    self::TICKETS  => UPLOADS_PATH . DS . 'tickets',
    self::EQUIPOS  => UPLOADS_PATH . DS . 'equipos',
  ];


  public $errors = array();
  public $upload_errors = array(
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'Ningun archivo fue subido',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.'
  );
  public$upload_extensions = array(
   'gif',
   'jpg',
   'jpeg',
   'png',
  );
  public function file_ext($filename){
     $ext = strtolower(substr( $filename, strrpos( $filename, '.' ) + 1 ) );
     if(in_array($ext, $this->upload_extensions)){
       return true;
     }
   }

  public function upload($file)
  {
    if(!$file || empty($file) || !is_array($file)):
      $this->errors[] = "Ningún archivo subido.";
      return false;
    elseif($file['error'] != 0):
      $this->errors[] = $this->upload_errors[$file['error']];
      return false;
    elseif(!$this->file_ext($file['name'])):
      $this->errors[] = 'Formato de archivo incorrecto ';
      return false;
    else:
      $this->imageInfo = getimagesize($file['tmp_name']);
      $this->fileName  = basename($file['name']);
      $this->fileType  = $this->imageInfo['mime'];
      $this->fileTempPath = $file['tmp_name'];
     return true;
    endif;

  }
  public function setModule($module){

    if(isset($this->paths[$module])){

        $this->module = $module;

        $this->uploadPath = $this->paths[$module];

        return true;

    }

    $this->errors[] = "El módulo '{$module}' no existe.";

    return false;

  }

  public function process(){
    if(empty($this->uploadPath)){
      $this->errors[] = "No se ha definido la ruta de almacenamiento.";
      return false;
    }
    if(!empty($this->errors)):
      return false;
    elseif(empty($this->fileName) || empty($this->fileTempPath)):
      $this->errors[] = "La ubicación del archivo no esta disponible.";
      return false;
    elseif(!is_writable($this->uploadPath)):
      $this->errors[] = $this->uploadPath." Debe tener permisos de escritura!!!.";
      return false;
    elseif(file_exists($this->uploadPath."/".$this->fileName)):
      $this->errors[] = "El archivo {$this->fileName} realmente existe.";
      return false;
    else:
     return true;
    endif;
 }
 /*--------------------------------------------------------------*/
 /* Function for Process media file
 /*--------------------------------------------------------------*/
  public function process_media(){
    if(!empty($this->errors)){
        return false;
      }
    if(empty($this->fileName) || empty($this->fileTempPath)){
        $this->errors[] = "La ubicación del archivo no se encuenta disponible.";
        return false;
      }

    if(!is_writable($this->uploadPath)){
        $this->errors[] = $this->uploadPath." Debe tener permisos de escritura!!!.";
        return false;
      }

    if(file_exists($this->uploadPath."/".$this->fileName)){
      $this->errors[] = "El archivo {$this->fileName} Realmente existe.";
      return false;
    }

    if(move_uploaded_file($this->fileTempPath,$this->uploadPath.'/'.$this->fileName))
    {

      if($this->insert_media()){
        unset($this->fileTempPath);
        return true;
      }

    } else {

      $this->errors[] = "Error en la carga del archivo, posiblemente debido a permisos incorrectos en la carpeta de carga.";
      return false;
    }

  }
  /*--------------------------------------------------------------*/
  /* Function for Process user image
  /*--------------------------------------------------------------*/
 public function process_user($id){

    if(!empty($this->errors)){
        return false;
      }
    if(empty($this->fileName) || empty($this->fileTempPath)){
        $this->errors[] = "La ubicación del archivo no estaba disponible.";
        return false;
      }
    if(!is_writable($this->userPath)){
        $this->errors[] = $this->userPath." Debe tener permisos de escritura";
        return false;
      }
    if(!$id){
      $this->errors[] = " ID de usuario ausente.";
      return false;
    }
    $ext = explode(".",$this->fileName);
    $new_name = randString(8).$id.'.' . end($ext);
    $this->fileName = $new_name;
    if($this->user_image_destroy($id))
    {
    if(move_uploaded_file($this->fileTempPath,$this->userPath.'/'.$this->fileName))
       {

         if($this->update_userImg($id)){
           unset($this->fileTempPath);
           return true;
         }

       } else {
         $this->errors[] = "Error en la carga del archivo, posiblemente debido a permisos incorrectos en la carpeta de carga.";
         return false;
       }
    }
 }
 /*--------------------------------------------------------------*/
 /* Function for Update user image
 /*--------------------------------------------------------------*/
  private function update_userImg($id){
     global $db;
      $sql = "UPDATE users SET";
      $sql .=" image='{$db->escape($this->fileName)}'";
      $sql .=" WHERE id='{$db->escape($id)}'";
      $result = $db->query($sql);
      return ($result && $db->affected_rows() === 1 ? true : false);

   }
 /*--------------------------------------------------------------*/
 /* Function for Delete old image
 /*--------------------------------------------------------------*/
  public function user_image_destroy($id){
     $image = find_by_id('users',$id);
     if($image['image'] === 'no_image.jpg')
     {
       return true;
     } else {
       unlink($this->userPath.'/'.$image['image']);
       return true;
     }
   }
/*--------------------------------------------------------------*/
/* Function for insert media image
/*--------------------------------------------------------------*/
  private function insert_media(){
    global $db;
    $sql  = "INSERT INTO media ( file_name,file_type )";
    $sql .=" VALUES ";
    $sql .="(
      '{$db->escape($this->fileName)}',
      '{$db->escape($this->fileType)}'
    )";
    return ($db->query($sql) ? true : false);
  }
/*--------------------------------------------------------------*/
/* Function for Delete media by id
/*--------------------------------------------------------------*/
   public function media_destroy($id,$file_name){
     $this->fileName = $file_name;
     if(empty($this->fileName)){
         $this->errors[] = "Falta el archivo de foto.";
         return false;
       }
     if(!$id){
       $this->errors[] = "ID de foto ausente.";
       return false;
     }
     if(delete_by_id('media',$id)){
         unlink($this->uploadPath.'/'.$this->fileName);
         return true;
     } else {
       $this->error[] = "Se ha producido un error en la eliminación de fotografías.";
       return false;
     }
   }
}
?>