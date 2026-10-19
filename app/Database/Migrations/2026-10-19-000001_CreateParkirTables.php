<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

final class CreateParkirTables extends Migration
{
    public function up()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            throw new RuntimeException('Proyek ini memerlukan MySQL 8.0.16+ atau MariaDB 10.4+.');
        }
        $version = $this->db->query('SELECT VERSION() AS version')->getRowArray()['version'];
        $maria = stripos($version, 'MariaDB') !== false;
        preg_match($maria ? '/(\d+\.\d+\.\d+)-MariaDB/i' : '/^(\d+\.\d+\.\d+)/', $version, $match);
        if (
            PHP_INT_SIZE < 8 ||
            !isset($match[1]) ||
            version_compare($match[1], $maria ? '10.4.0' : '8.0.16', '<')
        ) {
            throw new RuntimeException('Gunakan PHP 64 bit dan MySQL 8.0.16+ atau MariaDB 10.4+.');
        }

        $tables = [
            "CREATE TABLE users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nama VARCHAR(100) NOT NULL,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('admin','petugas') NOT NULL DEFAULT 'petugas',
                aktif TINYINT(1) NOT NULL DEFAULT 1,
                auth_version INT UNSIGNED NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE kendaraan (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                plat_nomor VARCHAR(12) NOT NULL UNIQUE,
                jenis ENUM('motor','mobil') NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE slot_parkir (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                kode_slot VARCHAR(20) NOT NULL,
                area VARCHAR(50) NOT NULL,
                jenis ENUM('motor','mobil') NOT NULL,
                aktif TINYINT(1) NOT NULL DEFAULT 1,
                UNIQUE KEY slot_area_unique (area, kode_slot),
                KEY slot_jenis_aktif (jenis, aktif)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE tarif (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                jenis ENUM('motor','mobil') NOT NULL UNIQUE,
                tarif_awal BIGINT UNSIGNED NOT NULL,
                tarif_lanjutan BIGINT UNSIGNED NOT NULL,
                updated_at DATETIME NOT NULL,
                CONSTRAINT tarif_max CHECK (tarif_awal <= 1000000000 AND tarif_lanjutan <= 1000000000),
                CONSTRAINT tarif_motor_sama CHECK (jenis <> 'motor' OR tarif_awal = tarif_lanjutan)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE tiket_parkir (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE pembayaran (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($tables as $sql) {
            $this->db->query($sql);
        }
    }

    public function down()
    {
        foreach (
            ['pembayaran', 'tiket_parkir', 'tarif', 'slot_parkir', 'kendaraan', 'users']
            as $table
        ) {
            $this->forge->dropTable($table, true);
        }
    }
}
