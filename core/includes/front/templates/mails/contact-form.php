
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
<p>Este mensaje fue enviado porque se utilizó esta dirección de correo electrónico para registrarse en <?php echo APP_NAME?>. Si no reconocés esta acción o creés que alguien ingresó tu dirección por error, podés ignorar este mensaje. No responder a este correo. Si necesitás ayuda, contactanos en <?php echo URL?> .</p>