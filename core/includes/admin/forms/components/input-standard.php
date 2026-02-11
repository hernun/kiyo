<?php if ($this->isHidden()) : ?>
    <input <?php echo $this->showStandardInputProps() ?>>
<?php else : ?>
    <?php $small = $this->getSmall() ?>
    <div class="input-container">
        <div class="mb-2">
            <input <?php echo $this->showStandardInputProps() ?>>
            <?php if ($small) : ?>
                <div class="input-small"><?php echo $small ?></div>
            <?php endif ?>
        </div>
    </div>
<?php endif ?>
<?php $scripts = $this->getScripts() ?>
<?php if (!empty($scripts)) : ?>
    <script type="text/javascript" charset="utf-8">
        <?php foreach ($scripts as $script) : ?>
            <?php echo $script ?>
        <?php endforeach ?>
    </script>
<?php endif ?>