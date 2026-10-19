<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

final class AddPenggunaRole extends Migration
{
    public function up()
    {
        $this->db->query(
            "ALTER TABLE users MODIFY role ENUM('admin','petugas','pengguna') NOT NULL DEFAULT 'petugas'",
        );
    }

    public function down()
    {
        if ($this->db->table('users')->where('role', 'pengguna')->countAllResults() > 0) {
            throw new RuntimeException('Rollback dibatalkan agar akun pengguna tetap tersimpan.');
        }

        $this->db->query(
            "ALTER TABLE users MODIFY role ENUM('admin','petugas') NOT NULL DEFAULT 'petugas'",
        );
    }
}
