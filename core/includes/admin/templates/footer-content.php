<?php
$formId = 'items-form';
$currentValue = nqv::getConfig('footer-content');
if(submitted($formId)) {
    $items = [];
    foreach($_POST['item']['name'] as $k => $name) {
        $items[] = ['name' => $name, 'url' => $_POST['item']['url'][$k],'class' => $_POST['item']['class'][$k]];
    }
    try {       
        nqv::setConfig('footer-content',json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        nqvNotifications::add('la configuración se actualizó con éxito','success');
    } catch(Exception $e) {
        nqvNotifications::add($e->getMessage(),'error');
    }
    header('location:');
    exit;
}
nqvNotifications::flush();
?>

<div class="container">
    <h2 class="my-5 pb-5">Contenido del footer</h2>

    <form id="<?= $formId ?>" method="post" class="container">
        <input type="hidden" name="form-token" value="<?= get_token($formId) ?>" />

        <div class="my-3">
            <!-- SOCIAL -->
            <h4 class="mb-4">Items</h4>

            <div id="item-container">
                <?php foreach($currentValue as $s):?>
                    <div class="row mb-3 align-items-center item-row">
                        <div class="col-md-3">
                            <input type="text" name="item[name][]" class="form-control" placeholder="Nombre (ej: Mail)" value="<?= $s['name'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <input type="url" name="item[url][]" class="form-control" placeholder="https://..." value="<?= $s['url'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="item[class][]" class="form-control" placeholder="CSS class" value="<?= $s['class'] ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-item">x</button>
                        </div>
                    </div>
                <?php endforeach?>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mb-5" id="add-item">+ Agregar item</button>
        </div>

        <div class="mb-5">
            <button type="submit" class="btn btn-success">Guardar configuración</button>
        </div>

        </form>
</div>
<script>
    const itemContainer = document.getElementById('item-container');
    const addSocialBtn = document.getElementById('add-item');

    function createSocialRow(name = '', url = '') {

        const row = document.createElement('div');
        row.className = 'row mb-3 align-items-center item-row';

        row.innerHTML = `
            <div class="col-md-3">
                <input type="text" name="item[name][]" class="form-control" placeholder="Nombre (ej: Mail)" value="${name}">
            </div>
            <div class="col-md-4">
                <input type="url" name="item[url][]" class="form-control" placeholder="https://..." value="${url}">
            </div>
            <div class="col-md-3">
                <input type="text" name="item[class][]" class="form-control" placeholder="CSS class" value="${url}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-item">×</button>
            </div>
        `;

        itemContainer.appendChild(row);
    }

    addSocialBtn.addEventListener('click', () => createSocialRow());

    itemContainer.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-item')) {
            e.target.closest('.item-row').remove();
        }
    });
</script>