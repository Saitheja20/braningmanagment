<div class="card auth-card">
  <div class="card-body p-4">
    <h2 class="h4 mb-2"><?= e($title ?? 'Error') ?></h2>
    <p class="text-muted mb-4"><?= e($message ?? 'Something went wrong.') ?></p>
    <a href="/dashboard" class="btn btn-primary">Go to dashboard</a>
  </div>
</div>
