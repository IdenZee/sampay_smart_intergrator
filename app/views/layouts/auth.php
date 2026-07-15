<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — <?= $pageTitle ?? 'Login' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: #fff;
            border-radius: 12px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .auth-logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a1a2e;
            letter-spacing: -0.5px;
        }
        .auth-logo span { color: #f0a500; }
        .btn-primary {
            background: #1a1a2e;
            border-color: #1a1a2e;
        }
        .btn-primary:hover {
            background: #f0a500;
            border-color: #f0a500;
            color: #1a1a2e;
        }
        .form-control:focus { border-color: #f0a500; box-shadow: 0 0 0 .2rem rgba(240,165,0,.25); }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="auth-logo">
                <i class="bi bi-fuel-pump-fill me-1"></i>
                <?= APP_NAME ?><span>.</span>
            </div>
            <p class="text-muted small mt-1">Filling Station Management System</p>
        </div>

        <?= Flash::render() ?>

        <?= $content ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
