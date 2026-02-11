
<?php if(user_is_logged()):?>
    <div class="px-4">
        <div class="container">
            <h1 class="mt-5"><?php echo APP_NAME?> Setup</h1>
            <?php $template = nqv::getVars(1);?>
            <?php  if(!empty($template)):?>
                <div class="mt-5">
                    <?php getBreadcrumb();?>
                </div>
                <?php include_template( $template, [], true, ADMIN_TEMPLATES_PATH . 'setup/');?>
            <?php else:?>
                <div class="my-5 d-flex flex-wrap gap-3">
                    <?php if(nqv::userCan(['crud','setup']) && nqv::getConfig('version') === '1.0'): ?>
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Migrar la base de datos</h5>
                                <p class="card-text mb-4">Migrar desde la versión 1.0 a la versión 1.1</p>
                                <a href="<?php echo getAdminUrl()?>/setup/sql-migration" class="card-link">Actualizar</a>
                            </div>
                        </div>
                    <?php endif?>

                    <?php if(nqv::userCan(['crud','maintenance-mode'])): ?>
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Modo mantenimiento</h5>
                                <p class="card-text mb-4">Definir la publicación del front o su ocultamiento para usuarios no identificados durante las tareas de edición o mantenimiento</p>
                                <a href="<?php echo getAdminUrl()?>/maintenance-mode" class="card-link">Ir al formulario</a>
                            </div>
                        </div>
                    <?php endif?>

                    <?php if(nqv::userCan(['crud','categories-menu'])): ?>
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Administrar menú de categorías</h5>
                                <p class="card-text mb-4">Agregar o quitar acategorías al menú principal.</p>
                                <a href="<?php echo getAdminUrl()?>/categories-menu" class="card-link">Ir al formulario</a>
                            </div>
                        </div>
                    <?php endif?>

                        <?php if(nqv::userCan(['crud','categories-menu'])): ?>
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Ordenar el listado de categorías</h5>
                                <p class="card-text mb-4">Modificar el orden en el que se muestran las categorías en el listado principal (todas las categorías).</p>
                                <a href="<?php echo getAdminUrl()?>/categories-order" class="card-link">Ir al formulario</a>
                            </div>
                        </div>
                    <?php endif?>

                    <?php if(nqv::userCan(['crud','images-resize'])): ?>
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Crear miniaturas</h5>
                                <p class="card-text mb-4">Crear miniaturas para todas las imágenes principales.</p>
                                <a href="<?php echo getAdminUrl()?>/thumbnails-create" class="card-link">Ir al formulario</a>
                            </div>
                        </div>
                    <?php endif?>

                    <?php if(nqv::userCan(['crud','images-resize'])): ?>
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Crear tablas SQL a partir de archivos CVS</h5>
                                <p class="card-text mb-4">Formulario para importar CVS a la DB.</p>
                                <a href="<?php echo getAdminUrl()?>/sql-from-cvs" class="card-link">Ir al formulario</a>
                            </div>
                        </div>
                    <?php endif?>

                    <?php if(userIs('root')): ?>
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Eliminar todas las tablas</h5>
                                <p class="card-text mb-4">Esta acción eliminará todas las tablas d ela base de datos, lo cual equivale a resetear el sistema a su estado de origen.</p>
                                <a href="<?php echo getAdminUrl()?>/dropdatabase" class="card-link">Ir al formulario</a>
                            </div>
                        </div>
                    <?php endif?>
                </div>
            <?php endif?>
        </div>
    </div>
<?php else:?>
    <?php getForm('login')?>
<?php endif?>