<footer class="ovo">
    <div class="ovo-social">
        <?php foreach(nqv::getConfig('socials') as $social):?>
            <div class="<?= $social['class'] ?>"><a href="<?= $social['url'] ?>" target="_blank"><img src="<?php echo getAsset('images/ovo-social/' . $social['class'] . '.png')?>" /></a></div>
        <?php endforeach ?>
        <?php foreach(nqv::getConfig('footer-content') as $item):?>
            <div class="<?= $item['class'] ?>"><a href="<?= $item['url'] ?>" target="_blank"><img src="<?php echo getAsset('images/ovo-social/' . $item['class'] . '.png')?>" /></a></div>
        <?php endforeach ?>
    </div>
</footer>