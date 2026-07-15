<?php

// ── Timezone ───────────────────────────────────────────────────────────────
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Africa/Lusaka');

// ── Error reporting ────────────────────────────────────────────────────────
if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}

// ── Session ────────────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name($_ENV['SESSION_NAME'] ?? 'fsms_session');
session_start();

// ── Global constants ───────────────────────────────────────────────────────
define('APP_NAME',     $_ENV['APP_NAME']  ?? 'FSMS');
define('APP_URL',      rtrim($_ENV['APP_URL'] ?? '', '/'));
define('APP_VERSION',  '1.0.0');
define('CURRENCY',     'ZMW');
