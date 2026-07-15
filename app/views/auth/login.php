<?php $pageTitle = 'Login'; ?>

<h5 class="mb-4 fw-semibold">Sign in to your account</h5>

<?php if ($error): ?>
    <div class="alert alert-danger py-2 small"><?= Format::e($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/login">
    <div class="mb-3">
        <label class="form-label fw-medium">Email address</label>
        <input type="email" name="email" class="form-control"
               value="<?= Format::e($_POST['email'] ?? '') ?>"
               placeholder="you@company.com" required autofocus>
    </div>
    <div class="mb-4">
        <label class="form-label fw-medium d-flex justify-content-between">
            Password
            <a href="<?= APP_URL ?>/forgot-password" class="text-muted small fw-normal">Forgot password?</a>
        </label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
    </button>
</form>
