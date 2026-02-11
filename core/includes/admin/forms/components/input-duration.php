<?php
$value = format_duration($this->getValue());
$id = $this->getName() . '-input';
$labelInputName = $this->getName() . '_label';
?>
<div class="mb-2">
    <input <?php echo $this->showStandardInputProps(['value' => $this->getValue(), 'type' => "hidden"]) ?>>
    <input step='1' min="00:00:00" max="20:00:00" <?php echo $this->showStandardInputProps(['value' => $value, 'name' => $labelInputName, 'type' => "time"]) ?>>
</div>
<script type="text/javascript" charset="utf8">
    $('#<?php echo $labelInputName ?>-input').on({
        change: function() {
            var value = $(this).val();
            $('#<?php echo $id ?>').val(format_seconds(value));
        }
    });
</script>