<?php
$tables = nqvDB::getTablenames();
$formID = 'export-backup';

if(submitted($formID)) {

    $scope = $_POST['backup_scope'] ?? 'database';
    $dbType = $_POST['db_type'] ?? 'full';
    $exclude = [];

    if(!empty($_POST['exclude_tables']) && is_array($_POST['exclude_tables'])) {
        $exclude = array_map('trim', $_POST['exclude_tables']);
    }
my_print([$exclude, $dbType,$scope]);
    switch($scope) {

        case 'database':
            $file = nqvDB::exportDatabase($exclude, $dbType);
            break;

        case 'uploads':
            $file = nqvBackup::exportUploads();
            break;

        case 'full':
            $file = nqvBackup::exportFullBackup($exclude, $dbType);
            break;
    }

    nqvNotifications::add("Backup generado correctamente: " . basename($file),'success');
    header("Location: /admin/widgets-view");
    exit;
}

nqvNotifications::flush();
?>

<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-ovo-primary text-white">
            <h5 class="mb-0">Exportación de Backup</h5>
        </div>

        <div class="card-body">

            <div class="alert alert-warning">
                <strong>Información importante</strong>
                <ul class="mb-0 mt-2">
                    <li>Puede exportar únicamente la base de datos.</li>
                    <li>Puede exportar únicamente el directorio de archivos subidos (uploads).</li>
                    <li>Puede generar un backup completo (base de datos + uploads).</li>
                </ul>
                <div class="mt-2">
                    Los archivos generados se almacenarán en el directorio configurado como BKP_PATH: <strong><?= BKP_PATH ?></strong>.
                </div>
            </div>

            <form id="<?= $formID ?>" method="post">
                <input name="form-token" type="hidden" value="<?= get_token($formID) ?>" />

                <!-- Tipo de exportación -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Tipo de Exportación</label>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" 
                               name="backup_scope" value="database" id="scopeDatabase" checked>
                        <label class="form-check-label" for="scopeDatabase">
                            Solo Base de Datos
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" 
                               name="backup_scope" value="uploads" id="scopeUploads">
                        <label class="form-check-label" for="scopeUploads">
                            Solo Uploads
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" 
                               name="backup_scope" value="full" id="scopeFull">
                        <label class="form-check-label" for="scopeFull">
                            Backup Completo (Base de Datos + Uploads)
                        </label>
                    </div>
                </div>

                <!-- Opciones base de datos -->
                <div id="db-section" class="mb-3 w-100">
                    <label class="form-label fw-bold">Opciones de Base de Datos</label>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Exportación</label>
                        <select name="db_type" class="form-select">
                            <option value="full">Estructura + Datos</option>
                            <option value="structure">Solo Estructura</option>
                            <option value="data">Solo Datos</option>
                        </select>
                    </div>

                    <!-- Exclusión de tablas -->
                    <div class="mb-3">
                        <label class="form-label">Excluir tablas</label>
                        <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="select_all">
                                <label class="form-check-label" for="select_all"><small>Seleccionar / deseleccionar todas</small></label>
                            </div>
                        <div class="border rounded p-3">
                            <?php foreach($tables as $table): ?>
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        name="exclude_tables[]" 
                                        value="<?= $table ?>" 
                                        id="table_<?= $table ?>"
                                    >
                                    <label class="form-check-label" for="table_<?= $table ?>">
                                        <?= $table ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <hr>
                </div>


                <div class="d-flex justify-content-end">
                    <button type="submit" 
                            class="btn btn-success"
                            onclick="return confirm('¿Confirma que desea generar el backup?');">
                        Generar Backup
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
<script>
    document.getElementById('select_all').addEventListener('change', function() {
        document.querySelectorAll('input[name="exclude_tables[]"]').forEach(cb => {
            cb.checked = this.checked;
        });
    });
    
    document.addEventListener('DOMContentLoaded', function() {

        const radios = document.querySelectorAll('input[name="backup_scope"]');
        const dbOptions = document.querySelectorAll('select[name="db_type"], input[name="exclude_tables[]"]');

        const dbSection = document.getElementById('db-section');

        function toggleDbOptions() {
            const selected = document.querySelector('input[name="backup_scope"]:checked').value;
            dbOptions.forEach(el => el.disabled = (selected === 'uploads'));
            dbSection.style.display = selected === 'uploads' ? 'none':'block';
        }

        radios.forEach(r => r.addEventListener('change', toggleDbOptions));
        toggleDbOptions();
    });
</script>
