const mainImageDropArea = document.getElementById('dropArea');
const mainImageDropAreaBg = document.getElementById('dropAreaBg');
const mainImageCanvas = document.getElementById('mainImageCanvas');
const mainImageFile = document.getElementById('main-image-input');

// Prevenir el comportamiento por defecto (evitar abrir la imagen en el navegador)
mainImageDropArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    mainImageDropAreaBg.classList.add('dragover'); // Cambiar el estilo cuando el archivo es arrastrado
});

mainImageDropArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    mainImageDropArea.classList.remove('dragover'); // Restaurar el estilo cuando el archivo sale
});

mainImageDropArea.addEventListener('drop', function(e) {
    e.preventDefault();
    mainImageDropArea.classList.remove('error');
    mainImageDropArea.classList.remove('dragover'); // Restaurar el estilo
    const file = e.dataTransfer.files[0]; // Obtener el archivo que se soltó
    if (file) {
        handleImage(file); // Procesar la imagen
    }
});

function handleImage(file) {
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
        alert('Solo se aceptan imágenes JPEG o PNG.');
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            // Procesar la imagen (redimensionar, etc.)
            cropImage(img);
            addImageToFileInput(file.type);
        };
        img.src = e.target.result; // Cargar la imagen como base64
    };
    reader.readAsDataURL(file); // Leer la imagen como base64
}

function cropImage(img) {
    const ctx = mainImageCanvas.getContext('2d');

    // Establecer el tamaño del canvas al tamaño del recorte deseado (500x500)
    const squareSize = 500;
    mainImageCanvas.width = squareSize;
    mainImageCanvas.height = squareSize;

    // Rellenar el fondo del canvas con blanco para evitar transparencia
    ctx.fillStyle = '#c7c7c7'; // Fondo blanco
    ctx.fillRect(0, 0, mainImageCanvas.width, mainImageCanvas.height); // Rellenar el canvas con color

    // Calcular el factor de escala para que el lado más corto sea 500px
    let scaleFactor = 1;
    if (img.width > img.height) {
        // Si el ancho es mayor que el alto, ajustamos el ancho a 500px
        scaleFactor = squareSize / img.height;
    } else {
        // Si el alto es mayor que el ancho, ajustamos el alto a 500px
        scaleFactor = squareSize / img.width;
    }

    // Calcular las nuevas dimensiones de la imagen redimensionada
    const newWidth = img.width * scaleFactor;
    const newHeight = img.height * scaleFactor;

    // Redibujar la imagen redimensionada en el canvas
    const xOffset = (newWidth - squareSize) / 2;
    const yOffset = (newHeight - squareSize) / 2;

    ctx.drawImage(img, -xOffset, -yOffset, newWidth, newHeight);

    // Mostrar el canvas con la previsualización del recorte
    mainImageCanvas.style.display = 'block';
}

function addImageToFileInput(mimeType) {
    const extension = mimeType === 'image/png' ? 'png' : 'jpg';
    mainImageCanvas.toBlob(function(blob) {
        const mainImageFileInput = document.getElementById("main-image-input");
        // Crear un archivo a partir del blob
        const file = new File([blob], `imagen_recortada.${extension}`, { type: mimeType });        
        // Crear un DataTransfer para simular la carga de archivos
        const dataTransfer = new DataTransfer();
        // Añadir el archivo al DataTransfer
        dataTransfer.items.add(file);
        // Asignar los archivos al input de tipo file
        mainImageFileInput.files = dataTransfer.files;
    }, mimeType);
}

mainImageFile.addEventListener('change',function(e){
    const file = e.target.files[0]; // Obtener el archivo que se soltó
    handleImage(file)
});