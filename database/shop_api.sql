-- =====================================================================
--  SHOP API - Database MySQL
--  Bài tập lớn: Website bán hàng theo kiến trúc RESTful API (Laravel)
--
--  Cách dùng:
--    1. Import file này vào MySQL (phpMyAdmin / Workbench / dòng lệnh):
--         mysql -u root -p < database/shop_api.sql
--    2. Sửa .env:
--         DB_CONNECTION=mysql
--         DB_HOST=127.0.0.1
--         DB_PORT=3306
--         DB_DATABASE=shop_api
--         DB_USERNAME=root
--         DB_PASSWORD=
--    3. php artisan serve
--
--  Tài khoản mẫu (mật khẩu đều là 123456):
--    admin@example.com     (admin)
--    customer@example.com  (customer)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `shop_api` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `shop_api`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `migrations`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
--  BẢNG HỆ THỐNG CỦA LARAVEL
-- ---------------------------------------------------------------------

CREATE TABLE `migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  NGƯỜI DÙNG + XÁC THỰC (Sanctum)
-- ---------------------------------------------------------------------

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) NULL,
  `address` VARCHAR(255) NULL,
  `role` ENUM('admin','customer') NOT NULL DEFAULT 'customer' COMMENT 'Phân quyền',
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Token đăng nhập của Laravel Sanctum
CREATE TABLE `personal_access_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` TEXT NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `abilities` TEXT NULL,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  NGHIỆP VỤ: DANH MỤC, SẢN PHẨM, ĐƠN HÀNG
-- ---------------------------------------------------------------------

CREATE TABLE `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL COMMENT 'Tên dạng URL, ví dụ: dien-thoai',
  `description` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `stock` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Tồn kho',
  `image` VARCHAR(255) NULL COMMENT 'Đường dẫn ảnh, ví dụ: /images/products/iphone.jpg',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'Đang bán / ngừng bán',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status` ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'pending -> processing -> completed | cancelled',
  `shipping_address` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) NOT NULL,
  `note` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_foreign` (`user_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `price` DECIMAL(12,2) NOT NULL COMMENT 'Giá tại thời điểm mua',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  DỮ LIỆU MẪU
-- =====================================================================

-- Đánh dấu các migration đã chạy để `php artisan migrate` không tạo lại bảng
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2026_11_14_091143_create_personal_access_tokens_table', 1),
('2026_11_14_100000_create_categories_table', 1),
('2026_11_14_100001_create_products_table', 1),
('2026_11_14_100002_create_orders_table', 1);

-- Mật khẩu của cả 2 tài khoản: 123456 (bcrypt)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$12$3M/uBGTnH0uULdPYSIIvQ.GEMcHzOCIe49xPphO8g3tcDyBBuQcgu', '0900000001', 'Hà Nội', 'admin', NOW(), NOW()),
(2, 'Nguyễn Văn A', 'customer@example.com', '$2y$12$3M/uBGTnH0uULdPYSIIvQ.GEMcHzOCIe49xPphO8g3tcDyBBuQcgu', '0900000002', 'TP. Hồ Chí Minh', 'customer', NOW(), NOW());

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Điện thoại', 'dien-thoai', 'Danh mục Điện thoại', NOW(), NOW()),
(2, 'Laptop', 'laptop', 'Danh mục Laptop', NOW(), NOW()),
(3, 'Máy tính bảng', 'may-tinh-bang', 'Danh mục Máy tính bảng', NOW(), NOW()),
(4, 'Phụ kiện', 'phu-kien', 'Danh mục Phụ kiện', NOW(), NOW());

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'iPhone 15', 'Mô tả sản phẩm iPhone 15', 22990000.00, 20, '/images/products/iphone.jpg', 'active', NOW(), NOW()),
(2, 1, 'Samsung Galaxy S24', 'Mô tả sản phẩm Samsung Galaxy S24', 19990000.00, 15, '/images/products/samsung.jpg', 'active', NOW(), NOW()),
(3, 1, 'Xiaomi 14', 'Mô tả sản phẩm Xiaomi 14', 14990000.00, 30, '/images/products/xiaomi.jpg', 'active', NOW(), NOW()),
(4, 2, 'MacBook Air M3', 'Mô tả sản phẩm MacBook Air M3', 27990000.00, 10, NULL, 'active', NOW(), NOW()),
(5, 2, 'Dell XPS 13', 'Mô tả sản phẩm Dell XPS 13', 32990000.00, 8, '/images/products/dell.jpg', 'active', NOW(), NOW()),
(6, 3, 'iPad Air M2', 'Mô tả sản phẩm iPad Air M2', 16990000.00, 12, '/images/products/ipad.jpg', 'active', NOW(), NOW()),
(7, 4, 'Tai nghe AirPods Pro', 'Mô tả sản phẩm Tai nghe AirPods Pro', 5990000.00, 50, '/images/products/airpod.jpg', 'active', NOW(), NOW()),
(8, 4, 'Sạc nhanh 65W', 'Mô tả sản phẩm Sạc nhanh 65W', 490000.00, 100, '/images/products/65w.jpg', 'active', NOW(), NOW()),
(9, 4, 'Chuột không dây', 'Mô tả sản phẩm Chuột không dây', 350000.00, 0, NULL, 'inactive', NOW(), NOW());

-- Một đơn hàng mẫu đã hoàn thành (để trang thống kê có dữ liệu)
INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_address`, `phone`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 51970000.00, 'completed', '123 Lê Lợi, Quận 1, TP.HCM', '0912345678', 'Giao giờ hành chính', NOW(), NOW());

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 22990000.00, NOW(), NOW()),
(2, 1, 7, 1, 5990000.00, NOW(), NOW());
