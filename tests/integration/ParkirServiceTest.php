<?php

namespace Tests\Integration;

use App\Exceptions\AturanParkirException;
use App\Services\LaporanService;
use App\Services\ParkirService;
use Tests\Support\ParkirTestCase;

final class ParkirServiceTest extends ParkirTestCase
{
    public function testMasukMenyimpanSnapshotDanMengisiSlot(): void
    {
        $service = new ParkirService($this->db);
        $id = $service->kendaraanMasuk('be 1234 ab', 'motor', $this->petugasId);
        $tiket = $service->tiket($id);
        self::assertSame('BE1234AB', $tiket['plat_nomor']);
        self::assertSame('aktif', $tiket['status']);
        self::assertSame(3000, (int) $tiket['tarif_awal_snapshot']);
        self::assertSame((int) $tiket['slot_id'], (int) $tiket['slot_aktif_id']);
    }

    public function testSlotPenuhTidakMenyimpanTiketAtauKendaraanBaru(): void
    {
        $service = new ParkirService($this->db);
        $service->kendaraanMasuk('BE1AA', 'motor', $this->petugasId);
        try {
            $service->kendaraanMasuk('BE2AA', 'motor', $this->petugasId);
            self::fail('Slot penuh harus ditolak.');
        } catch (AturanParkirException $e) {
            self::assertStringContainsString('penuh', $e->getMessage());
        }
        self::assertSame(1, $this->db->table('tiket_parkir')->countAllResults());
        self::assertSame(1, $this->db->table('kendaraan')->countAllResults());
    }

    public function testPlatAktifDuplikatDitolak(): void
    {
        $service = new ParkirService($this->db);
        $service->kendaraanMasuk('BE1234AB', 'motor', $this->petugasId);
        $this->expectException(AturanParkirException::class);
        $service->kendaraanMasuk('be 1234 ab', 'motor', $this->petugasId);
    }

    public function testPembayaranAtomikMelepasSlotDanMencegahPembayaranGanda(): void
    {
        $service = new ParkirService($this->db);
        $id = $service->kendaraanMasuk('BE1111AA', 'mobil', $this->petugasId);
        $this->db->table('tiket_parkir')->where('id', $id)->update(['waktu_masuk' => date('Y-m-d H:i:s', time() - 8100)]);
        self::assertSame(11000, $service->hitungTagihan($id)['total']);
        $service->kendaraanKeluar($id, $this->petugasId, 20000, 11000);
        self::assertSame('selesai', $service->tiket($id)['status']);
        self::assertNull($service->tiket($id)['slot_aktif_id']);
        $payment = $this->db->table('pembayaran')->get()->getRowArray();
        self::assertSame(9000, (int) $payment['kembalian']);
        try {
            $service->kendaraanKeluar($id, $this->petugasId, 20000, 11000);
            self::fail('Pembayaran ulang harus ditolak.');
        } catch (AturanParkirException) {
            self::assertSame(1, $this->db->table('pembayaran')->countAllResults());
        }
        self::assertGreaterThan($id, $service->kendaraanMasuk('BE1111AA', 'mobil', $this->petugasId));
    }

    public function testKurangBayarDanTotalBerubahTidakMelepasSlot(): void
    {
        $service = new ParkirService($this->db);
        $id = $service->kendaraanMasuk('BE2222AA', 'motor', $this->petugasId);
        foreach ([[1000, 3000], [10000, 0]] as [$uang, $total]) {
            try {
                $service->kendaraanKeluar($id, $this->petugasId, $uang, $total);
                self::fail('Pembayaran tidak valid harus ditolak.');
            } catch (AturanParkirException) {
                self::assertSame(0, $this->db->table('pembayaran')->countAllResults());
                self::assertSame('aktif', $service->tiket($id)['status']);
            }
        }
    }

    public function testTarifBerubahTidakMengubahTiketAktifDanHistori(): void
    {
        $service = new ParkirService($this->db);
        $id = $service->kendaraanMasuk('BE3333AA', 'motor', $this->petugasId);
        $this->db->table('tarif')->where('jenis', 'motor')->update(['tarif_awal' => 10000, 'tarif_lanjutan' => 10000]);
        self::assertSame(3000, $service->hitungTagihan($id)['total']);
        $service->kendaraanKeluar($id, $this->petugasId, 5000, 3000);
        $this->db->table('tarif')->where('jenis', 'motor')->update(['tarif_awal' => 20000, 'tarif_lanjutan' => 20000]);
        self::assertSame(3000, (int) $this->db->table('pembayaran')->get()->getRowArray()['total']);
        $new = $service->kendaraanMasuk('BE4444AA', 'motor', $this->petugasId);
        self::assertSame(20000, $service->hitungTagihan($new)['total']);
    }

    public function testKegagalanUpdateTiketMembatalkanPembayaran(): void
    {
        $service = new ParkirService($this->db);
        $id = $service->kendaraanMasuk('BE5555AA', 'motor', $this->petugasId);
        $this->db->query("CREATE TRIGGER simulasi_gagal BEFORE UPDATE ON tiket_parkir
            FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Simulasi gagal'");
        try {
            $service->kendaraanKeluar($id, $this->petugasId, 5000, 3000);
            self::fail('Transaksi seharusnya gagal.');
        } catch (AturanParkirException) {
            self::assertSame(0, $this->db->table('pembayaran')->countAllResults());
            self::assertSame('aktif', $service->tiket($id)['status']);
        } finally {
            $this->db->query('DROP TRIGGER IF EXISTS simulasi_gagal');
        }
    }

    public function testLaporanHanyaMenghitungPembayaranDalamPeriode(): void
    {
        $service = new ParkirService($this->db);
        $motor = $service->kendaraanMasuk('BE6666AA', 'motor', $this->petugasId);
        $mobil = $service->kendaraanMasuk('BE7777AA', 'mobil', $this->petugasId);
        $service->kendaraanKeluar($motor, $this->petugasId, 10000, 3000);
        $service->kendaraanKeluar($mobil, $this->petugasId, 10000, 5000);
        $service->kendaraanMasuk('BE8888AA', 'motor', $this->petugasId);
        $this->db->table('pembayaran')->where('tiket_id', $motor)->update(['waktu_bayar' => '2026-08-31 23:59:59']);
        $this->db->table('pembayaran')->where('tiket_id', $mobil)->update(['waktu_bayar' => '2026-09-01 00:00:00']);
        $report = new LaporanService($this->db);
        $period = $report->periode('2026-09-01', '2026-09-30');
        $summary = $report->ringkasan($period);
        self::assertSame(1, (int) $summary['transaksi']);
        self::assertSame(5000, (int) $summary['pendapatan']);
        self::assertCount(1, $report->harian($period));
        self::assertSame('2026-09', $report->bulanan($period)[0]['bulan']);
        self::assertSame(5000, (int) $report->bulanan($period)[0]['pendapatan']);
        self::assertSame(1, $report->pembayaran($period)->countAllResults());
    }

    public function testTanggalTidakValidDitolak(): void
    {
        $this->expectException(AturanParkirException::class);
        (new LaporanService($this->db))->periode('2026-02-30', '2026-03-01');
    }

    public function testCsvMencegahFormula(): void
    {
        self::assertSame("'=HYPERLINK(1)", LaporanService::csvAman('=HYPERLINK(1)'));
        self::assertSame("' +CMD", LaporanService::csvAman(' +CMD'));
        self::assertSame('Petugas', LaporanService::csvAman('Petugas'));
    }
}
