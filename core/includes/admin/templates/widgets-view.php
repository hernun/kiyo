
<?php if(user_is_logged() && userIs('root')):?>
    <?php
    nqvNotifications::flush();
    $widgets = nqv::get('widgets');
    ?>
    <div class="px-4">
        <div class="container">
            <h1 class="mt-5"><?php echo APP_NAME?> Widgets</h1>
            <?php $template = nqv::getVars(1);?>
            <?php  if(!empty($template)):?>
                <div class="mt-5">
                    <?php getBreadcrumb();?>
                </div>
                <?php include_template( $template, [], true, ADMIN_TEMPLATES_PATH . 'setup/');?>
            <?php else:?>
                <div class="my-5 d-flex flex-wrap gap-3">
                    <?php foreach($widgets as $widget):?>
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><?php echo $widget['name']?></h5>
                                <p class="card-text mb-4"><?php echo $widget['description']?></p>
                                <a href="<?php echo getAdminUrl()?><?php echo $widget['slug']?>" class="card-link">Ir o ejecutar</a>
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