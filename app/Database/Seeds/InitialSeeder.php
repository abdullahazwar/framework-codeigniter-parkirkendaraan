<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

final class InitialSeeder extends Seeder
{
    public function run()
    {
        $hasAdmin = $this->db->table('users')->where('role', 'admin')->countAllResults() > 0;
        $password = (string) env('parkir.adminPassword', '');
        $username = (string) env('parkir.adminUsername', 'admin');

        if (!$hasAdmin && (strlen($password) < 12 || strlen($password) > 72)) {
            throw new RuntimeException(
                'Isi parkir.adminPassword di .env dengan 12 sampai 72 karakter sebelum seed.',
            );
        }
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/D', $username)) {
            throw new RuntimeException(
                'Username admin harus 3 sampai 50 huruf, angka, atau underscore.',
            );
        }

        $this->db->transException(true)->transStart();
        if (!$hasAdmin) {
            $this->db->table('users')->insert([
                'nama' => 'Administrator',
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'admin',
                'aktif' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        foreach (['motor' => [3000, 3000], 'mobil' => [5000, 3000]] as $jenis => $nilai) {
            if ($this->db->table('tarif')->where('jenis', $jenis)->countAllResults() === 0) {
                $this->db->table('tarif')->insert([
                    'jenis' => $jenis,
                    'tarif_awal' => $nilai[0],
                    'tarif_lanjutan' => $nilai[1],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
            for ($i = 1; $i <= ($jenis === 'motor' ? 20 : 10); $i++) {
                $kode = ($jenis === 'motor' ? 'M' : 'C') . sprintf('%02d', $i);
                if (
                    $this->db
                        ->table('slot_parkir')
                        ->where('kode_slot', $kode)
                        ->where('area', 'Utama')
                        ->countAllResults() === 0
                ) {
                    $this->db->table('slot_parkir')->insert([
                        'kode_slot' => $kode,
                        'area' => 'Utama',
                        'jenis' => $jenis,
                        'aktif' => 1,
                    ]);
                }
            }
        }
        if (!$this->db->transComplete()) {
            throw new RuntimeException('Seed gagal disimpan. Periksa koneksi dan database.');
        }
    }
}
