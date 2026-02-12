CREATE TABLE `users` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(125) NOT NULL,
 `lastname` varchar(125) NOT NULL,
 `email` varchar(255) NOT NULL,
 `password` varchar(119) NOT NULL,
 `token` varchar(64) DEFAULT NULL,
 `session_types_id` bigint unsigned NOT NULL,
 `status` enum('active','suspended','banned','pendent') DEFAULT 'active',
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `created_by` bigint unsigned NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 UNIQUE KEY `UQI` (`name`,`lastname`),
 UNIQUE KEY `EMAIL` (`email`),
 KEY `sessiontype` (`session_types_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` SET `name`='Hernán',`lastname`='Mancuso',`email`='hernun@navequeva.com.ar',`password`='fake',`session_types_id`=1,`status`='active',`created_by`=0;

CREATE TABLE `session_types` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(125) NOT NULL,
 `slug` varchar(255) DEFAULT NULL,
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 UNIQUE KEY `UQI` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `session_types` SET `name` = 'root', `slug` = 'root';
INSERT INTO `session_types` SET `name` = 'admin', `slug` = 'admin';
INSERT INTO `session_types` SET `name` = 'editor', `slug` = 'editor';
INSERT INTO `session_types` SET `name` = 'moderator', `slug` = 'moderator';
INSERT INTO `session_types` SET `name` = 'contributor', `slug` = 'contributor';
INSERT INTO `session_types` SET `name` = 'member', `slug` = 'member';
INSERT INTO `session_types` SET `name` = 'subscriber', `slug` = 'subscriber';
INSERT INTO `session_types` SET `name` = 'guest', `slug` = 'guest';

CREATE TABLE `config` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `slug` varchar(255) DEFAULT NULL,
 `value` text ,
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `created_by` bigint unsigned NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `config` SET `name` = 'version', `slug` = 'version',`value` = '2.0.0',`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Modo Mantenimiento', `slug` = 'maintenance-mode',`value` = '1',`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Vistas accesibles en modo mantenimiento', `slug` = 'maintenance-enabled-templates',`value` = 'login,password-reset',`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Plantilla del Modo Mantenimiento', `slug` = 'maintenance-template',`value` = 'maintenance',`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Permisos', `slug` = 'permissions',`value` = '{"admin":{"crud":["adds","advertisers","config","images","mainimages","tags","users"]}}',`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Permisos adicionales', `slug` = 'additional-permissions',`value` = 'tables,permissions',`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Home Widgets', `slug` = 'home-widgets',`value` = '[1,2]',`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Items en menú Contenido', `slug` = 'items-admin',`value` = '',`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Mostrar pie de página en Admin', `slug` = 'admin_footer',`value` = 0,`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Mostrar pie de página en Front', `slug` = 'front_footer',`value` = 1,`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Mostrar encabezado de página en Admin', `slug` = 'admin_header',`value` = 1,`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Mostrar encabezado de página en Front', `slug` = 'front_header',`value` = 1,`created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Configuración de correo', `slug` = 'mail-settings', `value` = '{"active":"mailhog","drivers":{"mailhog":{"type":"smtp","host":"127.0.0.1","port":1025,"smtp_auth":false,"smtp_secure":false,"smtp_auto_tls":false,"from":{"address":"no-reply@local.test","name":"Aplicación"}},"smtp":{"type":"smtp","host":"","port":587,"smtp_auth":true,"smtp_secure":"tls","smtp_auto_tls":true,"from":{"address":"","name":""}},"gmail":{"type":"gmail","host":"smtp.gmail.com","port":587,"smtp_auth":true,"smtp_secure":"tls","smtp_auto_tls":true,"from":{"address":"","name":""}},"ses":{"type":"ses","region":"","endpoint":"","from":{"address":"","name":""}}}}', `created_at` = NOW(), `created_by` = 0;
INSERT INTO `config` SET `name` = 'Página de inicio', `slug` = 'homepage',`value` = '{"page_id":0}',`created_at` = NOW(), `created_by` = 0;

CREATE TABLE `mainimages` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(125) NOT NULL,
 `slug` varchar(125) NOT NULL,
 `tablename` varchar(125) NOT NULL,
 `element_id` bigint unsigned NOT NULL,
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `created_by` bigint unsigned NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `UQI` (`slug`),
 UNIQUE KEY `element` (`tablename`,`element_id`),
 KEY `tablename` (`tablename`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `images` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(125) NOT NULL,
 `slug` varchar(125) NOT NULL,
 `tablename` varchar(125) NOT NULL,
 `element_id` bigint unsigned NOT NULL,
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `created_by` bigint unsigned NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `UQI` (`slug`),
 KEY `tablename` (`tablename`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `advertisers` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `company_name` varchar(125) NOT NULL,
 `name` varchar(125) NOT NULL,
 `lastname` varchar(125) NOT NULL,
 `email` varchar(255) NOT NULL,
 `cellphone` int unsigned NOT NULL,
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `created_by` bigint unsigned NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `UQI` (`name`,`lastname`),
 UNIQUE KEY `EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 CREATE TABLE `adds` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(125) NOT NULL,
 `advertisers_id` bigint unsigned NOT NULL,
 `priority` tinyint unsigned NOT NULL,
 `section` enum('primary','secondary','tertiary','quaternary') DEFAULT 'primary',
 `link` text ,
 `publish_from` datetime DEFAULT NULL,
 `publish_to` datetime DEFAULT NULL,
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `created_by` bigint unsigned NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `UQI` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pages` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
    `content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
    `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
    `slug` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` bigint unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `tags` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `value` varchar(255) ,
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `created_by` bigint unsigned NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `altname` varchar(255) DEFAULT NULL,
 `description` text ,
 `slug` varchar(255) NOT NULL,
 `order` tinyint unsigned DEFAULT '0',
 `public` tinyint unsigned DEFAULT NULL,
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `created_by` bigint unsigned NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `Name` (`name`),
 UNIQUE KEY `AltName` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `widgets` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `description` text ,
 `slug` varchar(255) NOT NULL,
 `order` tinyint unsigned DEFAULT '0',
 `public` tinyint unsigned DEFAULT NULL,
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
 `modified_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `created_by` bigint unsigned NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `Name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `widgets` SET `name` = 'Página de inicio',`description` = 'Establecer un apágina como landing page del sitio',`slug` = 'homepage',`public` = 1, `order` = 0,`created_at` = NOW(),`created_by` = 0,`modified_at` = NOW();
INSERT INTO `widgets` SET `name` = 'Modo mantenimiento',`description` = 'Ocultar o publicar el front para usuarios no identificados durante las tareas de edición o mantenimiento',`slug` = 'maintenance-mode',`public` = 1, `order` = 1,`created_at` = NOW(),`created_by` = 0,`modified_at` = NOW();
INSERT INTO `widgets` SET `name` = 'Usuarios',`description` = 'Creación, modificación o eliminación de usuarios de la sección ADMIN',`slug` = 'list-users',`public` = 1, `order` = 2,`created_at` = NOW(),`created_by` = 0,`modified_at` = NOW();
INSERT INTO `widgets` SET `name` = 'Crear miniaturas',`description` = 'Crear miniaturas para todas las imágenes principales',`slug` = 'thumbnails-create',`public` = 1, `order` = 3,`created_at` = NOW(),`created_by` = 0,`modified_at` = NOW();
INSERT INTO `widgets` SET `name` = 'Crear tablas SQL a partir de archivos CVS',`description` = 'Crear tablas SQL a partir de archivos CVS',`slug` = 'sql-from-cvs',`public` = 1, `order` = 4,`created_at` = NOW(),`created_by` = 0,`modified_at` = NOW();
INSERT INTO `widgets` SET `name` = 'Eliminar todas las tablas',`description` = 'Esta acción eliminará todas las tablas de la base de datos, lo cual equivale a resetear el sistema a su estado de origen',`slug` = 'dropdatabase',`public` = 1, `order` = 5,`created_at` = NOW(),`created_by` = 0,`modified_at` = NOW();