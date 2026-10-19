<?php

namespace Tests\Integration;

use App\Services\ParkirService;
use App\Services\PenggunaParkirService;
use Config\Database;
use Tests\Support\ParkirTestCase;

final class SqlParkirTest extends ParkirTestCase
{
    private ?array $riwayatMigration = null;

    private const TABEL = [
        'users', 'kendaraan', 'slot_parkir', 'tarif', 'tiket_parkir', 'pembayaran',
        'kendaraan_pengguna', 'permintaan_parkir',
    ];

    protected function tearDown(): void
    {
        $this->db->table('permintaan_parkir')->emptyTable();
        $this->db->table('kendaraan_pengguna')->emptyTable();
        $this->db->table('users')->where('role', 'pengguna')->delete();
        if ($this->riwayatMigration !== null) {
            $this->db->table('migrations')->emptyTable();
            foreach ($this->riwayatMigration as $row) {
                $this->db->table('migrations')->insert($row);
            }
        }
        parent::tearDown();
    }

    public function testImporBaruDanImporUlangMempertahankanKunjunganSertaPengaturan(): void
    {
        $forge = Database::forge($this->db);
        foreach (array_reverse(self::TABEL) as $table) {
            $forge->dropTable($table, true);
        }
        $this->riwayatMigration = $this->db->table('migrations')->get()->getResultArray();
        $forge->dropTable('migrations', true);
        $this->imporSqlParkir();

        foreach (self::TABEL as $table) {
            self::assertTrue($this->db->tableExists($table), $table);
        }
        self::assertSame(30, $this->db->table('slot_parkir')->countAllResults());
        self::assertSame(2, $this->db->table('tarif')->countAllResults());
        self::assertSame(1, $this->db->table('users')->countAllResults());
        self::assertSame(3, $this->db->table('migrations')->where('group', 'default')
            ->where('namespace', 'App')->countAllResults());
        $admin = (int) $this->db->table('users')->where('username', 'admin')->get()->getRowArray()['id'];
        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'nama' => 'Pengguna SQL', 'username' => 'pengguna_sql', 'role' => 'pengguna', 'aktif' => 1,
            'password_hash' => password_hash('PasswordKhususTes123', PASSWORD_DEFAULT),
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $pengguna = (int) $this->db->insertID();
        $service = new PenggunaParkirService($this->db);
        $vehicle = $service->tambahKendaraan($pengguna, 'BE9011AA', 'motor');
        $id = $service->ajukan($pengguna, $vehicle);
        $tiket = $service->terima($id, $admin);
        $service->mintaKeluar($pengguna, $id);
        (new ParkirService($this->db))->kendaraanKeluar($tiket, $admin, 10000, 3000);
        $pendingVehicle = $service->tambahKendaraan($pengguna, 'BE9012AA', 'mobil');
        $pending = $service->ajukan($pengguna, $pendingVehicle);

        $hash = password_hash('PasswordAdminDiubah123', PASSWORD_DEFAULT);
        $this->db->table('users')->where('id', $admin)->update(['password_hash' => $hash]);
        $this->db->table('tarif')->where('jenis', 'motor')
            ->update(['tarif_awal' => 7000, 'tarif_lanjutan' => 7000]);
        $this->db->table('slot_parkir')->where('kode_slot', 'M20')->update(['aktif' => 0]);
        $before = $this->isiTabel([...self::TABEL, 'migrations']);
        $this->imporSqlParkir();
        $this->imporSqlParkir();

        self::assertSame($before, $this->isiTabel([...self::TABEL, 'migrations']));
        self::assertSame('selesai', $service->detail($pengguna, $id)['status']);
        self::assertSame(3000, (int) $service->detail($pengguna, $id)['total_bayar']);
        self::assertSame('menunggu', $service->detail($pengguna, $pending)['status']);
    }

    public function testImporPadaSkemaLamaMenambahFiturTanpaMengubahDataLama(): void
    {
        $forge = Database::forge($this->db);
        $forge->dropTable('permintaan_parkir', true);
        $forge->dropTable('kendaraan_pengguna', true);
        $this->db->resetDataCache();
        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin','petugas') NOT NULL DEFAULT 'petugas'");
        $parkir = new ParkirService($this->db);
        $tiket = $parkir->kendaraanMasuk('BE9021AA', 'motor', $this->petugasId);
        $parkir->kendaraanKeluar($tiket, $this->petugasId, 10000, 3000);
        $this->db->table('tarif')->where('jenis', 'motor')
            ->update(['tarif_awal' => 9000, 'tarif_lanjutan' => 9000]);
        $this->db->table('slot_parkir')->where('kode_slot', 'C01')->update(['aktif' => 0]);
        $lama = array_slice(self::TABEL, 0, 6);
        $before = $this->isiTabel($lama);

        $this->imporSqlParkir();
        $this->imporSqlParkir();

        self::assertSame($before, $this->isiTabel($lama));
        self::assertTrue((new PenggunaParkirService($this->db))->siap());
        $role = $this->db->query("SHOW COLUMNS FROM users LIKE 'role'")->getRowArray();
        self::assertStringContainsString('pengguna', $role['Type']);
        self::assertSame(2, $this->db->table('slot_parkir')->countAllResults());
    }

    private function isiTabel(array $tables): array
    {
        $hasil = [];
        foreach ($tables as $table) {
            $hasil[$table] = $this->db->table($table)->orderBy('id')->get()->getResultArray();
        }

        return $hasil;
    }
}
