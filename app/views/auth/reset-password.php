<?php $pageTitle = 'Reset Password'; ?>

<h5 class="mb-1 fw-semibold">Choose a new password</h5>
<p class="text-muted small mb-4">Hi <?= Format::e($reset['first_name']) ?>, set your new password below.</p>

<?php if ($error): ?>
    <div class="alert alert-danger py-2 small"><?= Format::e($error) ?></div>
<?php endif; ?>

<form method="POST">
    <div class="mb-3">
        <label class="form-label fw-medium">New Password</label>
        <input type="password" name="password" class="form-control"
               placeholder="Min. 8 characters" required minlength="8" autofocus>
    </div>
    <div class="mb-4">
        <label class="form-label fw-medium">Confirm Password</label>
        <input type="password" name="password_confirm" class="form-control"
               placeholder="Repeat password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-shield-check me-1"></i> Reset Password
    </button>
</form>
