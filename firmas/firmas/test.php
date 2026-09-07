<?php

require_once('../../includes/load.php');
require_once(__DIR__.'/includes/firmas.php');

echo render_firma([
    'modulo'      => FirmaModulo::VACACIONES,
    'registro_id' => 15,
    'tipo'        => FirmaTipo::EMPLEADO,
    'usuario_id'  => 14335
]);

?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/signature_pad.min.js"></script>
<script src="assets/js/firmas.js"></script>