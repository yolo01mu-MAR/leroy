<?php
  $page_title = 'Almacen - inventario';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $limite = 10;

  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $pagina = ($pagina < 1) ? 1 : $pagina;

  // --- Búsqueda y filtro por tipo ---
  $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
  $tipo_id  = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;

  $total_registros = count_inventario($busqueda, $tipo_id); // COUNT(*)
  $total_paginas   = ceil($total_registros / $limite);

  if ($pagina > $total_paginas && $total_paginas > 0) {
    $qs = http_build_query(array_filter(['page' => $total_paginas, 'q' => $busqueda, 'tipo' => $tipo_id ?: null]));
    redirect('?'.$qs);
  }

  $estatusMap = [
    1 => ['texto' => 'ACTIVO',       'class' => 'label-success'],
    2 => ['texto' => 'INACTIVO',     'class' => 'label-default'],
    3 => ['texto' => 'BAJA',         'class' => 'label-danger'],
    4 => ['texto' => 'INCAPACIDAD',  'class' => 'label-warning'],
    5 => ['texto' => 'VACACIONES',   'class' => 'label-info'],
  ];

  $offset = ($pagina - 1) * $limite;

  // CONSULTA FINAL
  $products = find_productos_paginated($limite, $offset, $busqueda, $tipo_id);

  // Lista de tipos/categorías para el filtro (id => nombre)
  $tipos = find_categorias(); // debe regresar algo tipo [ ['id'=>1,'nombre'=>'Herramienta'], ... ]

  // Checkin What level user has permission to view this page
  page_require_level(11);
?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>

<style>
  /* --- Estilos de refresco visual, no rompen las clases de Bootstrap existentes --- */
  .inv-panel { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); border-radius: 6px; overflow: hidden; }
  .inv-panel .panel-heading {
    background: #fff;
    border-bottom: 1px solid #eef0f2;
    padding: 16px 20px;
  }
  .inv-panel .panel-heading strong { font-size: 15px; color: #2c3542; }
  .inv-toolbar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
  .inv-toolbar .form-control { border-radius: 4px; }
  .inv-table thead th {
    background: #f7f8fa;
    border-bottom: 2px solid #eef0f2 !important;
    color: #6b7280;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: .04em;
    font-weight: 600;
    vertical-align: middle;
  }
  .inv-table tbody tr { transition: background-color .12s ease; }
  .inv-table tbody tr:hover { background-color: #f9fafb; }
  .inv-table td { vertical-align: middle; }
  .inv-table .img-avatar {
    width: 48px; height: 48px; object-fit: cover;
    border: 1px solid #eef0f2;
  }
  .inv-nombre { font-weight: 600; color: #2c3542; }
  .inv-desc { color: #8a94a3; font-size: 12px; display: block; }
  .inv-categoria {
    display: inline-block; padding: 3px 10px; border-radius: 12px;
    background: #eef2ff; color: #4f5fd6; font-size: 12px; font-weight: 600;
  }
  .inv-stock { font-weight: 700; }
  .inv-stock.low { color: #d9534f; }
  .table-footer { padding: 14px 4px 4px; }
  .inv-empty { text-align: center; padding: 40px 0; color: #9aa2af; }
  @media (max-width: 767px) {
    .inv-toolbar { width: 100%; }
    .inv-toolbar .form-control, .inv-toolbar select { width: 100% !important; }
  }
</style>

<div class="row">
   <div class="col-md-12">
     <?php echo display_msg($msg); ?>
   </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default inv-panel">
      <div class="panel-heading clearfix">
        <div class="pull-left" style="padding-top:6px;">
              <strong>
                  <span class="glyphicon glyphicon-th"></span>
                  <span>Inventario Almacen</span>
              </strong>
          </div>
          <div class="pull-right inv-toolbar">
              <select id="filtro-tipo" class="form-control input-sm" style="width:170px;">
                <option value="0">Todos los tipos</option>
                <?php foreach ($tipos as $t): ?>
                  <option value="<?php echo (int)$t['id']; ?>" <?php echo ($tipo_id === (int)$t['id']) ? 'selected' : ''; ?>>
                    <?php echo remove_junk($t['nombre']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="input-group input-group-sm" style="width:260px;">
                  <span class="input-group-addon">
                      <span class="glyphicon glyphicon-search"></span>
                  </span>
                  <input type="text"
                        id="buscador"
                        class="form-control"
                        value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES); ?>"
                        placeholder="Buscar por # de pieza o nombre">
              </div>
          </div>
      </div>
        <div class="panel-body">
          <table class="table table-bordered inv-table">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th>Imagen</th>
                <th>Detalles especificos</th>
                <th class="text-center" style="width: 10%;">Categoría</th>
                <th class="text-center" style="width: 10%;">Stock</th>
                <th class="text-center" style="width: 10%;">Agregado</th>
                <th class="text-center" style="width: 100px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($products)): ?>
              <tr>
                <td colspan="7" class="inv-empty">No se encontraron productos con esos criterios.</td>
              </tr>
              <?php else: foreach ($products as $product):?>
              <tr>
                <td class="text-center"><?php echo count_id();?></td>
                <td>
                  <?php if($product['id_media'] === '1'): ?>
                    <img class="img-avatar img-circle" src="uploads/products/no_image.jpg" alt="">
                  <?php else: ?>
                    <img class="img-avatar img-circle" src="uploads/products/<?php echo $product['foto']; ?>" alt="">
                  <?php endif; ?>
                </td>
                <td>
                    <span class="inv-nombre"><?php echo remove_junk($product['des_gral']); ?></span>
                    <?php if (!empty($product['numero_pieza'])): ?>
                      <span class="inv-desc">Pieza #: <?php echo remove_junk($product['numero_pieza']); ?></span>
                    <?php endif; ?>
                    <span class="inv-desc"><?php echo remove_junk($product['des_detallada']); ?></span>
                </td>
                <td class="text-center">
                  <span class="inv-categoria"><?php echo remove_junk($product['categoria']); ?></span>
                </td>
                <td class="text-center">
                  <span class="inv-stock <?php echo ((int)$product['stock'] <= 5) ? 'low' : ''; ?>">
                    <?php echo remove_junk($product['stock']); ?>
                  </span>
                </td>
                <td class="text-center"> <?php echo read_date($product['fecha']); ?></td>
                <td class="text-center">
                  <div class="btn-group">
                    <a href="edit_product.php?id=<?php echo (int)$product['id'];?>" class="btn btn-info btn-xs"  title="Editar" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>
                     <a href="delete_product.php?id=<?php echo (int)$product['id'];?>" class="btn btn-danger btn-xs"  title="Eliminar" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-trash"></span>
                    </a>
                  </div>
                </td>
              </tr>
             <?php endforeach; endif; ?>
            </tbody>
          </table>
        <div class="table-footer clearfix">
            <div class="pull-left text-muted" style="font-size:12px; line-height:30px;">
              Mostrando <?php echo count($products); ?> de <?php echo $total_registros; ?> registros
            </div>
            <div class="pull-right">
              <div id="paginacion" class="pull-right">
                <ul class="pagination pagination-sm no-margin">
                  <li class="<?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                    <a href="?<?php echo http_build_query(['page' => $pagina - 1, 'q' => $busqueda, 'tipo' => $tipo_id ?: null]); ?>">&laquo;</a>
                  </li>
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="<?php echo ($i == $pagina) ? 'active' : ''; ?>">
                      <a href="?<?php echo http_build_query(['page' => $i, 'q' => $busqueda, 'tipo' => $tipo_id ?: null]); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="<?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                      <a href="?<?php echo http_build_query(['page' => $pagina + 1, 'q' => $busqueda, 'tipo' => $tipo_id ?: null]); ?>">&raquo;</a>
                    </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var input  = document.getElementById('buscador');
    var select = document.getElementById('filtro-tipo');
    var timer  = null;

    function goSearch() {
      var params = new URLSearchParams(window.location.search);
      params.set('q', input.value.trim());
      params.set('tipo', select.value);
      params.set('page', 1); // toda nueva búsqueda regresa a la página 1
      window.location.search = params.toString();
    }

    input.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(goSearch, 450); // debounce para no recargar en cada tecla
    });

    select.addEventListener('change', goSearch);
  })();
</script>

  <?php include_once BASE_PATH . '/layouts/footer.php'; ?>