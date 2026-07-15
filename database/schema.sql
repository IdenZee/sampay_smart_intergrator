-- ============================================================
-- SamPay Integrator — Database Schema
-- Multi-tenant: one installation serves many businesses
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Roles ──────────────────────────────────────────────────────────────
CREATE TABLE roles (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    name         VARCHAR(50)  NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description  TEXT,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. Users (platform staff — not per-business) ──────────────────────────
CREATE TABLE users (
    id                   INT PRIMARY KEY AUTO_INCREMENT,
    role_id              INT          NOT NULL,
    employee_id          VARCHAR(20),
    first_name           VARCHAR(50)  NOT NULL,
    last_name            VARCHAR(50)  NOT NULL,
    email                VARCHAR(100) NOT NULL UNIQUE,
    phone                VARCHAR(20),
    password_hash        VARCHAR(255) NOT NULL,
    is_active            TINYINT(1)   DEFAULT 1,
    must_change_password TINYINT(1)   DEFAULT 0,
    last_login           TIMESTAMP    NULL,
    last_login_ip        VARCHAR(45),
    created_by           INT          NULL,
    created_at           TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id)    REFERENCES roles(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Businesses (SamPay Business Accounts) ──────────────────────────────
CREATE TABLE businesses (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    sampay_business_id  VARCHAR(100) NULL COMMENT 'External ref from SamPay platform',
    name                VARCHAR(150) NOT NULL,
    tpin                VARCHAR(20)  NOT NULL,
    branch_code         VARCHAR(20)  NULL COMMENT 'ZRA branch code for VSDC',
    address             TEXT,
    city                VARCHAR(50),
    country             VARCHAR(50)  DEFAULT 'Zambia',
    phone               VARCHAR(20),
    email               VARCHAR(100),
    currency_code       VARCHAR(5)   DEFAULT 'ZMW',
    is_active           TINYINT(1)   DEFAULT 1,
    created_by          INT          NULL,
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. Business Users (which staff manage which business) ─────────────────
CREATE TABLE business_users (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    user_id     INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_bu (business_id, user_id),
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. VSDC Config (per business) ─────────────────────────────────────────
CREATE TABLE vsdc_config (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    business_id     INT          NOT NULL,
    label           VARCHAR(100) NOT NULL DEFAULT 'Primary VSDC',
    vsdc_url        VARCHAR(255) NOT NULL,
    device_serial   VARCHAR(100),
    tax_office_name VARCHAR(100),
    mrc_no          VARCHAR(100),
    is_active       TINYINT(1)   DEFAULT 1,
    initialized     TINYINT(1)   DEFAULT 0,
    initialized_at  TIMESTAMP    NULL,
    last_std_codes  TIMESTAMP    NULL COMMENT 'Last standard codes fetch',
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 6. ZRA Item Classes (cached from VSDC standard codes) ─────────────────
CREATE TABLE item_classes (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    business_id  INT          NOT NULL,
    cls_code     VARCHAR(20)  NOT NULL,
    cls_name     VARCHAR(150) NOT NULL,
    tax_ty_cd    VARCHAR(10),
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cls (business_id, cls_code),
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 7. Items / Stock Catalogue (per business, generic) ────────────────────
CREATE TABLE items (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    business_id      INT          NOT NULL,
    item_code        VARCHAR(50)  NOT NULL COMMENT 'Your internal code',
    item_cls_code    VARCHAR(20)  NOT NULL COMMENT 'ZRA item class code',
    item_name        VARCHAR(150) NOT NULL,
    orgin_natrs_cd   VARCHAR(10)  DEFAULT 'ZM' COMMENT 'Origin nature code',
    pkg_unit_cd      VARCHAR(10)  DEFAULT 'NT' COMMENT 'Package unit code',
    qty_unit_cd      VARCHAR(10)  DEFAULT 'U'  COMMENT 'Quantity unit code',
    tax_ty_cd        VARCHAR(10)  DEFAULT 'A'  COMMENT 'Tax type: A=16%VAT,B=0%,C=Excise,D=VAT+Excise,E=Zero-rated',
    btch_no          VARCHAR(50)  NULL          COMMENT 'Batch number if applicable',
    bcd              VARCHAR(100) NULL          COMMENT 'Barcode',
    selling_price    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    stock_qty        DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    description      TEXT,
    is_active        TINYINT(1)   DEFAULT 1,
    vsdc_registered  TINYINT(1)   DEFAULT 0,
    vsdc_registered_at TIMESTAMP  NULL,
    created_by       INT          NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_item_code (business_id, item_code),
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 8. Sales / Invoices ───────────────────────────────────────────────────
CREATE TABLE sales (
    id                INT PRIMARY KEY AUTO_INCREMENT,
    business_id       INT          NOT NULL,
    sale_ref          VARCHAR(50)  NOT NULL COMMENT 'Internal invoice number',
    sale_date         DATE         NOT NULL,
    customer_tpin     VARCHAR(20)  DEFAULT '1000000000',
    customer_name     VARCHAR(150) DEFAULT 'Cash Customer',
    customer_email    VARCHAR(100) NULL,
    payment_method    VARCHAR(20)  DEFAULT 'CASH' COMMENT 'CASH,CARD,MOBILE',
    subtotal          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount_amount   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_amount        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_amount      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    -- VSDC fiscalisation
    is_fiscalised     TINYINT(1)   DEFAULT 0,
    vsdc_rcpt_no      VARCHAR(50)  NULL,
    vsdc_intrl_data   TEXT         NULL,
    vsdc_rcpt_sign    TEXT         NULL,
    vsdc_rcpt_dt      VARCHAR(20)  NULL,
    vsdc_qr_url       TEXT         NULL,
    fiscalised_at     TIMESTAMP    NULL,
    vsdc_error        TEXT         NULL COMMENT 'Stored if VSDC call failed',
    -- Source
    source            VARCHAR(20)  DEFAULT 'android' COMMENT 'android|web|api',
    android_device_id VARCHAR(100) NULL,
    created_by        INT          NULL,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 9. Sale Items (line items) ────────────────────────────────────────────
CREATE TABLE sale_items (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    sale_id      INT           NOT NULL,
    item_id      INT           NULL COMMENT 'NULL if item deleted',
    item_code    VARCHAR(50)   NOT NULL,
    item_name    VARCHAR(150)  NOT NULL,
    tax_ty_cd    VARCHAR(10)   NOT NULL DEFAULT 'A',
    qty          DECIMAL(12,3) NOT NULL,
    unit_price   DECIMAL(12,2) NOT NULL,
    discount     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_amount   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 10. API Keys (Android POS authentication) ─────────────────────────────
CREATE TABLE api_keys (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    business_id  INT          NOT NULL,
    key_name     VARCHAR(100) NOT NULL COMMENT 'e.g. POS Terminal 1',
    key_hash     VARCHAR(255) NOT NULL UNIQUE COMMENT 'SHA-256 hash of the raw key',
    device_info  VARCHAR(255) NULL,
    last_used_at TIMESTAMP    NULL,
    is_active    TINYINT(1)   DEFAULT 1,
    created_by   INT          NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 11. Webhook Configs (push signed receipts to SamPay platform) ─────────
CREATE TABLE webhook_configs (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    business_id      INT          NOT NULL,
    url              VARCHAR(255) NOT NULL,
    secret           VARCHAR(255) NULL  COMMENT 'HMAC signing secret',
    events           JSON         NULL  COMMENT '["sale.fiscalised","sale.failed"]',
    is_active        TINYINT(1)   DEFAULT 1,
    last_triggered_at TIMESTAMP   NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 12. Settings (global, no per-business scope needed yet) ───────────────
CREATE TABLE settings (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    setting_key   VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50)  DEFAULT 'general',
    display_name  VARCHAR(100),
    description   TEXT,
    updated_by    INT          NULL,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 13. Audit Log ─────────────────────────────────────────────────────────
CREATE TABLE audit_log (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT          NULL,
    business_id INT          NULL,
    action      VARCHAR(50)  NOT NULL,
    module      VARCHAR(50),
    record_id   INT          NULL,
    description TEXT,
    old_values  JSON         NULL,
    new_values  JSON         NULL,
    ip_address  VARCHAR(45),
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE SET NULL,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 14. Password Resets ───────────────────────────────────────────────────
CREATE TABLE password_resets (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    user_id    INT          NOT NULL,
    token      VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP    NOT NULL,
    used       TINYINT(1)   DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ══════════════════════════════════════════════════════════════════════════
-- SEEDS
-- ══════════════════════════════════════════════════════════════════════════

INSERT INTO roles (name, display_name, description) VALUES
('admin',    'Administrator', 'Full system access — Etheeden/SamPay staff'),
('director', 'Director',      'Read-only overview, audit log access'),
('manager',  'Manager',       'Manage items, view sales for assigned businesses'),
('staff',    'Staff',         'View-only access');

INSERT INTO settings (setting_key, setting_value, setting_group, display_name, description) VALUES
('default_customer_name', 'Cash Customer',  'pos',  'Default Customer Name',  'Used when no customer is captured on POS'),
('default_tpin',          '1000000000',     'pos',  'Default Customer TPIN',  'ZRA walk-in customer TPIN'),
('vsdc_enabled',          '1',              'vsdc', 'VSDC Fiscalisation',     'Enable or disable ZRA Smart Invoice globally'),
('vsdc_sandbox',          '1',              'vsdc', 'Sandbox Mode',           'When enabled, uses sandbox VSDC endpoint'),
('api_rate_limit',        '120',            'api',  'API Rate Limit (req/min)','Max API requests per minute per key');

-- Default super-admin (password: Admin@1234 — MUST CHANGE ON FIRST LOGIN)
INSERT INTO users (role_id, first_name, last_name, email, password_hash, is_active, must_change_password)
VALUES (
    1,
    'System',
    'Admin',
    'admin@sampay.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    1,
    1
);
