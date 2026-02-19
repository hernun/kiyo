<?php
$formID = 'import-backup';

if(submitted($formID)) {
    $mode = $_POST['import_mode'] ?? 'replace';
    $source = $_POST['source_type'] ?? 'server';

    if(!in_array($mode,['replace','append'])) {
        nqvNotifications::add('Modo inválido','danger');
    } else {
        try {
            if($source === 'server') {
                $selectedFile = $_POST['server_file'] ?? '';
                if(empty($selectedFile)) throw new Exception('Debe seleccionar un archivo.');
                $filePath = BKP_PATH . basename($selectedFile);
                if(!file_exists($filePath)) throw new Exception('El archivo no existe.');
            } else {
                if (empty($_FILES['uploaded_file']['name'])) throw new Exception('Debe subir un archivo.');
                $ext = strtolower(pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['sql','zip'])) throw new Exception('Tipo de archivo no soportado.');
                $filePath = sys_get_temp_dir() . '/import_' . uniqid() . '.' . $ext;
                if (!move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $filePath)) throw new Exception('Error moviendo el archivo subido.');
            }

            // Determinar tipo de archivo
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if($ext === 'sql') nqvDB::importDatabase($filePath,$mode);
            elseif($ext === 'zip') nqvBackup::importFullBackup($filePath,$mode);
            else throw new Exception('Tipo de archivo no soportado.');

            nqvNotifications::add('Importación realizada correctamente.','success');
        } catch(Exception $e) {
            nqvNotifications::add($e->getMessage(),'danger');
        }
        if($source === 'upload' && isset($filePath) && file_exists($filePath)) unlink($filePath);
    }
}

nqvNotifications::flush();
?>
<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Importación de Backup</h5>
        </div>

        <div class="card-body">

            <div class="alert alert-danger">
                <strong>Advertencia crítica</strong>
                <ul class="mb-0 mt-2">
                    <li>Las tablas incluidas en el archivo a importar serán eliminadas si ya existen en la base de datos y creadas nuevamente (DROP TABLE IF EXISTS).</li>
                    <li>En caso d esuperposición o conflicto, los datos actuales pueden perderse en beneficio de los nuevos.</li>
                    <li>No se realiza rollback automático.</li>
                    <li>Si el archivo de exportación no fue generado por OVO, el comportamiento es impredecible.</li>
                    <li>La operación es irreversible</li>
                </ul>
            </div>

            <form id="<?= $formID ?>" method="post" enctype="multipart/form-data">
                <input name="form-token" type="hidden" value="<?= get_token($formID) ?>" />

                <!-- Fuente -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Origen del archivo</label>

                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                               name="source_type" value="server" id="sourceServer" checked>
                        <label class="form-check-label" for="sourceServer">
                            Seleccionar archivo desde el servidor
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                               name="source_type" value="upload" id="sourceUpload">
                        <label class="form-check-label" for="sourceUpload">
                            Subir archivo desde mi equipo
                        </label>
                    </div>
                </div>

                <!-- Selección servidor -->
                <div id="server-section" class="mb-3">
                    <label class="form-label">Archivos disponibles en BKP_PATH</label>
                    <select name="server_file" class="form-select">
                        <option value="">-- Seleccione un archivo --</option>
                        <?php $files = nqvBackup::getBackups(nqvBackup::CATEGORY_BACKUP);?>
                        <?php foreach($files as $file):?>
                            <option value="<?= $file ?>"><?= $file ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Subida -->
                <div id="upload-section" class="mb-3" style="display:none;">
                    <label class="form-label">Subir archivo (.sql o .zip)</label>
                    <input type="file" name="uploaded_file" class="form-control" accept=".sql,.zip">
                </div>

                <!-- Modo -->
                <div class="mb-4">
                    <label class="form-label fw-bold mb-0">Modo de Importación</label>
                    <div class="my-3 form-text">Cuando la importación de la base de datos incluye únicamente los datos y no la estructura, la opción "agregar" no reemplaza los datos existentes sino que solamente suma los nuevos. Si, en cambio, se importa la estructura los datos actuales serán eliminaods. Esto depende de cómo se haya realizado la exportación.</div>
                    <div class="mb-4 form-text"> Para el directorio de uploads, el modo replace elimina completamente la carpeta actual antes de restaurarla, mientras que el modo append conserva los archivos existentes y sólo sobrescribe aquellos incluidos en el backup.</div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="import_mode" value="replace" checked>
                        <label class="form-check-label">Reemplazar (sobrescribe tablas y/o archivos)</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                               name="import_mode" value="append">
                        <label class="form-check-label">
                            Agregar (no elimina archivos existentes)
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit"
                            class="btn btn-danger"
                            onclick="return confirm('¿Confirma que desea importar el backup? Esta acción puede sobrescribir datos.');">
                        Ejecutar Importación
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {

    const radios = document.querySelectorAll('input[name="source_type"]');
    const serverSection = document.getElementById('server-section');
    const uploadSection = document.getElementById('upload-section');

    function toggleSource() {
        const selected = document.querySelector('input[name="source_type"]:checked').value;
        serverSection.style.display = selected === 'server' ? 'block' : 'none';
        uploadSection.style.display = selected === 'upload' ? 'block' : 'none';
    }

    radios.forEach(r => r.addEventListener('change', toggleSource));
    toggleSource();
});
</script>
