-- ========================================================
-- FASTENDER - PLATFORM E-COMMERCE PRE-ORDER APPAREL
-- Skema Database MySQL (database.sql)
-- Untuk phpMyAdmin / XAMPP / Laragon (MySQL / MariaDB)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `fastender_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fastender_db`;

-- 1. TABEL PENGGUNA & ADMINISTRATOR
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Akun Default Admin: admin@fastender.id / admin123
INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
('Super Administrator', 'admin@fastender.id', '$2y$10$wO3h6/y0WcWqL12E.oA0v.pT7oO9eX7D/o6Jd4k3R98/Y8d7hT.e.', 'admin');

-- 2. TABEL PRODUK PRE-ORDER (PO)
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(50) NOT NULL,
  `category_name` VARCHAR(100) NOT NULL,
  `price` INT NOT NULL,
  `dp_price` INT NOT NULL,
  `quota_current` INT DEFAULT 0,
  `quota_target` INT DEFAULT 100,
  `badge` VARCHAR(50) DEFAULT 'PO Baru',
  `batch` VARCHAR(100) DEFAULT 'Batch 1',
  `description` TEXT DEFAULT NULL,
  `specs` TEXT DEFAULT NULL,
  `sizes` VARCHAR(255) DEFAULT 'S, M, L, XL, XXL, 3XL',
  `colors` VARCHAR(255) DEFAULT 'Navy, Hitam, Maroon, Putih',
  `icon_type` VARCHAR(50) DEFAULT 'varsity',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TABEL MULTI-FOTO PRODUK
CREATE TABLE `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_product_images` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABEL PESANAN PRE-ORDER
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `customer_email` VARCHAR(100) NOT NULL,
  `organization` VARCHAR(150) DEFAULT NULL,
  `shipping_address` TEXT NOT NULL,
  `province` VARCHAR(100) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `courier` VARCHAR(50) NOT NULL,
  `payment_scheme` ENUM('dp', 'full') DEFAULT 'dp',
  `subtotal` INT NOT NULL,
  `shipping_cost` INT NOT NULL DEFAULT 15000,
  `total_price` INT NOT NULL,
  `dp_amount` INT NOT NULL,
  `payment_status` VARCHAR(50) DEFAULT 'Menunggu Verifikasi DP',
  `production_status` VARCHAR(50) DEFAULT 'Verifikasi Pesanan',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TABEL RINCIAN ITEM PESANAN
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `size` VARCHAR(20) NOT NULL,
  `color` VARCHAR(50) NOT NULL,
  `custom_text` VARCHAR(100) DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` INT NOT NULL,
  `subtotal` INT NOT NULL,
  CONSTRAINT `fk_order_items` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. TABEL PENGATURAN SITUS & HERO BANNER
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Pengaturan Hero Section
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('hero_title', 'Pre-Order Batch 1 telah dibuka'),
('hero_badge', 'BATCH PERDANA TAHUN AKADEMIK 2026'),
('hero_description', 'Wujudkan identitas kebanggaan kelas, jurusan, dan organisasimu bersama Fastender. Bahan standar distro, gratis konsultasi desain bordir, sistem DP 50%, dan jaminan tepat waktu.'),
('hero_countdown_label', 'PO DITUTUP DALAM'),
('hero_countdown_deadline', '2026-10-25 23:59:59'),
('hero_banner_image', 'assets/images/hero-showcase.png'),
('hero_cta_text', 'Pesan Sekarang'),
('hero_cta_link', 'katalog.php');

