<?php
if(submitted('login')) {
    if(!nqv::login()) {
        nqvNotifications::add('Acceso incorrecto','error');
        nqv::reload();
    }
}

if(user_is_logged()) {
    if(nqv::getVars(0) === 'login') header('location:/admin');
    else header('location:/admin/' . implode('/',nqv::getVars()));
    exit;
}
?>
<div class="center-center">
    <?php nqvNotifications::flush()?>
    <div class="m-2">
        <form method="post" id="login" class="nqv-w500 needs-validation" accept-charset="utf8" enctype="multipart/form-data" novalidate>
            <h4>Ingresar</h4>
            <input type="hidden" value="<?php echo get_token('login')?>" name="form-token" />
            <div class="my-3 form-floating">
                <input type="email" id="email-input" class="form-control" name="email" value="" placeholder="Email" required />
                <label for="email-input">Email</label>
            </div>
            <div class="my-3 input-group form-floating password-container">
                <input type="password" id="password-input" class="form-control" name="password" value="" placeholder="Contraseña" required />
                <span class="input-group-text"><i class="fas fa-eye-slash"></i></span>
                <label for="password-input">Contraseña</label>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary mb-3">enviar</button>
            </div>
            <span><a class="text-uppercase nqv-hover-underline" href="/admin/password-reset">Olvidé mi contraseña</a></span>
        </form>
    </div>
</div>
<script type="text/javascript">
    $('.password-container').find('.input-group-text').on({
        click: function() {
            if($('#password-input').attr('type') === 'text') {
                $('#password-input').attr('type','password');
                $(this).find('i').addClass('fa-eye-slash').removeClass('fa-eye');
            } else {
                $('#password-input').attr('type','text');
                $(this).find('i').addClass('fa-eye').removeClass('fa-eye-slash');
            }
        }
    });
</script>