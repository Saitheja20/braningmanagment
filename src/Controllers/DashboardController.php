<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Services\AuthService;

final class DashboardController
{
    public function index(): void
    {
        View::render('dashboard/index', [
            'title' => 'Admin Dashboard',
            'user' => AuthService::user(),
        ], 'layouts/app');
    }
}
