<?php
/**
 * Variables disponibles:
 * - $entity
 * - $elementId
 * - $opts: array con keys 'formats', 'default_format', 'dpi_options', 'default_dpi'
 */

if($entity && $elementId) {
    $image = nqvMainImages::getByElementId($entity,$elementId);
    $bg = $image ? $image->getSrc() : null;
} else {
    $image = null;
    $bg = null;
}

$format = !empty($opts['format']) ? $opts['format']:$opts['default_format'];
?>

<style>
.main-image-input-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.drop-area-pro {
    width:100%;
    max-height: 100%;
    border-radius: 10px;
    display: block;
    border: 2px dashed var(--main-color);
    color: var(--main-color);
    font-size: 16px;
    text-align: center;
    cursor: pointer;
    overflow: hidden;
    position: absolute;
}

.drop-area-pro.dragover {
    background-color: #e0ffe0;
}

.main-image-canvas-pro {
    display: block;
    height: auto;
    border-radius: 10px;
}


</style>

<div class="main-image-input-wrapper mb-3"
     data-has-image="<?= $bg ? '1' : '0' ?>"
     data-image-src="<?= $bg ?: '' ?>">

    <!-- ORIGINAL -->
    <input type="file"
           class="main-image-input-pro d-none"
           name="main-image-original"
           accept="image/jpeg,image/png,image/webp">

    <!-- PROCESADA -->
    <input type="file"
           class="main-image-processed d-none"
           name="main-image-processed">

    <div class="drop-area-pro border border-secondary rounded d-flex justify-content-center align-items-center text-center" >

        <?php if(empty($bg)): ?>
            Arrastrá y soltá la imagen aquí<br>o hacé click para seleccionar
        <?php endif; ?>

    </div>

    <canvas class="main-image-canvas-pro rounded"></canvas>

</div>

<div class="mb-2">
    <label class="form-label">
        Formato de la <?= nqv::translate('main image') ?>
    </label>
    <select class="form-select main-image-format-select" name="mainimageformat">
        <?php foreach($opts['formats'] as $key => $label): ?>
            <option value="<?= $key ?>"
                <?= ($format === $key)?'selected':'' ?>>
                <?= $label ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<script src="<?= getAsset('js/nqv-main-image-process-pro.js') ?>"></script>
