document.querySelectorAll('.main-image-input-wrapper').forEach(wrapper => {

    const dropArea     = wrapper.querySelector('.drop-area-pro');
    const canvas       = wrapper.querySelector('.main-image-canvas-pro');
    const fileInput    = wrapper.querySelector('input[name="main-image-original"]');
    const formatSelect = wrapper.parentElement.querySelector('.main-image-format-select');
    const form = wrapper.closest('form');

    const MAX_WIDTH  = form.width;
    const MAX_HEIGHT = 400;

    let currentImage = null;

    /* =========================
     * RATIOS POR FORMATO
     * =========================
     * Ajustá estos valores a tus formatos reales
     */
    const formatRatios = {
        banner: 3040/1020,
        square: 1,
        full: 16/9
    };

    function getSelectedRatio() {
        const format = formatSelect ? formatSelect.value : null;
        return formatRatios[format] || 1;
    }

    function drawImageToCanvas() {

        if (!currentImage) return;

        let ratio;

        format = formatSelect.value;
        if(format === 'full') ratio = currentImage.width / currentImage.height;
        else ratio = getSelectedRatio();

        // Definir tamaño base según ratio
        let canvasHeight = MAX_HEIGHT;
        let canvasWidth  = MAX_HEIGHT * ratio;

        if (canvasWidth > MAX_WIDTH) {
            canvasWidth  = MAX_WIDTH;
            canvasHeight = MAX_WIDTH / ratio;
        }

        canvas.width  = canvasWidth;
        canvas.height = canvasHeight;

        dropArea.style.width = canvasWidth + 'px';
        dropArea.style.height = canvasHeight + 'px';

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvasWidth, canvasHeight);

        // COVER
        const scale = Math.max(
            canvasWidth  / currentImage.width,
            canvasHeight / currentImage.height
        );

        const newW = currentImage.width  * scale;
        const newH = currentImage.height * scale;

        const offsetX = (canvasWidth  - newW) / 2;
        const offsetY = (canvasHeight - newH) / 2;

        ctx.drawImage(currentImage, offsetX, offsetY, newW, newH);

        canvas.style.display = 'block';
    }

    function handleImage(file) {
        if (!file) return;

        const reader = new FileReader();

        reader.onload = e => {
            const img = new Image();
            img.onload = () => {
                currentImage = img;
                drawImageToCanvas();
            };
            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    }

    /* =========================
     * IMAGEN EXISTENTE (pages-edit)
     * ========================= */

    if (wrapper.dataset.hasImage === "1") {

        const img = new Image();
        img.onload = () => {
            currentImage = img;
            drawImageToCanvas();
        };
        img.src = wrapper.dataset.imageSrc;
    }

    /* =========================
     * EVENTOS
     * ========================= */

    // Drag & drop
    dropArea.addEventListener('dragover', e => {
        e.preventDefault();
        dropArea.classList.add('dragover');
    });

    dropArea.addEventListener('dragleave', e => {
        e.preventDefault();
        dropArea.classList.remove('dragover');
    });

    dropArea.addEventListener('drop', e => {
        e.preventDefault();
        dropArea.classList.remove('dragover');

        const file = e.dataTransfer.files[0];
        if (!file) return;

        // 🔥 Reemplaza el archivo del input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        handleImage(file);
    });

    // Click selector
    dropArea.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', e => {
        handleImage(e.target.files[0]);
    });

    // Cambio de formato
    if (formatSelect) {
        formatSelect.addEventListener('change', () => {
            drawImageToCanvas();
        });
    }

});
