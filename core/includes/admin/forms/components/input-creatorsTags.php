<div class="input-container">
    <?php

    $creatorsTags = nqvS::get('creators', [], ['name as label', 'id as value']);
    
    $labels = [];
    $arrayValue = array_filter(explode(',',(string) $this->getValue()));
    
    foreach ($arrayValue as $k => $val) {
        $listValue = array_filter($creatorsTags,function($v) use ($val) {
            return intval($v['value']) === intval($val);
        });
        if(empty($listValue)) {
            unset($arrayValue[$k]);
            continue;
        }
        foreach ($listValue as $c) {
            if (intval($c['value']) === intval($val)) $labels[] = $c['label'];
        }
    }

    $labelsValue = !empty($labels) ? implode(',',$arrayValue) : '';
    $value = !empty($labels) ? implode(',', $labels) : '';
    $atts = [
        'Default' => $this->getDefault(),
        'Null' => $this->canBeNull() ? 'YES' : 'NO',
        'Type' => $this->itype
    ];
    $atts['Field'] = $this->getName();
    $labelsInput = new nqvDbField($atts, [], 'nqvCreators');
    $labelsInput->setHtmlData('crosserForm', 'creators');
    $labelsInput->setHtmlInputType('hidden');
    $labelsInput->setValue($labelsValue);
    echo $labelsInput->getHtmlInput();
    $typeCss = null;
    if ($this->getProp('isDirector')) $typeCss = 'director';
    elseif ($this->getProp('isProductor')) $typeCss = 'productor';
    elseif ($this->getProp('isActor')) $typeCss = 'actor';
    $this->setCssClasses(['creatorsTagInput', $typeCss]);
    $this->setValue($value);
    ?>

    <?php $small = $this->getSmall() ?>
    <div class="mb-2">
        <input <?php echo $this->showStandardInputProps(['name' => $this->getName() . '_labels']) ?>>
        <?php if ($small) : ?>
            <div class="input-small"><?php echo $small ?></div>
        <?php endif ?>
    </div>
</div>

<script type="text/javascript" charset="utf8">   
    console.log('test');
    if (nqv.creatorsList === undefined) nqv.creatorsList = <?php echo json_encode($creatorsTags) ?>;
    console.log('test2');
    var input = $('#<?php echo $this->getName() ?>_labels-input');
    console.log('test3');
    input.creatorsManager().init();
    console.log('test4');
</script>