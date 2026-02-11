<?php
/**
 * Variables disponibles:
 * - $entity
 * - $elementId
 * - $opts: array con keys 'formats', 'default_format', 'dpi_options', 'default_dpi'
 */

$image = nqvMainImages::getByElementId($entity,$elementId);
$bg = $image->getSrc();
?>

<style>
    #dropAreaPro {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 2px dashed var(--main-color);
        color: var(--main-color);
        font-size: 16px;
        text-align: center;
        cursor: pointer;
    }
    #dropAreaPro.dragover { background-color: #e0ffe0; }
    #mainImageCanvasPro { display:none; max-width:100%; border-radius:10px; border:1px solid var(--main-color); position:absolute; z-index:2; }
</style>

<div class="main-image-input-wrapper position-relative mb-3"
     data-has-image="<?= $bg ? '1' : '0' ?>"
     data-image-src="<?= $bg ?: '' ?>">
    <input type="file" class="main-image-input-pro d-none" name="main-image" accept="image/jpeg,image/png">
    <div class="drop-area-pro border border-secondary rounded d-flex justify-content-center align-items-center text-center" style="cursor:pointer;">
        <?php if(empty($bg)): ?>
            Arrastrá y soltá la imagen aquí<br>o hacé click para seleccionar
        <?php endif; ?>
    </div>
    <canvas class="main-image-canvas-pro position-absolute top-0 start-0 rounded" style="display:none;"></canvas>
</div>

<div class="mb-2">
    <label class="form-label">Formato de la <?php echo nqv::translate('main image') ?></label>
    <select class="form-select main-image-format-select">
        <?php foreach($opts['formats'] as $key=>$label): ?>
            <option value="<?= $key ?>" <?= ($opts['default_format']===$key)?'selected':'' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
</div>

<script src="<?= getAsset('js/nqv-main-image-process-pro.js') ?>"></script>
<script>
document.querySelectorAll('.main-image-input-wrapper').forEach(wrapper => {
    const dropArea = wrapper.querySelector('.drop-area-pro');
    const canvas = wrapper.querySelector('.main-image-canvas-pro');
    const fileInput = wrapper.querySelector('.main-image-input-pro');
    const formatSelect = wrapper.parentElement.querySelector('.main-image-format-select');
    const dpiCheckbox = wrapper.parentElement.querySelector('.main-image-dpi');

    const MAX_HEIGHT = 300;
    let currentImage = null;
    let file = null;
    
    function drawImageToCanvas(){
        // Obtener el contexto 2D del canvas donde se dibujará la imagen
        const ctx = canvas.getContext('2d');

        // Limpiar completamente el canvas antes de dibujar la nueva imagen
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Calcular la escala necesaria para que la imagen cubra todo el canvas
        // Math.max asegura que cubra completamente en ambas dimensiones (recorte si sobra)
        let scale = Math.max(canvas.width / currentImage.width, canvas.height / currentImage.height);

        // Calcular el ancho y alto de la imagen escalada
        let newW = currentImage.width * scale;
        let newH = currentImage.height * scale;

        // Calcular desplazamiento para centrar la imagen dentro del canvas
        // Si la imagen es más grande que el canvas, parte se recortará
        let offsetX = (canvas.width - newW) / 2;
        let offsetY = (canvas.height - newH) / 2;

        // Dibujar la imagen escalada y centrada en el canvas
        ctx.drawImage(currentImage, offsetX, offsetY, newW, newH);

        // Mostrar el canvas (puede estar oculto al inicio)
        canvas.style.display = 'block';

        // Si hay un archivo asociado (subido por el usuario), actualizar el input file con la versión procesada
        if(file){
            canvas.toBlob(blob => {
                // Determinar extensión según tipo de archivo original
                const ext = file.type==='image/png' ? 'png' : 'jpg';

                // Crear un nuevo objeto File a partir del blob generado desde el canvas
                const newFile = new File([blob], 'imagen_procesada.'+ext, { type:file.type });

                // Crear un DataTransfer para poder asignar un File al input type="file"
                const dt = new DataTransfer();
                dt.items.add(newFile);

                // Asignar el nuevo archivo procesado al input, listo para enviar al backend
                fileInput.files = dt.files;
            }, file.type);
        }
    }


    function handleImage(f){
        if(!f) return;
        file = f;
        const reader = new FileReader();
        reader.onload = e => {
            const img = new Image();
            img.onload = () => { currentImage = img; drawImageToCanvas(); };
            img.src = e.target.result;
        };
        reader.readAsDataURL(f);
    }

    // Eventos
    dropArea.addEventListener('dragover', e=>{ e.preventDefault(); dropArea.classList.add('dragover'); });
    dropArea.addEventListener('dragleave', e=>{ e.preventDefault(); dropArea.classList.remove('dragover'); });
    dropArea.addEventListener('drop', e=>{
        e.preventDefault();
        dropArea.classList.remove('dragover');
        handleImage(e.dataTransfer.files[0]);
    });
});
</script>
