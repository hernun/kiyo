<?php
if(!empty($page_id)) {
    $page = getPageById($page_id);
} else {
    $page = getPageBySlug(nqv::getVars(0));
}
$content = json_decode($page['content'], true);

// Decodificamos el JSON de EditorJS
$content = json_decode($page['content'], true);

// Recorremos los bloques
foreach ($content['blocks'] as &$block) {
    if ($block['type'] === 'shortcode' && isset($block['data']['tag'])) {
        $templateName = $block['data']['tag'];
        $file = TEMPLATES_PATH . "{$templateName}.php";

        if (file_exists($file)) {
            // Capturamos la salida del include
            ob_start();
            include $file;
            $block['data']['html'] = ob_get_clean();
        } else {
            $block['data']['html'] = "<!-- Template '{$templateName}' no encontrado -->";
        }
    }
}

// Volvemos a codificar a JSON para usarlo en JS o en el template
$page['content'] = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<?php if(empty($page)):?>
    <div class="center-center">
        <p class="fs-5">404 | <?php echo nqv::translate('The page you are looking for does not exist.',$_SESSION['CURRENT_LANGUAGE'])?>.</p>
    </div>
<?php else:?>
    <?php
        $properties = isValidJson($page['properties']) ? json_decode($page['properties'],true):[];
        $image = nqvMainImages::getByElementId('pages',$page['id']);
        $mainimageformat = $properties['mainimageformat'] ?? null;
    ?>
    <div class="page">
        <?php if($image):?>
        <div class="main-image format-<?php echo $mainimageformat?>"><img src="<?php echo $image->getSrc($mainimageformat);?>" /></div>
        <?php endif?>
        <?php if(@$properties['showtitle'] === 'on'):?><h1><?php echo $page['title']?></h1><?php endif?>
        <section id="page-content"></section>
    </div>
<?php endif?>
<script src="https://cdn.jsdelivr.net/npm/editorjs-html@3.0.3/build/edjsHTML.browser.js"></script>
<script>
    window.OVO_PAGE_CONTENT = <?= json_encode($page['content'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="<?= getAsset('js/editor/page-render.js') ?>"></script>
