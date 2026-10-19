<?php

namespace Tests\Feature;

use App\Exceptions\AturanParkirException;
use App\Services\ParkirService;
use App\Services\PenggunaParkirService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Tests\Support\ParkirTestCase;

final class PenggunaParkirTest extends ParkirTestCase
{
    use FeatureTestTrait;

    private int $satu;
    private int $dua;
    private PenggunaParkirService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $now = date('Y-m-d H:i:s');
        foreach (['satu', 'dua'] as $name) {
            $this->db->table('users')->insert([
                'nama' => 'Pengguna ' . $name, 'username' => 'pengguna_' . $name,
                'role' => 'pengguna', 'aktif' => 1,
                'password_hash' => password_hash('PasswordKhususTes123', PASSWORD_DEFAULT),
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->{$name} = (int) $this->db->insertID();
        }
        $this->service = new PenggunaParkirService($this->db);
    }

    protected function tearDown(): void
    {
        $this->db->table('permintaan_parkir')->emptyTable();
        $this->db->table('kendaraan_pengguna')->emptyTable();
        $this->db->table('users')->where('role', 'pengguna')->delete();
        parent::tearDown();
    }

    public function testAlurPenggunaSampaiBuktiPembayaran(): void
    {
        $kendaraan = $this->service->tambahKendaraan($this->satu, 'be 1234 ab', 'motor');
        $id = $this->service->ajukan($this->satu, $kendaraan);
        self::assertSame($id, $this->service->ajukan($this->satu, $kendaraan));
        self::assertSame(0, $this->db->table('tiket_parkir')->countAllResults());
        self::assertSame('menunggu', $this->service->detail($this->satu, $id)['status']);
        $tiket = $this->service->terima($id, $this->petugasId);
        self::assertSame($tiket, $this->service->terima($id, $this->petugasId));
        self::assertSame(1, $this->db->table('tiket_parkir')->countAllResults());
        $this->service->mintaKeluar($this->satu, $id);
        $this->service->mintaKeluar($this->satu, $id);
        self::assertSame('minta_keluar', $this->service->detail($this->satu, $id)['status']);
        self::assertSame(0, $this->db->table('pembayaran')->countAllResults());
        self::assertNotNull((new ParkirService($this->db))->tiket($tiket)['slot_aktif_id']);
        (new ParkirService($this->db))->kendaraanKeluar($tiket, $this->petugasId, 10000, 3000);
        $hasil = $this->service->detail($this->satu, $id);
        self::assertSame('selesai', $hasil['status']);
        self::assertSame(3000, (int) $hasil['total_bayar']);
        self::assertSame(7000, (int) $hasil['kembalian']);
        self::assertSame(1, $this->service->milikPengguna($this->satu, true)->countAllResults());
        self::assertSame(0, $this->service->milikPengguna($this->dua, true)->countAllResults());
        self::assertNull((new ParkirService($this->db))->tiket($tiket)['slot_aktif_id']);
    }

    public function testKendaraanAkunLainTidakBisaDipakai(): void
    {
        $kendaraan = $this->service->tambahKendaraan($this->satu, 'BE1111AA', 'motor');
        foreach (['ajukan', 'hapusKendaraan'] as $method) {
            try {
                $this->service->{$method}($this->dua, $kendaraan);
                self::fail('Kendaraan milik akun lain harus ditolak.');
            } catch (AturanParkirException) {
                self::assertCount(1, $this->service->kendaraan($this->satu));
            }
        }
        self::assertSame(0, $this->db->table('permintaan_parkir')->countAllResults());
    }

    public function testPlatTidakBisaDiambilAkunLain(): void
    {
        $this->service->tambahKendaraan($this->satu, 'BE2222AA', 'motor');
        $this->expectException(AturanParkirException::class);
        $this->service->tambahKendaraan($this->dua, 'be 2222 aa', 'motor');
    }

    public function testPermintaanBelumMengunciSlotDanSlotPenuhSaatVerifikasiDitangani(): void
    {
        $kendaraan = $this->service->tambahKendaraan($this->satu, 'BE3333AA', 'motor');
        $id = $this->service->ajukan($this->satu, $kendaraan);
        (new ParkirService($this->db))->kendaraanMasuk('BE3334AA', 'motor', $this->petugasId);
        try {
            $this->service->terima($id, $this->petugasId);
            self::fail('Verifikasi ketika slot penuh harus ditolak.');
        } catch (AturanParkirException $error) {
            self::assertStringContainsString('penuh', $error->getMessage());
            $request = $this->service->detail($this->satu, $id);
            self::assertSame('menunggu', $request['status']);
            self::assertNull($request['tiket_id']);
        }
    }

    public function testTidakBisaMengajukanSaatSlotSudahPenuh(): void
    {
        (new ParkirService($this->db))->kendaraanMasuk('BE4441AA', 'motor', $this->petugasId);
        $kendaraan = $this->service->tambahKendaraan($this->satu, 'BE4442AA', 'motor');
        try {
            $this->service->ajukan($this->satu, $kendaraan);
            self::fail('Pengajuan saat penuh harus ditolak.');
        } catch (AturanParkirException) {
            self::assertSame(0, $this->db->table('permintaan_parkir')->countAllResults());
        }
    }

    public function testBatalSebelumVerifikasiTidakMembuatTiket(): void
    {
        $id = $this->permintaan();
        $this->service->batalkan($this->satu, $id);
        $this->service->batalkan($this->satu, $id);
        try {
            $this->service->terima($id, $this->petugasId);
            self::fail('Permintaan yang dibatalkan tidak boleh diterima.');
        } catch (AturanParkirException) {
            self::assertSame('dibatalkan', $this->service->detail($this->satu, $id)['status']);
            self::assertSame(0, $this->db->table('tiket_parkir')->countAllResults());
        }
    }

    public function testPenolakanMenyimpanAlasanDanBisaMengajukanLagi(): void
    {
        $id = $this->permintaan();
        $this->service->tolak($id, $this->petugasId, 'Data kendaraan tidak sesuai.');
        $row = $this->service->detail($this->satu, $id);
        self::assertSame('ditolak', $row['status']);
        self::assertSame('Data kendaraan tidak sesuai.', $row['alasan_penolakan']);
        $vehicle = $this->service->kendaraan($this->satu)[0];
        self::assertNull($vehicle['permintaan_id']);
        self::assertGreaterThan($id, $this->service->ajukan($this->satu, (int) $vehicle['id']));
    }

    public function testPenggunaTidakBisaMengonfirmasiMasukSendiri(): void
    {
        $id = $this->permintaan();
        $this->expectException(AturanParkirException::class);
        $this->service->terima($id, $this->satu);
    }

    public function testTiketWalkInTidakDiklaimHanyaDenganPlatSama(): void
    {
        $staff = new ParkirService($this->db);
        $tiket = $staff->kendaraanMasuk('BE5555AA', 'motor', $this->petugasId);
        $staff->kendaraanKeluar($tiket, $this->petugasId, 10000, 3000);
        $this->service->tambahKendaraan($this->satu, 'BE5555AA', 'motor');
        self::assertSame(0, $this->service->milikPengguna($this->satu, true)->countAllResults());
        $this->withSession(['user' => $this->user($this->satu)])
            ->get('parkir/tiket/' . $tiket)->assertStatus(403);
        $this->expectException(PageNotFoundException::class);
        $this->withSession(['user' => $this->user($this->satu)])
            ->get('pengguna/tiket/' . $tiket);
    }

    public function testMasukManualMewajibkanVerifikasiPermintaanPengguna(): void
    {
        $this->permintaan();
        try {
            (new ParkirService($this->db))->kendaraanMasuk('BE1234AA', 'motor', $this->petugasId);
            self::fail('Jalur manual tidak boleh melewati verifikasi kode permintaan.');
        } catch (AturanParkirException $error) {
            self::assertStringContainsString('Verifikasi kode', $error->getMessage());
            self::assertSame(0, $this->db->table('tiket_parkir')->countAllResults());
        }
    }

    public function testPembayaranLangsungPetugasTetapMenyelesaikanPermintaan(): void
    {
        $id = $this->permintaan();
        $tiket = $this->service->terima($id, $this->petugasId);
        (new ParkirService($this->db))->kendaraanKeluar($tiket, $this->petugasId, 10000, 3000);
        self::assertSame('selesai', $this->service->detail($this->satu, $id)['status']);
    }

    public function testKendaraanTidakBisaDilepasKetikaParkirMasihTerbuka(): void
    {
        $id = $this->permintaan();
        $vehicle = $this->service->kendaraan($this->satu)[0];
        try {
            $this->service->hapusKendaraan($this->satu, (int) $vehicle['id']);
            self::fail('Kendaraan dengan permintaan terbuka tidak boleh dilepas.');
        } catch (AturanParkirException) {
            self::assertSame('menunggu', $this->service->detail($this->satu, $id)['status']);
        }
    }

    public function testMelepasKendaraanTidakMenghapusRiwayatPemilikLama(): void
    {
        $id = $this->permintaan();
        $vehicle = $this->service->kendaraan($this->satu)[0];
        $tiket = $this->service->terima($id, $this->petugasId);
        (new ParkirService($this->db))->kendaraanKeluar($tiket, $this->petugasId, 10000, 3000);
        $this->service->hapusKendaraan($this->satu, (int) $vehicle['id']);
        $this->service->tambahKendaraan($this->dua, 'BE1234AA', 'motor');
        self::assertSame('selesai', $this->service->detail($this->satu, $id)['status']);
        self::assertSame(0, $this->service->milikPengguna($this->dua, true)->countAllResults());
    }

    public function testAkunLainTidakBisaMembacaAtauMengubahPermintaan(): void
    {
        $id = $this->permintaan();
        foreach (['detail', 'batal', 'keluar'] as $action) {
            try {
                if ($action === 'detail') {
                    $this->withSession(['user' => $this->user($this->dua)])->get('pengguna/tiket/' . $id);
                } else {
                    $this->kirim('pengguna/tiket/' . $id . '/' . $action, [], $this->dua);
                }
                self::fail('Permintaan akun lain harus ditolak.');
            } catch (PageNotFoundException $error) {
                self::assertSame(404, $error->getCode());
            }
        }
        self::assertSame('menunggu', $this->service->detail($this->satu, $id)['status']);
    }

    public function testHalamanPenggunaDanPembatasanPeran(): void
    {
        foreach (['pengguna', 'pengguna/kendaraan', 'pengguna/parkir', 'pengguna/riwayat', 'pengguna/profil'] as $path) {
            $this->withSession([])->get($path)->assertRedirectTo('/login');
            $this->withSession(['user' => $this->user($this->satu)])->get($path)->assertStatus(200);
            $this->withSession(['user' => $this->user($this->adminId)])->get($path)->assertStatus(403);
        }
        $this->withSession(['user' => $this->user($this->satu)])->get('permintaan')->assertStatus(403);
        $this->kirim('permintaan/1/terima', ['konfirmasi_masuk' => '1'])->assertStatus(403);
        $this->kirim('permintaan/1/tolak', ['alasan' => 'Ditolak'])->assertStatus(403);
    }

    public function testMutasiPenggunaMemerlukanCsrf(): void
    {
        try {
            $this->withSession(['user' => $this->user($this->satu)])
                ->post('pengguna/kendaraan', ['plat_nomor' => 'BE9999AA', 'jenis' => 'motor']);
            self::fail('Mutasi tanpa CSRF seharusnya ditolak.');
        } catch (SecurityException $error) {
            self::assertSame(403, $error->getCode());
        }
        self::assertCount(0, $this->service->kendaraan($this->satu));
    }

    public function testKonfirmasiPetugasWajibMencocokkanKendaraan(): void
    {
        $id = $this->permintaan();
        $this->kirim('permintaan/' . $id . '/terima', [], $this->petugasId)->assertRedirect();
        self::assertSame('menunggu', $this->service->detail($this->satu, $id)['status']);
        self::assertSame(0, $this->db->table('tiket_parkir')->countAllResults());
    }

    public function testKegagalanPengaitanTiketMembatalkanSeluruhTransaksiMasuk(): void
    {
        $id = $this->permintaan();
        $this->db->query("CREATE TRIGGER gagal_kait BEFORE UPDATE ON permintaan_parkir
            FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Simulasi gagal'");
        try {
            $this->service->terima($id, $this->petugasId);
            self::fail('Transaksi seharusnya dibatalkan.');
        } catch (AturanParkirException) {
            self::assertSame(0, $this->db->table('tiket_parkir')->countAllResults());
            self::assertSame('menunggu', $this->service->detail($this->satu, $id)['status']);
        } finally {
            $this->db->query('DROP TRIGGER IF EXISTS gagal_kait');
        }
    }

    public function testSqlPenggunaBisaDiulangTanpaMengubahDataKunjungan(): void
    {
        $id = $this->permintaan();
        $before = $this->service->detail($this->satu, $id);
        $sql = file_get_contents(APPPATH . 'Database/fitur_pengguna_parkir.sql');
        for ($i = 0; $i < 2; $i++) {
            foreach (explode(';', $sql) as $statement) {
                if (trim($statement) !== '') {
                    $this->db->query($statement);
                }
            }
        }
        self::assertSame($before, $this->service->detail($this->satu, $id));
    }

    public function testKegagalanSinkronisasiPembayaranTidakMelepasSlotAtauMencatatUang(): void
    {
        $id = $this->permintaan();
        $tiket = $this->service->terima($id, $this->petugasId);
        $this->service->mintaKeluar($this->satu, $id);
        $this->db->query("CREATE TRIGGER gagal_selesai BEFORE UPDATE ON permintaan_parkir
            FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Simulasi gagal'");
        try {
            (new ParkirService($this->db))->kendaraanKeluar($tiket, $this->petugasId, 10000, 3000);
            self::fail('Pembayaran seharusnya dibatalkan.');
        } catch (AturanParkirException) {
            self::assertSame(0, $this->db->table('pembayaran')->countAllResults());
            self::assertSame('minta_keluar', $this->service->detail($this->satu, $id)['status']);
            self::assertNotNull((new ParkirService($this->db))->tiket($tiket)['slot_aktif_id']);
        } finally {
            $this->db->query('DROP TRIGGER IF EXISTS gagal_selesai');
        }
    }

    private function permintaan(): int
    {
        $vehicle = $this->service->tambahKendaraan($this->satu, 'BE1234AA', 'motor');

        return $this->service->ajukan($this->satu, $vehicle);
    }

    private function kirim(string $path, array $data, ?int $userId = null): TestResponse
    {
        $token = csrf_hash();

        return $this->withSession(['user' => $this->user($userId ?? $this->satu), csrf_token() => $token])
            ->post($path, $data + [csrf_token() => $token]);
    }
}
