<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use LogicException;

abstract class ParkirTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $refresh = true;
    protected int $adminId;
    protected int $petugasId;

    protected function setUp(): void
    {
        $config = config('Database');
        if (
            !str_ends_with($config->tests['database'], '_test') ||
            $config->tests['database'] === $config->default['database']
        ) {
            throw new LogicException('Tes hanya boleh memakai database terpisah berakhiran _test.');
        }
        parent::setUp();
        $now = date('Y-m-d H:i:s');
        foreach (['admin', 'petugas'] as $role) {
            $this->db->table('users')->insert([
                'nama' => ucfirst($role) . ' Uji',
                'username' => $role . '_uji',
                'password_hash' => password_hash('PasswordKhususTes123', PASSWORD_DEFAULT),
                'role' => $role,
                'aktif' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $key = $role === 'admin' ? 'adminId' : 'petugasId';
            $this->{$key} = (int) $this->db->insertID();
        }
        foreach (['motor' => [3000, 3000], 'mobil' => [5000, 3000]] as $jenis => $rate) {
            $this->db->table('tarif')->insert([
                'jenis' => $jenis,
                'tarif_awal' => $rate[0],
                'tarif_lanjutan' => $rate[1],
                'updated_at' => $now,
            ]);
            $this->db->table('slot_parkir')->insert([
                'kode_slot' => $jenis === 'motor' ? 'M01' : 'C01',
                'area' => 'Utama',
                'jenis' => $jenis,
                'aktif' => 1,
            ]);
        }
    }

    protected function user(int $id): array
    {
        $user = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        unset($user['password_hash']);

        return $user;
    }
}
