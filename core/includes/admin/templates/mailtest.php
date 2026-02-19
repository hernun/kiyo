<?php

$mail = new nqvMail([
    'to' => [
        ['address' => 'test@example.com', 'name' => 'Tester']
    ],
    'template' => 'test-template',
    'subject' => 'Mail básico'
]);
?>
<div class="container">
    <p class="fs-5"><?= $mail->test(); ?></p>
</div>
