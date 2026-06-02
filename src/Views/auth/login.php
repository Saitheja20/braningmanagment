<?php

use App\Core\Csrf;
?>
<div class="card auth-card">
  <div class="card-body p-4">
    <div class="mb-4">
      <h2 class="h4 mb-1">Sign in</h2>
      <p class="text-muted mb-0">Access your agency workspace.</p>
    </div>

    <?php if ($message = flash('success')): ?>
      <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>

    <form method="post" action="/login" novalidate>
      <?= Csrf::field() ?>

      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input id="email" name="email" type="email" class="form-control <?= error('email') ? 'is-invalid' : '' ?>" value="<?= e(old('email')) ?>" required autocomplete="email">
        <?php if ($message = error('email')): ?>
          <div class="invalid-feedback"><?= e($message) ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input id="password" name="password" type="password" class="form-control <?= error('password') ? 'is-invalid' : '' ?>" required autocomplete="current-password">
        <?php if ($message = error('password')): ?>
          <div class="invalid-feedback"><?= e($message) ?></div>
        <?php endif; ?>
      </div>

      <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
          <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <a href="/forgot-password" class="text-decoration-none">Forgot password?</a>
      </div>

      <button type="submit" class="btn btn-primary w-100">Sign in</button>
    </form>
  </div>
  <div class="card-footer bg-white text-center py-3">
    <span class="text-muted">New here?</span>
    <a href="/register" class="text-decoration-none">Create an account</a>
  </div>
</div>
