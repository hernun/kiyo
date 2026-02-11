<?php
if(submitted('create-root-user')) {
    $vars = $_POST;
    $vars['type'] = nqv::getRootSessionTypeId();
    $created = nqv::createUser($vars);
    if($created) {
        nqvNotifications::add('El usuario ha sido creado con éxito','success');
        header('location:/admin');
        exit;
    }
}
?>
<div class="center-center">
    <div class="m-2">
        <form method="post" id="create-root-user" class="nqv-w500 needs-validation" accept-charset="utf8" enctype="multipart/form-data" novalidate>
            <h4>Crear usuario root</h4>
            <input type="hidden" value="<?php echo get_token('create-root-user')?>" name="form-token" />
            <div class="my-3 form-floating">
                <input type="text" id="name-input" class="form-control" name="name" value="" placeholder="Nombre" required />
                <label for="name-input">Nombre</label>
            </div>
            <div class="my-3 form-floating">
                <input type="text" id="lastname-input" class="form-control" name="lastname" value="" placeholder="Apellido" required />
                <label for="lastname-input">Apellido</label>
            </div>
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
                <button type="submit" class="btn btn-sm btn-primary mb-3">Crear usuario</button>
            </div>
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