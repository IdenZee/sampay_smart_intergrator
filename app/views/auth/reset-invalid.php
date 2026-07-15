<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invalid Reset Link — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #1a1a2e; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border: none; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <div style="width:100%;max-width:420px;padding:1rem">
        <div class="card p-4 text-center">
            <div class="mb-3">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:3rem"></i>
            </div>
            <h4 class="fw-bold">Invalid or Expired Link</h4>
            <p class="text-muted">This password reset link is invalid or has already been used. Reset links expire after 1 hour.</p>
            <a href="<?= APP_URL ?>/forgot-password" class="btn btn-primary w-100">
                <i class="bi bi-arrow-repeat me-1"></i> Request a New Link
            </a>
            <div class="mt-3">
                <a href="<?= APP_URL ?>/login" class="text-muted small">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
