<?php $ch = $this->getValue() ? 'checked':null ?>
<label id="<?php echo $this->getName()?>-label" class="form-check-label"><?php echo $this->getLabel()?> <input class="form-check-input" id="<?php echo $this->getName()?>-input" name="<?php echo $this->getName()?>" type="checkbox" value="" <?php echo $ch?>></label>
