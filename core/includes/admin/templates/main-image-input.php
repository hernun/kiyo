<?php 
$id = $args[0];
$bg = $args[2] ?? null;
?>
<style>
    /* Estilo para la zona de arrastre */
    #dropArea {
        position:absolute;
        width: 100%;
        height: 100%;
        border-radius: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--main-color);
        font-size: 18px;
        text-align: center;
        z-index: 3;
        cursor:pointer;
    }

    #dropArea.is-invalid {
        border: 5px solid var(--bs-form-invalid-border-color);
    }

    #dropAreaBg {
        position:absolute;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--main-color);
        font-size: 16px;
        text-align: center;
        z-index: 1;
        padding:20px;
        background-size: cover;
        border-radius: 10px;
    }

    #dropAreaBg.dragover {
        background-color: #e0ffe0;
    }

    #mainImageCanvas {
        border: 1px solid var(--main-color);
        z-index: 2;
        position: absolute;
        border-radius: 10px;
        max-width: 100%;
    }

    #file-trigger {
        cursor: pointer;
        padding: 5px;
    }

    #drop-container:hover #file-trigger {
        color: white;
        background-color: var(--main-color);
        border-radius:5px;
    }
</style>
<input type="file" id="main-image-input" name="main-image" style="display: none;">
<div id="drop-container" style="width:200px;height:200px;position:relative">
    <!-- Zona de arrastre -->
    <?php $style = !empty($bg) ? null:'border: 2px dashed var(--main-color)';?>
    <div id="dropArea" class="main-image-drop-area" style="<?php echo $style?>" onclick="document.getElementById('main-image-input').click();"></div>
    <!-- Canvas para procesar la imagen -->
    <canvas id="mainImageCanvas" style="display: none;"></canvas>
    <?php $style = empty($bg) ? null:'background-color:var(--btn-background);background-image:url(\'' . $bg . '\')';?>
    <div id="dropAreaBg" style="<?php echo $style?>">
        <?php if(empty($bg)):?>
            <div class="d-flex flex-column gap-2 pt-4">
                <div>Arrastrá y soltá la imagen aquí</div>
                <div id="file-trigger"><i class="bi bi-upload"></i></div>
            </div>
        <?php endif?>
    </div>
</div>
<script src="<?php echo getAsset('js/nqv-main-image-process.js')?>"></script>