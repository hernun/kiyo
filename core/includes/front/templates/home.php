<?php
$current_homepage = nqv::getConfig('homepage');
$page_id = $current_homepage[$_SESSION['CURRENT_LANGUAGE']]['pages_id'];
?>
<?php if(!empty($page_id)):?>
	<?php include_template('page',['page_id'=>$page_id])?>
<?php else:?>
	<div class="center-center">
		Página de inicio de la vista pública
	</div>
<?php endif?>