<?php

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');

// ── Load .env ──────────────────────────────────────────────────────────────
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
        putenv(trim($key) . '=' . trim($val));
    }
}

// ── Autoloader ────────────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $paths = [
        APP_PATH . '/core/'        . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/models/'      . $class . '.php',
        APP_PATH . '/helpers/'     . $class . '.php',
        APP_PATH . '/middleware/'  . $class . '.php',
        APP_PATH . '/services/'    . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) { require_once $path; return; }
    }
});

// ── Config ────────────────────────────────────────────────────────────────
require_once APP_PATH . '/config/app.php';

// ── Bootstrap ─────────────────────────────────────────────────────────────
$app = new App();
$app->run();
