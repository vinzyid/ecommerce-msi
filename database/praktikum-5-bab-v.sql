-- =====================================================================
-- PRAKTIKUM 5 - MANAJEMEN SISTEM INFORMASI
-- Rafi Pandya Prabowo / 24051130076 / Teknologi Informasi J
-- Database: ecommerce
--
-- Jalankan per-bagian di MySQL Workbench, screenshot tiap hasilnya
-- sesuai nomor bagian pada template laporan.
-- =====================================================================

USE ecommerce;


-- =====================================================================
-- BAGIAN 1  ->  SCREENSHOT 1
-- Script SQL Pembuatan Tabel brands
-- =====================================================================

CREATE TABLE IF NOT EXISTS `brands` (
  `brand_id`    INT         NOT NULL AUTO_INCREMENT,
  `brand_title` VARCHAR(80) NOT NULL,
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `brands` (`brand_id`, `brand_title`) VALUES
(1, 'HP'),
(2, 'Samsung'),
(3, 'Apple'),
(4, 'motorolla'),
(5, 'LG'),
(6, 'Cloth Brand');

SELECT * FROM `brands`;


-- =====================================================================
-- BAGIAN 2  ->  SCREENSHOT 2
-- Script SQL Relasi Produk ke Brand
-- =====================================================================

ALTER TABLE `products`
  ADD COLUMN `brand_id` INT NULL AFTER `category_id`;

ALTER TABLE `products`
  ADD INDEX `products_brand_id_index` (`brand_id`);

ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign`
  FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

-- Isi brand_id untuk 12 produk yang sudah ada
UPDATE `products` SET `brand_id` = 6 WHERE `sku` IN ('HRN-001','HRN-002','HRN-003');
UPDATE `products` SET `brand_id` = 5 WHERE `sku` IN ('RMH-001','RMH-002','RMH-003');
UPDATE `products` SET `brand_id` = 1 WHERE `sku` IN ('KRJ-001','KRJ-002','KRJ-003');
UPDATE `products` SET `brand_id` = 2 WHERE `sku` IN ('HDH-001','HDH-002','HDH-003');


-- =====================================================================
-- BAGIAN 3  ->  SCREENSHOT 3
-- Script SQL Pembuatan Tabel logs
-- =====================================================================

CREATE TABLE IF NOT EXISTS `logs` (
  `id`      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED     NULL,
  `action`  VARCHAR(100)    NOT NULL,
  `date`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `logs_user_id_index` (`user_id`),
  INDEX `logs_date_index` (`date`),
  CONSTRAINT `logs_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- BAGIAN 4  ->  SCREENSHOT 4
-- Verifikasi Struktur Tabel
-- =====================================================================

SHOW TABLES;

DESCRIBE `products`;

-- Daftar foreign key pada tabel products
SELECT
  `CONSTRAINT_NAME`        AS `nama_constraint`,
  `COLUMN_NAME`            AS `kolom`,
  `REFERENCED_TABLE_NAME`  AS `tabel_referensi`,
  `REFERENCED_COLUMN_NAME` AS `kolom_referensi`
FROM `information_schema`.`KEY_COLUMN_USAGE`
WHERE `TABLE_SCHEMA` = 'ecommerce'
  AND `TABLE_NAME`   = 'products'
  AND `REFERENCED_TABLE_NAME` IS NOT NULL;


-- =====================================================================
-- BAGIAN 5  ->  SCREENSHOT 5
-- Pengujian Relasi Produk - Brand (JOIN)
-- =====================================================================

SELECT
  p.`id`          AS `ID Produk`,
  p.`name`        AS `Nama Produk`,
  p.`sku`         AS `SKU`,
  b.`brand_title` AS `Brand`,
  c.`name`        AS `Kategori`,
  p.`price`       AS `Harga`,
  p.`stock`       AS `Stok`
FROM `products` p
LEFT JOIN `brands`     b ON p.`brand_id`    = b.`brand_id`
LEFT JOIN `categories` c ON p.`category_id` = c.`id`
ORDER BY b.`brand_title`, p.`name`;

-- Rekap jumlah produk per brand
SELECT
  b.`brand_title`  AS `Brand`,
  COUNT(p.`id`)    AS `Jumlah Produk`
FROM `brands` b
LEFT JOIN `products` p ON p.`brand_id` = b.`brand_id`
GROUP BY b.`brand_id`, b.`brand_title`
ORDER BY `Jumlah Produk` DESC;


-- =====================================================================
-- BAGIAN 6  ->  SCREENSHOT 6
-- Pengujian Tabel logs
-- =====================================================================

INSERT INTO `logs` (`user_id`, `action`, `date`) VALUES
(1, 'login',            '2026-01-15 08:12:45'),
(1, 'create_product',   '2026-01-15 08:30:10'),
(1, 'update_stock',     '2026-01-15 09:05:33'),
(1, 'create_category',  '2026-01-15 09:41:02'),
(1, 'update_product',   '2026-01-15 10:17:58'),
(1, 'logout',           '2026-01-15 11:02:20');

SELECT * FROM `logs`;

-- Log digabung dengan data user
SELECT
  l.`id`       AS `ID Log`,
  u.`username` AS `User`,
  l.`action`   AS `Aktivitas`,
  l.`date`     AS `Waktu`
FROM `logs` l
LEFT JOIN `users` u ON l.`user_id` = u.`id`
ORDER BY l.`date` DESC;


-- =====================================================================
-- BAGIAN 7  ->  SCREENSHOT 7
-- Data pendukung ERD (diagram dibuat lewat Database > Reverse Engineer)
-- =====================================================================

SELECT
  `TABLE_NAME`             AS `Tabel`,
  `COLUMN_NAME`            AS `Kolom`,
  `CONSTRAINT_NAME`        AS `Constraint`,
  `REFERENCED_TABLE_NAME`  AS `Tabel Tujuan`,
  `REFERENCED_COLUMN_NAME` AS `Kolom Tujuan`
FROM `information_schema`.`KEY_COLUMN_USAGE`
WHERE `TABLE_SCHEMA` = 'ecommerce'
  AND `REFERENCED_TABLE_NAME` IS NOT NULL
ORDER BY `TABLE_NAME`, `CONSTRAINT_NAME`;


-- =====================================================================
-- TAMBAHAN (opsional) - Stored Procedure getcat dari dump Bab V
-- =====================================================================

DROP PROCEDURE IF EXISTS `getcat`;

DELIMITER $$
CREATE PROCEDURE `getcat` (IN `cid` INT)
BEGIN
  SELECT * FROM `categories` WHERE `id` = cid;
END$$
DELIMITER ;

CALL `getcat`(1);


-- =====================================================================
-- ROLLBACK - jalankan hanya jika perlu mengulang dari awal
-- =====================================================================
-- DROP TABLE IF EXISTS `logs`;
-- ALTER TABLE `products` DROP FOREIGN KEY `products_brand_id_foreign`;
-- ALTER TABLE `products` DROP INDEX `products_brand_id_index`;
-- ALTER TABLE `products` DROP COLUMN `brand_id`;
-- DROP PROCEDURE IF EXISTS `getcat`;
