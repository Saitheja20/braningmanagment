<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProjectController;
use App\Core\App;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;

require dirname(__DIR__) . '/src/bootstrap.php';

$router = new Router();

$router->get('/', fn () => App::redirect('/dashboard'));

$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister'], [GuestMiddleware::class]);
$router->post('/register', [AuthController::class, 'register'], [GuestMiddleware::class]);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], [GuestMiddleware::class]);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [GuestMiddleware::class]);
$router->get('/reset-password', [AuthController::class, 'showResetPassword'], [GuestMiddleware::class]);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [GuestMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

$router->get('/dashboard', [DashboardController::class, 'index'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'dashboard.view'],
]);

$router->get('/projects', [ProjectController::class, 'index'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'projects.view'],
]);
$router->post('/projects/store', [ProjectController::class, 'store'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'projects.manage'],
]);
$router->post('/projects/update', [ProjectController::class, 'update'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'projects.manage'],
]);
$router->post('/projects/delete', [ProjectController::class, 'delete'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'projects.manage'],
]);
$router->post('/projects/status', [ProjectController::class, 'status'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'projects.manage'],
]);
$router->get('/projects/kanban', [ProjectController::class, 'kanban'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'projects.view'],
]);
$router->post('/tasks/store', [ProjectController::class, 'taskStore'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);
$router->post('/tasks/status', [ProjectController::class, 'taskStatus'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);
$router->post('/milestones/store', [ProjectController::class, 'milestoneStore'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'projects.manage'],
]);

$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
