<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Services\AuthService;

final class RoleMiddleware
{
    public function handle(string $permission): void
    {
        if (!AuthService::can($permission)) {
            App::abort(403, 'You do not have permission to access this page.');
        }
    }
}
