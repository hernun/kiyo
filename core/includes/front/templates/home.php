<?php
$current_homepage = nqv::getConfig('homepage');
?>
<?php if(!empty($current_homepage['pages_id'])):?>
	<?php include_template('page',['page_id'=>$current_homepage['pages_id']])?>
<?php else:?>
	<div class="center-center">
		Página de inicio de la vista pública
	</div>
<?php endif?>