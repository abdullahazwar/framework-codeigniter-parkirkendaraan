-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2026 at 12:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
--
-- Untuk instalasi baru pada database kosong.
-- Tabel users sudah mendukung peran admin, petugas, dan pengguna.
-- Untuk memperbaiki pendaftaran pada database yang sudah berisi data,
-- impor perbaikan_daftar_user.sql pada database aplikasi yang sama.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `parkir`
--

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plat_nomor` varchar(12) NOT NULL,
  `jenis` enum('motor','mobil') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-10-19-000001', 'App\\Database\\Migrations\\CreateParkirTables', 'default', 'App', 1792405241, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tiket_id` bigint(20) UNSIGNED NOT NULL,
  `petugas_id` bigint(20) UNSIGNED NOT NULL,
  `durasi_menit` int(10) UNSIGNED NOT NULL,
  `total` bigint(20) UNSIGNED NOT NULL,
  `uang_diterima` bigint(20) UNSIGNED NOT NULL,
  `kembalian` bigint(20) UNSIGNED NOT NULL,
  `metode` enum('tunai') NOT NULL DEFAULT 'tunai',
  `waktu_bayar` datetime NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `slot_parkir`
--

CREATE TABLE `slot_parkir` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_slot` varchar(20) NOT NULL,
  `area` varchar(50) NOT NULL,
  `jenis` enum('motor','mobil') NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `slot_parkir`
--

INSERT INTO `slot_parkir` (`id`, `kode_slot`, `area`, `jenis`, `aktif`) VALUES
(1, 'M01', 'Utama', 'motor', 1),
(2, 'M02', 'Utama', 'motor', 1),
(3, 'M03', 'Utama', 'motor', 1),
(4, 'M04', 'Utama', 'motor', 1),
(5, 'M05', 'Utama', 'motor', 1),
(6, 'M06', 'Utama', 'motor', 1),
(7, 'M07', 'Utama', 'motor', 1),
(8, 'M08', 'Utama', 'motor', 1),
(9, 'M09', 'Utama', 'motor', 1),
(10, 'M10', 'Utama', 'motor', 1),
(11, 'M11', 'Utama', 'motor', 1),
(12, 'M12', 'Utama', 'motor', 1),
(13, 'M13', 'Utama', 'motor', 1),
(14, 'M14', 'Utama', 'motor', 1),
(15, 'M15', 'Utama', 'motor', 1),
(16, 'M16', 'Utama', 'motor', 1),
(17, 'M17', 'Utama', 'motor', 1),
(18, 'M18', 'Utama', 'motor', 1),
(19, 'M19', 'Utama', 'motor', 1),
(20, 'M20', 'Utama', 'motor', 1),
(21, 'C01', 'Utama', 'mobil', 1),
(22, 'C02', 'Utama', 'mobil', 1),
(23, 'C03', 'Utama', 'mobil', 1),
(24, 'C04', 'Utama', 'mobil', 1),
(25, 'C05', 'Utama', 'mobil', 1),
(26, 'C06', 'Utama', 'mobil', 1),
(27, 'C07', 'Utama', 'mobil', 1),
(28, 'C08', 'Utama', 'mobil', 1),
(29, 'C09', 'Utama', 'mobil', 1),
(30, 'C10', 'Utama', 'mobil', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tarif`
--

CREATE TABLE `tarif` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jenis` enum('motor','mobil') NOT NULL,
  `tarif_awal` bigint(20) UNSIGNED NOT NULL,
  `tarif_lanjutan` bigint(20) UNSIGNED NOT NULL,
  `updated_at` datetime NOT NULL
) ;

--
-- Dumping data for table `tarif`
--

INSERT INTO `tarif` (`id`, `jenis`, `tarif_awal`, `tarif_lanjutan`, `updated_at`) VALUES
(1, 'motor', 3000, 3000, '2026-10-19 17:20:57'),
(2, 'mobil', 5000, 3000, '2026-10-19 17:20:57');

-- --------------------------------------------------------

--
-- Table structure for table `tiket_parkir`
--

CREATE TABLE `tiket_parkir` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_tiket` varchar(32) NOT NULL,
  `kendaraan_id` bigint(20) UNSIGNED NOT NULL,
  `slot_id` bigint(20) UNSIGNED NOT NULL,
  `petugas_masuk_id` bigint(20) UNSIGNED NOT NULL,
  `waktu_masuk` datetime NOT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `status` enum('aktif','selesai') NOT NULL DEFAULT 'aktif',
  `tarif_awal_snapshot` bigint(20) UNSIGNED NOT NULL,
  `tarif_lanjutan_snapshot` bigint(20) UNSIGNED NOT NULL,
  `slot_aktif_id` bigint(20) UNSIGNED GENERATED ALWAYS AS (case when `status` = 'aktif' then `slot_id` else NULL end) STORED,
  `kendaraan_aktif_id` bigint(20) UNSIGNED GENERATED ALWAYS AS (case when `status` = 'aktif' then `kendaraan_id` else NULL end) STORED
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','petugas','pengguna') NOT NULL DEFAULT 'petugas',
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `auth_version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password_hash`, `role`, `aktif`, `auth_version`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$iF9PLp9Ffu0qfjhQvlGkUe42cSVMtL4DD2GhBcEprifgeFYHvSV5a', 'admin', 1, 1, '2026-10-19 17:20:57', '2026-10-19 17:20:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plat_nomor` (`plat_nomor`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tiket_id` (`tiket_id`),
  ADD KEY `bayar_waktu` (`waktu_bayar`),
  ADD KEY `bayar_petugas` (`petugas_id`);

--
-- Indexes for table `slot_parkir`
--
ALTER TABLE `slot_parkir`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slot_area_unique` (`area`,`kode_slot`),
  ADD KEY `slot_jenis_aktif` (`jenis`,`aktif`);

--
-- Indexes for table `tarif`
--
ALTER TABLE `tarif`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jenis` (`jenis`);

--
-- Indexes for table `tiket_parkir`
--
ALTER TABLE `tiket_parkir`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_tiket` (`kode_tiket`),
  ADD UNIQUE KEY `satu_slot_aktif` (`slot_aktif_id`),
  ADD UNIQUE KEY `satu_kendaraan_aktif` (`kendaraan_aktif_id`),
  ADD KEY `tiket_masuk` (`waktu_masuk`),
  ADD KEY `tiket_status_id` (`status`,`id`),
  ADD KEY `tiket_kendaraan` (`kendaraan_id`),
  ADD KEY `tiket_slot` (`slot_id`),
  ADD KEY `tiket_petugas` (`petugas_masuk_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kendaraan`
--
ALTER TABLE `kendaraan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slot_parkir`
--
ALTER TABLE `slot_parkir`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tarif`
--
ALTER TABLE `tarif`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiket_parkir`
--
ALTER TABLE `tiket_parkir`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `bayar_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bayar_tiket` FOREIGN KEY (`tiket_id`) REFERENCES `tiket_parkir` (`id`);

--
-- Constraints for table `tiket_parkir`
--
ALTER TABLE `tiket_parkir`
  ADD CONSTRAINT `tiket_kendaraan` FOREIGN KEY (`kendaraan_id`) REFERENCES `kendaraan` (`id`),
  ADD CONSTRAINT `tiket_petugas` FOREIGN KEY (`petugas_masuk_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tiket_slot` FOREIGN KEY (`slot_id`) REFERENCES `slot_parkir` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
