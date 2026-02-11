<?php
if(!isDev()) {
    header('location:/');
    exit;
}

?>

<div class="container p-4">
    <?php
        echo nqvMdParser::getAccordionFromDirPath(ROOT_PATH . 'docs');
    ?>
</div>
