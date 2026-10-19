-- Pilih database aplikasi di phpMyAdmin lalu impor file ini.
-- Memperbarui peran dan menambah tabel pengguna tanpa menghapus data lama.
-- Boleh diimpor ulang. Tidak memerlukan Composer.

ALTER TABLE `users`
  MODIFY COLUMN `role`
    ENUM('admin', 'petugas', 'pengguna') NOT NULL DEFAULT 'petugas';

CREATE TABLE IF NOT EXISTS `kendaraan_pengguna` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pengguna_id` BIGINT UNSIGNED NOT NULL,
  `kendaraan_id` BIGINT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `kp_satu_akun_per_kendaraan` (`kendaraan_id`),
  KEY `kp_pengguna` (`pengguna_id`),
  CONSTRAINT `kp_user_fk` FOREIGN KEY (`pengguna_id`) REFERENCES `users` (`id`),
  CONSTRAINT `kp_kendaraan_fk` FOREIGN KEY (`kendaraan_id`) REFERENCES `kendaraan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permintaan_parkir` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kode_permintaan` VARCHAR(32) NOT NULL,
  `pengguna_id` BIGINT UNSIGNED NOT NULL,
  `kendaraan_id` BIGINT UNSIGNED NOT NULL,
  `tiket_id` BIGINT UNSIGNED NULL,
  `status` ENUM('menunggu','aktif','minta_keluar','selesai','dibatalkan','ditolak')
    NOT NULL DEFAULT 'menunggu',
  `alasan_penolakan` VARCHAR(200) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `waktu_minta_keluar` DATETIME NULL,
  `kendaraan_terbuka_id` BIGINT UNSIGNED GENERATED ALWAYS AS (
    CASE WHEN `status` IN ('menunggu','aktif','minta_keluar')
      THEN `kendaraan_id` ELSE NULL END
  ) STORED,
  UNIQUE KEY `pp_kode_unik` (`kode_permintaan`),
  UNIQUE KEY `pp_tiket_unik` (`tiket_id`),
  UNIQUE KEY `pp_satu_permintaan_terbuka` (`kendaraan_terbuka_id`),
  KEY `pp_pengguna_status` (`pengguna_id`, `status`, `id`),
  KEY `pp_antrean` (`status`, `id`),
  CONSTRAINT `pp_user_fk` FOREIGN KEY (`pengguna_id`) REFERENCES `users` (`id`),
  CONSTRAINT `pp_kendaraan_fk` FOREIGN KEY (`kendaraan_id`) REFERENCES `kendaraan` (`id`),
  CONSTRAINT `pp_tiket_fk` FOREIGN KEY (`tiket_id`) REFERENCES `tiket_parkir` (`id`),
  CONSTRAINT `pp_status_tiket` CHECK (
    (`status` IN ('menunggu','dibatalkan','ditolak') AND `tiket_id` IS NULL)
    OR (`status` IN ('aktif','minta_keluar','selesai') AND `tiket_id` IS NOT NULL)
  )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
