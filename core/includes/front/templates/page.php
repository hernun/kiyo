<?php
if(!empty($page_id)) {
    $page = getPageById($page_id);
} else {
    $page = getPageBySlug(nqv::getVars(0));
}
?>
<?php if(empty($page)):?>
    <div class="center-center">
        404 | <?php echo nqv::translate('The page you are looking for does not exist',$_SESSION['CURRENT_LANGUAGE'])?>.
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
            const edjsParser = edjsHTML();

            // Obtenemos el string JSON desde PHP
            const savedData = JSON.parse(`<?= $page['content'] ?>`);

            const html = edjsParser.parse(savedData); // Devuelve un array de bloques HTML
            document.getElementById('page-content').innerHTML = html.join('');
        </script>