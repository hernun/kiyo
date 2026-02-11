<?php
    $scheme = [
        ['value' => 'int', 'mask' => 'integer', 'label' => 'Integer'],
        ['value' => 'bigint', 'mask' => 'bigint','label' => 'Big Integer'],
        ['value' => 'text', 'mask' => 'text', 'label' => 'Text'],
        ['value' => 'varchar(255)', 'mask' => 'varchar', 'label' => 'Varchar'],
        ['value' => 'tinyint(1) unsigned', 'mask' => 'bool', 'label' => 'Bool'],
        ['value' => 'datetime', 'mask' => 'datetime', 'label' => 'DateTime'],
    ];
?>
<form id="get-form-file" method="post" class="must-validate" novalidate>
    <h4 class="text-uppercase my-4">Crear Tabla</h4>
    <input type="hidden" name="form-token" value="<?php echo get_token('db-parse-fields')?>"/>
    <div class="mb-4">
        <label class="fw-lighter form-label">Nombre de la tabla</label>
        <input type="text" id="fieldname-input" class="form-control form-control-sm" name="tablename" value="<?php echo $tablename?>" required />
    </div>
    <?php foreach($fields as $field):?>
        <h4><?php echo $field['Field']?> <small class="fw-lighter">(<?php echo $field['original_name']?>)</small></h4>
        <div class="mb-2">
            <label class="fw-lighter form-label">Nombre del campo</label>
            <input type="text" id="fieldname-input" class="form-control form-control-sm" name="fieldnames[]" value="<?php echo $field['Field']?>" required />
        </div>
        <div class="mb-3">
            <label class="fw-lighter form-label">Tipo de dato</label>
            <select name="fieldtypes[]" class="form-select form-select-sm fieldtype-input">
                <?php foreach($scheme as $model):?>
                    <?php $selected = $model['mask'] === $field['Type'] ? 'selected':null?>
                    <option value="<?php echo $model['value']?>" <?php echo $selected?>><?php echo $model['label']?></option>
                <?php endforeach?>
            </select>
            <div class="row ms-0 mt-2">
                <div class="form-check w-auto">
                    <?php $checked = $field['notnull'] ? 'checked':''?>
                    <input class="form-check-input not-null" type="checkbox" value="<?php echo $field['Field']?>" name="not-null[]" <?php echo $checked?>>
                    <label class="form-check-label">Not Null</label>
                </div>
                <?php $class = $field['unsigned'] ? '':' d-none'?>
                <div class="form-check w-auto<?php echo $class?>">
                <?php $checked = $field['unsigned'] ? 'checked':''?>
                    <input class="form-check-input unsigned" type="checkbox" value="<?php echo $field['Field']?>" name="unsigned[]" <?php echo $checked?>>
                    <label class="form-check-label">Unsigned</label>
                </div>
                <div class="form-check w-auto">
                <?php $checked = $field['unique'] ? 'checked':''?>
                    <input class="form-check-input unique" type="checkbox" value="<?php echo $field['Field']?>" name="unique[]" <?php echo $checked?>>
                    <label class="form-check-label">Unique</label>
                </div>
            </div>
        </div>
    <?php endforeach?>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
<script type="text/javascript">
    $(function(){
        $('.fieldtype-input').each(function(){
            $(this).on({
                change: function(){
                    const val = $(this).val();
                    if(val === 'int' || val === 'bigint') $(this).parent().find('.unsigned').parent().removeClass('d-none');
                    else $(this).parent().find('.unsigned').parent().addClass('d-none');
                }
            });
        });
    });
</script>