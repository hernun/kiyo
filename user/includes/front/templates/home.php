<?php
$current_homepage = nqv::getConfig('homepage');
$page_id = @$current_homepage[$_SESSION['CURRENT_LANGUAGE']]['pages_id'];
?>
<?php if(!empty($page_id)):?>
	<?php include_template('page',['page_id'=>$page_id])?>
<?php else:?>
	<div class="center-center">
        404 | <?php echo nqv::translate('The page you are looking for does not exist',$_SESSION['CURRENT_LANGUAGE'])?>.
	</div>
<?php endif?>