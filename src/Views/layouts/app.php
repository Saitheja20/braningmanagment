<?php

use App\Core\Config;
use App\Core\Csrf;
use App\Services\AuthService;

$currentUser = AuthService::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$pageTitle = isset($title) ? e($title) . ' | ' . e(Config::get('APP_NAME', 'Branding PM')) : e(Config::get('APP_NAME', 'Branding PM'));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= e(App\Core\Csrf::token()) ?>">
  <title><?= $pageTitle ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
  <?php ob_start(); ?>
    <div class="sidebar-brand">
      <span class="brand-mark">BP</span>
      <div>
        <div class="sidebar-title"><?= e(Config::get('APP_NAME', 'Branding PM')) ?></div>
        <small>Agency OS</small>
      </div>
    </div>

    <div class="sidebar-section">Workspace</div>
    <nav class="sidebar-nav">
      <a class="<?= $currentPath === '/dashboard' ? 'active' : '' ?>" href="/dashboard"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
      <a class="<?= $currentPath === '/projects' ? 'active' : '' ?>" href="/projects"><i class="bi bi-kanban"></i><span>Projects</span></a>
      <a class="<?= str_starts_with($currentPath, '/tasks') || $currentPath === '/projects/kanban' ? 'active' : '' ?>" href="/tasks"><i class="bi bi-check2-square"></i><span>Tasks</span></a>
      <a class="<?= $currentPath === '/portal' ? 'active' : '' ?>" href="/portal"><i class="bi bi-building"></i><span>Client Portal</span></a>
      <a class="<?= str_starts_with($currentPath, '/employees') ? 'active' : '' ?>" href="/employees"><i class="bi bi-people"></i><span>Employees</span></a>
      <a class="<?= str_starts_with($currentPath, '/realtime') ? 'active' : '' ?>" href="/realtime"><i class="bi bi-chat-left-text"></i><span>Realtime</span></a>
      <a href="#"><i class="bi bi-folder2-open"></i><span>Files</span></a>
      <a href="#"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    </nav>

    <div class="sidebar-section">System</div>
    <nav class="sidebar-nav">
      <a href="#"><i class="bi bi-shield-lock"></i><span>Roles</span></a>
      <a href="#"><i class="bi bi-gear"></i><span>Settings</span></a>
    </nav>

    <div class="sidebar-profile">
      <div class="avatar"><?= e(strtoupper(substr($currentUser['name'] ?? 'A', 0, 1))) ?></div>
      <div class="min-w-0">
        <div class="text-truncate fw-semibold"><?= e($currentUser['name'] ?? 'Admin User') ?></div>
        <small><?= e($currentUser['role_name'] ?? 'Agency Admin') ?></small>
      </div>
    </div>
  <?php $sidebarContent = ob_get_clean(); ?>

  <div class="app-shell">
    <aside class="app-sidebar d-none d-xl-flex">
      <?= $sidebarContent ?>
    </aside>

    <div class="offcanvas offcanvas-start mobile-sidebar" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
      <div class="offcanvas-header">
        <h2 class="offcanvas-title h5" id="mobileSidebarLabel">Navigation</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <?= $sidebarContent ?>
      </div>
    </div>

    <div class="app-main">
      <header class="top-navbar">
        <div class="d-flex align-items-center gap-3 min-w-0">
          <button class="icon-btn d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Open navigation">
            <i class="bi bi-list"></i>
          </button>
          <div class="min-w-0">
            <p class="eyebrow mb-1">Command center</p>
            <h1 class="page-title mb-0"><?= e($title ?? 'Dashboard') ?></h1>
          </div>
        </div>

        <div class="top-actions">
          <div class="search-box d-none d-md-flex">
            <i class="bi bi-search"></i>
            <input type="search" placeholder="Search projects, clients, files" aria-label="Search">
          </div>

          <div class="dropdown">
            <button class="icon-btn notification-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
              <i class="bi bi-bell"></i>
              <span></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-menu">
              <div class="dropdown-header d-flex align-items-center justify-content-between">
                <span>Notifications</span>
                <span class="badge text-bg-primary">4 new</span>
              </div>
              <a class="dropdown-item" href="#">
                <strong>Brand kit approved</strong>
                <small>Acme Studio approved the logo direction.</small>
              </a>
              <a class="dropdown-item" href="#">
                <strong>Review due today</strong>
                <small>Website wireframes need PM feedback.</small>
              </a>
              <a class="dropdown-item" href="#">
                <strong>New upload</strong>
                <small>Client added creative references.</small>
              </a>
            </div>
          </div>

          <form method="post" action="/logout" class="mb-0">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i><span class="d-none d-sm-inline ms-1">Logout</span></button>
          </form>
        </div>
      </header>

      <main class="dashboard-wrap">
        <?= $content ?>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script src="/assets/js/dashboard.js"></script>
  <script src="/assets/js/project-management.js"></script>
</body>
</html>
