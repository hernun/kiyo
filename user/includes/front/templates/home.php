<?php
$current_homepage = nqv::getConfig('homepage');
?>
<?php if(!empty($current_homepage['pages_id'])):?>
	<?php include_template('page',['page_id'=>$current_homepage['pages_id']])?>
<?php else:?>
	<div class="center-center">
		<a href="/admin"><img src="<?= getAsset('images/logo-150.png') ?>" /></a>
	</div>
<?php endif?>