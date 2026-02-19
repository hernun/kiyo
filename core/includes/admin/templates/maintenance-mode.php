<?php
$maintenanceMode = nqv::getConfig('maintenance-mode');
$maintenanceTemplate = nqv::getConfig('maintenance-template');
if(is_null($maintenanceMode)) {
    nqv::createConfig('Modo Mantenimiento','maintenance-mode',0);
    nqvNotifications::flush(null);
}
if(is_null($maintenanceTemplate)) {
    nqv::createConfig('Plantilla del Modo Mantenimiento','maintenance-template','maintenance');
    nqvNotifications::flush(null);
}
$maintenanceMode = intval(nqv::getConfig('maintenance-mode'));
$maintenanceTemplate = nqv::getConfig('maintenance-template');
$formId = 'set-maintenance-mode';
$templates = parseDirectory(FRONT_TEMPLATES_PATH,0,function($filepath){
    $info = pathinfo($filepath);
    $filename = $info['filename'];
    if(!in_array($filename,['footer','header'])) return $filename;
});

if(submitted($formId)) {
    nqv::cleanView(true);
    $mm = !empty($_POST['mm']) && $_POST['mm'] === 'on';
    if(intval($maintenanceMode) !== intval($mm)) nqv::setConfig('maintenance-mode',$mm);
    if($maintenanceTemplate !== $_POST['maintenance-template']) nqv::setConfig('maintenance-template',$_POST['maintenance-template']);
    header('location:');
    exit;
}

if(empty($maintenanceTemplate)) $maintenanceTemplate = 'maintenance';
?>
<div class="container">
    <h1 class="my-5">Modo Mantenimiento</h1>
    <form id="<?php echo $formId?>" method="post" class="col-lg-6 col-12" accept-charset="utf-8">
        <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
        <div class="form-check form-switch mt-5">
            <?php $checked = $maintenanceMode ? 'checked':null;?>
            <input class="form-check-input" type="checkbox" name="mm" role="switch" id="mm-input" <?php echo $checked?>>
            <label class="form-check-label" for="mm-input">Activar el modo mantenimiento</label>
        </div>

        <div class="mt-5">
            <label for="maintenance-template-input">Plantillas</label>
            <div id="templateHelp" class="form-text mb-3">Seleccioná la plantilla que se mostrará cuando el modo mantenimiento esté activado.</div>
            <select id="maintenance-template-input" name="maintenance-template" class="form-select" aria-label="Seleccionar plantilla de modo mantenimiento">
                <?php foreach($templates as $template):?>
                    <?php $selected = ($maintenanceTemplate === $template) ? 'selected':''?>
                    <option value="<?php echo $template?>" <?php echo $selected?>><?php echo $template?></option>
                <?php endforeach?>
            </select>
        </div>

        <div class="my-4 mx-0">
            <button type="submit" class="btn btn-success btn-sm">Guardar</button>
        </div>
    </form>
</div>