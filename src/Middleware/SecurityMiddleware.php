<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Core\Config;

final class SecurityMiddleware
{
    public static function headers(): void
    {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 0');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        if (Config::get('APP_ENV', 'local') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            header("Content-Security-Policy: default-src 'self'; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; script-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self'");
        }
    }

    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['GET', 'POST'], true)) {
            App::abort(405, 'Method not allowed.');
        }
    }
}
