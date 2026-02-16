<?php
if(!empty($page_id)) {
    $page = getPageById($page_id);
} else {
    $page = getPageBySlug(nqv::getVars(0));
}
$properties = isValidJson($page['properties']) ? json_decode($page['properties'],true):[];
$image = nqvMainImages::getByElementId('pages',$page['id']);
$mainimageformat = $properties['mainimageformat'] ?? null;


        global $test;
        $test = 'momo';
?>

<?php if(empty($page)):?>
    <div class="center-center">
        404 | La página que buscás no existe.
    </div>
<?php else:?>
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