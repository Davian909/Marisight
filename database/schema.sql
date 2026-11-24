-- ============================================================================
-- MARISIGHT - Unified Database Schema (Pulau Tunda, Banten)
-- Database tunggal: marisight (menggabungkan DB lama 'asik' & 'pasangsurut')
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `marisight`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `marisight`;

-- ----------------------------------------------------------------------------
-- Tabel Pengguna (Autentikasi Opsional & Akses Generator Admin)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabel Data Pasang Surut Pulau Tunda
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `data_pasut` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tanggal` DATE NOT NULL,
  `jam_pasang` VARCHAR(10) NOT NULL,
  `jam_surut` VARCHAR(10) NOT NULL,
  `tinggi_maks` DECIMAL(5,2) NOT NULL,
  `tinggi_min` DECIMAL(5,2) NOT NULL,
  `amplitudo` DECIMAL(5,2) NOT NULL,
  `intensitas_ikan` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_data_pasut_tanggal` (`tanggal`),
  KEY `idx_data_pasut_tanggal` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Seed Awal Dinamis (15 hari mulai hari ini agar grafik & tabel langsung terisi)
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO `data_pasut`
  (`tanggal`, `jam_pasang`, `jam_surut`, `tinggi_maks`, `tinggi_min`, `amplitudo`, `intensitas_ikan`)
VALUES
  (DATE_ADD(CURDATE(), INTERVAL 0 DAY),  '04:15', '10:40', 1.24, 0.72, 0.52, 'Aktif'),
  (DATE_ADD(CURDATE(), INTERVAL 1 DAY),  '04:55', '11:20', 1.31, 0.68, 0.63, 'Aktif'),
  (DATE_ADD(CURDATE(), INTERVAL 2 DAY),  '05:35', '12:00', 1.36, 0.65, 0.71, 'Aktif'),
  (DATE_ADD(CURDATE(), INTERVAL 3 DAY),  '06:15', '12:45', 1.38, 0.64, 0.74, 'Aktif'),
  (DATE_ADD(CURDATE(), INTERVAL 4 DAY),  '06:55', '13:30', 1.34, 0.69, 0.65, 'Aktif'),
  (DATE_ADD(CURDATE(), INTERVAL 5 DAY),  '07:40', '14:15', 1.26, 0.76, 0.50, 'Aktif'),
  (DATE_ADD(CURDATE(), INTERVAL 6 DAY),  '08:25', '15:00', 1.17, 0.82, 0.35, 'Sedang'),
  (DATE_ADD(CURDATE(), INTERVAL 7 DAY),  '09:10', '15:50', 1.09, 0.86, 0.23, 'Rendah'),
  (DATE_ADD(CURDATE(), INTERVAL 8 DAY),  '09:55', '16:35', 1.05, 0.85, 0.20, 'Rendah'),
  (DATE_ADD(CURDATE(), INTERVAL 9 DAY),  '10:40', '17:20', 1.08, 0.80, 0.28, 'Rendah'),
  (DATE_ADD(CURDATE(), INTERVAL 10 DAY), '11:25', '18:05', 1.15, 0.76, 0.39, 'Sedang'),
  (DATE_ADD(CURDATE(), INTERVAL 11 DAY), '12:10', '18:50', 1.23, 0.71, 0.52, 'Aktif'),
  (DATE_ADD(CURDATE(), INTERVAL 12 DAY), '12:55', '19:35', 1.30, 0.67, 0.63, 'Aktif'),
  (DATE_ADD(CURDATE(), INTERVAL 13 DAY), '13:40', '20:20', 1.35, 0.65, 0.70, 'Aktif'),
  (DATE_ADD(CURDATE(), INTERVAL 14 DAY), '14:25', '21:05', 1.33, 0.68, 0.65, 'Aktif');
