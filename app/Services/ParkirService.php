<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Kendaraan;
use App\Exceptions\AturanParkirException;
use App\Services\Tarif\TarifMobil;
use App\Services\Tarif\TarifMotor;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

final class ParkirService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function kendaraanMasuk(
        string $plat,
        string $jenis,
        int $petugas,
        ?int $permintaanId = null,
    ): int
    {
        $plat = Kendaraan::normalisasiPlat($plat);
        if (!Kendaraan::platValid($plat) || !in_array($jenis, ['motor', 'mobil'], true)) {
            throw new AturanParkirException(
                'Plat atau jenis kendaraan tidak valid. Contoh plat: BE1234AB.',
            );
        }

        return $this->atomik(function () use ($plat, $jenis, $petugas, $permintaanId): int {
            // Upsert locks the unique plate, including simultaneous first visits.
            $this->db->query(
                'INSERT INTO kendaraan (plat_nomor, jenis) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)',
                [$plat, $jenis],
            );
            $kendaraan = $this->db
                ->query('SELECT * FROM kendaraan WHERE plat_nomor = ? FOR UPDATE', [$plat])
                ->getRowArray();
            if ($kendaraan['jenis'] !== $jenis) {
                throw new AturanParkirException(
                    'Plat sudah terdaftar untuk jenis kendaraan berbeda.',
                );
            }
            $permintaan = null;
            if ($this->db->tableExists('permintaan_parkir')) {
                if ($permintaanId !== null) {
                    $permintaan = $this->db->query(
                        'SELECT * FROM permintaan_parkir WHERE id = ? AND kendaraan_id = ? FOR UPDATE',
                        [$permintaanId, $kendaraan['id']],
                    )->getRowArray();
                    if ($permintaan && $permintaan['tiket_id']
                        && in_array($permintaan['status'], ['aktif', 'minta_keluar', 'selesai'], true)) {
                        return (int) $permintaan['tiket_id'];
                    }
                } else {
                    $permintaan = $this->db->query(
                        "SELECT id FROM permintaan_parkir
                         WHERE kendaraan_id = ? AND status = 'menunggu' FOR UPDATE",
                        [$kendaraan['id']],
                    )->getRowArray();
                    if ($permintaan) {
                        throw new AturanParkirException(
                            'Plat memiliki permintaan pengguna. Verifikasi kode melalui menu Permintaan pengguna.',
                        );
                    }
                }
            }
            if ($permintaanId !== null && (!$permintaan || $permintaan['status'] !== 'menunggu')) {
                throw new AturanParkirException('Permintaan sudah dibatalkan, ditolak, atau tidak ditemukan.');
            }
            if (
                $this->db
                    ->query('SELECT id FROM tiket_parkir WHERE kendaraan_aktif_id = ? FOR UPDATE', [
                        $kendaraan['id'],
                    ])
                    ->getRowArray()
            ) {
                throw new AturanParkirException('Kendaraan ini masih memiliki tiket aktif.');
            }

            // A current locking read plus unique generated columns protects capacity.
            $slot = $this->db
                ->query(
                    "SELECT s.id FROM slot_parkir s
                 LEFT JOIN tiket_parkir t ON t.slot_aktif_id = s.id
                 WHERE s.jenis = ? AND s.aktif = 1 AND t.id IS NULL
                 ORDER BY s.id LIMIT 1 FOR UPDATE",
                    [$jenis],
                )
                ->getRowArray();
            if (!$slot) {
                throw new AturanParkirException(
                    'Slot ' . $jenis . ' penuh. Tidak ada tiket yang dibuat.',
                );
            }
            $tarif = $this->db
                ->query('SELECT * FROM tarif WHERE jenis = ? FOR UPDATE', [$jenis])
                ->getRowArray();
            if (!$tarif) {
                throw new AturanParkirException('Tarif belum tersedia. Hubungi admin.');
            }

            $this->db->table('tiket_parkir')->insert([
                'kode_tiket' => 'PK-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(8))),
                'kendaraan_id' => $kendaraan['id'],
                'slot_id' => $slot['id'],
                'petugas_masuk_id' => $petugas,
                'waktu_masuk' => date('Y-m-d H:i:s'),
                'status' => 'aktif',
                'tarif_awal_snapshot' => $tarif['tarif_awal'],
                'tarif_lanjutan_snapshot' => $tarif['tarif_lanjutan'],
            ]);

            $tiketId = (int) $this->db->insertID();
            if ($permintaanId !== null) {
                $this->db->table('permintaan_parkir')->where('id', $permintaanId)->update([
                    'tiket_id' => $tiketId,
                    'status' => 'aktif',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return $tiketId;
        });
    }

    public function tiket(int $id): array
    {
        $row = $this->db
            ->table('tiket_parkir t')
            ->select('t.*, k.plat_nomor, k.jenis, s.kode_slot, s.area, u.nama AS petugas_masuk')
            ->join('kendaraan k', 'k.id = t.kendaraan_id')
            ->join('slot_parkir s', 's.id = t.slot_id')
            ->join('users u', 'u.id = t.petugas_masuk_id')
            ->where('t.id', $id)
            ->get()
            ->getRowArray();
        if (!$row) {
            throw new AturanParkirException('Tiket tidak ditemukan.');
        }

        return $row;
    }

    public function hitungTagihan(int $id): array
    {
        $tiket = $this->tiket($id);
        if ($tiket['status'] !== 'aktif') {
            throw new AturanParkirException(
                'Tiket sudah selesai. Buka bukti pembayaran di riwayat.',
            );
        }

        return $this->tagihan($tiket, date('Y-m-d H:i:s'));
    }

    public function kendaraanKeluar(int $id, int $petugas, int $uang, int $totalDisetujui): array
    {
        if ($uang < 0 || $uang > 9000000000000000) {
            throw new AturanParkirException('Nominal uang tunai tidak valid.');
        }

        return $this->atomik(function () use ($id, $petugas, $uang, $totalDisetujui): array {
            $locked = $this->db
                ->query('SELECT * FROM tiket_parkir WHERE id = ? FOR UPDATE', [$id])
                ->getRowArray();
            if (!$locked || $locked['status'] !== 'aktif') {
                throw new AturanParkirException(
                    'Tiket tidak aktif atau pembayaran sudah diproses.',
                );
            }
            $sekarang = date('Y-m-d H:i:s');
            $tagihan = $this->tagihan($this->tiket($id), $sekarang);
            // Recalculate on the server. Never silently charge a changed hourly total.
            if ($tagihan['total'] !== $totalDisetujui) {
                throw new AturanParkirException(
                    'Durasi berubah. Periksa tagihan terbaru lalu konfirmasi kembali.',
                );
            }
            if ($uang < $tagihan['total']) {
                throw new AturanParkirException('Uang tunai kurang dari total tagihan.');
            }

            $this->db->table('pembayaran')->insert([
                'tiket_id' => $id,
                'petugas_id' => $petugas,
                'durasi_menit' => $tagihan['durasi_menit'],
                'total' => $tagihan['total'],
                'uang_diterima' => $uang,
                'kembalian' => $uang - $tagihan['total'],
                'metode' => 'tunai',
                'waktu_bayar' => $sekarang,
            ]);
            $this->db
                ->table('tiket_parkir')
                ->where('id', $id)
                ->update([
                    'waktu_keluar' => $sekarang,
                    'status' => 'selesai',
                ]);
            if ($this->db->tableExists('permintaan_parkir')) {
                $this->db->table('permintaan_parkir')->where('tiket_id', $id)
                    ->whereIn('status', ['aktif', 'minta_keluar'])
                    ->update(['status' => 'selesai', 'updated_at' => $sekarang]);
            }

            return $tagihan + ['uang_diterima' => $uang, 'kembalian' => $uang - $tagihan['total']];
        });
    }

    private function tagihan(array $tiket, string $sekarang): array
    {
        $zona = new DateTimeZone('Asia/Jakarta');
        $detik =
            (new DateTimeImmutable($sekarang, $zona))->getTimestamp() -
            (new DateTimeImmutable($tiket['waktu_masuk'], $zona))->getTimestamp();
        if ($detik < 0) {
            throw new AturanParkirException(
                'Waktu server lebih awal dari waktu masuk. Periksa jam server.',
            );
        }
        $menit = intdiv($detik, 60) + ($detik % 60 > 0 ? 1 : 0);
        $tarif =
            $tiket['jenis'] === 'motor'
                ? new TarifMotor((int) $tiket['tarif_awal_snapshot'])
                : new TarifMobil(
                    (int) $tiket['tarif_awal_snapshot'],
                    (int) $tiket['tarif_lanjutan_snapshot'],
                );

        return [
            'tiket' => $tiket,
            'durasi_menit' => $menit,
            'jam_tagihan' => max(1, (int) ceil($menit / 60)),
            'total' => $tarif->hitung($menit),
            'waktu_hitung' => $sekarang,
        ];
    }

    private function atomik(callable $aksi): mixed
    {
        // Retry deadlocks caused by concurrent allocations; constraints remain authoritative.
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->db->transException(true)->transBegin();
            try {
                $hasil = $aksi();
                if (!$this->db->transStatus()) {
                    throw new AturanParkirException('Transaksi gagal. Silakan ulangi.');
                }
                if (!$this->db->transCommit()) {
                    throw new AturanParkirException('Transaksi belum tersimpan. Coba kembali.');
                }

                return $hasil;
            } catch (Throwable $e) {
                $this->db->transRollback();
                $this->db->resetTransStatus();
                if ($e instanceof AturanParkirException) {
                    throw $e;
                }
                $code = (int) $e->getCode();
                if (in_array($code, [1205, 1213, 1062], true) && $attempt < 2) {
                    usleep(random_int(10000, 40000));
                    continue;
                }
                log_message('error', 'Transaksi parkir gagal: {exception}', ['exception' => $e]);
                throw new AturanParkirException(
                    'Transaksi belum tersimpan. Periksa tiket dan coba kembali.',
                );
            }
        }
        throw new AturanParkirException('Parkir sedang sibuk. Silakan ulangi.');
    }
}
