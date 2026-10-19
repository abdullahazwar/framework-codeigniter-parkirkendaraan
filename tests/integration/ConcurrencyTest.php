<?php

namespace Tests\Integration;

use App\Services\ParkirService;
use App\Services\PenggunaParkirService;
use Tests\Support\ParkirTestCase;

final class ConcurrencyTest extends ParkirTestCase
{
    protected function tearDown(): void
    {
        $this->db->table('permintaan_parkir')->emptyTable();
        $this->db->table('kendaraan_pengguna')->emptyTable();
        $this->db->table('users')->where('role', 'pengguna')->delete();
        parent::tearDown();
    }

    public function testPengajuanGandaMengembalikanSatuPermintaan(): void
    {
        [$pengguna, $kendaraan] = $this->kendaraanPengguna();
        $value = $pengguna . ':' . $kendaraan;
        $results = $this->race('ajukan', [$value, $value]);
        self::assertTrue($results[0]['ok'], json_encode($results));
        self::assertTrue($results[1]['ok'], json_encode($results));
        self::assertSame($results[0]['id'], $results[1]['id']);
        self::assertSame(1, $this->db->table('permintaan_parkir')->countAllResults());
        self::assertSame(0, $this->db->table('tiket_parkir')->countAllResults());
    }

    public function testVerifikasiGandaMenerbitkanSatuTiket(): void
    {
        [$pengguna, $kendaraan] = $this->kendaraanPengguna();
        $id = (new PenggunaParkirService($this->db))->ajukan($pengguna, $kendaraan);
        $results = $this->race('terima', [(string) $id, (string) $id]);
        self::assertTrue($results[0]['ok'], json_encode($results));
        self::assertTrue($results[1]['ok'], json_encode($results));
        self::assertSame($results[0]['id'], $results[1]['id']);
        self::assertSame(1, $this->db->table('tiket_parkir')->countAllResults());
    }

    public function testPembatalanBersamaanDenganVerifikasiKonsisten(): void
    {
        [$pengguna, $kendaraan] = $this->kendaraanPengguna();
        $service = new PenggunaParkirService($this->db);
        $id = $service->ajukan($pengguna, $kendaraan);
        $results = $this->race('batal_atau_terima', [$pengguna . ':' . $id, (string) $id]);
        self::assertSame(1, count(array_filter($results, static fn($r) => $r['ok'])), json_encode($results));
        $row = $service->detail($pengguna, $id);
        self::assertSame($results[0]['ok'] ? 'dibatalkan' : 'aktif', $row['status']);
        self::assertSame($results[0]['ok'] ? 0 : 1, $this->db->table('tiket_parkir')->countAllResults());
    }

    public function testPermintaanKeluarBersamaanDenganPembayaranTetapSelesai(): void
    {
        [$pengguna, $kendaraan] = $this->kendaraanPengguna();
        $service = new PenggunaParkirService($this->db);
        $id = $service->ajukan($pengguna, $kendaraan);
        $tiket = $service->terima($id, $this->petugasId);
        $results = $this->race('keluar_atau_bayar', [$pengguna . ':' . $id, (string) $tiket]);
        self::assertTrue($results[1]['ok'], json_encode($results));
        self::assertSame('selesai', $service->detail($pengguna, $id)['status']);
        self::assertSame(1, $this->db->table('pembayaran')->countAllResults());
        self::assertNull((new ParkirService($this->db))->tiket($tiket)['slot_aktif_id']);
    }

    private function kendaraanPengguna(): array
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'nama' => 'Pengguna Uji', 'username' => 'pengguna_race', 'role' => 'pengguna', 'aktif' => 1,
            'password_hash' => password_hash('PasswordKhususTes123', PASSWORD_DEFAULT),
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $id = (int) $this->db->insertID();
        $vehicle = (new PenggunaParkirService($this->db))->tambahKendaraan($id, 'BE8899AA', 'motor');

        return [$id, $vehicle];
    }

    public function testDuaPetugasBerebutSlotTerakhir(): void
    {
        $results = $this->race('masuk', ['BE1001AA', 'BE1002AA']);
        self::assertSame(
            1,
            count(array_filter($results, static fn($r) => $r['ok'])),
            json_encode($results),
        );
        self::assertSame(
            1,
            $this->db->table('tiket_parkir')->where('status', 'aktif')->countAllResults(),
        );
        self::assertSame(1, $this->db->table('kendaraan')->countAllResults());
    }

    public function testPlatSamaMasukBersamaan(): void
    {
        $this->db
            ->table('slot_parkir')
            ->insert(['kode_slot' => 'M02', 'area' => 'Utama', 'jenis' => 'motor', 'aktif' => 1]);
        $results = $this->race('masuk', ['BE2001AA', 'BE2001AA']);
        self::assertSame(
            1,
            count(array_filter($results, static fn($r) => $r['ok'])),
            json_encode($results),
        );
        self::assertSame(1, $this->db->table('tiket_parkir')->countAllResults());
    }

    public function testDuaPetugasMembayarTiketSama(): void
    {
        $id = (new ParkirService($this->db))->kendaraanMasuk('BE3001AA', 'motor', $this->petugasId);
        $results = $this->race('bayar', [(string) $id, (string) $id]);
        self::assertSame(
            1,
            count(array_filter($results, static fn($r) => $r['ok'])),
            json_encode($results),
        );
        self::assertSame(1, $this->db->table('pembayaran')->countAllResults());
        self::assertSame(
            'selesai',
            $this->db->table('tiket_parkir')->where('id', $id)->get()->getRowArray()['status'],
        );
    }

    private function race(string $action, array $values): array
    {
        if (!function_exists('proc_open')) {
            self::markTestSkipped(
                'proc_open diperlukan untuk menguji dua koneksi secara bersamaan.',
            );
        }
        $gate = tempnam(sys_get_temp_dir(), 'parkir-race-');
        unlink($gate);
        $processes = [];
        try {
            foreach ($values as $i => $value) {
                $process = proc_open(
                    [
                        PHP_BINARY,
                        ROOTPATH . 'tests/_support/parkir_worker.php',
                        $gate,
                        (string) $i,
                        $action,
                        $value,
                        (string) $this->petugasId,
                    ],
                    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                    $pipes,
                    ROOTPATH,
                );
                self::assertIsResource($process);
                $processes[] = [$process, $pipes];
            }
            $deadline = microtime(true) + 10;
            while (!is_file($gate . '.0.ready') || !is_file($gate . '.1.ready')) {
                self::assertLessThan($deadline, microtime(true), 'Worker tidak mencapai barrier.');
                usleep(10000);
            }
            touch($gate);
            $results = [];
            foreach ($processes as [$process, $pipes]) {
                $output = stream_get_contents($pipes[1]);
                $error = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                self::assertSame(0, proc_close($process), $error . $output);
                $results[] = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
            }

            return $results;
        } finally {
            foreach (glob($gate . '*') ?: [] as $file) {
                unlink($file);
            }
        }
    }
}
