<?php
require_once __DIR__ . '/../../app/bootstrap.php';

// =========================================================
// 1. VALIDAR ID
// =========================================================
$id_norma = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_norma <= 0) {
    $session->msg("d=Norma no válida.&type=danger");
    redirect('normas.php');
    exit;
}

// =========================================================
// 2. OBTENER NORMA Y CLASIFICACIONES
// =========================================================
$sql_norma = "
    SELECT 
        sn.id, 
        sn.id_clasificacion, 
        sn.codigo_norma, 
        sn.nombre_norma, 
        sn.imagen
    FROM seguridad_normas sn
    WHERE sn.id = {$id_norma}
    LIMIT 1
";
$result_norma = find_by_sql($sql_norma);

if (empty($result_norma)) {
    $session->msg("d=La norma no existe.&type=danger");
    redirect('normas.php');
    exit;
}

$norma = $result_norma[0];
$clasificaciones = find_by_sql("SELECT id, nombre FROM seguridad_clasificacion ORDER BY nombre ASC");

// =========================================================
// 3. OBTENER PUNTOS EXISTENTES
// =========================================================
$sql_puntos = "
    SELECT 
        snp.id,
        snp.`no` AS punto,
        snp.desc_requisito AS requisito,
        snp.tipo_comprobacion AS comprobacion,
        snp.desc_evidencia AS evidencia
    FROM seguridad_normas_p snp
    WHERE snp.id_norma = {$id_norma}
    ORDER BY snp.`no` ASC
";
$puntos = find_by_sql($sql_puntos);

$page_title = "Editar Norma: " . remove_junk($norma['codigo_norma']);

// =========================================================
// 4. PROCESAR FORMULARIO (POST)
// =========================================================
if (isset($_POST['actualizar_norma'])) {
    
    // Validar token CSRF o campos obligatorios
    $codigo_norma    = $db->escape($_POST['codigo_norma']);
    $nombre_norma    = $db->escape($_POST['nombre_norma']);
    $id_clasificacion = (int) $_POST['id_clasificacion'];
    
    // Manejo de la Imagen de Portada
    $ruta_imagen = $norma['imagen']; // Conservar la actual por defecto
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['imagen']['name'];
        $file_tmp  = $_FILES['imagen']['tmp_name'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        
        if (in_array($ext, $allowed)) {
            $nuevo_nombre = 'norma_' . $id_norma . '_' . time() . '.' . $ext;
            $destino = __DIR__ . '/../../uploads/normas/' . $nuevo_nombre;
            
            // Crear carpeta si no existe
            if (!is_dir(__DIR__ . '/../../uploads/normas/')) {
                mkdir(__DIR__ . '/../../uploads/normas/', 0777, true);
            }
            
            if (move_uploaded_file($file_tmp, $destino)) {
                $ruta_imagen = 'uploads/normas/' . $nuevo_nombre;
            }
        }
    }
    
    // Update Norma principal
    $query_update_norma = "
        UPDATE seguridad_normas SET
            codigo_norma = '{$codigo_norma}',
            nombre_norma = '{$nombre_norma}',
            id_clasificacion = {$id_clasificacion},
            imagen = '{$ruta_imagen}'
        WHERE id = {$id_norma}
    ";
    
    $db->query($query_update_norma);
    
    // Procesar Puntos/Requisitos (Estrategia: Sincronizar)
    // 1. Obtener IDs de los puntos enviados desde el form para conservar
    $puntos_enviados = $_POST['puntos'] ?? [];
    $ids_conservados = [];

    foreach ($puntos_enviados as $p) {
        $p_id           = isset($p['id']) ? (int)$p['id'] : 0;
        $p_no           = $db->escape($p['no']);
        $p_requisito    = $db->escape($p['requisito']);
        $p_comprobacion = $db->escape($p['comprobacion']);
        $p_evidencia    = $db->escape($p['evidencia']);
        
        if ($p_id > 0) {
            // Actualizar existente
            $sql_u = "UPDATE seguridad_normas_p SET
                        `no` = '{$p_no}',
                        desc_requisito = '{$p_requisito}',
                        tipo_comprobacion = '{$p_comprobacion}',
                        desc_evidencia = '{$p_evidencia}'
                      WHERE id = {$p_id} AND id_norma = {$id_norma}";
            $db->query($sql_u);
            $ids_conservados[] = $p_id;
        } else {
            // Insertar nuevo
            if (!empty($p_no) || !empty($p_requisito)) {
                $sql_i = "INSERT INTO seguridad_normas_p 
                            (id_norma, `no`, desc_requisito, tipo_comprobacion, desc_evidencia)
                          VALUES 
                            ({$id_norma}, '{$p_no}', '{$p_requisito}', '{$p_comprobacion}', '{$p_evidencia}')";
                $db->query($sql_i);
                $ids_conservados[] = $db->insert_id();
            }
        }
    }
    
    // 2. Eliminar puntos que fueron quitados de la interfaz
    if (!empty($ids_conservados)) {
        $ids_str = implode(',', array_map('intval', $ids_conservados));
        $db->query("DELETE FROM seguridad_normas_p WHERE id_norma = {$id_norma} AND id NOT IN ({$ids_str})");
    } else {
        $db->query("DELETE FROM seguridad_normas_p WHERE id_norma = {$id_norma}");
    }
    
    $session->msg("s=Norma actualizada correctamente.&type=success");
    redirect('ver_norma.php?id=' . $id_norma);
    exit;
}

?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>

<style>
:root {
    --primary-color: #0f172a;
    --accent-color: #2563eb;
    --bg-subtle: #f8fafc;
    --border-color: #e2e8f0;
    --text-muted: #64748b;
    --radius: 14px;
}

.edit-norma-container {
    margin-bottom: 90px; /* deja espacio para la barra sticky */
}

.btn-volver-top {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    margin-bottom: 16px;
}

.btn-volver-top:hover {
    color: var(--accent-color);
    text-decoration: none;
}

.edit-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 18px;
}

.edit-header h1 {
    font-size: 21px;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0;
    letter-spacing: -.3px;
}

.edit-header p {
    margin: 2px 0 0;
    color: var(--text-muted);
    font-size: 13px;
}

.card-panel {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(15,23,42,0.03);
}

.card-panel-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.card-panel-title .glyphicon {
    color: var(--accent-color);
    margin-right: 4px;
}

.form-group label {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 5px;
}

.form-control {
    border-radius: 8px;
    border: 1px solid var(--border-color);
    box-shadow: none;
    font-size: 13.5px;
}

.form-control:focus {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

/* Vista previa en vivo */
.preview-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--bg-subtle);
    border: 1px dashed var(--border-color);
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 22px;
}

.preview-thumb {
    width: 54px;
    height: 54px;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.preview-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-info {
    min-width: 0;
}

.preview-codigo {
    font-size: 11px;
    font-weight: 700;
    color: var(--accent-color);
    letter-spacing: .4px;
    display: block;
}

.preview-nombre {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--primary-color);
    margin: 2px 0 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    transition: all .15s ease;
}

.preview-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #94a3b8;
    font-weight: 700;
    margin-bottom: 8px;
    display: block;
}

/* Dropzone de imagen */
.img-upload-row {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}

.img-preview {
    width: 84px;
    height: 84px;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    background: var(--bg-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.img-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dropzone {
    flex: 1;
    min-width: 220px;
    border: 2px dashed var(--border-color);
    border-radius: 10px;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: all .2s ease;
    background: var(--bg-subtle);
}

.dropzone:hover, .dropzone.dragover {
    border-color: var(--accent-color);
    background: #eff6ff;
}

.dropzone .glyphicon {
    font-size: 18px;
    color: #94a3b8;
    margin-bottom: 4px;
    display: block;
}

.dropzone small {
    display: block;
    color: var(--text-muted);
    margin-top: 3px;
}

.dropzone input[type="file"] {
    display: none;
}

/* Filas de requisitos */
.punto-row {
    display: flex;
    gap: 14px;
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    position: relative;
    transition: all .2s ease;
}

.punto-row:hover {
    border-color: #cbd5e1;
}

.punto-badge {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid var(--border-color);
    color: var(--primary-color);
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
}

.punto-content {
    flex: 1;
    min-width: 0;
}

.punto-content .form-group {
    margin-bottom: 8px;
}

.punto-content label {
    font-size: 10.5px;
}

.btn-remove-punto {
    color: #ef4444;
    background: #ffffff;
    border: 1px solid #fecaca;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    cursor: pointer;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    align-self: flex-start;
    margin-top: 2px;
}

.btn-remove-punto:hover {
    background: #fee2e2;
}

.empty-puntos {
    text-align: center;
    padding: 30px 20px;
    color: var(--text-muted);
    border: 1px dashed var(--border-color);
    border-radius: 12px;
    background: var(--bg-subtle);
    font-size: 13px;
}

.btn-add-punto {
    border-radius: 8px;
    font-weight: 600;
    font-size: 12.5px;
}

/* Barra de acciones sticky */
.actions-bar {
    position: sticky;
    bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 14px 22px;
    box-shadow: 0 8px 24px rgba(15,23,42,0.08);
    z-index: 10;
}

.actions-bar .contador-puntos {
    font-size: 12.5px;
    color: var(--text-muted);
}

.actions-bar .contador-puntos strong {
    color: var(--primary-color);
}

.btn-guardar {
    border-radius: 9px;
    font-weight: 700;
    padding: 10px 22px;
    font-size: 13px;
    box-shadow: 0 2px 6px rgba(37,99,235,.25);
}

@media (max-width: 767px) {
    .punto-row { flex-direction: column; }
    .btn-remove-punto { align-self: flex-end; }
    .actions-bar { flex-direction: column; align-items: stretch; text-align: center; }
}

/* Nuevo Encabezado Unificado */
.edit-header-card {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 20px 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
    position: relative;
}

.edit-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px dashed var(--border-color);
}

.btn-volver-header {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 8px;
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
    transition: all .2s ease;
}

.btn-volver-header:hover {
    color: var(--accent-color);
    border-color: #bfdbfe;
    background: #eff6ff;
    text-decoration: none;
}

.header-status-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: #0284c7;
    background: #f0f9ff;
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid #bae6fd;
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: #0284c7;
}

.edit-header-main {
    display: flex;
    align-items: center;
    gap: 16px;
}

.edit-header-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: #eff6ff;
    color: var(--accent-color);
    border: 1px solid #bfdbfe;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.edit-header-titles h1 {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0;
    letter-spacing: -.3px;
}

.edit-header-titles p {
    margin: 3px 0 0;
    color: var(--text-muted);
    font-size: 13px;
}
</style>

<div class="edit-norma-container">

    <form action="edit_norma.php?id=<?php echo $id_norma; ?>" method="POST" enctype="multipart/form-data" id="formEditarNorma">

    <!-- DATOS GENERALES Y PORTADA -->
        <div class="card-panel">
            <div class="edit-header-top">
                <a href="normas.php?id=<?php echo $id_norma; ?>" class="btn-volver-header">
                    <span class="glyphicon glyphicon-arrow-left"></span>
                    <span>Volver a la norma</span>
                </a>
                <span class="header-status-tag">
                    <span class="status-dot"></span> Modo Edición
                </span>
            </div>
            <h3 class="card-panel-title">
                <span><span class="glyphicon glyphicon-info-sign"></span> Información general</span>
            </h3>

            <!-- Vista previa en vivo -->
            <span class="preview-label">Así se verá</span>
            <div class="preview-card">
                <div class="preview-thumb">
                    <?php if (!empty($norma['imagen'])): ?>
                        <img src="<?php echo remove_junk($norma['imagen']); ?>" id="preview-live-img" alt="Portada">
                    <?php else: ?>
                        <span class="glyphicon glyphicon-picture" id="preview-live-icon" style="font-size:20px; color:#94a3b8;"></span>
                        <img src="" id="preview-live-img" style="display:none;" alt="Portada">
                    <?php endif; ?>
                </div>
                <div class="preview-info">
                    <span class="preview-codigo" id="preview-codigo"><?php echo remove_junk($norma['codigo_norma']) ?: 'CÓDIGO-000'; ?></span>
                    <div class="preview-nombre" id="preview-nombre"><?php echo remove_junk($norma['nombre_norma']) ?: 'Nombre de la norma'; ?></div>
                    <span class="preview-badge" id="preview-badge">
                        <span class="glyphicon glyphicon-tag"></span>
                        <span id="preview-badge-texto">Clasificación</span>
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label>Código de la Norma</label>
                        <input type="text" id="input-codigo" name="codigo_norma" class="form-control" required value="<?php echo remove_junk($norma['codigo_norma']); ?>">
                    </div>
                </div>

                <div class="col-md-5 col-sm-6">
                    <div class="form-group">
                        <label>Nombre de la Norma</label>
                        <input type="text" id="input-nombre" name="nombre_norma" class="form-control" required value="<?php echo remove_junk($norma['nombre_norma']); ?>">
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label>Clasificación</label>
                        <select name="id_clasificacion" id="input-clasificacion" class="form-control" required>
                            <?php foreach ($clasificaciones as $c): ?>
                                <option value="<?php echo $c['id']; ?>"
                                        data-nombre="<?php echo remove_junk(strtolower($c['nombre'])); ?>"
                                        <?php if ($c['id'] == $norma['id_clasificacion']) echo 'selected'; ?>>
                                    <?php echo remove_junk(strtoupper($c['nombre'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 6px;">
                <div class="col-md-12">
                    <label>Foto / Portada de la Norma</label>
                    <div class="img-upload-row">
                        <div class="img-preview">
                            <?php if (!empty($norma['imagen'])): ?>
                                <img src="<?php echo remove_junk($norma['imagen']); ?>" id="preview-img" alt="Portada">
                            <?php else: ?>
                                <span class="glyphicon glyphicon-picture" id="preview-icon" style="font-size: 22px; color: #94a3b8;"></span>
                                <img src="" id="preview-img" style="display:none;" alt="Portada">
                            <?php endif; ?>
                        </div>
                        <label class="dropzone" id="dropzone" for="input-imagen">
                            <span class="glyphicon glyphicon-cloud-upload"></span>
                            Arrastra una imagen aquí o haz clic para elegir un archivo
                            <small>JPG, PNG, WEBP o SVG</small>
                            <input type="file" name="imagen" id="input-imagen" accept="image/*">
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- REQUISITOS / PUNTOS -->
        <div class="card-panel">
            <div class="card-panel-title">
                <span><span class="glyphicon glyphicon-list-alt"></span> Requisitos y puntos de evaluación</span>
                <button type="button" class="btn btn-sm btn-success btn-add-punto" id="btnAgregarPunto">
                    <span class="glyphicon glyphicon-plus"></span> Agregar requisito
                </button>
            </div>

            <div id="contenedorPuntos">
                <?php if (!empty($puntos)): ?>
                    <?php foreach ($puntos as $idx => $punto): ?>
                        <div class="punto-row">
                            <span class="punto-badge"><?php echo ($idx + 1); ?></span>
                            <input type="hidden" name="puntos[<?php echo $idx; ?>][id]" value="<?php echo $punto['id']; ?>">

                            <div class="punto-content">
                                <div class="row">
                                    <div class="col-md-2 col-sm-3">
                                        <div class="form-group">
                                            <label>N° / Punto</label>
                                            <input type="text" name="puntos[<?php echo $idx; ?>][no]" class="form-control input-sm" required value="<?php echo remove_junk($punto['punto']); ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-5 col-sm-9">
                                        <div class="form-group">
                                            <label>Requisito / Descripción</label>
                                            <textarea name="puntos[<?php echo $idx; ?>][requisito]" class="form-control input-sm" rows="2" required><?php echo remove_junk($punto['requisito']); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-2 col-sm-6">
                                        <div class="form-group">
                                            <label>Comprobación</label>
                                            <select name="puntos[<?php echo $idx; ?>][comprobacion]" class="form-control input-sm">
                                                <option value="Documental" <?php if ($punto['comprobacion'] == 'Documental') echo 'selected'; ?>>Documental</option>
                                                <option value="Física" <?php if ($punto['comprobacion'] == 'Física') echo 'selected'; ?>>Física</option>
                                                <option value="Entrevista" <?php if ($punto['comprobacion'] == 'Entrevista') echo 'selected'; ?>>Entrevista</option>
                                                <option value="Registros" <?php if ($punto['comprobacion'] == 'Registros') echo 'selected'; ?>>Registros</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-sm-6">
                                        <div class="form-group">
                                            <label>Evidencia Requerida</label>
                                            <textarea name="puntos[<?php echo $idx; ?>][evidencia]" class="form-control input-sm" rows="2"><?php echo remove_junk($punto['evidencia']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn-remove-punto btnEliminarPunto" title="Eliminar punto">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-puntos" id="mensajeSinPuntos">
                        <span class="glyphicon glyphicon-list-alt" style="font-size:22px; display:block; margin-bottom:8px; color:#cbd5e1;"></span>
                        Aún no hay requisitos. Usa "Agregar requisito" para crear el primero.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BARRA DE ACCIONES -->
        <div class="actions-bar">
            <span class="contador-puntos">
                <strong id="contadorPuntosNum"><?php echo count($puntos); ?></strong> requisito(s) en este formulario
            </span>
            <div>
                <a href="ver_norma.php?id=<?php echo $id_norma; ?>" class="btn btn-default">
                    Cancelar
                </a>
                <button type="submit" name="actualizar_norma" class="btn btn-primary btn-guardar">
                    <span class="glyphicon glyphicon-floppy-disk"></span> Guardar cambios
                </button>
            </div>
        </div>

    </form>
</div>

<!-- PLANTILLA JS PARA AGREGAR NUEVO PUNTO -->
<template id="tpl-punto">
    <div class="punto-row">
        <span class="punto-badge">{NUM}</span>
        <input type="hidden" name="puntos[{INDEX}][id]" value="0">

        <div class="punto-content">
            <div class="row">
                <div class="col-md-2 col-sm-3">
                    <div class="form-group">
                        <label>N° / Punto</label>
                        <input type="text" name="puntos[{INDEX}][no]" class="form-control input-sm" placeholder="Ej: 5.1" required>
                    </div>
                </div>

                <div class="col-md-5 col-sm-9">
                    <div class="form-group">
                        <label>Requisito / Descripción</label>
                        <textarea name="puntos[{INDEX}][requisito]" class="form-control input-sm" rows="2" placeholder="Descripción del requisito..." required></textarea>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="form-group">
                        <label>Comprobación</label>
                        <select name="puntos[{INDEX}][comprobacion]" class="form-control input-sm">
                            <option value="Documental">Documental</option>
                            <option value="Física">Física</option>
                            <option value="Entrevista">Entrevista</option>
                            <option value="Registros">Registros</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label>Evidencia Requerida</label>
                        <textarea name="puntos[{INDEX}][evidencia]" class="form-control input-sm" rows="2" placeholder="Evidencia esperada..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="btn-remove-punto btnEliminarPunto" title="Eliminar punto">
            <span class="glyphicon glyphicon-trash"></span>
        </button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // Colores de vista previa por clasificación (solo UI)
    // =====================================================
    var estilosClasificacion = {
        'seguridad':    { bg: '#eff6ff', color: '#1d4ed8', border: '#bfdbfe', icon: 'glyphicon-shield' },
        'organizacion': { bg: '#f5f3ff', color: '#6d28d9', border: '#ddd6fe', icon: 'glyphicon-cog' },
        'organización': { bg: '#f5f3ff', color: '#6d28d9', border: '#ddd6fe', icon: 'glyphicon-cog' },
        'salud':        { bg: '#ecfdf5', color: '#047857', border: '#a7f3d0', icon: 'glyphicon-heart' }
    };
    var estiloDefault = { bg: '#eff6ff', color: '#2563eb', border: '#bfdbfe', icon: 'glyphicon-tag' };

    var inputCodigo = document.getElementById('input-codigo');
    var inputNombre = document.getElementById('input-nombre');
    var selectClas  = document.getElementById('input-clasificacion');

    var previewCodigo = document.getElementById('preview-codigo');
    var previewNombre = document.getElementById('preview-nombre');
    var previewBadge  = document.getElementById('preview-badge');
    var previewBadgeTexto = document.getElementById('preview-badge-texto');

    function actualizarPreview() {
        previewCodigo.textContent = inputCodigo.value || 'CÓDIGO-000';
        previewNombre.textContent = inputNombre.value || 'Nombre de la norma';

        var opcion = selectClas.options[selectClas.selectedIndex];
        var nombreClas = opcion ? (opcion.getAttribute('data-nombre') || '') : '';
        var estilo = estilosClasificacion[nombreClas] || estiloDefault;

        previewBadge.style.background = estilo.bg;
        previewBadge.style.color = estilo.color;
        previewBadge.style.borderColor = estilo.border;
        previewBadge.querySelector('.glyphicon').className = 'glyphicon ' + estilo.icon;
        previewBadgeTexto.textContent = opcion ? opcion.textContent.trim() : 'Clasificación';
    }

    inputCodigo.addEventListener('input', actualizarPreview);
    inputNombre.addEventListener('input', actualizarPreview);
    selectClas.addEventListener('change', actualizarPreview);
    actualizarPreview();

    // =====================================================
    // Previsualización de imagen (portada + vista previa)
    // =====================================================
    var inputImg = document.getElementById('input-imagen');
    var previewImg = document.getElementById('preview-img');
    var previewIcon = document.getElementById('preview-icon');
    var previewLiveImg = document.getElementById('preview-live-img');
    var previewLiveIcon = document.getElementById('preview-live-icon');
    var dropzone = document.getElementById('dropzone');

    function mostrarImagen(src) {
        previewImg.src = src;
        previewImg.style.display = 'block';
        if (previewIcon) previewIcon.style.display = 'none';

        previewLiveImg.src = src;
        previewLiveImg.style.display = 'block';
        if (previewLiveIcon) previewLiveIcon.style.display = 'none';
    }

    function cargarArchivo(file) {
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (evt) {
            mostrarImagen(evt.target.result);
        };
        reader.readAsDataURL(file);
    }

    if (inputImg) {
        inputImg.addEventListener('change', function (e) {
            cargarArchivo(e.target.files[0]);
        });
    }

    if (dropzone) {
        ['dragover', 'dragenter'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', function (e) {
            var file = e.dataTransfer.files[0];
            if (file) {
                inputImg.files = e.dataTransfer.files;
                cargarArchivo(file);
            }
        });
    }

    // =====================================================
    // Agregar / eliminar requisitos + contador
    // =====================================================
    var btnAgregar = document.getElementById('btnAgregarPunto');
    var contenedor = document.getElementById('contenedorPuntos');
    var tpl = document.getElementById('tpl-punto').innerHTML;
    var indexCounter = <?php echo !empty($puntos) ? count($puntos) : 0; ?>;
    var contadorPuntosNum = document.getElementById('contadorPuntosNum');

    function actualizarContador() {
        var total = contenedor.querySelectorAll('.punto-row').length;
        contadorPuntosNum.textContent = total;
    }

    function renumerarBadges() {
        var badges = contenedor.querySelectorAll('.punto-badge');
        badges.forEach(function (badge, i) {
            badge.textContent = i + 1;
        });
    }

    btnAgregar.addEventListener('click', function () {
        var mensajeVacio = document.getElementById('mensajeSinPuntos');
        if (mensajeVacio) mensajeVacio.remove();

        var html = tpl.replace(/{INDEX}/g, indexCounter).replace('{NUM}', indexCounter + 1);
        contenedor.insertAdjacentHTML('beforeend', html);
        indexCounter++;
        renumerarBadges();
        actualizarContador();
    });

    contenedor.addEventListener('click', function (e) {
        var btn = e.target.closest('.btnEliminarPunto');
        if (btn) {
            var row = btn.closest('.punto-row');
            if (row) {
                row.remove();
                renumerarBadges();
                actualizarContador();
            }
        }
    });
});
</script>

<?php include_once BASE_PATH . '/layouts/footer.php'; ?>