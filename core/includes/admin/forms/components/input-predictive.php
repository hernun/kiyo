<?php $name = empty($force['name']) ? $this->getName() : $force['name'];?>
<div class="input-container predictive-input-container <?php echo $this->getName()?>">
    <div class="mb-2">
        <?php $small = $this->getSmall() ?>
        <input <?php echo $this->showStandardInputProps(['type'=>'text','canBeNull'=>true]) ?> placeholder="Escribí algo..." autocomplete="off" />
        <div class="predictive-suggestion"></div>
        <div class="predictive-inform" data-elements="<?php echo $this->getValue() ?>">
            <?php $arr = []?>
            <?php foreach((array) explode(',',(string) $this->getValue()) as $elmentId):?>
                <?php foreach($this->getOptions() as $opt):?>
                    <?php if((int) $opt['value'] === (int) $elmentId):?>
                        <?php $arr[] = '<span id="'. $name . '-input-' . $elmentId . '" data-element-id="' . $elmentId . '">' . $opt['label'] . '</span>';?>
                    <?php endif?> 
                <?php endforeach?> 
            <?php endforeach?>
            <?php echo implode('',$arr)?>
        </div>
        <?php $required = ($this->canBeNull()) ? '':'required="required" '?>
        <input class="predictive-values" type="hidden" name="<?php echo $name ?>_ids" id="<?php echo $name ?>_ids-input" value="<?php echo $this->getValue() ?>" <?php echo $required?> />
        <?php if ($small) : ?>
            <div class="input-small"><?php echo $small ?></div>
        <?php endif ?>
    </div>
</div>
<script>
    window.<?php echo camelize($name).'InputWords'?> =  JSON.parse('<?php echo json_encode($this->getOptions())?>');
    window.<?php echo camelize($name).'InputWords'?>.sort(function(a, b) {
        return a.label > b.label;
    });
</script>
<?php if(!$this->canBeNull()) :?>
    <script>
        let input = $('#<?php echo $name?>-input');
        input.parents('form').on({
            submit: function(e){
                let value = $('#<?php echo $name ?>_ids-input').val();
                if(!value) {
                    input.addClass('is-invalid');
                    e.preventDefault();
                    return false;
                }
            }
        })
    </script>
<?php endif ?>
<?php if(isset($this->attributes['onselect'])) :?>
    <?php $funcname = $this->attributes['onselect']['funcname']?>
    <script>
        <?php $onSelectFuncname = camelize($name.'InputOnSelect')?>
        <?php $onSelectAtts= camelize($name.'InputOnSelectAtts')?>
        window["<?php echo $onSelectFuncname?>"] = '<?php echo $funcname?>';
        window["<?php echo $onSelectAtts?>"] = '<?php echo json_encode($this->attributes['onselect']['args'])?>';
    </script>
<?php endif ?>
<?php if(isset($this->attributes['onaddition'])) :?>
    <?php $funcname = $this->attributes['onaddition']['funcname']?>
    <script>
        <?php $onAdditionFuncname = camelize($name.'InputOnAddition')?>
        <?php $onAdditiontAtts= camelize($name.'InputOnAdditionAtts')?>
        window["<?php echo $onAdditionFuncname?>"] = '<?php echo $funcname?>';
        window["<?php echo $onAdditiontAtts?>"] = '<?php echo json_encode((array) @$this->attributes['onaddition']['args'])?>';
    </script>
<?php endif ?>