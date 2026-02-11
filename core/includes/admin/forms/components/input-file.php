<?php
if($this->getHtmlInputType() === 'multiple-file') {
    $this->setHtmlInputType('file');
    $this->setMultiple(true);
}
?>
<div class="sublabel">Tamaño máximo: <?php echo ini_get('upload_max_filesize') ?></div>
<div class="input-group mb-2">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo convertToBytes(ini_get('post_max_size'))?>" />
    <input <?php echo $this->showStandardInputProps()?> >
</div>