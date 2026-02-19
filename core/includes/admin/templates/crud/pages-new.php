<?php
getOvoEditor();
$tablename = nqv::getVars(1);
$table = new nqvDbTable($tablename);
$fields = $table->getTableFields();
$formId = 'create-' . $tablename;

$properties = nqv::getConfig('pagesdefaultproperties');
$showtitle = empty($properties['showtitle']) ? 'off':'on';
$object = new nqvPages();

if(submitted($formId)) {
    try {
        nqv::parseTags($tablename);

        // Suponiendo que recibís el JSON desde el input hidden
        $rawContent = $_POST['content'] ?? '{}';
        $_POST['lang'] = $_POST['lang'] ? strtoupper($_POST['lang']):$_SESSION['CURRENT_LANGUAGE'];

        // 1. Convertir a objeto PHP
        $data = json_decode($rawContent, true);

        // 2. Recorrer los bloques y limpiar los textos
        if (!empty($data['blocks'])) {
            foreach ($data['blocks'] as &$block) {
                if (!empty($block['data']['text'])) {
                    // Reemplaza saltos de línea y tabs por espacios
                    $block['data']['text'] = str_replace(["\r", "\n", "\t"], ' ', $block['data']['text']);
                    // Opcional: quitar múltiples espacios consecutivos
                    $block['data']['text'] = preg_replace('/\s+/', ' ', $block['data']['text']);
                    // Trim
                    $block['data']['text'] = trim($block['data']['text']);
                }
            }
        }

        // 3. Volver a JSON limpio para guardar
        $cleanJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        foreach($properties as $k => $v) {
            if(isset($_POST[$k])) {
                $properties[$k] = $_POST[$k];
                unset($_POST[$k]);
            }
            else $properties[$k] = null;
        }
        
        if(isset($_POST['mainimageformat'])) $properties['mainimageformat'] = $_POST['mainimageformat'];

        $_POST['properties'] = json_encode($properties, JSON_UNESCAPED_UNICODE);

        // Guardar $cleanJson en la DB
        $_POST['content'] = $cleanJson;

        if(nqvDB::save($tablename, $_POST)) nqvNotifications::add('El registro ha sido actualizado con éxito','success');
    } catch(Exception $e) {
        nqvNotifications::add('Hubo un error que detuvo el proceso: ' . $e->getMessage(),'error');
    }
    header('location:' . getAdminUrl() . $tablename);
    exit;
}
?>
<div class="my-4">
    <?php if(nqv::userCan(['create',$tablename])):?>
        <?php $list = new nqvList($tablename)?>
        <?php echo $list->getHeader()?>
        <div class="form-container d-flex justify-content-center">
            <form id="<?php echo $formId?>" class="needs-validation" method="post" accept-charset="utf8" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
                <div class="row my-lg-4">
                    <div class="form-group mb-3 mb-lg-0 col-lg">
                        <label style="width:200px;text-align:center">Imagen principal</label>
                        <?php echo get_main_image_input_pro($tablename, null, null, null)?>
                    </div>
                </div>
                <div class="row" style="max-width:1400px">
                    <div class="col-12 pages-title-field mb-3"><?php echo $object->getShowtitleInput($showtitle);?></div>
                    <?php $f = new nqvDbField($fields['title'],$tablename);?>
                    <div class="col-12 pages-title-field col-lg-6 col-xl-4"><?php echo $f;?></div>
                    <?php $f = new nqvDbField($fields['slug'],$tablename);?>
                    <div class="col-12 pages-slug-field col-lg-6 col-xl-4"><?php echo $f;?></div>
                    <?php $f = new nqvDbField($fields['lang'],$tablename)?>
                    <?php 
                        $f->setHtmlInputType('select');
                        $f->setCanBeNull(false);
                        $f->setOptions(getEnabledLangs());
                    ?>
                    <div class="col-12 pages-slug-field col-lg-6 col-xl-4"><?php echo $f->setValue($_SESSION['CURRENT_LANGUAGE']);?></div>

                    <?php $f = new nqvDbField($fields['description'],$tablename);?>
                    <div class="col-12 pages-description-field"><?php echo $f;?></div>
                    <div class="col-12 pages-content-field">
                        <div class="editor-wrapper">
                            <div id="editorjs-content"></div>
                        </div>
                        <input type="hidden" id="editorjs-input" name="content">
                    </div>
                </div>
                <div class="my-3">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    <?php else:?>
        <div class="center-center">
            <p class="fs-5"><?php echo nqv::translate('You do not have permission to access this section')?></p>
        </div>
    <?php endif?>
</div>
<script>
    parseSlugOnForm();
    window.editorJsData = null; // no hay contenido aún
    window.ovoFormId = '<?= $formId ?>';
</script>
<script src="/core/assets/js/editor/ovo-editor-init.js"></script>