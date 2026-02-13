<?php $this->hideLabel()?>
<div class="form-check form-switch p-0 mt-5 d-flex align-items-center gap-2 align-items-center">
    <?php $checked = $this->getValue() === 'on' ? 'checked':null;?>
    <label class="form-check-label ms-0" for="<?php echo  $this->getName() ?>-input"><?php echo $this->getLabel()?></label>
    <input class="form-check-input m-0 mb-1" type="checkbox" name="<?php echo  $this->getName() ?>" role="switch" id="<?php echo  $this->getName() ?>-input" <?php echo $checked?>>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('<?php echo  $this->getName() ?>-input');
    const form = input.closest('form');

    form.addEventListener('submit', function (e) {
        if(!input.checked) {
            input.type = 'hidden';
            input.value= 'off';
        }
    });
});
</script>