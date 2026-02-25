<?php nqv::setAccess(['crud','permissions'])?>
<?php
$perms = nqv::getConfig('permissions');

$permsTypes = ['create','read','update','delete','crud'];
$types = nqv::getSessionTypes();

$formid = 'set-permissions';
if(submitted($formid)) {
    //$tables = json_encode($_POST['tables'],JSON_HEX_APOS);
    $validTables = array_merge(nqvDB::getTablenames(),explode(',',(string) nqv::getConfig('additional-permissions')));
    $cleanTables = array_filter($_POST['tables'] ?? [], fn($t) => in_array($t, $validTables));
    $tables = array_values($cleanTables);

    if (in_array($_POST['permissions-type'], $permsTypes, true) && in_array($_POST['sessions-type'], array_column($types, 'slug'), true)) {
        $perms[$_POST['sessions-type']][$_POST['permissions-type']] = $tables;
    }
    $permissions = json_encode($perms,JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);
    nqv::setConfig('permissions',$permissions);
    header('location:');
    exit;
}

$tables = array_merge(nqvDB::getTablenames(),explode(',',(string) nqv::getConfig('additional-permissions')));
sort($tables);
?>
<div class="container my-4">
    <h1 class="my-3">Permisos</h1>
    <form id="<?php echo $formid?>" method="post" accept-charset="utf8">
        <input type="hidden" name="form-token" value="<?php echo get_token($formid)?>" />
            <div class="row">
                <div class="col-4">
                    <div class="mb-3">
                        <label for="sessions-type-input" class="form-label">Tipos de usuario</label>
                        <select id="sessions-type-input" name="sessions-type" class="form-select" aria-label="Usertypes select">
                            <option value="" disabled selected>Seleccioná un tipo de usuario</option>
                            <?php foreach($types as $type):?>
                                <option value="<?php echo $type['slug']?>"><?php echo $type['name']?></option>
                            <?php endforeach?>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="mb-3">
                        <label for="permissions-type-input" class="form-label">Permisos</label>
                        <select id="permissions-type-input" name="permissions-type" class="form-select" aria-label="Actions select">
                            <option value="" disabled selected>Seleccioná una acción</option>
                            <?php foreach($permsTypes as $permsType):?>
                                <option value="<?php echo $permsType?>"><?php echo $permsType?></option>
                            <?php endforeach?>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label for="table-filter" class="form-label">Buscar tablas</label>
                        <input id="table-filter" class="form-control mb-2" type="text" placeholder="Filtrar tablas...">

                        <label for="tables-input" class="form-label">Permisos</label>
                        <select id="tables-input" name="tables[]" class="form-select" size="15" multiple>
                            <?php foreach($tables as $table): ?>
                                <option id="<?= $table ?>-option" value="<?= $table ?>" disabled><?= $table ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        <div class="col-auto my-4">
            <button type="submit" class="btn btn-sm btn-primary mb-3">Enviar</button>
        </div>
    </form>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
    const typesSelect = document.getElementById('sessions-type-input');
    const actionsSelect = document.getElementById('permissions-type-input');
    const tableSelect = document.getElementById('tables-input');
    const filterInput = document.getElementById('table-filter');

    const allOptions = Array.from(tableSelect.options);

    const perms = <?php echo json_encode($perms ?? [],JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    function resetTableSelect() {
        tableSelect.querySelectorAll('option').forEach(opt => {
            opt.disabled = true;
            opt.selected = false;
        });
    }

    function populateSelection() {
        const sessionType = typesSelect.value;
        const action = actionsSelect.value;

        if(!sessionType || !action) return;

        // Habilitar todas las opciones primero (sin seleccionarlas)
        tableSelect.querySelectorAll('option').forEach(opt => {
            opt.disabled = false;
            opt.selected = false;
        });

        // Si hay permisos definidos para este tipo y acción, seleccionarlos
        if (sessionType && action && perms[sessionType] && perms[sessionType][action]) {
            try {
                console.log(perms[sessionType][action])
                //const selectedTables = JSON.parse(perms[sessionType][action]);
                const selectedTables = perms[sessionType][action];
                selectedTables.forEach(table => {
                    const option = document.getElementById(`${table.trim()}-option`);
                    if (option) {
                        option.selected = true;
                    }
                });
            } catch (e) {
                console.warn('Error parsing permissions JSON', e);
            }
        }
    }


    function filterOptions(term) {
        const normalized = normalize(term.toLowerCase());
        allOptions.forEach(option => {
            const text = normalize(option.text.toLowerCase());
            option.style.display = text.includes(normalized) ? '' : 'none';
        });
    }

    function normalize(str) {
        const accentMap = { "á": "a", "é": "e", "í": "i", "ó": "o", "ú": "u", "ñ": "n" };
        return str.replace(/[áéíóúñ]/gi, m => accentMap[m] || m);
    }

    typesSelect.addEventListener('change', populateSelection);
    actionsSelect.addEventListener('change', populateSelection);
    filterInput.addEventListener('input', function () {
        filterOptions(this.value);
    });

    // Inicializar selección si ya hay valores cargados
    populateSelection();
});
</script>
