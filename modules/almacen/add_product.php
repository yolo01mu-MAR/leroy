<?php
  $page_title = 'Agregar producto';
  require_once __DIR__ . '/../../app/bootstrap.php';
  // Checkin What level user has permission to view this page
  page_require_level(11);
  $all_categories = find_categorias();
  $all_photo = find_all('media');
?>
<?php
 if(isset($_POST['add_product'])){
   $req_fields = array('product-title','product-categorie','product-quantity','buying-price', 'saleing-price' );
   validate_fields($req_fields);
   if(empty($errors)){
     $p_name  = remove_junk($db->escape($_POST['product-title']));
     $p_cat   = remove_junk($db->escape($_POST['product-categorie']));
     $p_qty   = remove_junk($db->escape($_POST['product-quantity']));
     $p_buy   = remove_junk($db->escape($_POST['buying-price']));
     $p_sale  = remove_junk($db->escape($_POST['saleing-price']));
     if (is_null($_POST['product-photo']) || $_POST['product-photo'] === "") {
       $media_id = '0';
     } else {
       $media_id = remove_junk($db->escape($_POST['product-photo']));
     }
     $date    = make_date();
     $query  = "INSERT INTO products (";
     $query .=" name,quantity,buy_price,sale_price,categorie_id,media_id,date";
     $query .=") VALUES (";
     $query .=" '{$p_name}', '{$p_qty}', '{$p_buy}', '{$p_sale}', '{$p_cat}', '{$media_id}', '{$date}'";
     $query .=")";
     $query .=" ON DUPLICATE KEY UPDATE name='{$p_name}'";
     if($db->query($query)){
       $session->msg('s',"Producto agregado exitosamente. ");
       redirect('add_product.php', false);
     } else {
       $session->msg('d',' Lo siento, registro falló.');
       redirect('product.php', false);
     }

   } else{
     $session->msg("d", $errors);
     redirect('add_product.php',false);
   }

 }

?>
<style>
    .product-card{
        background:#fff;
        border-radius:8px;
        border:1px solid #ddd;
        padding:20px;
        margin-bottom:20px;
        box-shadow:0 2px 8px rgba(0,0,0,.05);
    }

    .section-title{
        font-size:18px;
        font-weight:bold;
        color:#337ab7;
        margin-bottom:20px;
        border-bottom:2px solid #eee;
        padding-bottom:10px;
    }

    .image-box{
        border:2px dashed #ccc;
        border-radius:8px;
        height:250px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:#fafafa;
    }

    .image-box i{
        font-size:70px;
        color:#bbb;
    }

    .form-group{
        margin-bottom:18px;
    }

    .btn-save{
        padding:10px 30px;
        font-size:16px;
    }

    .panel-heading h3{
        margin:0;
    }

    .panel-heading small{
        color:#ddd;
    }
    label{
        font-weight:600;
        color:#555;
        margin-bottom:6px;
    }
</style>
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
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Agregar producto</span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="add_product.php">
                    <div class="row">
                        <!-- Imagen -->
                        <div class="col-md-3">
                        <div class="product-card">
                            <div class="section-title">
                            Imagen
                            </div>
                            <div class="image-box">
                                <div class="text-center">
                                    <i class="glyphicon glyphicon-picture"></i>
                                    <br><br>
                                    <small class="text-muted">
                                        Sin imagen
                                    </small>
                                </div>
                            </div>
                            <br>
                            <button
                                type="button"
                                class="btn btn-roy btn-block">
                                <i class="glyphicon glyphicon-upload"></i>
                                Subir foto del producto
                            </button>
                        </div>
                        </div>
                        <!-- Información -->
                        <div class="col-md-9">
                            <div class="product-card">
                                <div class="section-title">
                                    Información General
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>No. Parte</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            name="product-code"
                                            placeholder="Código">
                                    </div>
                                    <div class="col-md-8">
                                        <label>Descripción</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            name="product-title"
                                            placeholder="Descripción del producto">
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Categoría</label>
                                        <select class="form-control" name="product-categorie">
                                            <option value="">
                                                Seleccione
                                            </option>
                                            <?php foreach($all_categories as $cat): ?>
                                                <option value="<?php echo (int)$cat['id']; ?>">
                                                    <?php echo $cat['nombre']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="product-card">
                                <div class="section-title">
                                    Inventario
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Stock</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            name="product-quantity">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Punto Reorden</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            name="reorder">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Stock Seguro</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            name="safe_stock">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Stock Mínimo</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            name="minimum_stock">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <a href="alm_inventario.php" class="btn btn-default">
                            <i class="glyphicon glyphicon-arrow-left"></i>
                            Cancelar
                        </a>
                        <button
                            class="btn btn-roy btn-save"
                            name="add_product">
                            <i class="glyphicon glyphicon-floppy-disk"></i>
                            Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>
