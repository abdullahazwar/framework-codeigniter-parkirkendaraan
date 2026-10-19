-- Satu database untuk halaman admin, petugas, dan pengguna parkir.
-- Pilih database aplikasi di phpMyAdmin, lalu impor parkir.sql.
-- Berlaku untuk instalasi baru dan pembaruan database proyek ini.
-- Boleh diimpor ulang. Tidak menghapus data atau menimpa akun/tarif/slot lama.
-- Data awal hanya ditambahkan jika seluruh tabel aplikasi masih kosong.
-- Memerlukan MySQL 8.0.16+ atau MariaDB 10.4+ dengan InnoDB.

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','petugas','pengguna') NOT NULL DEFAULT 'petugas',
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    auth_version INT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kendaraan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plat_nomor VARCHAR(12) NOT NULL UNIQUE,
    jenis ENUM('motor','mobil') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS slot_parkir (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_slot VARCHAR(20) NOT NULL,
    area VARCHAR(50) NOT NULL,
    jenis ENUM('motor','mobil') NOT NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY slot_area_unique (area, kode_slot),
    KEY slot_jenis_aktif (jenis, aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tarif (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jenis ENUM('motor','mobil') NOT NULL UNIQUE,
    tarif_awal BIGINT UNSIGNED NOT NULL,
    tarif_lanjutan BIGINT UNSIGNED NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT tarif_max CHECK (tarif_awal <= 1000000000 AND tarif_lanjutan <= 1000000000),
    CONSTRAINT tarif_motor_sama CHECK (jenis <> 'motor' OR tarif_awal = tarif_lanjutan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tiket_parkir (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_tiket VARCHAR(32) NOT NULL UNIQUE,
    kendaraan_id BIGINT UNSIGNED NOT NULL,
    slot_id BIGINT UNSIGNED NOT NULL,
    petugas_masuk_id BIGINT UNSIGNED NOT NULL,
    waktu_masuk DATETIME NOT NULL,
    waktu_keluar DATETIME NULL,
    status ENUM('aktif','selesai') NOT NULL DEFAULT 'aktif',
    tarif_awal_snapshot BIGINT UNSIGNED NOT NULL,
    tarif_lanjutan_snapshot BIGINT UNSIGNED NOT NULL,
    slot_aktif_id BIGINT UNSIGNED GENERATED ALWAYS AS
        (CASE WHEN status = 'aktif' THEN slot_id ELSE NULL END) STORED,
    kendaraan_aktif_id BIGINT UNSIGNED GENERATED ALWAYS AS
        (CASE WHEN status = 'aktif' THEN kendaraan_id ELSE NULL END) STORED,
    UNIQUE KEY satu_slot_aktif (slot_aktif_id),
    UNIQUE KEY satu_kendaraan_aktif (kendaraan_aktif_id),
    KEY tiket_masuk (waktu_masuk),
    KEY tiket_status_id (status, id),
    CONSTRAINT tiket_kendaraan FOREIGN KEY (kendaraan_id) REFERENCES kendaraan(id),
    CONSTRAINT tiket_slot FOREIGN KEY (slot_id) REFERENCES slot_parkir(id),
    CONSTRAINT tiket_petugas FOREIGN KEY (petugas_masuk_id) REFERENCES users(id),
    CONSTRAINT tiket_waktu CHECK (
        (status = 'aktif' AND waktu_keluar IS NULL) OR
        (status = 'selesai' AND waktu_keluar >= waktu_masuk)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pembayaran (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tiket_id BIGINT UNSIGNED NOT NULL UNIQUE,
    petugas_id BIGINT UNSIGNED NOT NULL,
    durasi_menit INT UNSIGNED NOT NULL,
    total BIGINT UNSIGNED NOT NULL,
    uang_diterima BIGINT UNSIGNED NOT NULL,
    kembalian BIGINT UNSIGNED NOT NULL,
    metode ENUM('tunai') NOT NULL DEFAULT 'tunai',
    waktu_bayar DATETIME NOT NULL,
    KEY bayar_waktu (waktu_bayar),
    CONSTRAINT bayar_tiket FOREIGN KEY (tiket_id) REFERENCES tiket_parkir(id),
    CONSTRAINT bayar_petugas FOREIGN KEY (petugas_id) REFERENCES users(id),
    CONSTRAINT bayar_uang CHECK (uang_diterima >= total AND kembalian = uang_diterima - total)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BEGIN PENGGUNA
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
-- END PENGGUNA

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(255) NOT NULL,
  `class` VARCHAR(255) NOT NULL,
  `group` VARCHAR(255) NOT NULL,
  `namespace` VARCHAR(255) NOT NULL,
  `time` INT NOT NULL,
  `batch` INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Isi awal hanya untuk instalasi yang belum memiliki data aplikasi.
START TRANSACTION;

SET @parkir_isi_awal :=
  NOT EXISTS (SELECT 1 FROM users LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM kendaraan LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM slot_parkir LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM tarif LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM tiket_parkir LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM pembayaran LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM kendaraan_pengguna LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM permintaan_parkir LIMIT 1);

-- Akun admin mengikuti data awal yang sebelumnya disertakan proyek.
INSERT INTO users (nama, username, password_hash, role, aktif, auth_version, created_at, updated_at)
SELECT 'Administrator', 'admin', '$2y$10$iF9PLp9Ffu0qfjhQvlGkUe42cSVMtL4DD2GhBcEprifgeFYHvSV5a', 'admin', 1, 1, NOW(), NOW()
WHERE @parkir_isi_awal;

INSERT INTO slot_parkir (kode_slot, area, jenis, aktif)
SELECT awal.kode_slot, awal.area, awal.jenis, awal.aktif
FROM (
  SELECT 'M01' AS kode_slot, 'Utama' AS area, 'motor' AS jenis, 1 AS aktif
UNION ALL SELECT 'M02', 'Utama', 'motor', 1
UNION ALL SELECT 'M03', 'Utama', 'motor', 1
UNION ALL SELECT 'M04', 'Utama', 'motor', 1
UNION ALL SELECT 'M05', 'Utama', 'motor', 1
UNION ALL SELECT 'M06', 'Utama', 'motor', 1
UNION ALL SELECT 'M07', 'Utama', 'motor', 1
UNION ALL SELECT 'M08', 'Utama', 'motor', 1
UNION ALL SELECT 'M09', 'Utama', 'motor', 1
UNION ALL SELECT 'M10', 'Utama', 'motor', 1
UNION ALL SELECT 'M11', 'Utama', 'motor', 1
UNION ALL SELECT 'M12', 'Utama', 'motor', 1
UNION ALL SELECT 'M13', 'Utama', 'motor', 1
UNION ALL SELECT 'M14', 'Utama', 'motor', 1
UNION ALL SELECT 'M15', 'Utama', 'motor', 1
UNION ALL SELECT 'M16', 'Utama', 'motor', 1
UNION ALL SELECT 'M17', 'Utama', 'motor', 1
UNION ALL SELECT 'M18', 'Utama', 'motor', 1
UNION ALL SELECT 'M19', 'Utama', 'motor', 1
UNION ALL SELECT 'M20', 'Utama', 'motor', 1
UNION ALL SELECT 'C01', 'Utama', 'mobil', 1
UNION ALL SELECT 'C02', 'Utama', 'mobil', 1
UNION ALL SELECT 'C03', 'Utama', 'mobil', 1
UNION ALL SELECT 'C04', 'Utama', 'mobil', 1
UNION ALL SELECT 'C05', 'Utama', 'mobil', 1
UNION ALL SELECT 'C06', 'Utama', 'mobil', 1
UNION ALL SELECT 'C07', 'Utama', 'mobil', 1
UNION ALL SELECT 'C08', 'Utama', 'mobil', 1
UNION ALL SELECT 'C09', 'Utama', 'mobil', 1
UNION ALL SELECT 'C10', 'Utama', 'mobil', 1
) AS awal
WHERE @parkir_isi_awal;

INSERT INTO tarif (jenis, tarif_awal, tarif_lanjutan, updated_at)
SELECT awal.jenis, awal.tarif_awal, awal.tarif_lanjutan, NOW()
FROM (
  SELECT 'motor' AS jenis, 3000 AS tarif_awal, 3000 AS tarif_lanjutan
  UNION ALL SELECT 'mobil', 5000, 3000
) AS awal
WHERE @parkir_isi_awal;

-- Catat migration yang sudah dipenuhi oleh impor SQL tanpa menggandakan riwayat.
SET @parkir_grup_migrasi := COALESCE(
  (SELECT `group` FROM migrations WHERE namespace = 'App' ORDER BY id LIMIT 1),
  'default'
);
SET @parkir_batch := (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);

INSERT INTO migrations (version, class, `group`, namespace, time, batch)
SELECT '2026-10-19-000001',
  CONCAT('App', CHAR(92), 'Database', CHAR(92), 'Migrations', CHAR(92), 'CreateParkirTables'),
  @parkir_grup_migrasi, 'App', UNIX_TIMESTAMP(), @parkir_batch
WHERE NOT EXISTS (
  SELECT 1 FROM migrations
  WHERE version = '2026-10-19-000001' AND namespace = 'App'
    AND CAST(`group` AS BINARY) = CAST(@parkir_grup_migrasi AS BINARY)
);

INSERT INTO migrations (version, class, `group`, namespace, time, batch)
SELECT '2026-10-19-000002',
  CONCAT('App', CHAR(92), 'Database', CHAR(92), 'Migrations', CHAR(92), 'AddPenggunaRole'),
  @parkir_grup_migrasi, 'App', UNIX_TIMESTAMP(), @parkir_batch
WHERE NOT EXISTS (
  SELECT 1 FROM migrations
  WHERE version = '2026-10-19-000002' AND namespace = 'App'
    AND CAST(`group` AS BINARY) = CAST(@parkir_grup_migrasi AS BINARY)
);

INSERT INTO migrations (version, class, `group`, namespace, time, batch)
SELECT '2026-10-19-000003',
  CONCAT('App', CHAR(92), 'Database', CHAR(92), 'Migrations', CHAR(92), 'CreatePenggunaParkirTables'),
  @parkir_grup_migrasi, 'App', UNIX_TIMESTAMP(), @parkir_batch
WHERE NOT EXISTS (
  SELECT 1 FROM migrations
  WHERE version = '2026-10-19-000003' AND namespace = 'App'
    AND CAST(`group` AS BINARY) = CAST(@parkir_grup_migrasi AS BINARY)
);

COMMIT;
