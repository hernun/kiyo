
<?php 
$filepath = getAsset('images/logo-mail.png') ? getAsset('images/logo-mail.png'):getAsset('images/logo.png');

if(isDev()) $img = url($filepath);
else $img = is_file($filepath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($filepath)):null;
?>
<?php if($img):?>
    <img src="<?php echo $img?>" alt="logo" />
<?php endif?>
<h2>Hola <?php echo $_POST['name']?></h2>
<p><?= $_POST['message'] ?>
<br/>
<p>---</p>
<p>Este mensaje fue enviado a través del formulario de contacto de <strong><?php echo URL?></strong>. Si no sos el administrador de  <strong><?php echo APP_TITLE?></strong> por favor comunicate con nosotros a través de otros medios. No respondas a este correo. Muchas gracias, y disculpas por el inconveniente.</p>