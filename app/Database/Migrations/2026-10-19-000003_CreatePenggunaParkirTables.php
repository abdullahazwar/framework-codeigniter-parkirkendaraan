<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

final class CreatePenggunaParkirTables extends Migration
{
    public function up()
    {
        $sql = file_get_contents(APPPATH . 'Database/parkir.sql');
        if ($sql === false) {
            throw new RuntimeException('SQL parkir tidak ditemukan.');
        }
        if (!preg_match('/-- BEGIN PENGGUNA\R(.*?)-- END PENGGUNA/s', $sql, $bagian)) {
            throw new RuntimeException('Bagian tabel pengguna tidak ditemukan dalam parkir.sql.');
        }
        foreach (explode(';', $bagian[1]) as $statement) {
            if (trim($statement) !== '') {
                $this->db->query($statement);
            }
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        foreach (['permintaan_parkir', 'kendaraan_pengguna'] as $table) {
            if ($this->db->tableExists($table) && $this->db->table($table)->countAllResults() > 0) {
                throw new RuntimeException('Rollback dibatalkan agar data pengguna tetap tersimpan.');
            }
        }
        $this->forge->dropTable('permintaan_parkir', true);
        $this->forge->dropTable('kendaraan_pengguna', true);
    }
}
