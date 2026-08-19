<?php
  $page_title = 'Lista de encargados';
  require_once __DIR__ . '/../../app/bootstrap.php';

  $sql = "SELECT 
            p.id,
            p.nombre as categoria,
            s.nombre as paro
          FROM paro_general p
          JOIN subparos s ON s.paro_id = p.id
          ORDER BY p.id";
  
  $result = $db->query($sql);
  
  $paros = [];
  
  while($row = $result->fetch_assoc()){
  
      $paros[$row['id']]['categoria'] = $row['categoria'];
      $paros[$row['id']]['items'][] = $row['paro'];
  
  }

  page_require_level(4);
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
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>LIsta de paros</span>
       </strong>
      </div>
      <div class="panel-body">
        <div class="tab-content">
          <ul class="nav nav-tabs">
            <?php $first = true; ?>
            <?php foreach($paros as $id => $data): ?>
              <li class="<?php echo $first ? 'active' : ''; ?>">
              <a data-toggle="tab" href="#tab_<?php echo $id; ?>">
                <?php echo str_replace('LISTA DE PAROS ', '', $data['categoria']); ?>
              </a>
              </li>
              <?php $first = false; ?>
            <?php endforeach; ?>
          </ul>
          <div class="tab-content">
            <?php $first = true; ?>
              <?php foreach($paros as $id => $data): ?>
                <div id="tab_<?php echo $id; ?>" class="tab-pane fade <?php echo $first ? 'in active' : ''; ?>">
                  <table class="table table-bordered">
                    <?php foreach($data['items'] as $paro): ?>
                    <tr>
                      <td><?php echo $paro; ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </table>
                </div>
                <?php $first = false; ?>
              <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>