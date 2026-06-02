<?php

use App\Core\Csrf;
?>
<div class="card auth-card">
  <div class="card-body p-4">
    <div class="mb-4">
      <h2 class="h4 mb-1">Create account</h2>
      <p class="text-muted mb-0">Start with secure client portal access.</p>
    </div>

    <form method="post" action="/register" novalidate>
      <?= Csrf::field() ?>

      <div class="mb-3">
        <label for="name" class="form-label">Full name</label>
        <input id="name" name="name" type="text" class="form-control <?= error('name') ? 'is-invalid' : '' ?>" value="<?= e(old('name')) ?>" required autocomplete="name">
        <?php if ($message = error('name')): ?>
          <div class="invalid-feedback"><?= e($message) ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input id="email" name="email" type="email" class="form-control <?= error('email') ? 'is-invalid' : '' ?>" value="<?= e(old('email')) ?>" required autocomplete="email">
        <?php if ($message = error('email')): ?>
          <div class="invalid-feedback"><?= e($message) ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input id="phone" name="phone" type="tel" class="form-control" value="<?= e(old('phone')) ?>" autocomplete="tel">
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input id="password" name="password" type="password" class="form-control <?= error('password') ? 'is-invalid' : '' ?>" required autocomplete="new-password">
        <?php if ($message = error('password')): ?>
          <div class="invalid-feedback"><?= e($message) ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control <?= error('password_confirmation') ? 'is-invalid' : '' ?>" required autocomplete="new-password">
        <?php if ($message = error('password_confirmation')): ?>
          <div class="invalid-feedback"><?= e($message) ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary w-100">Create account</button>
    </form>
  </div>
  <div class="card-footer bg-white text-center py-3">
    <span class="text-muted">Already registered?</span>
    <a href="/login" class="text-decoration-none">Sign in</a>
  </div>
</div>
