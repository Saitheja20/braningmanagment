<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

require __DIR__ . '/Helpers/functions.php';

App\Core\Config::load(dirname(__DIR__) . '/.env');
App\Middleware\SecurityMiddleware::headers();
set_exception_handler(function (Throwable $exception): void {
    App\Core\Logger::error($exception);
    if (App\Core\Config::bool('APP_DEBUG')) {
        http_response_code(500);
        echo '<pre>' . e($exception->getMessage()) . '</pre>';
        exit;
    }
    App\Core\App::abort(500, 'A server error occurred.');
});
App\Core\Session::start();
App\Services\AuthService::bootRememberedUser();
