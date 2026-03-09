<?php
$formId = ' selected-font';
$currentFont = getMainFont();
$fonts = nqv::getConfig('fonts-list');
if(submitted($formId)) {
    $font = $fonts[$_POST['font-key']];
    nqv::setConfig('fonts',json_encode($font, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('location:');
    exit;
}
nqvNotifications::flush();
?>

<div class="container">
    <h3 class="mb-4">Fuente predeterminada</h3>

    <div class="preview my-5" id="googlefont-preview">The quick brown fox jumps over the lazy dog</div>
    <form method="post" id="fontForm">
        <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
        <div class="row my-3">
            <div class="col-md-3">
                <label class="form-label">Seleccionar fuente</label>
                <?php echo $currentFont['family']?>
                <select class="form-select" name="font-key" id="font-input">
                    <option value="<?= $k ?>">Gotham</option>
                    <?php foreach($fonts as $k => $font):?>
                        <?php $selected = $currentFont['family'] === $font['family'] ? 'selected="selected"':'';?>
                        <option value="<?= $k ?>" <?= $selected ?>><?= $font['family'] ?></option>
                    <?php endforeach?>
                </select>
            </div>
        </div>

        <button class="btn btn-primary">Guardar fuente</button>
    </form>
</div>