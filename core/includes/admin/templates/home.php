<?php 
$widgets = nqv::getConfig('home-widgets');
if(isValidJson($widgets)) $widgets = json_decode($widgets,true);
else $widgets = [];
if(user_is_logged()):?>
    <div class="h-100 px-4">
        <div class="container h-100">
            <?php if(empty($widgets)):?>
                <div id="main-rhino" class="center-center">
                    <img src="/core/assets/images/main-image.png" width="350px" />
                </div>
            <?php else: ?>
                <h1 CLASS="mt-5">Administrador de <?php echo APP_NAME?></h1>
                <div class="my-5 d-flex flex-wrap gap-3">
                    <?php foreach($widgets as $w_id):?>
                        <?php $widget = new nqvWidgets(['id'=>$w_id])?>
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><?php echo $widget->get('name')?></h5>
                                <p class="card-text mb-4"><?php echo $widget->get('description')?></p>
                                <a href="<?php echo getAdminUrl()?><?php echo $widget->get('slug')?>" class="card-link">Ir al formulario</a>
                            </div>
                        </div>
                    <?php endforeach?>
                </div>
            <?php endif?>
        </div>
    </div>
<?php else:?>
    <?php getForm('login')?>
<?php endif?>