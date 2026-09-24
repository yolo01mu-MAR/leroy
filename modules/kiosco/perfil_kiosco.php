<?php
// perfil_kiosco.php
// Requiere que la variable $empleado ya esté definida previamente en el archivo que lo incluya

$foto = !empty($empleado['image'])
    ? BASE_URL . '/uploads/users/' . $empleado['image']
    : BASE_URL . '/uploads/users/no_image.jpg';
?>

<div class="card border-0 shadow-sm text-center">
    
    <div class="card-body">
        <img src="<?php echo $foto; ?>"
            class="rounded-circle border border-3 border-warning mb-3"
            width="130"
            height="130"
            alt="Foto de perfil">
        <h4 class="mb-0 fw-bold">
            <?php echo remove_junk($empleado['nombre']); ?>
        </h4>
        <small class="text-muted d-block mb-2">
            <?php echo remove_junk($empleado['puesto']); ?>
        </small>
        <hr class="my-3">
        <div class="text-start">
            <p class="mb-2">
                <strong>Nómina:</strong>
                <span class="text-secondary"><?php echo remove_junk($empleado['id']); ?></span>
            </p>
            <p class="mb-2">
                <strong>Departamento:</strong><br>
                <span class="text-secondary"><?php echo remove_junk($empleado['departamento'] . " - " . $empleado['lugar']); ?></span>
            </p>
            <p class="mb-2">
                <strong>Grupo:</strong>
                <span class="text-secondary"><?php echo remove_junk($empleado['grupos']); ?></span>
            </p>
            <p class="mb-2">
                <strong>Fecha ingreso:</strong>
                <span class="text-secondary"><?php echo remove_junk($empleado['fecha_ingreso']); ?></span>
            </p>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger w-100 mt-3" onclick="Kiosco.cerrarSesion()">
            <i class="bi bi-arrow-left me-1"></i>
            Cerrar sesión
        </button>
    </div>
</div>