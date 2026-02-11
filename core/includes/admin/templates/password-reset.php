<?php
$vars = nqv::getVars();
$token = @$vars[1];
if(submitted('email-confirm')) {
    $email = filter_var($_POST['email'],FILTER_VALIDATE_EMAIL);
    $user = new nqvUsers(['email' => $email]);
    if(!$user->exists()) nqvNotifications::add('El usuario no existe','error');
    else {
        try {
            if($user->sendResetPasswordConfirmMail()) nqvNotifications::add('El correo ha sido enviado correctamente.','success');
            else nqvNotifications::add('Error en el envío.','error');
        } catch (Exception $e) {
            _log($e->getMessage(),'password-reset-error');
            nqvNotifications::add('Error en el envío del correo.', 'error');
        }
    }
    header('location:');
    exit;
} elseif(submitted('reset-password-form')) {
    $id = intval(@$_POST['id']);
    $user = new nqvUsers(['id' => $id]);
    if (!$user->exists()) nqvNotifications::add('El usuario no existe','error');
    else {
        if(empty($_POST['password'])) nqvNotifications::add('La contraseña está vacía','error');
        elseif($_POST['password'] !== @$_POST['re-password']) nqvNotifications::add('Las contraseñas no coinciden','error');
        $user->set('password',$_POST['password']);
        $user->set('token',null);
        if($user->save(null)) nqvNotifications::add('Tu contraseña ha sido actualizada.','success');
        header('location:/admin');
        exit;
    }
    header('location:/admin/password-reset');
    exit;
}
?>
<main id="front-login" class="px-4">
    <div class="center-center">
        <div class="m-2 mw-100">
            <?php if(!empty($token)):?>
                <?php $user = new nqvUsers(['token'=>$token]);?>
                <?php if(!$user->exists()):?>
                    <h2 class="mb-5">El usuario no existe o el token no es válido</h2>
                <?php else:?>
                    <form id="reset-password-form" class="text-start w-100 needs-validation mb-5" method="post" accept-charset="utf-8" novalidate>
                        <?php nqvNotifications::flush(null);?>
                        <input type="hidden" name="form-token" id="form-token-input" value="<?php echo get_token('reset-password-form')?>" />
                        <input type="hidden" name="phone-number" id="form-token-input" value="" />
                        <input type="hidden" name="id" id="id-input" value="<?php echo $user->get('id')?>" />
                        <div id="step-1" class="step">
                            <h2 class="m-4">Ingresá tu nueva contraseña</h2>
                            <div class="form-error-message mx-4"></div>
                            <div class="row my-lg-4 mx-3">
                                <div class="form-group mb-3 mb-lg-0 col-lg flex-column ">
                                    <div class="form-group mb-3 mb-lg-0 col-lg">
                                        <label for="password-input">Tu contraseña</label>
                                        <div class="input-group mb-3">
                                            <input type="password" class="form-control" id="password-input" name="password" placeholder="Ingresá tu contraseña" value="1234qwerQWER!!" data-required="true" required>
                                            <span class="input-group-text" ><i class="bi bi-eye-slash togglePassword" style="margin-top:-5px"></i></span>
                                        </div>
                                    </div>
                                    <div class="form-group my-3 mb-lg-0 col-lg">
                                        <div class="input-group mb-3">
                                            <input type="password" class="form-control" id="re-password-input" name="re-password" placeholder="Repetí tu contraseña" value="1234qwerQWER!!" data-required="true" required>
                                            <span class="input-group-text" ><i class="bi bi-eye-slash togglePassword" style="margin-top:-5px"></i></span>
                                        </div>
                                    </div>
                                    <div id="passwordHelp" class="form-text">La contraseña debe tener al menos 8 caracteres usando minúsculas, mayúsculas, números y signos.</div>
                                </div>
                            </div>
                            <div class="form-group m-4 mt-0">
                                <button id="send" class="btn btn-success m-4 mx-0" disabled>enviar</button>
                            </div>
                        </div>
                    </form>
                <?php endif?>
            <?php else:?>
                <form id="confirm-email-form" class="text-center w-100 needs-validation d-flex flex-column mb-5" method="post" accept-charset="utf-8" novalidate>
                    <?php nqvNotifications::flush('unmarged w-100 nqv-mw1250');?>
                    <h4>Ingresá tu email y te enviaremos un correo de confirmación</h4>
                    <input type="hidden" value="<?php echo get_token('email-confirm')?>" name="form-token" />
                    <div class="my-3 form-floating align-self-center" style="width:500px;"> 
                        <input type="email" id="email-input" class="form-control" name="email" value="" placeholder="Email" required />
                        <label for="email-input">Email</label>
                    </div>
                    <div class="form-group mt-0">
                        <button id="send" class="btn btn-success m-4 mx-0" disabled>enviar</button>
                    </div>
                </form>
            <?php endif?>
        </div>
    </div>
</main>
<script>
    $('#email-input').on({
        input: function() {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!regex.test($(this).val())) {
                $(this).addClass('is-invalid');
                $(this).removeClass('is-valid');
                $('#send').prop('disabled',true);
                return false;
            } else {
                $(this).addClass('is-valid');
                $(this).removeClass('is-invalid');
                $('#send').prop('disabled',false);
                return true;
            }
        }
    });

    function emailValidation(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
</script>
<script type="text/javascript">

    const togglePassword = document.querySelectorAll('.togglePassword');
    togglePassword.forEach(function(icon){
        icon.addEventListener('click', () => {
            const password = $(icon).parents('.input-group').find('input');
            const type = password.attr('type') === 'password' ? 'text' : 'password';
            password.attr('type', type);
            // Toggle the eye and bi-eye icon
            icon.classList.toggle('bi-eye');
        });
    })

    function passwordValidation() {
        const passInput = document.getElementById('password-input');
        const rePassInput = document.getElementById('re-password-input');
        const t1 =  testPassword();
        const t2 = testRePassword();
        if(t1 && t2) $('#send').prop('disabled',false);
        else $('#send').prop('disabled',true);
        return t1 && t2;
    }

    function testPassword() {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        const passInput = document.getElementById('password-input');
        const pass = passInput.value;
        if(!regex.test(pass)) {
            passInput.classList.add('is-invalid');
            passInput.classList.remove('is-valid');
            $('#passwordHelp').addClass('text-danger')
            return false;
        } else {
            passInput.classList.remove('is-invalid');
            passInput.classList.add('is-valid');
            $('#passwordHelp').removeClass('text-danger')
            return true;
        }
    }

    function testRePassword() {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        const rePassInput = document.getElementById('re-password-input');
        const passInput = document.getElementById('password-input');
        const pass = passInput.value;
        const repass = rePassInput.value;
        if(!regex.test(repass) || repass !== pass) {
            rePassInput.classList.add('is-invalid');
            rePassInput.classList.remove('is-valid');
            return false;
        } else {
            rePassInput.classList.remove('is-invalid');
            rePassInput.classList.add('is-valid');
            return true;
        }
    }

    $('#password-input').on({
        input: function() {
            passwordValidation();
        }
    });

    $('#re-password-input').on({
        input: function() {
            passwordValidation();
        }
    });

    $('#send').on({
        click: function(e) {
            form = document.getElementById('reset-password-form');
            if(!form.checkValidity()) {
                form.classList.add('was-validated')
                return;
            }
            if(!passwordValidation()) return;
            return true;
        }
    });
</script>