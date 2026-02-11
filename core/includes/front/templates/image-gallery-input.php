<?php $images = !empty($args[2]) ? $args[2]:[];?>
<style>
h2 {
    text-align: center;
}

.dropzone {
    width: 100%;
    min-height: 150px;
    border: 2px dashed var(--main-color);
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
    color: #777;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s;
}

.dropzone:hover {
    background-color: #f0f0f0;
}

#fileList {
    margin-top: 20px;
}

.file-preview {
    display: inline-block;
    margin: 10px;
    text-align: center;
}

.file-preview img {
    height: 100px;
    object-fit: cover;
    border-radius: 5px;
    margin-bottom: 10px;
}

.file-preview p {
    font-size: 12px;
    color: #555;
}

</style>
<!-- Zona de arrastrar y soltar -->
<div class="dropzone" id="dropzone" ondrop="handleDrop(event)" ondragover="allowDrop(event)">
    <div class="my-3">
        <p class="text-center">Arrastrá los archivos aquí o hacé clic para seleccionar</p>
        <input type="file" id="image-gallery-input" name="gallery-files[]" multiple style="display: none;">
        <input type="hidden" id="files-to-delete-input" name="files-to-delete" value="" />
        <button class="btn btn-default" type="button" onclick="document.getElementById('image-gallery-input').click();">Seleccionar Archivos</button>
    </div>
    <div id="fileList">
        <!-- Aquí se mostrarán las previsualizaciones -->
        <?php foreach($images as $image):?>
            <div class="file-preview">
                <img src="<?php echo $image->getEncodedSrc()?>">
                <div class="delete-button" data-id="<?php echo $image->get('id')?>">X</div>
                <p>
                    <?php echo pathinfo($image->get('filepath'),PATHINFO_FILENAME)?>.<?php echo pathinfo($image->get('filepath'),PATHINFO_EXTENSION)?>
                </p>
            </div>
        <?php endforeach?>
    </div>
</div>
<script src="<?php echo getAsset('js/nqv-image-gallery-process.js')?>"></script>