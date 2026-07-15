<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 — Access Denied</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="text-center">
        <h1 class="display-1 fw-bold text-muted">403</h1>
        <p class="lead">You don't have permission to access this page.</p>
        <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>/dashboard" class="btn btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>
