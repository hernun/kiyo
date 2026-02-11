<?php
declare(strict_types=1);

use OVO\Core\App;
use OVO\Http\Request;

$bootstrap = [
    $_SERVER['DOCUMENT_ROOT'] . '/user/app.php',
    $_SERVER['DOCUMENT_ROOT'] . '/core/app.php',
];

foreach ($bootstrap as $file) {
    if (is_file($file)) {
        require_once $file;
    }
}

$request  = Request::fromGlobals();
$response = App::handle($request);
$response->send();