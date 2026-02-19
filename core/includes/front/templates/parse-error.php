<div class="center-center">
    <div>
        <p class="fs-5">404 | <?php echo nqv::translate('the view you request does not exist');?></p>
        <?php if(DEBUG):?>
            <?php my_print_more(nqv::getVars())?>
        <?php endif ?>
    </div>
</div>