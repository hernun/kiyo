<?php 
$this->setCssClasses(['form-control']);
$label = $this->getHtmlLabel();
$this->setHtmlInputType('hidden');
$labels = [];
$values = explode(',',(string) $this->getValue());
$list = json_decode(base64_decode($this->getProps('data-json')),true);

foreach($values as $value) {
    foreach($list as $item) {
        if(intval($item['value']) === intval($value)) {
            $close = '<span class="close" contenteditable="false"></span>';
            $labels[] = '<span id="autocomplete-tag-' . $item['value'] . '" class="autocomplete-tag" contenteditable="false">' . $item['label'] . $close . '</span>';
        }
    }
}
?>

<?php $small = $this->getSmall() ?>
    <div class="input-container">
        <?php echo $label?>
        <div class="mb-2">
            <div 
                id="<?php echo $this->getHtmlInputName()?>-labels-input"
                data-crosser="<?php echo $this->getCrosser()?>" 
                class="<?php echo $this->getCssClassesString()?> input-autocomplete"
                contenteditable><?php echo implode('',$labels)?></div>
            <input <?php echo $this->showStandardInputProps() ?>>
            <?php if ($small) : ?>
                <div class="input-small"><?php echo $small ?></div>
            <?php endif ?>
        </div>
    </div>
<?php $scripts = $this->getScripts() ?>
<?php if (!empty($scripts)) : ?>
    <script type="text/javascript" charset="utf-8">
        <?php foreach ($scripts as $script) : ?>
            <?php echo $script ?>
        <?php endforeach ?>
    </script>
<?php endif ?>
<script>
    autocomplete_elmnt = document.getElementById('<?php echo $this->getHtmlInputName()?>-labels-input');
    new nqvAutocomplete(document.getElementById('<?php echo $this->getHtmlInputName()?>-labels-input'), {
        data: JSON.parse(atob($('#<?php echo $this->getHtmlInputName()?>-input').data('json'))),
        threshold: 1,
        isMultiple: 1,
        crosser: autocomplete_elmnt.dataset.crosser,
        ae: autocomplete_elmnt,
        onSelectItem: ({
            label,
            value,
            isMultiple,
            ae
        }) => {
            let siblings = ae.parentNode.childNodes;
            for(let i=0;i<siblings.length;i++) if(siblings[i].type === 'hidden') {
                if(isMultiple) {
                    const vals = siblings[i].value.split(',').filter(function(e){
                        return e.length;
                    });
                    vals.push(value);
                    value = vals.join(',');
                }
                siblings[i].value = value;
            }
        }
    });
</script>