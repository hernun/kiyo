
<?php 
$filepath = ROOT_PATH . '/core/assets/images/logo-mail.png';
if(isDev()) $img = url($filepath);
else $img = is_file($filepath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($filepath)):null;
?>
<?php if($img):?>
    <img src="<?php echo $img?>" alt="logo" />
<?php endif?>
<h2>Hola <?php echo $ops['to'][0]['name']?></h2>
<p>Para actualizar tu contraseña en <?php echo APP_NAME?> es necesario que confirmes tu solicitud siguiendo el siguiente enlace:</p>
<a href="<?php echo URL?>/admin/password-reset/<?php echo $ops['token']?>"><?php echo URL?>/admin/password-reset/<?php echo $ops['token']?></a>
<br/>
<p>Si el enlace no funciona directamente desde tu gestor de correos, por favor copiá la dirección y pegala en tu navegador.</p>
<p>---</p>
<p>Este mensaje fue enviado porque se utilizó esta dirección de correo electrónico para registrarse en <?php echo APP_NAME?>. Si no reconocés esta acción o creés que alguien ingresó tu dirección por error, podés ignorar este mensaje. No responder a este correo. Si necesitás ayuda, contactanos en <?php echo URL?> .</p>