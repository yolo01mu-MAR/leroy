<?php
$page_title = 'Nuevo Ticket';

$scripts = [
    'ticket_nuevo'
];

require_once __DIR__ . '/../../app/bootstrap.php';
require_once('includes/ticket_helpers.php');

$usuario = current_user();
$usuario_id = $usuario['id'];

// --- Guardar ticket nuevo ---
if (isset($_POST['btnCrearTicket'])) {

    $categoria_id = (int)$_POST['categoria_id'];
    $asunto       = strtoupper($db->escape($_POST['asunto']));
    $descripcion  = strtoupper($db->escape($_POST['descripcion']));
    $equipo_id = isset($_POST['equipo_id']) && $_POST['equipo_id'] != '' ? (int)$_POST['equipo_id'] : null;
    $hardware_detalle = !empty($_POST['hardware_detalle']) ? strtoupper($db->escape($_POST['hardware_detalle'])) : null;

    if ($categoria_id == 2) {

        $tipoHardware = $_POST['hardware_tipo'] ?? '';

        if ($tipoHardware == 'equipo' && empty($_POST['equipo_personal'])) {
            $session->msg('d','Debes seleccionar un equipo.');
            redirect('ticket_nuevo.php', false);
        }

        if ($tipoHardware == 'periferico' && empty($_POST['hardware_detalle'])) {
            $session->msg('d','Debes seleccionar un periférico.');
            redirect('ticket_nuevo.php', false);
        }

    }

    if ($categoria_id == 6 && empty($_POST['equipo_impresora'])) {
        $session->msg('d','Debes seleccionar una impresora.');
        redirect('ticket_nuevo.php', false);
    }

    if ($categoria_id == 7 && empty($_POST['equipo_zebra'])) {
        $session->msg('d','Debes seleccionar una etiquetadora Zebra.');
        redirect('ticket_nuevo.php', false);
    }

    if ($categoria_id == 2) {

        if (($_POST['hardware_tipo'] ?? '') == 'equipo') {

            $equipo_id = !empty($_POST['equipo_personal'])
                ? (int)$_POST['equipo_personal']
                : null;

            $hardware_detalle = null;

        } else {

            $equipo_id = null;

            $hardware_detalle = !empty($_POST['hardware_detalle'])
                ? strtoupper($db->escape($_POST['hardware_detalle']))
                : null;

        }

    } elseif ($categoria_id == 6) {

        $equipo_id = !empty($_POST['equipo_impresora'])
            ? (int)$_POST['equipo_impresora']
            : null;

        $hardware_detalle = null;

    } elseif ($categoria_id == 7) {

        $equipo_id = !empty($_POST['equipo_zebra'])
            ? (int)$_POST['equipo_zebra']
            : null;

        $hardware_detalle = null;

    }

    if ($categoria_id > 0 && $asunto != '' && $descripcion != '') {

        $resultado = crear_ticket($usuario['id'], $categoria_id, $equipo_id, $hardware_detalle, $asunto, $descripcion);

        if($resultado['ok']){
            $session->msg('s', 'Tu ticket se creó correctamente. Folio: '.$resultado['folio']);
            redirect("ticket_detalle_user.php?id=".$resultado['id'], false);
        }else{
            $session->msg('d', $resultado['mensaje']);
        }

    } else {
        $session->msg('d', 'Completa la categoría, el asunto y la descripción.');
    }
    redirect("ticket_histo_user.php", false);
}

$categorias = get_ticket_categoria();
$equipos =  get_equipos_asignados_usuarios($usuario_id);
$impresoras = get_ticket_impresoras();
$zebras = get_ticket_zebras();

// Iconos por categoría -- ajusta el mapeo id => glyphicon a tu catálogo real
$iconosCategoria = [
    1 => 'bi bi-headset',
    2 => 'bi bi-pc-display',
    3 => 'bi bi-windows',
    4 => 'bi bi-wifi',
    5 => 'bi bi-envelope',
    6 => 'bi bi-printer',
    7 => 'bi bi-ticket-perforated',
    8 => 'bi bi-box-seam'
];
$iconoDefault = 'glyphicon-question-sign';

// Ajusta a tus IDs reales de categoría
$categoriasConEquipo = [2, 6];

?>
<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/ticket.css"/>
<!-- <link rel="stylesheet" href="style.css"> -->
<div class="row">
   <div class="col-md-12">
     <?php echo display_msg($msg); ?>
   </div>
</div>

<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
          <div class="pull-left" style="padding-top:6px;">
              <strong>
                  <span class="glyphicon glyphicon-plus-sign"></span>
                  <span>Nuevo Ticket</span>
              </strong>
              <br>
              <small class="text-muted">
                Cuéntanos qué necesitas y un técnico de sistemas te ayudará.
              </small>
          </div>
      </div>
      <div class="panel-body" style="padding:30px;">

        <form method="post" id="formNuevoTicket">

            <!-- Paso 1: Categoría -->
            <div class="tk-step-title">
                <span class="tk-step-num">1</span> ¿Sobre qué es tu problema?
            </div>

            <input type="hidden" name="categoria_id" id="categoria_id" required>

            <div class="tk-cat-grid" id="catGrid">
                <?php foreach ($categorias as $cat):
                    $icono = isset($iconosCategoria[$cat['id']]) ? $iconosCategoria[$cat['id']] : $iconoDefault;
                ?>
                    <div class="tk-cat-card" data-id="<?php echo (int)$cat['id']; ?>">
                        <span class="glyphicon <?php echo $icono; ?>"></span>
                        <span class="tk-cat-nombre"><?php echo remove_junk($cat['nombre_categoria']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="tk-campos-extra" id="hardwareOpciones">
                <div class="form-group mt-3">
                    <label><strong>¿Qué deseas reportar?</strong></label><br>
                    <label class="radio-inline">
                        <input type="radio" name="hardware_tipo" value="equipo">
                            Equipo de cómputo
                    </label>
                    <label class="radio-inline" style="margin-left:20px;">
                        <input type="radio" name="hardware_tipo" value="periferico">
                            Periférico
                    </label>
                </div>
            </div>

            <!-- Campos condicionales: Equipo personal (categoría "Equipo", ej. id 2) -->
            <div class="tk-campos-extra" id="camposEquipoPersonal">
                <label class="tk-label">Selecciona tu equipo</label>
                <select id="equipoPersonal" name="equipo_personal" class="tk-select">
                    <option value="">-- Elige un equipo --</option>
                    <?php foreach ($equipos as $eq): ?>
                        <option value="<?php echo (int)$eq['id']; ?>">
                            <?php echo remove_junk($eq['tipo'] . ' — ' . $eq['marca'] . ' ' . $eq['modelo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="tk-campos-extra" id="camposPeriferico">
                <label class="tk-label">
                    Selecciona el periférico
                </label>
                <select id="hardwareDetalle" name="hardware_detalle" class="tk-select">
                    <option value="">-- Selecciona --</option>
                    <option value="MONITOR">Monitor</option>
                    <option value="MOUSE">Mouse</option>
                    <option value="TECLADO">Teclado</option>
                    <option value="AUDIFONOS">Audifonos</option>
                    <option value="BOCINAS">Bocinas</option>
                    <option value="CARGADOR">Cargador</option>
                    <option value="OTRO">Otro</option>
                </select>
            </div>

            <!-- Campos condicionales: Impresora (categoría "Impresora", ej. id 6) -->
            <div class="tk-campos-extra" id="camposImpresora">
                <label class="tk-label">Selecciona la impresora</label>
                <select id="impresora" name="equipo_impresora" class="tk-select">
                    <option value="">-- Elige una impresora --</option>
                    <?php foreach ($impresoras as $imp): ?>
                        <option value="<?php echo (int)$imp['id']; ?>">
                            <?php echo remove_junk( $imp['departamento'] . ' - ' . $imp['marca'] . ' ' . $imp['modelo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Campos condicionales: Impresora (categoría "Zebra", ej. id 7) -->
            <div class="tk-campos-extra" id="camposZebra">
                <label class="tk-label">Selecciona la Etiquetadora Zebra</label>
                <select id="zebra" name="equipo_zebra" class="tk-select">
                    <option value="">-- Elige una Zebra --</option>
                    <?php foreach ($zebras as $z): ?>
                        <option value="<?php echo (int)$z['id']; ?>">
                            <?php echo remove_junk( $z['departamento'] . ' - ' . $z['marca'] . ' ' . $z['modelo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr class="tk-divider">

            <!-- Paso 2: Detalles -->
            <div class="tk-step-title">
                <span class="tk-step-num">2</span> Cuéntanos qué pasa
            </div>

            <label class="tk-label">Asunto</label>
            <input
                type="text"
                class="form-control input-lg"
                name="asunto"
                placeholder="Ej. No enciende la impresora"
                maxlength="120"
                required
                style="margin-bottom:20px;">

            <label class="tk-label">Describe el problema con más detalle</label>
            <textarea
                class="form-control"
                name="descripcion"
                rows="6"
                style="font-size:15px;"
                placeholder="Ej. Desde esta mañana la impresora no enciende, ya revisé que esté conectada..."
                required></textarea>

            <div class="tk-hint">
                <span class="glyphicon glyphicon-info-sign"></span>
                <span>
                    Entre más detalle nos des, más rápido podemos ayudarte.
                    Un técnico de sistemas revisará tu ticket y te asignará prioridad.
                </span>
            </div>

            <button type="submit" name="btnCrearTicket" class="btn btn-primary btn-lg tk-btn-crear">
                <span class="glyphicon glyphicon-send"></span>
                Crear ticket
            </button>

        </form>

      </div>
    </div>
  </div>
</div>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>