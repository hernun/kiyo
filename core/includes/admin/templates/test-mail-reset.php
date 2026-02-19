<?php
$formId = 'test-mail-form';
if(submitted($formId)) {

    try {
        $address = empty($_POST['email']) ? ADMIN_EMAIL:$_POST['email'];
        $mail = new nqvMail([
            'to'       => [['address'=> $address,'name' => $_POST['email']]],
            'subject'  => 'Test nqvMail',
            'template' => 'test-template',
            'message'  => 'Esto es un mail de prueba desde el widget.'
        ]);

        $mail->send();

        nqvNotifications::add('Mail enviado correctamente', 'success');

    } catch(Exception $e) {

        nqvNotifications::add($e->getMessage(), 'error');
    }

    header('location:');
    exit;
}
nqvNotifications::flush();
?>

<div class="container">
    <h3>Test nqvMail</h3>
    <p>Si el campo email queda vacío se enviará un correo de prueba a <?php echo ADMIN_EMAIL?></p>

    <form method="post">
        <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
        <input type="email" name="email" placeholder="Email destino" />
        <button type="submit" name="test-mail-form">Enviar mail de prueba</button>
    </form>
</div>
