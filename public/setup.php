<?php
/**
 * SamPay Integrator — One-time database setup script.
 * Run once at: http://localhost:8087/fsm/public/setup.php
 * DELETE this file immediately after running.
 */

define('ROOT_PATH', dirname(__DIR__));

// Load .env
foreach (file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $_ENV[trim($k)] = trim($v);
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$name = $_ENV['DB_NAME'] ?? 'fsms';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$name`");
} catch (PDOException $e) {
    die('<p style="color:red">DB connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

$errors = [];
$steps  = [];

function run(PDO $pdo, string $label, string $sql): void {
    global $errors, $steps;
    try {
        $pdo->exec($sql);
        $steps[] = "✅ $label";
    } catch (PDOException $e) {
        $errors[] = "❌ $label — " . $e->getMessage();
    }
}

// ── Drop old tables ────────────────────────────────────────────────────────
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
foreach (['webhook_configs','api_keys','sale_items','sales','items','item_classes',
          'vsdc_config','business_users','businesses','audit_log','password_resets',
          'api_tokens','settings','users','roles','company','branches'] as $t) {
    $pdo->exec("DROP TABLE IF EXISTS `$t`");
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
$steps[] = '✅ Old tables dropped';

// ── Create tables ──────────────────────────────────────────────────────────
run($pdo, 'roles', "CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'users', "CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    business_id INT NULL,
    employee_id VARCHAR(20),
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    must_change_password TINYINT(1) DEFAULT 0,
    last_login TIMESTAMP NULL,
    last_login_ip VARCHAR(45),
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'businesses', "CREATE TABLE businesses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sampay_business_id VARCHAR(100) NULL,
    name VARCHAR(150) NOT NULL,
    tpin VARCHAR(20) NOT NULL,
    branch_code VARCHAR(20) NULL,
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50) DEFAULT 'Zambia',
    phone VARCHAR(20),
    email VARCHAR(100),
    currency_code VARCHAR(5) DEFAULT 'ZMW',
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'business_users', "CREATE TABLE business_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_bu (business_id, user_id),
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'vsdc_config', "CREATE TABLE vsdc_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    label VARCHAR(100) NOT NULL DEFAULT 'Primary VSDC',
    vsdc_url VARCHAR(255) NOT NULL,
    device_serial VARCHAR(100),
    tax_office_name VARCHAR(100),
    mrc_no VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    initialized TINYINT(1) DEFAULT 0,
    initialized_at TIMESTAMP NULL,
    last_std_codes TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'item_classes', "CREATE TABLE item_classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    cls_code VARCHAR(20) NOT NULL,
    cls_name VARCHAR(150) NOT NULL,
    tax_ty_cd VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cls (business_id, cls_code),
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'items', "CREATE TABLE items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    item_code VARCHAR(50) NOT NULL,
    item_cls_code VARCHAR(20) NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    orgin_natrs_cd VARCHAR(10) DEFAULT 'ZM',
    pkg_unit_cd VARCHAR(10) DEFAULT 'NT',
    qty_unit_cd VARCHAR(10) DEFAULT 'U',
    tax_ty_cd VARCHAR(10) DEFAULT 'A',
    btch_no VARCHAR(50) NULL,
    bcd VARCHAR(100) NULL,
    selling_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    stock_qty DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    vsdc_registered TINYINT(1) DEFAULT 0,
    vsdc_registered_at TIMESTAMP NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_item_code (business_id, item_code),
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'sales', "CREATE TABLE sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    sale_ref VARCHAR(50) NOT NULL,
    sale_date DATE NOT NULL,
    customer_tpin VARCHAR(20) DEFAULT '1000000000',
    customer_name VARCHAR(150) DEFAULT 'Cash Customer',
    customer_email VARCHAR(100) NULL,
    payment_method VARCHAR(20) DEFAULT 'CASH',
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    is_fiscalised TINYINT(1) DEFAULT 0,
    vsdc_rcpt_no VARCHAR(50) NULL,
    vsdc_intrl_data TEXT NULL,
    vsdc_rcpt_sign TEXT NULL,
    vsdc_rcpt_dt VARCHAR(20) NULL,
    vsdc_qr_url TEXT NULL,
    fiscalised_at TIMESTAMP NULL,
    vsdc_error TEXT NULL,
    source VARCHAR(20) DEFAULT 'android',
    android_device_id VARCHAR(100) NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'sale_items', "CREATE TABLE sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    item_id INT NULL,
    item_code VARCHAR(50) NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    tax_ty_cd VARCHAR(10) NOT NULL DEFAULT 'A',
    qty DECIMAL(12,3) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'api_keys', "CREATE TABLE api_keys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    device_info VARCHAR(255) NULL,
    last_used_at TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'webhook_configs', "CREATE TABLE webhook_configs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    secret VARCHAR(255) NULL,
    events JSON NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_triggered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'settings', "CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    display_name VARCHAR(100),
    description TEXT,
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'audit_log', "CREATE TABLE audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    business_id INT NULL,
    action VARCHAR(50) NOT NULL,
    module VARCHAR(50),
    record_id INT NULL,
    description TEXT,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'password_resets', "CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Seeds ──────────────────────────────────────────────────────────────────
run($pdo, 'Seed roles', "INSERT INTO roles (name, display_name, description) VALUES
    ('admin',          'SamPay Admin',   'Full system access — SamPay staff only'),
    ('business_admin', 'Business Admin', 'Manages own business: users, items, sales, audit log'),
    ('business_user',  'Business User',  'View-only access to own business items and sales')");

run($pdo, 'Seed settings', "INSERT INTO settings (setting_key, setting_value, setting_group, display_name, description) VALUES
    ('default_customer_name', 'Cash Customer',  'pos',  'Default Customer Name',   'Used when no customer is captured'),
    ('default_tpin',          '1000000000',     'pos',  'Default Customer TPIN',   'ZRA walk-in customer TPIN'),
    ('vsdc_enabled',          '1',              'vsdc', 'VSDC Fiscalisation',      'Enable ZRA Smart Invoice globally'),
    ('vsdc_sandbox',          '1',              'vsdc', 'Sandbox Mode',            'Use sandbox VSDC endpoint'),
    ('api_rate_limit',        '120',            'api',  'API Rate Limit (req/min)','Max requests per minute per key')");

// Admin user with correct password hash
$hash = password_hash('Admin@1234', PASSWORD_BCRYPT);
$stmt = $pdo->prepare(
    "INSERT INTO users (role_id, first_name, last_name, email, password_hash, is_active, must_change_password)
     VALUES (1, 'System', 'Admin', 'admin@sampay.local', ?, 1, 1)"
);
$stmt->execute([$hash]);
$steps[] = '✅ Admin user seeded (admin@sampay.local / Admin@1234)';

// ── Output ─────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html>
<head>
    <title>SamPay Integrator Setup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-5">
<div class="container" style="max-width:700px">
    <h3 class="fw-bold mb-1"><i>⚡</i> SamPay Integrator Setup</h3>
    <p class="text-muted mb-4">Database: <code><?= htmlspecialchars($name) ?></code></p>

    <?php foreach ($steps as $s): ?>
    <div class="py-1 small"><?= htmlspecialchars($s) ?></div>
    <?php endforeach; ?>

    <?php if ($errors): ?>
    <div class="alert alert-danger mt-3">
        <strong>Errors:</strong>
        <?php foreach ($errors as $e): ?><div class="small"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-success mt-4">
        <strong>✅ Setup complete!</strong><br>
        Login: <code>admin@sampay.local</code> / <code>Admin@1234</code><br>
        You will be prompted to change your password on first login.
    </div>
    <div class="alert alert-warning">
        <strong>⚠️ Delete this file immediately:</strong><br>
        <code>public/setup.php</code>
    </div>
    <a href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>" class="btn btn-primary">
        Go to Login →
    </a>
    <?php endif; ?>
</div>
</body>
</html>
