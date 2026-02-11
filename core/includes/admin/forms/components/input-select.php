<div class="mb-2">
    <select <?php echo $this->showStandardInputProps() ?>>
        <?php if($this->hasPlaceholder()):?>
            <option disabled selected><?php echo $this->getPlaceholder()?></option> 
        <?php endif?>
        <?php echo $this->selectOptions['string'] ?>
    </select>
</div>