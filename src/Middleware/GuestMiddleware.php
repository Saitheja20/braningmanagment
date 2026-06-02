<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Services\AuthService;

final class GuestMiddleware
{
    public function handle(): void
    {
        if (AuthService::check()) {
            App::redirect('/dashboard');
        }
    }
}
