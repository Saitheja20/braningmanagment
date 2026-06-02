<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ERROR REPORTING
|--------------------------------------------------------------------------
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| BOOTSTRAP
|--------------------------------------------------------------------------
*/

require __DIR__ . '/src/bootstrap.php';

/*
|--------------------------------------------------------------------------
| IMPORTS
|--------------------------------------------------------------------------
*/

use App\Controllers\AuthController;
use App\Controllers\ClientPortalController;
use App\Controllers\DashboardController;
use App\Controllers\EmployeeController;
use App\Controllers\ProjectController;
use App\Controllers\RealtimeController;
use App\Controllers\TaskController;

use App\Core\App;
use App\Core\Router;

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\SecurityMiddleware;

/*
|--------------------------------------------------------------------------
| ROUTER INIT
|--------------------------------------------------------------------------
*/

$router = new Router();

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

$router->get('/', function () {
    App::redirect('/dashboard');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

$router->get('/login', [AuthController::class, 'showLogin'], [
    GuestMiddleware::class
]);

$router->post('/login', [AuthController::class, 'login'], [
    GuestMiddleware::class
]);

$router->get('/register', [AuthController::class, 'showRegister'], [
    GuestMiddleware::class
]);

$router->post('/register', [AuthController::class, 'register'], [
    GuestMiddleware::class
]);

$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], [
    GuestMiddleware::class
]);

$router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [
    GuestMiddleware::class
]);

$router->get('/reset-password', [AuthController::class, 'showResetPassword'], [
    GuestMiddleware::class
]);

$router->post('/reset-password', [AuthController::class, 'resetPassword'], [
    GuestMiddleware::class
]);

$router->post('/logout', [AuthController::class, 'logout'], [
    AuthMiddleware::class
]);

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

$router->get('/dashboard', [DashboardController::class, 'index'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'dashboard.view'],
]);

/*
|--------------------------------------------------------------------------
| PROJECTS
|--------------------------------------------------------------------------
*/

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

$router->post('/milestones/store', [ProjectController::class, 'milestoneStore'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'projects.manage'],
]);

/*
|--------------------------------------------------------------------------
| TASKS
|--------------------------------------------------------------------------
*/

$router->get('/tasks', [TaskController::class, 'index'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);

$router->get('/tasks/detail', [TaskController::class, 'detail'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);

$router->post('/tasks/update', [TaskController::class, 'update'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);

$router->post('/tasks/status', [TaskController::class, 'status'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);

$router->post('/tasks/priority', [TaskController::class, 'priority'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);

$router->post('/tasks/comment', [TaskController::class, 'comment'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);

$router->post('/tasks/checklist/store', [TaskController::class, 'checklistStore'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);

$router->post('/tasks/checklist/toggle', [TaskController::class, 'checklistToggle'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);

$router->post('/tasks/attachments/store', [TaskController::class, 'attachmentStore'], [
    AuthMiddleware::class,
    [RoleMiddleware::class, 'tasks.manage'],
]);

/*
|--------------------------------------------------------------------------
| EMPLOYEES
|--------------------------------------------------------------------------
*/

$router->get('/employees', [EmployeeController::class, 'index'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'employees.manage'],
]);

$router->get('/employees/detail', [EmployeeController::class, 'detail'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'employees.manage'],
]);

$router->post('/employees/store', [EmployeeController::class, 'store'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'employees.manage'],
]);

$router->post('/employees/update', [EmployeeController::class, 'update'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'employees.manage'],
]);

$router->post('/employees/attendance', [EmployeeController::class, 'attendance'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'employees.manage'],
]);

$router->post('/employees/work-log', [EmployeeController::class, 'workLog'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'employees.manage'],
]);

/*
|--------------------------------------------------------------------------
| REALTIME
|--------------------------------------------------------------------------
*/

$router->get('/realtime', [RealtimeController::class, 'index'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'realtime.view'],
]);

$router->get('/api/realtime/snapshot', [RealtimeController::class, 'snapshot'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
]);

$router->post('/api/realtime/message', [RealtimeController::class, 'message'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
]);

$router->post('/api/notifications/read', [RealtimeController::class, 'readNotification'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
]);

/*
|--------------------------------------------------------------------------
| CLIENT PORTAL
|--------------------------------------------------------------------------
*/

$router->get('/portal', [ClientPortalController::class, 'index'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'client_portal.use'],
]);

$router->post('/portal/approval', [ClientPortalController::class, 'approval'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'client_portal.use'],
]);

$router->post('/portal/feedback', [ClientPortalController::class, 'feedback'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'client_portal.use'],
]);

$router->get('/portal/file', [ClientPortalController::class, 'file'], [
    SecurityMiddleware::class,
    AuthMiddleware::class,
    [RoleMiddleware::class, 'client_portal.use'],
]);

/*
|--------------------------------------------------------------------------
| URI FIX FOR SUBFOLDER
|--------------------------------------------------------------------------
*/

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = str_replace('/Branding', '', $uri);

if ($uri === '') {
    $uri = '/';
}

/*
|--------------------------------------------------------------------------
| DISPATCH
|--------------------------------------------------------------------------
*/

$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);