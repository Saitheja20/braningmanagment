<?php

use App\Core\Config;

$pageTitle = isset($title) ? e($title) . ' | ' . e(Config::get('APP_NAME', 'Branding PM')) : e(Config::get('APP_NAME', 'Branding PM'));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
  <main class="auth-shell">
    <div class="container d-flex align-items-center justify-content-center py-5">
      <div class="auth-panel">
        <div class="d-flex align-items-center gap-3 mb-4">
          <span class="brand-mark">BP</span>
          <div>
            <h1 class="h4 mb-0"><?= e(Config::get('APP_NAME', 'Branding PM')) ?></h1>
            <p class="mb-0 text-muted">Agency project management</p>
          </div>
        </div>

        <?= $content ?>
      </div>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
