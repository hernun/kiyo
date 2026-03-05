<?php
$formId = 'socials-form';
$currentValue = nqv::getConfig('socials');
if(submitted($formId)) {
    $socials = [];
    foreach($_POST['social']['name'] as $k => $name) {
        $socials[] = ['name' => $name, 'url' => $_POST['social']['url'][$k],'class' => $_POST['social']['class'][$k]];
    }
    try {       
        nqv::setConfig('socials',json_encode($socials, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
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
    <h2 class="my-5 pb-5">Redes sociales</h2>

    <form id="<?= $formId ?>" method="post" class="container">
        <input type="hidden" name="form-token" value="<?= get_token($formId) ?>" />

        <div class="my-3">
            <!-- SOCIAL -->
            <h4 class="mb-4">Redes sociales</h4>

            <div id="social-container">
                <?php foreach($currentValue as $s):?>
                    <div class="row mb-3 align-items-center social-row">
                        <div class="col-md-3">
                            <input type="text" name="social[name][]" class="form-control" placeholder="Nombre (ej: Instagram)" value="<?= $s['name'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <input type="url" name="social[url][]" class="form-control" placeholder="https://..." value="<?= $s['url'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="social[class][]" class="form-control" placeholder="CSS class" value="<?= $s['class'] ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-social">x</button>
                        </div>
                    </div>
                <?php endforeach?>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mb-5" id="add-social">+ Agregar red</button>
        </div>

        <div class="mb-5">
            <button type="submit" class="btn btn-success">Guardar configuración</button>
        </div>

        </form>
</div>
<script>
    const socialContainer = document.getElementById('social-container');
    const addSocialBtn = document.getElementById('add-social');

    function createSocialRow(name = '', url = '') {

        const row = document.createElement('div');
        row.className = 'row mb-3 align-items-center social-row';

        row.innerHTML = `
            <div class="col-md-3">
                <input type="text" name="social[name][]" class="form-control" placeholder="Nombre (ej: Instagram)" value="${name}">
            </div>
            <div class="col-md-4">
                <input type="url" name="social[url][]" class="form-control" placeholder="https://..." value="${url}">
            </div>
            <div class="col-md-3">
                <input type="text" name="social[class][]" class="form-control" placeholder="CSS class" value="${url}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-social">×</button>
            </div>
        `;

        socialContainer.appendChild(row);
    }

    addSocialBtn.addEventListener('click', () => createSocialRow());

    socialContainer.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-social')) {
            e.target.closest('.social-row').remove();
        }
    });
</script>