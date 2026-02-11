document.addEventListener('DOMContentLoaded', () => {
    const wrappers = document.querySelectorAll('.main-image-input-wrapper');

    wrappers.forEach(wrapper => {
        const dropArea = wrapper.querySelector('.drop-area-pro');
        const canvas = wrapper.querySelector('.main-image-canvas-pro');
        const fileInput = wrapper.querySelector('.main-image-input-pro');
        const formatSelect = wrapper.parentElement.querySelector('.main-image-format-select');
        const form = wrapper.closest('form');
        let isSubmitting = false;
        const hasImage   = wrapper.dataset.hasImage === '1';
        const imageSrc   = wrapper.dataset.imageSrc;

        if(!dropArea || !canvas || !fileInput) return;

        let currentFile = null;       // Archivo cargado por el usuario
        let currentFileWidth = 0;     // Ancho real de la imagen
        let currentFileHeight = 0;    // Alto real de la imagen
        
        async function loadBgAsFile(src, filename){
            const res = await fetch(src, { cache: 'force-cache' });
            const blob = await res.blob();

            return new File(
                [blob],
                filename,
                { type: blob.type || 'image/jpeg' }
            );
        }

        if(hasImage && imageSrc){
            loadBgAsFile(imageSrc).then(file => {
                handleFile(file);
            });
        }

        // Click para abrir selector
        dropArea.addEventListener('click', () => fileInput.click());

        // Drag & Drop
        dropArea.addEventListener('dragover', e => { e.preventDefault(); dropArea.classList.add('dragover'); });
        dropArea.addEventListener('dragleave', e => { e.preventDefault(); dropArea.classList.remove('dragover'); });
        dropArea.addEventListener('drop', e => { 
            e.preventDefault(); 
            dropArea.classList.remove('dragover'); 
            handleFile(e.dataTransfer.files[0]); 
        });
        fileInput.addEventListener('change', e => handleFile(e.target.files[0]));
        formatSelect.addEventListener('change', () => adjustDropArea());

        function handleImage(img, { fromFile = false } = {}){
            currentImage = img;

            currentFileWidth  = img.width;
            currentFileHeight = img.height;

            if(formatSelect.value === 'full'){
                const MAX_WIDTH = wrapper.clientWidth;
                const MAX_HEIGHT = 300;

                let w = Math.min(img.width, MAX_WIDTH);
                let h = Math.min(img.height, MAX_HEIGHT);

                dropArea.style.width = w + 'px';
                dropArea.style.height = h + 'px';
                canvas.width = w;
                canvas.height = h;
            }

            processImage(img);
        }

        function handleFile(file){
            if(!file) return;

            currentFile = file;

            const reader = new FileReader();
            reader.onload = e => {
                const img = new Image();
                img.onload = () => handleImage(img, { fromFile: true });
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function setAreaSize(size) {
            if(!size) size = getSize()
            // Aplicar al canvas y dropArea
            dropArea.style.width = size.w+'px';
            dropArea.style.height = size.h+'px';
            canvas.width = size.w;
            canvas.height = size.h;
        }

        function adjustDropArea(){
            setAreaSize()

            // Redibujar imagen existente según canvas
            if(currentFile){
                const img = new Image();
                img.onload = () => processImage(img);
                const reader = new FileReader();
                reader.onload = e => img.src = e.target.result;
                reader.readAsDataURL(currentFile);
            }
        }

        function getSize() {
            const MAX_WIDTH = wrapper.clientWidth;
            const MAX_HEIGHT = 300;
            const format = formatSelect.value;

            let w = currentFileWidth;
            let h = currentFileHeight;
            let ratio = 1;

            if(format === 'square'){
                w = h = Math.min(300, MAX_WIDTH, currentFileWidth);
            } else if(format === 'banner'){
                ratio = 3040 / 1020;
                w = Math.min(MAX_WIDTH, 3040, currentFileWidth);
                h = w / ratio;

                if(h > MAX_HEIGHT){
                    h = MAX_HEIGHT;
                    w = h * ratio;
                }
            } else if(format === 'full'){
                if(currentFile){
                    if(w > MAX_WIDTH){
                        const scale = MAX_WIDTH / w;
                        w *= scale;
                        h *= scale;
                    }

                    if(h > MAX_HEIGHT){
                        const scale = MAX_HEIGHT / h;
                        h *= scale;
                        w *= scale;
                    }

                } else {
                    ratio = 16 / 9;
                    w = MAX_WIDTH;
                    h = w / ratio;

                    if(h > MAX_HEIGHT){
                        h = MAX_HEIGHT;
                        w = h * ratio;
                    }
                }
            }

            return { w, h };
        }

        function processImage(img){
            let { w: canvasW, h: canvasH } = getSize();

            setAreaSize({ w: canvasW, h: canvasH });

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvasW, canvasH);

            const imgRatio    = img.width / img.height;
            const canvasRatio = canvasW / canvasH;

            let drawW, drawH, offsetX = 0, offsetY = 0;

            if(formatSelect.value === 'full'){
                // === CONTAIN (sin recorte) ===
                const scale = Math.min(
                    canvasW / img.width,
                    canvasH / img.height,
                    1
                );

                drawW = img.width  * scale;
                drawH = img.height * scale;

                offsetX = (canvasW - drawW) / 2;
                offsetY = (canvasH - drawH) / 2;

            } else {
                // === COVER (recorte centrado) ===
                if(imgRatio > canvasRatio){
                    // Imagen más ancha → ajustar alto, recortar ancho
                    drawH = canvasH;
                    drawW = canvasH * imgRatio;
                    offsetX = (canvasW - drawW) / 2;
                } else {
                    // Imagen más alta → ajustar ancho, recortar alto
                    drawW = canvasW;
                    drawH = canvasW / imgRatio;
                    offsetY = (canvasH - drawH) / 2;
                }
            }

            ctx.drawImage(img, offsetX, offsetY, drawW, drawH);
            canvas.style.display = 'block';
        }

        if(form){
            form.addEventListener('submit', e => {
                if(isSubmitting) return;

                // No hay imagen → dejar seguir
                if(!currentFile){
                    isSubmitting = true;
                    return;
                }

                e.preventDefault();

                canvas.toBlob(blob => {
                    if(!blob) return;

                    const ext = currentFile.type === 'image/png' ? 'png' : 'jpg';
                    const newFile = new File(
                        [blob],
                        currentFile.name || `imagen_procesada.${ext}`,
                        { type: currentFile.type || 'image/jpeg' }
                    );

                    const dt = new DataTransfer();
                    dt.items.add(newFile);
                    fileInput.files = dt.files;

                    isSubmitting = true;
                    form.requestSubmit();
                }, currentFile.type || 'image/jpeg');
            });
        }

        window.addEventListener('resize', adjustDropArea);
        // Inicializar tamaño
        adjustDropArea();
    });
});
