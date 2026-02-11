<div class="input-container">
    <?php
    $atts = [
        'Default' => $this->getDefault(),
        'Null' => $this->canBeNull() ? 'YES' : 'NO',
        'Type' => $this->itype
    ];
    $atts['Field'] = $this->getName();
    $labels = new nqvDbField($atts, [], 'nqvCreators');
    $labels->setHtmlInputType('hidden');
    echo $labels->getHtmlInput();
    ?>

    <?php $small = $this->getSmall() ?>
    <div class="mb-2">
        <input <?php echo $this->showStandardInputProps(['name' => $this->getName() . '_labels']) ?>>
        <?php if ($small) : ?>
            <div class="input-small"><?php echo $small ?></div>
        <?php endif ?>
    </div>
</div>