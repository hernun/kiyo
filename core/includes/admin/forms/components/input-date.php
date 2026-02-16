<?php
$small = $this->getSmall();
try {
    $value = $this->getValue(time());
    $day = date('d',strtotime($value));
    $month = date('m',strtotime($value));
    $year = date('Y',strtotime($value));
    $days = cal_days_in_month(CAL_GREGORIAN,$month,$year);
} catch(Exception $e) {
    my_print($e->getMessage());
    exit;
}

?>
<input <?php echo $this->showStandardInputProps(['type'=>'hidden']) ?>>

<div class="input-container row nqv-date input-group">
    <div class="col mx-0 mb-2 row">
        <div class="input-small">Día</div>
        <select class="form-select required form-control form-control-sm" id="day-<?php echo $this->getHtmlInputId()?>">
            <?php for($i = 1; $i <= $days;$i++):?>
                <?php $selected  = intval($day) === $i ? 'selected="selected"':null;?>
                <option value="<?php echo str_pad($i,2,'0',STR_PAD_LEFT)?>" <?php echo $selected?>><?php echo str_pad($i,2,'0',STR_PAD_LEFT)?></option>
            <?php endfor?>
        </select>
    </div>
    <div class="col mx-0 mb-2 row">
        <div class="input-small">Mes</div>
        <select class="form-select required form-control form-control-sm" id="month-<?php echo $this->getHtmlInputId()?>">
            <?php for($i = 1; $i <= 12;$i++):?>
                <?php $selected  = intval($month) === $i ? 'selected="selected"':null;?>
                <option value="<?php echo str_pad($i,2,'0',STR_PAD_LEFT)?>" <?php echo $selected?>><?php echo ucfirst(nqv::getMonth($i,'ES'))?></option>
            <?php endfor?>
        </select>
    </div>
    <div class="col mx-0 mb-2 row">
        <div class="input-small">Año</div>
        <select class="form-select required form-control form-control-sm" id="year-<?php echo $this->getHtmlInputId()?>">
            <option value="<?php echo date('Y')?>" <?php if($year === date('Y')) echo 'selected'?>><?php echo date('Y')?></option>
            <option value="<?php echo date('Y',strtotime('+1 year'))?>" <?php if($year === date('Y',strtotime('+1 year'))) echo 'selected'?>><?php echo date('Y',strtotime('+1 year'))?></option>
        </select>
    </div>
    <div id="dateInvalidFeedback" class="invalid-feedback">
        La fecha de lanzamiento debe ser futura.
    </div>

    <?php if ($small) : ?>
        <div class="input-small"><?php echo $small ?></div>
    <?php endif ?>
</div>
<?php $scripts = $this->getScripts() ?>
<?php if (!empty($scripts)) : ?>
    <script type="text/javascript" charset="utf-8">
        <?php foreach ($scripts as $script) : ?>
            <?php echo $script ?>
        <?php endforeach ?>
    </script>
<?php endif ?>
<script type="text/javascript">
    $('.nqv-date').find('select').on({
        change: function() {
            const id = $(this).attr('id');
            const mainId = id.split('-').slice(-2).join('-');
            const mainInput = $('#' + mainId);
            const dayInput = $('#day-' + mainId);
            const monthInput = $('#month-' + mainId);
            const yearInput = $('#year-' + mainId);

            const day = dayInput.val();
            const month = monthInput.val();
            const year = yearInput.val();
            const date = year + '-' + month + '-' + day;
        
            mainInput.val(date);
            dayInput.addClass('is-valid');
            monthInput.addClass('is-valid');
            yearInput.addClass('is-valid');
        }
    })
</script>