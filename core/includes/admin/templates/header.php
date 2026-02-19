<header class="ovo">
    <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/" target="_blank"><?php echo APP_NAME?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/admin/home">Home</a>
                    </li>
                    <?php if(currentSessionTypeIs('root')):?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="<?php echo nqv::url(ROOT_PATH)?>admin/widgets-view">Widgets</a>
                        </li>
                    <?php endif?>
                    <?php if(nqv::userCan(['crud','config'])):?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="<?php echo nqv::url(ROOT_PATH)?>admin/config">Config</a>
                        </li>
                    <?php endif?>
                    <?php if(nqv::userCan(['crud','permissions'])):?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="<?php echo nqv::url(ROOT_PATH)?>admin/permissions">Permisos</a>
                        </li>
                    <?php endif?>
            
                    <?php if(currentSessionTypeIs('admin')):?>
                        <?php if(nqvDB::isTable('adds') && nqvDB::isTable('advertisers') && nqv::getConfig('adds-enabled')): ?>
                            <li class="nav-item">
                                <ul class="navbar-nav mb-2 mb-lg-0">
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Adds
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="/admin/adds">Anuncios</a></li>
                                            <li><a class="dropdown-item" href="/admin/advertisers">Anunciantes</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <ul class="navbar-nav mb-2 mb-lg-0">
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Banners
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="/admin/banners/main-banner">Banner principal</a></li>
                                            <li><a class="dropdown-item" href="/admin/banners-bottom">Banners de abajo</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        <?php endif?>
                        <?php $items = nqv::getDatabasePupupItems()?>
                        <?php if(!empty($items)):?>
                            <li class="nav-item">
                                <ul class="navbar-nav mb-2 mb-lg-0">
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Contenido
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <?php foreach($items as $k => $item):?>
                                                <?php if(!nqv::userCan(['read',$k])) continue?>
                                                <li><a class="dropdown-item" href="/admin/database/<?php echo $k?>"><?php echo ucfirst($item)?></a></li>
                                            <?php endforeach?>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        <?php endif?>
                    <?php endif?>
                    <li class="mobile"><hr class="dropdown-divider"></li>
                    <li class="mobile nav-item"><a class="dropdown-item nav-link" href="/admin/profile">Perfil</a></li>
                    <?php if(userIs('root')):?>
                        <li class="mobile nav-item"><a class="dropdown-item nav-link" href="/admin/toggletype/root"><?php echo nqv::translate('Use like')?> Root</a></li>
                        <li class="mobile nav-item"><a class="dropdown-item nav-link" href="/admin/toggletype/admin"><?php echo nqv::translate('Use like')?> Admin</a></li>
                        <li class="mobile nav-item"><a class="dropdown-item nav-link" href="/admin/toggletype/contributor"><?php echo nqv::translate('Use like')?> Contributor</a></li>
                    <?php endif?>
                    <li class="mobile nav-item"><hr class="dropdown-divider"></li>
                    <li class="mobile"><hr class="dropdown-divider"></li>
                    <li class="mobile nav-item"><a class="dropdown-item nav-link" href="/logout">Cerrar sesión</a></li>
                </ul>
            </div>
            <?php if(user_is_logged()):?>
                <ul class="navbar-nav mb-2 mb-lg-0 tablet desktop">

                    <li class="nav-item"><?php echo getLaguageSelector()?></li>
                    <li class="nav-item">
                        <span class="nav-link"><small>v<?php echo nqv::getConfig('version')?></small></span>
                    </li>
                    <?php if(nqv::checkUpgrades() && userIs('root')):?>
                        <li class="nav-item">
                            <a class="d-inline-block nav-link text-info" href="/admin/upgrade"><small>v<?php echo nqv::getNextVersion()?></small></a>
                        </li>
                    <?php endif?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="me-2"><?php echo nqv::getCurrentSessionTypeName()?></span>
                            <i class="fa-solid fa-user"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/admin/profile">Perfil</a></li>
                            <?php if(userIs('root')):?>
                                <li><a class="dropdown-item" href="/admin/toggletype/root"><?php echo nqv::translate('Use like')?> Root</a></li>
                                <li><a class="dropdown-item" href="/admin/toggletype/admin"><?php echo nqv::translate('Use like')?> Admin</a></li>
                                <li><a class="dropdown-item" href="/admin/toggletype/contributor"><?php echo nqv::translate('Use like')?> Contributor</a></li>
                            <?php endif?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout">Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
            <?php else:?>
                <a id="loggin-access" href="/admin/login">
                    <span>Login</span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"></path>
                    <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"></path>
                    </svg>
                </a>
            <?php endif?>
        </div>
    </nav>
</header>
<?php nqvNotifications::flush(null)?>