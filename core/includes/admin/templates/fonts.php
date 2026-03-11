<?php
$formId = ' selected-font';
$currentFont = getMainFont();
$fonts = nqv::getConfig('fonts-list');
if(submitted($formId)) {
    $font = $fonts[$_POST['font-key']];
    $font['heading'] = [
        'line-height' => $_POST['heading-line-height'],
        'letter-spacing' => $_POST['heading-letter-spacing']
    ];

    $font['body'] = [
        'line-height' => $_POST['body-line-height'],
        'letter-spacing' => $_POST['body-letter-spacing']
    ];
    nqv::setConfig('fonts',json_encode($font, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('location:');
    exit;
}
nqvNotifications::flush();
?>

<div class="container">
    <h3 class="mb-4">Fuente predeterminada</h3>

    <div class="preview my-5" id="googlefont-preview">
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eleifend ligula id orci interdum, eu facilisis risus tempor. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Nulla suscipit ligula et pretium scelerisque. Pellentesque cursus ex ut pulvinar venenatis. Maecenas finibus odio ac pulvinar maximus. Nunc pulvinar eleifend semper. Donec mattis malesuada sapien non maximus. Aliquam tempus risus quis ornare vehicula. Nunc in ornare leo, id maximus urna.</p>
        <p>Curabitur hendrerit consequat erat sed commodo. Vestibulum vitae sapien metus. Quisque iaculis nunc id urna feugiat, ac elementum ante scelerisque. Curabitur egestas ante a pretium pulvinar. Cras lacinia rhoncus augue, quis tempus eros ultrices vitae. Integer sed hendrerit justo. Nunc pharetra ultrices mauris ac suscipit. Proin et sagittis urna, vitae sollicitudin nunc.</p>
    </div>

    <form method="post" id="fontForm">
        <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
        <div class="row my-3 w-100">
                <label class="form-label my-3">Seleccionar fuente</label>
                <select class="form-select mx-2" name="font-key" id="font-input">
                    <option value="<?= $k ?>">Gotham</option>
                    <?php foreach($fonts as $k => $font):?>
                        <?php $selected = $currentFont['family'] === $font['family'] ? 'selected="selected"':'';?>
                        <option value="<?= $k ?>" <?= $selected ?>><?= $font['family'] ?></option>
                    <?php endforeach?>
                </select>
                <div class="row w-100 my-4">
                    <h3>Títulos</h3>
                    <div class="col-md-3">
                        <label class="form-label">Interlineado títulos</label>
                        <input 
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="heading-line-height"
                            value="<?= $currentFont['heading']['line-height'] ?? '1.2' ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Interletrado títulos</label>
                        <input 
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="heading-letter-spacing"
                            value="<?= $currentFont['heading']['letter-spacing'] ?? '0' ?>">
                    </div>
                </div>

                <div class="row w-100 my-4">
                    <h3>Párrafos</h3>
                    <div class="col-md-3">
                        <label class="form-label">Interlineado párrafos</label>
                        <input 
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="body-line-height"
                            value="<?= $currentFont['body']['line-height'] ?? '1.6' ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Interletrado párrafos</label>
                        <input 
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="body-letter-spacing"
                            value="<?= $currentFont['body']['letter-spacing'] ?? '0' ?>">
                    </div>
                </div>
        </div>

        <button class="btn btn-primary">Guardar fuente</button>
    </form>
</div>