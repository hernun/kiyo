<?php
$formId = 'email-form';

if(submitted($formId)) {
    $options = [
        'to' => [['address' => ADMIN_EMAIL, 'name' => APP_NAME]],
        'reply-to' => [['address' => $_POST['email'], 'name' => $_POST['name']]],
        'subject' => $_POST['subject'] ?? 'Mail de ' . APP_NAME,
        'template' => 'contact-form', // tu template que genera el body
        'isHtml' => false,
        'data' => $_POST // opcional, para que el template acceda a los campos
    ];

    $mail = new nqvMail($options);
    if($mail->send()) nqvNotifications::add('El correo fue enviado con éxito','success');
    else  nqvNotifications::add('El correo no fue enviado','error');

    header('location:');
    exit;
}
nqvNotifications::flush();
?>

<form id="<?= $formId ?>" method="POST" class="needs-validation" novalidate>
    <input type ="hidden" name="form-token" value="<?= get_token($formId) ?>" />
    <div class="mb-3">
        <label for="name" class="form-label"><?= nqv::translate('Name') ?></label>
        <input type="text" class="form-control" id="name" name="name" placeholder="<?= nqv::translate('Your name') ?>" required>
        <div class="invalid-feedback">
        <?= nqv::translate('Please enter your name.') ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label"><?= nqv::translate('Email') ?></label>
        <input type="email" class="form-control" id="email" name="email" placeholder="<?= nqv::translate('name@example.com') ?>" required>
        <div class="invalid-feedback">
        <?= nqv::translate('Please enter a valid email.') ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="subject" class="form-label"><?= nqv::translate('Subject') ?></label>
        <input type="text" class="form-control" id="subject" name="subject" placeholder="<?= nqv::translate('Message subject') ?>" required>
        <div class="invalid-feedback">
        <?= nqv::translate('Please enter a subject.') ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="message" class="form-label"><?= nqv::translate('Message') ?></label>
        <textarea class="form-control" id="message" name="message" rows="5" placeholder="<?= nqv::translate('Write your message here...') ?>" required></textarea>
        <div class="invalid-feedback">
        <?= nqv::translate('Please enter a message.') ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><?= nqv::translate('Send') ?></button>
</form>
