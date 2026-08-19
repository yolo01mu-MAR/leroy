     </div>
    </div>
  <!-- Script globales -->
  <script src="https://unpkg.com/html5-qrcode"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <!-- Script librerias -->
  <script type="text/javascript" src="<?= BASE_URL ?>/libs/js/functions.js"></script>
  <script type="text/javascript" src="<?= BASE_URL ?>/libs/js/reloj.js"></script>
  <script type="text/javascript" src="<?= BASE_URL ?>/libs/js/new_functions.js"></script>
  <script type="text/javascript" src="<?= BASE_URL ?>/libs/js/js_movil.js"></script>
  
  <!-- Script para la inactividad -->
  <?php if ($session->isUserLoggedIn(true)): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= BASE_URL ?>/libs/js/inactividad.js"></script>
  <?php endif; ?>

  <!-- Librerías opcionales -->
  <?php
    if(!empty($libraries)){
      foreach($libraries as $lib){
        echo '<script src="libs/js/'.$lib.'.js"></script>';
      }
    }
  ?>
  <!-- Script de la página -->
  <?php
    if(!empty($scripts)){
      foreach($scripts as $script){
        echo '<script src="assets/js/'.$script.'.js"></script>';
      }
    }
  ?>
<!-- 
  <script type="text/javascript" src="libs/js/buscar_usuario.js"></script>
  <script type="text/javascript" src="libs/js/edit_reporte_produccion.js"></script>
  <script type="text/javascript" src="libs/js/equipos_asignados.js"></script>
  <script type="text/javascript" src="libs/js/equipo_inventario.js"></script>
   -->

  <hr>
  <!-- MODAL AGREGAR REPORTE -->
  <div class="modal fade" id="modalReporte">
    <div class="modal-dialog">
      <div class="modal-content" style="border-radius:15px;">
        <div class="modal-header">
          <h4>Generar Reporte</h4>
        </div>
        <div class="modal-body">
          <form action="add_reporte_produccion.php" method="POST">
            <div class="form-group">
              <label>Fecha</label>
              <input type="date" name="fechaReporte" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Máquina</label>
              <select name="maquina_id" class="form-control" required>
                <?php
                  $maquinas = find_all('maquinas');
                  foreach($maquinas as $m):
                ?>
                <pre><?php print_r($m); ?></pre>
                <option value="<?= $m['id']; ?>">
                  <?= $m['nombre']; ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-roy btn-block">
              <strong>Generar</strong>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php if(isset($db)) { $db->db_disconnect(); } ?>
