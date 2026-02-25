<?php $lang = $_SESSION['CURRENT_LANGUAGE']?>
<ul class="lang<?= $lang ?>">
    <li class="close mobile tablet"><div class="close-button">X</div></li>
    <?php foreach(getHeaderMenuItems($lang) as $slug):?>
        <li><?= getPageLink($slug) ?></li>
    <?php endforeach?>
    <li class="desktop"><?php echo getLaguageSelector()?></li>
</ul>