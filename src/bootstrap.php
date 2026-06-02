<?php

declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| ROOT PATH
|--------------------------------------------------------------------------
*/

define('ROOT_PATH', dirname(__DIR__) . '/');

/*
|--------------------------------------------------------------------------
| AUTOLOADER
|--------------------------------------------------------------------------
*/

spl_autoload_register(function (string $class): void {

    $prefix = 'App\\';
    $baseDir = ROOT_PATH . 'src/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));

    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    } else {
        die("Autoload Error: {$file}");
    }
});

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

$helperPath = ROOT_PATH . 'src/Helpers/functions.php';

if (file_exists($helperPath)) {
    require $helperPath;
}

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

if (class_exists('App\Core\Config')) {
    App\Core\Config::load(ROOT_PATH . '.env');
}

/*
|--------------------------------------------------------------------------
| SECURITY
|--------------------------------------------------------------------------
*/

if (class_exists('App\Middleware\SecurityMiddleware')) {
    App\Middleware\SecurityMiddleware::headers();
}

/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (class_exists('App\Core\Session')) {
    App\Core\Session::start();
}

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

if (class_exists('App\Services\AuthService')) {
    App\Services\AuthService::bootRememberedUser();
}