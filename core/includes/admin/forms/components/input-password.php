<?php
$tablename = nqv::getVars(1);
$value = $this->getValue();

$atts = [
    'Field' => 'password_repeat',
    'Default' => '',
    'Null' => 'NO',
    'Type' => 'varchar(119)'
];
$repass = new nqvDbField($atts, $tablename);
$repass->setLabel('Repetir Contraseña');
$repass->setHtmlInputType('password');
$this->setHtmlInputType('password');
?>
<?php if(empty($value)):?>
    <?php if($this->getNewPassType() === 'auto'):?>
        <div class="input-group mb-3">
            <input id="password-input" name="password" class="required form-control form-control-sm" aria-describedby="password-help" autocomplete="off" aria-label="password" required="required" type="password" value="" data-label="password">
            <span class="input-group-text toggle-password-type" data-target="#password-input"><i class="fa-solid fa-eye-slash"></i></span>
            <button class="btn btn-success" type="button" id="password-auto-creation" data-target="#password-input">Crear Contraseña</button>
        </div>
    <?php else:?>
        <?php echo $this->getComponent('input-standard'); ?>
    <?php endif?>
<?php else:?>
    <div class="section-header nqv-swap-control condensed" data-target=".change-password">
        Cambiar contraseña
    </div>
    <?php if($this->getNewPassType() === 'auto'):?>
        <div class="section change-password">
            <div class="input-group mb-3">
                <input id="new_password-input" name="new_password" class="form-control form-control-sm" aria-describedby="password-help" autocomplete="off" aria-label="password" type="password" value="" data-label="password">
                <span class="input-group-text toggle-password-type" data-target="#new_password-input"><i class="fa-solid fa-eye-slash"></i></span>
            </div>
            <div class="input-group mb-3">
                <input id="password_repeat-input" name="password_repeat" class="form-control form-control-sm" aria-describedby="password-help" autocomplete="off" aria-label="password" type="password" value="" data-label="password">
                <span class="input-group-text toggle-password-type" data-target="#password_repeat-input"><i class="fa-solid fa-eye-slash"></i></span>
            </div>  
            <button class="btn btn-success" type="button" id="password-auto-creation" data-target="#new_password-input,#password_repeat-input">Crear Contraseña</button>
        </div>
    <?php else:?>
        <div class="section change-password">
            <?php
                $atts = [
                    'Field' => 'new_password',
                    'Default' => '',
                    'Null' => 'NO',
                    'Type' => 'varchar(119)'
                ];
                $newpass = new nqvDbField($atts, 'users');
                $newpass->setLabel('Nueva Contraseña');
                $newpass->setHtmlInputType('password');
                
                $this->setCanBeNull(true);
                $repass->setCanBeNull(true);
                $newpass->setCanBeNull(true);
                echo $this->getComponent('input-standard');
                echo $repass->getHtmlLabel() . $repass->getComponent('input-standard');
            ?>
        </div>
    <?php endif?>
<?php endif?>

<script type="text/javascript">
    $("form").nqvSwap();
    
    $('#password-auto-creation').on({
        click: function() {
            const target = $(this).data('target');
            var rand = function() {
                return Math.random().toString(36).substr(2); // remove `0.`
            };
            var token = function() {
                return rand() + rand(); // to make it longer
            };
            $(target).val(token());
        }
    });

    $('.toggle-password-type').on({
        click:function(){
            const ref = $(this).parent().find('input');
            if(!ref.length) return null;
            if(ref.attr('type') === 'text') {
                $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                ref.attr('type','password')
            } else {
                $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                ref.attr('type','text')
            }
        }
    })
</script>