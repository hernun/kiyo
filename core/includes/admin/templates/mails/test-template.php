
<?php 
$filepath = getAsset('images/logo-mail.png') ? getAsset('images/logo-mail.png'):getAsset('images/logo.png');

if(isDev()) $img = url($filepath);
else $img = is_file($filepath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($filepath)):null;
?>
<?php if($img):?>
    <img src="<?php echo $img?>" alt="logo" />
<?php endif?>
<h2>Hola <?php echo $ops['to'][0]['name']?></h2>
<p>Este es un mensaje de prueba de <strong><?php echo strtoupper(APP_TITLE)?></strong> </p>
<?php if(isset($ops['message'])):?><p><?= $ops['message']?><?php endif?></p>
<?php my_print($ops)?>
<br/>
<p>---</p>
<p>Este mensaje fue enviado a través del sistema de contacto de <strong><?php echo URL?></strong>.</p>