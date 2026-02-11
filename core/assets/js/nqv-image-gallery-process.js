const filesInput = document.getElementById("image-gallery-input");
const dataTransfer = new DataTransfer(); // Usamos DataTransfer para agregar los archivos al input

// Permitir el "drag over" en la zona de caída
function allowDrop(event) {
    event.preventDefault();
}
  
// Manejar los archivos arrastrados y soltados
function handleDrop(event) {
    event.preventDefault();
    const files = event.dataTransfer.files;
    showFiles(files);
    addFilesToInput(files);
}

function removeFileFromDropByIndex(index) {
    const dt = new DataTransfer();
    const children = document.querySelectorAll('.file-preview');

    // Eliminamos la vista previa correspondiente
    if (children[index]) children[index].remove();

    // Reconstruimos la lista de archivos en dataTransfer excluyendo el eliminado
    for (let i = 0; i < dataTransfer.files.length; i++) {
        if (i !== index - 1) {
            dt.items.add(dataTransfer.files[i]);
        }
    }

    // Reemplazamos el dataTransfer global y actualizamos el input
    dataTransfer.items.clear();
    addFilesToInput(dt.files);

    // Reasignamos índices a los botones restantes
    const deleteButtons = document.querySelectorAll('.delete-button');
    deleteButtons.forEach((btn, idx) => {
        btn.dataset.index = idx;
    });
}
  
// Mostrar los archivos en una lista de previsualización
function showFiles(files) {
    const fileList = document.getElementById("fileList");

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        // Previsualización de imágenes
        reader.onload = function(event) {
            const fileDiv = document.createElement("div");
            fileDiv.classList.add("file-preview");

            // Previsualización de la imagen si es una imagen
            if (file.type.startsWith("image/")) {
                const img = document.createElement("img");
                const deletebuttonDiv = document.createElement("div");
                const children = document.querySelectorAll('.file-preview');
                deletebuttonDiv.classList.add('delete-button');
                deletebuttonDiv.innerText = 'X';
                deletebuttonDiv.dataset.tagid = '';
                img.src = event.target.result;
                deletebuttonDiv.dataset.index = Math.max(children.length,0);
                fileDiv.appendChild(img);
                fileDiv.appendChild(deletebuttonDiv);
            }

            // Nombre del archivo
            const fileName = document.createElement("p");
            fileName.textContent = file.name;
            fileDiv.appendChild(fileName);
            fileList.appendChild(fileDiv);
        };
        
        // Leer el archivo como DataURL (solo para imágenes)
        reader.readAsDataURL(file);
    }
}
  
// Agregar los archivos al input file
function addFilesToInput(files) {
    for (let i = 0; i < files.length; i++) {
        dataTransfer.items.add(files[i]);
    }
    filesInput.files = dataTransfer.files; // Establecemos los archivos en el input de tipo file
}
  
// Permitir la selección de archivos manualmente (opcional)
filesInput.addEventListener("change", function(event) {
    showFiles(event.target.files);
});

$('body').on({
    click: function() {
        const id = $(this).data('id');
        if(id) {
            let value = $('#files-to-delete-input').val().split(",").map(s => s.trim()).filter(Boolean);
            if (!value.includes(id)) {
                value.push(id);
            }
            $('#files-to-delete-input').val(value.join(","));
            $(this).parents('.file-preview').fadeOut(function(){
                $(this).remove()
            });
        } else {
            removeFileFromDropByIndex($(this).data('index'));
        }
    }
},'.delete-button');