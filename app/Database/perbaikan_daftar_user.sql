-- Perbaikan pendaftaran untuk database aplikasi yang sudah berisi data.
-- Pilih database yang dipakai aplikasi di phpMyAdmin, lalu impor file ini.
-- Menambah pilihan peran pengguna tanpa menghapus akun atau transaksi.
-- Boleh diimpor ulang. Tidak memerlukan Composer atau perintah spark.

ALTER TABLE `users`
  MODIFY COLUMN `role`
    ENUM('admin', 'petugas', 'pengguna') NOT NULL DEFAULT 'petugas';
