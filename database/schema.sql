-- Zorvex Database Schema v1.0.0
-- High Performance, UTF8mb4 Unicode, Normalized Structure

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY,
  `username` VARCHAR(64) NULL,
  `first_name` VARCHAR(128) NULL,
  `phone` VARCHAR(32) NULL,
  `balance` BIGINT NOT NULL DEFAULT 0,
  `step` VARCHAR(128) NOT NULL DEFAULT 'none',
  `step_data` TEXT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `is_reseller` TINYINT(1) NOT NULL DEFAULT 0,
  `reseller_discount_pct` INT NOT NULL DEFAULT 0,
  `invited_by` BIGINT UNSIGNED NULL,
  `score` INT NOT NULL DEFAULT 0,
  `test_service_claimed` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active', 'banned') NOT NULL DEFAULT 'active',
  `created_at` INT UNSIGNED NOT NULL,
  `updated_at` INT UNSIGNED NOT NULL,
  INDEX `idx_users_invited_by` (`invited_by`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Panels Table
CREATE TABLE IF NOT EXISTS `panels` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('marzban', 'marzneshin', 'xui', 'hiddify', 'wireguard', 'mikrotik') NOT NULL DEFAULT 'marzban',
  `url` VARCHAR(255) NOT NULL,
  `username` VARCHAR(100) NULL,
  `password` VARCHAR(255) NULL,
  `token` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sub_url_template` VARCHAR(255) NULL,
  `extra_config` JSON NULL,
  `created_at` INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `icon` VARCHAR(32) DEFAULT '⚡',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NULL,
  `panel_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `traffic_gb` INT NOT NULL DEFAULT 30,
  `duration_days` INT NOT NULL DEFAULT 30,
  `price` BIGINT NOT NULL DEFAULT 100000,
  `is_test` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL,
  INDEX `idx_products_cat` (`category_id`),
  INDEX `idx_products_panel` (`panel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Services Table (Client VPN accounts)
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NULL,
  `panel_id` INT UNSIGNED NOT NULL,
  `service_username` VARCHAR(100) NOT NULL,
  `sub_id` VARCHAR(64) NOT NULL UNIQUE,
  `traffic_total_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `traffic_used_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `expire_date` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('active', 'expired', 'disabled', 'on_hold') NOT NULL DEFAULT 'active',
  `subscription_url` TEXT NULL,
  `configs_cache` MEDIUMTEXT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  `updated_at` INT UNSIGNED NOT NULL,
  INDEX `idx_services_user` (`user_id`),
  INDEX `idx_services_username` (`service_username`),
  INDEX `idx_services_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(32) NOT NULL UNIQUE,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NULL,
  `service_id` INT UNSIGNED NULL,
  `type` ENUM('new', 'renew', 'extra_traffic', 'wallet_charge') NOT NULL DEFAULT 'new',
  `amount` BIGINT NOT NULL,
  `discount_amount` BIGINT NOT NULL DEFAULT 0,
  `final_amount` BIGINT NOT NULL,
  `gateway` ENUM('card', 'zarinpal', 'nowpayments', 'aqayepardakht', 'wallet') NOT NULL DEFAULT 'wallet',
  `status` ENUM('pending', 'waiting_approval', 'paid', 'rejected', 'canceled') NOT NULL DEFAULT 'pending',
  `receipt_image` VARCHAR(255) NULL,
  `receipt_ref` VARCHAR(100) NULL,
  `admin_note` TEXT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  `updated_at` INT UNSIGNED NOT NULL,
  INDEX `idx_orders_user` (`user_id`),
  INDEX `idx_orders_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Transactions Table
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED NULL,
  `type` ENUM('deposit', 'purchase', 'refund', 'commission', 'bonus') NOT NULL,
  `amount` BIGINT NOT NULL,
  `balance_after` BIGINT NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  INDEX `idx_trans_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Coupons Table
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_pct` INT NOT NULL DEFAULT 10,
  `max_discount` BIGINT NOT NULL DEFAULT 0,
  `usage_limit` INT NOT NULL DEFAULT 100,
  `used_count` INT NOT NULL DEFAULT 0,
  `expire_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bank Cards Table (Card-to-card rotation)
CREATE TABLE IF NOT EXISTS `cards` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `card_number` VARCHAR(32) NOT NULL,
  `holder_name` VARCHAR(100) NOT NULL,
  `bank_name` VARCHAR(50) NULL,
  `daily_limit` BIGINT NOT NULL DEFAULT 50000000,
  `current_daily` BIGINT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Support Tickets & Messages
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `status` ENUM('open', 'answered', 'closed') NOT NULL DEFAULT 'open',
  `created_at` INT UNSIGNED NOT NULL,
  `updated_at` INT UNSIGNED NOT NULL,
  INDEX `idx_tickets_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ticket_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT UNSIGNED NOT NULL,
  `sender_type` ENUM('user', 'admin') NOT NULL,
  `sender_id` BIGINT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `file_id` VARCHAR(255) NULL,
  `created_at` INT UNSIGNED NOT NULL,
  INDEX `idx_ticket_msg` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Settings Key-Value Store
CREATE TABLE IF NOT EXISTS `settings` (
  `key_name` VARCHAR(100) NOT NULL PRIMARY KEY,
  `key_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Settings Seeding
INSERT IGNORE INTO `settings` (`key_name`, `key_value`) VALUES
('bot_status', 'online'),
('trial_enabled', '1'),
('trial_traffic_gb', '2'),
('trial_days', '1'),
('affiliate_percent', '15'),
('min_payout_amount', '50000'),
('card_to_card_enabled', '1'),
('zarinpal_enabled', '0'),
('zarinpal_merchant', ''),
('nowpayments_enabled', '0'),
('nowpayments_api_key', ''),
('support_text', 'جهت دریافت پشتیبانی از دکمه‌های زیر استفاده کنید.'),
('help_text', 'راهنمای اتصال به سرویس‌ها:\nبرای نرم‌افزارهای V2rayN، V2rayNG، Streisand و Sing-box لینک هوشمند اشتراک را در نرم‌افزار کپی کرده و Import کنید.'),
('welcome_message', 'سلام :name عزیز! 🌟\nبه ربات هوشمند مدیریت و خرید سرویس **Zorvex** خوش آمدید.');

SET FOREIGN_KEY_CHECKS = 1;
