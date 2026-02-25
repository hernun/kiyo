<?php $lang = $_SESSION['CURRENT_LANGUAGE']?>
<ul class="lang<?= $lang ?>">
    <?php foreach(getHeaderMenuItems($lang) as $slug):?>
        <li><?= getPageLink($slug) ?></li>
    <?php endforeach?>
    <li><?php echo getLaguageSelector()?></li>
</ul>