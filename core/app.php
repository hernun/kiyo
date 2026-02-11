<?php

/*

📌 Próxima limpieza natural (más adelante)
Cuando App madure un poco, estos archivos deberían registrar cosas, no ejecutar lógica.

Ejemplos de cosas válidas ahí:

- constantes
- config
- bindings simples
- includes legacy (nqv)

Ejemplos de cosas que no deberían quedar ahí a futuro:

session_start
- header()
- lógica por entorno
- routing

No los toquemos aún. Solo tenelo como brújula.

*/

if(!defined('ROOT_PATH')) define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);

define('CORE_PATH',ROOT_PATH . 'core' . DIRECTORY_SEPARATOR);
define('USER_PATH',ROOT_PATH . 'user' . DIRECTORY_SEPARATOR);

define('INCLUDES_PATH', CORE_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('USER_INCLUDES_PATH', USER_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('TMP_PATH', CORE_PATH . 'tmp' . DIRECTORY_SEPARATOR);

define('USER_CLASSES_PATH', USER_INCLUDES_PATH . 'classes' . DIRECTORY_SEPARATOR);
define('USER_FUNCTIONS_PATH', USER_INCLUDES_PATH . 'functions' . DIRECTORY_SEPARATOR);
define('USER_VENDOR', ROOT_PATH . 'vendor' . DIRECTORY_SEPARATOR);

define('CLASSES_PATH', INCLUDES_PATH . 'classes' . DIRECTORY_SEPARATOR);
define('FUNCTIONS_PATH', INCLUDES_PATH . 'functions' . DIRECTORY_SEPARATOR);
define('VENDOR', ROOT_PATH . 'vendor' . DIRECTORY_SEPARATOR);

define('ADMIN_PATH', INCLUDES_PATH . 'admin' . DIRECTORY_SEPARATOR);
define('USER_ADMIN_PATH', USER_INCLUDES_PATH . 'admin' . DIRECTORY_SEPARATOR);
define('ADMIN_TEMPLATES_PATH', ADMIN_PATH . 'templates' . DIRECTORY_SEPARATOR);
define('USER_ADMIN_TEMPLATES_PATH', USER_ADMIN_PATH . 'templates' . DIRECTORY_SEPARATOR);

define('SQL_PATH', ADMIN_PATH . 'sql' . DIRECTORY_SEPARATOR);
define('FORMS_PATH', ADMIN_PATH . 'forms' . DIRECTORY_SEPARATOR);

define('FRONT_PATH', INCLUDES_PATH . 'front' . DIRECTORY_SEPARATOR);
define('USER_FRONT_PATH', USER_INCLUDES_PATH . 'front' . DIRECTORY_SEPARATOR);
define('FRONT_TEMPLATES_PATH', FRONT_PATH . 'templates' . DIRECTORY_SEPARATOR);
define('USER_FRONT_TEMPLATES_PATH', USER_FRONT_PATH . 'templates' . DIRECTORY_SEPARATOR);

define('PARTS_PATH', INCLUDES_PATH . 'parts' . DIRECTORY_SEPARATOR);
define('FRONT_FORMS_PATH', FRONT_PATH . 'forms' . DIRECTORY_SEPARATOR);
define('DASHBOARD_PATH', FRONT_TEMPLATES_PATH . 'dashboard' . DIRECTORY_SEPARATOR);

define('UPLOADS_PATH',ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('UPGRADES_PATH',ROOT_PATH . 'upgrades' . DIRECTORY_SEPARATOR);
define('LOGS_PATH',ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR);

$confFilePath = ROOT_PATH.'.env';
$functionsFilePath = is_file(USER_FUNCTIONS_PATH.'functions.php') ? USER_FUNCTIONS_PATH.'functions.php':FUNCTIONS_PATH.'functions.php';
$classesFilePath = is_file(USER_CLASSES_PATH.'classes.php') ? USER_CLASSES_PATH.'classes.php':CLASSES_PATH.'classes.php';
$vendorPath = VENDOR.'autoload.php';

if(!is_file($confFilePath) || !is_file($functionsFilePath) || !is_file($classesFilePath) || !is_file($vendorPath)) {
    if(!is_file($confFilePath)) $msg = 'Falta el fichero de configuración';
    elseif(!is_file($functionsFilePath)) $msg = 'Falta el fichero de funciones';
    elseif(!is_file($classesFilePath)) $msg = 'Falta el fichero de clases';
    elseif(!is_file($vendorPath)) $msg = 'Falta el fichero de autoload';
    $style = [
        'text-transform:uppercase',
        'font-family:sans-serif',
        'align-items:center',
        'justify-content:center',
        'width:100vw',
        'display:flex',
        'height:100vh',
        'letter-spacing:2px',
        'font-weight:100'
    ];
    $style2 = [
        'margin-top:-2rem'
    ];
    $body = '<div style="' . implode(';',$style) . '"><div style="' . implode(';',$style2) . '">' . $msg . '</div></div>';
    $html = '<!doctype html><html><head>' . $title . '</head><body style="margin:0;padding:0">' . $body . '</body></html>';
    die($html);
}

error_reporting(E_ALL);
ini_set('log_errors', 'On');
ini_set('error_log', LOGS_PATH . 'php.log');

if(!empty($_SERVER['HTTP_HOST'])) define('DOMAIN',$_SERVER['HTTP_HOST']);
else define('DOMAIN',strtolower(APP_NAME . '.nqv'));

const BACKEND_ROLES = ['admin', 'editor', 'moderator', 'contributor'];
const FRONTEND_ROLES = ['member', 'subscriber', 'guest'];

$pathsToCreate = [
    UPLOADS_PATH => 0775,
    LOGS_PATH => 0775,
    TMP_PATH => 0700,
    UPGRADES_PATH => 0700,
];

foreach ($pathsToCreate as $path => $mode) {
    if (!is_dir($path)) {
        mkdir($path, $mode, true);
    }
}

define('URL','https://' . DOMAIN);

$version = '2.0.0';
$versionid = intval(str_replace('.','',$version));

define('ASSETVERSION',time());
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
define('DEBUG', true);

require_once $functionsFilePath;
require_once $classesFilePath;
require_once $vendorPath;

// Apuntas a la raíz donde vive el .env
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

if(empty($_ENV)) {
    include(CORE_PATH . 'errors/envfailure.html');
    exit;
}

define('ENV', $_ENV['ENVIRONMENT']);
define('ADMIN_EMAIL',$_ENV['ADMIN_EMAIL'] ? $_ENV['ADMIN_EMAIL'] :'no-reply@' . DOMAIN);
define('ADMIN_EMAIL_SENDER',$_ENV['EMAIL_SENDER'] ? $_ENV['EMAIL_SENDER'] :'no-reply@' . DOMAIN);
define('ADMIN_EMAIL_SENDER_PASSWORD',$_ENV['EMAIL_SENDER_PASSWORD'] );

define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USER',$_ENV['DB_USER']);
define('DB_NAME',$_ENV['DB_NAME']);
define('DB_PASS',$_ENV['DB_PASS']);

if(empty(DB_HOST)) {
    include(CORE_PATH . 'errors/dbfailure.html');
    exit;
}

try {
    if(!nqvDB::isTable('users')) {
        try {
            setupSql();
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }
} catch(Exception $e) {
    include(CORE_PATH . 'errors/db-bad-connection.php');
    exit;
}

define('APP_NAME',$_ENV['APP_NAME']);
define('APP_TITLE',$_ENV['APP_TITLE']);
define('APP_DESCRIPTION',$_ENV['APP_DESCRIPTION']);
define('APP_AUTHOR',$_ENV['APP_AUTHOR']);

nqvSession::getInstance()->open();
nqv::parseVars();

if(userIs('holder')) define('DESKTOP_PATH',DASHBOARD_PATH);
else define('DESKTOP_PATH',ADMIN_PATH);

if(isAdmin()) {
    define('TEMPLATES_PATH',ADMIN_TEMPLATES_PATH);
    define('USER_TEMPLATES_PATH',USER_ADMIN_TEMPLATES_PATH);
} else {
    define('TEMPLATES_PATH',FRONT_TEMPLATES_PATH);
    define('USER_TEMPLATES_PATH',USER_FRONT_TEMPLATES_PATH);
}