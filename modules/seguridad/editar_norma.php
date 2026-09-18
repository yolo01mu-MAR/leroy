<?php
require_once __DIR__ . '/../../app/bootstrap.php';

page_require_level(12);

  $scripts = [
    'editar_normas'
  ];

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
$result_norma = get_norma_clasificaciones($id_norma);

if (empty($result_norma)) {
    $session->msg("d=La norma no existe.&type=danger");
    redirect('normas.php');
    exit;
}

$norma = $result_norma[0];
$clasificaciones = find_clasificaciones_normas();

// =========================================================
// 3. OBTENER PUNTOS EXISTENTES
// =========================================================
$puntos = get_puntos_norma($id_norma);

$page_title = "Editar Norma: " . remove_junk($norma['codigo_norma']);

?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<link rel="stylesheet" href="assets/css/editar_normas.css">
<div class="edit-norma-container">

    <form method="POST" action="guardar_norma.php" enctype="multipart/form-data" id="formEditarNorma">
        
        <input type="hidden" name="puntos[{INDEX}][id]" value="<?php echo $punto['id']; ?>">
        <input type="hidden" name="id_norma" value="<?php echo (int)$norma['id']; ?>">

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
                    <!-- IMAGEN -->
                    <?php
                        $foto = !empty($norma['imagen'])
                            ? BASE_URL . '/uploads/normas/' . $norma['imagen']
                            : BASE_URL . '/uploads/normas/no_image.jpg';
                    ?>
                    <?php if (!empty($norma['imagen'])): ?>
                        <img src="<?php echo $foto; ?>" id="preview-live-img" alt="Portada">
                    <?php else: ?>
                        <span class="glyphicon glyphicon-picture" id="preview-live-icon" style="font-size:20px; color:#94a3b8;"></span>
                        <img src="<?php echo $foto; ?>" id="preview-live-img" style="display:none;" alt="Portada">
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
                                <img src="<?php echo $foto; ?>" id="preview-img" alt="Portada">
                            <?php else: ?>
                                <span class="glyphicon glyphicon-picture" id="preview-icon" style="font-size: 22px; color: #94a3b8;"></span>
                                <img src="<?php echo $foto; ?>" id="preview-img" style="display:none;" alt="Portada">
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
                <a href="normas.php" class="btn btn-default">
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
<?php include_once BASE_PATH . '/layouts/footer.php'; ?>