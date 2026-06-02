<?php

use App\Core\Csrf;
?>
<div class="card auth-card">
  <div class="card-body p-4">
    <div class="mb-4">
      <h2 class="h4 mb-1">Reset password</h2>
      <p class="text-muted mb-0">Enter your email and generate a secure reset link.</p>
    </div>

    <?php if ($message = flash('success')): ?>
      <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($resetLink = flash('reset_link')): ?>
      <div class="alert alert-info">
        Development reset link:
        <a href="<?= e($resetLink) ?>"><?= e($resetLink) ?></a>
      </div>
    <?php endif; ?>

    <form method="post" action="/forgot-password" novalidate>
      <?= Csrf::field() ?>

      <div class="mb-4">
        <label for="email" class="form-label">Email address</label>
        <input id="email" name="email" type="email" class="form-control <?= error('email') ? 'is-invalid' : '' ?>" value="<?= e(old('email')) ?>" required autocomplete="email">
        <?php if ($message = error('email')): ?>
          <div class="invalid-feedback"><?= e($message) ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary w-100">Send reset link</button>
    </form>
  </div>
  <div class="card-footer bg-white text-center py-3">
    <a href="/login" class="text-decoration-none">Back to sign in</a>
  </div>
</div>
