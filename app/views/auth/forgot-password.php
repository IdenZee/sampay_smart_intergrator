<?php $pageTitle = 'Forgot Password'; ?>

<h5 class="mb-1 fw-semibold">Reset your password</h5>
<p class="text-muted small mb-4">Enter your email and we'll send you a reset link.</p>

<?php if ($sent): ?>
    <div class="alert alert-success py-2 small">
        If that email exists in our system, a reset link has been sent.
    </div>
    <a href="<?= APP_URL ?>/login" class="btn btn-outline-secondary w-100 mt-2">Back to Login</a>
<?php else: ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-medium">Email address</label>
            <input type="email" name="email" class="form-control"
                   placeholder="you@company.com" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-send me-1"></i> Send Reset Link
        </button>
    </form>
    <div class="text-center mt-3">
        <a href="<?= APP_URL ?>/login" class="text-muted small">Back to Login</a>
    </div>
<?php endif; ?>
