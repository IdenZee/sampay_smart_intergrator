-- ============================================================
-- SamPay Integrator — Fresh Migration
-- DROPS and recreates all tables. Run this once to reset.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS webhook_configs;
DROP TABLE IF EXISTS api_keys;
DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS item_classes;
DROP TABLE IF EXISTS vsdc_config;
DROP TABLE IF EXISTS business_users;
DROP TABLE IF EXISTS businesses;
DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS api_tokens;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS company;
DROP TABLE IF EXISTS branches;

SET FOREIGN_KEY_CHECKS = 1;

-- Now run schema.sql
SOURCE schema.sql;
