<?php

use App\Core\Csrf;
?>
<div class="card auth-card">
  <div class="card-body p-4">
    <div class="mb-4">
      <h2 class="h4 mb-1">Choose new password</h2>
      <p class="text-muted mb-0">Use at least 8 characters.</p>
    </div>

    <?php if ($message = error('token')): ?>
      <div class="alert alert-danger"><?= e($message) ?></div>
    <?php endif; ?>

    <form method="post" action="/reset-password" novalidate>
      <?= Csrf::field() ?>
      <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

      <div class="mb-3">
        <label for="password" class="form-label">New password</label>
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

      <button type="submit" class="btn btn-primary w-100">Update password</button>
    </form>
  </div>
  <div class="card-footer bg-white text-center py-3">
    <a href="/login" class="text-decoration-none">Back to sign in</a>
  </div>
</div>
