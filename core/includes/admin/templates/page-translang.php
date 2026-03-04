<?php
getOvoEditor();
$tablename = 'pages';
$originalId = (int) nqv::getVars(2);
$lang = nqv::getVars(1);
$table = new nqvDbTable('pages');
$fields = $table->getTableFields();
$formId = 'create-' . $tablename;
$object = new nqvPages(['id'=>$originalId]);
if(!$object->exists()) {
    nqvNotifications::add('La página con id "' . $originalId . '" no existe','error');
    header('location:/admin/pages');
    exit;
}
$originalData = $object->getData();
$properties = $object->getProperties();
$showtitle = empty($properties['showtitle']) ? 'off':$properties['showtitle'];
$mainimageformat = empty($properties['mainimageformat']) ? 'full':$properties['mainimageformat'];

if(submitted($formId)) {
    try {
        $page = nqv::get('pages',['slug'=>$_POST['slug'],'lang'=>$_POST['lang']]);
        if(!empty($page)) {
            throw new Exception('Ya existe una traducción de esta apágina en ' . $_POST['lang']);
        }
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

        $id = nqvDB::save($tablename, $_POST);
        if($id) nqvNotifications::add('El registro ha sido actualizado con éxito','success');

        $originalMainimage = nqv::get('mainimages',['tablename'=>'pages','element_id'=>$originalId]);
        $originalMainimage = nqvMainImages::getByElementId('pages',$originalId);
        if($originalMainimage->exists()) $originalMainimage->duplicate('pages',$id);
    } catch(Exception $e) {
        nqvNotifications::add('Hubo un error que detuvo el proceso: ' . $e->getMessage(),'error');
    }
    header('location:/admin/pages');
    exit;
}
nqvNotifications::flush();
?>

<div class="my-4">
    <?php if(nqv::userCan(['create',$tablename])):?>
        <?php $list = new nqvList($tablename)?>
        <?php $list->hideSubtitle()?>
        <?php $list->setName(nqv::translate('Page translator'))?>
        <?php echo $list->getHeader(true)?>
        <div class="form-container d-flex justify-content-center">
            <form id="<?php echo $formId?>" class="needs-validation" method="post" accept-charset="utf8" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
                <div class="row my-lg-4">
                    <div class="form-group mb-3 mb-lg-0 col-lg d-flex flex-column">
                        <label style="width:200px;text-align:center">Imagen principal</label>
                        <?php echo get_main_image_input_pro($tablename, $originalId, ['format'=>$mainimageformat], null)?>
                    </div>
                </div>
                <div class="row" style="max-width:1400px">
                    <div class="col-12 pages-title-field mb-3"><?php echo $object->getShowtitleInput($showtitle);?></div>
                    <?php $f = new nqvDbField($fields['title'],$tablename);?>
                    <div class="col-12 pages-title-field col-lg-6 col-xl-4"><?php echo $f->setValue($originalData['title']);?></div>
                    <?php $f = new nqvDbField($fields['slug'],$tablename);?>
                    <div class="col-12 pages-slug-field col-lg-6 col-xl-4">
                        <?php echo $f->setValue($originalData['slug']);?>
                        <?php $url = 'https://' . DOMAIN . '/' . $originalData['slug']?>
                        <div class="single-page-url mb-3 mb-lg-0"><a href="<?php echo $url;?>" target="_blank"><?php echo $url;?></a></div>
                    </div>
                    <?php $f = new nqvDbField($fields['lang'],$tablename)?>
                    <?php 
                        $f->setHtmlInputType('select');
                        $f->setCanBeNull(false);
                        $f->setOptions(getEnabledLangs());
                    ?>
                    <div class="col-12 pages-slug-field col-lg-6 col-xl-4"><?php echo $f->setValue($lang);?></div>
                    
                    <?php $f = new nqvDbField($fields['description'],$tablename);?>
                    <div class="col-12 pages-description-field"><?php echo $f->setValue($originalData['description']);?></div>

                    <?php foreach($fields as $field):?>
                        <?php if($field['Field'] === 'title') continue?>
                        <?php if($field['Field'] === 'properties') continue?>
                        <?php if($field['Field'] === 'slug') continue?>
                        <?php if($field['Field'] === 'description') continue?>
                        <?php if($field['Field'] === 'created_at') continue?>
                        <?php if($field['Field'] === 'created_by') continue?>
                        <?php if($field['Field'] === 'modified_at') continue?>
                        <?php if($field['Field'] === 'content') continue?>
                        <?php if($field['Field'] === 'lang') continue?>
                        <?php if($field['Field'] === 'id') continue?>

                        <?php $f = new nqvDbField($field,$tablename)?>
                        <?php $f->setValue($originalData[$field['Field']])?>
                        <?php if(!currentSessionTypeIs('root') && $field['Field'] === 'slug') $f->setHtmlInputType('hidden')?>
                        <?php if($f->isHidden()):  echo $f;?>
                        <?php else:?>
                            <div class="col-lg-6 col-xl-4"><?php echo $f?></div>
                        <?php endif?>
                    <?php endforeach?>
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
    parseSlugOnForm('<?php echo $originalData['slug']?>');
    window.editorJsData = <?= !empty($originalData['content'])
        ? json_encode(json_decode($originalData['content'], true))
        : 'null'; ?>;

    window.ovoFormId = '<?= $formId ?>';
</script>

<script src="<?= getAsset('js/editor/ovo-editor-init.js') ?>"></script>