<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Kendaraan;
use App\Exceptions\AturanParkirException;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use Throwable;

final class PenggunaParkirService
{
    public const TERBUKA = ['menunggu', 'aktif', 'minta_keluar'];
    public const RIWAYAT = ['selesai', 'dibatalkan', 'ditolak'];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function siap(): bool
    {
        return $this->db->tableExists('kendaraan_pengguna')
            && $this->db->tableExists('permintaan_parkir');
    }

    public function kendaraan(int $pengguna): array
    {
        return $this->db->table('kendaraan_pengguna kp')
            ->select('kp.*, k.plat_nomor, k.jenis, r.id AS permintaan_id, r.status AS status_parkir')
            ->join('kendaraan k', 'k.id = kp.kendaraan_id')
            ->join('permintaan_parkir r',
                'r.kendaraan_terbuka_id = k.id AND r.pengguna_id = kp.pengguna_id', 'left')
            ->where('kp.pengguna_id', $pengguna)
            ->orderBy('kp.id', 'DESC')->get()->getResultArray();
    }

    public function tambahKendaraan(int $pengguna, string $plat, string $jenis): int
    {
        $plat = Kendaraan::normalisasiPlat($plat);
        if (!Kendaraan::platValid($plat) || !in_array($jenis, ['motor', 'mobil'], true)) {
            throw new AturanParkirException('Periksa plat dan jenis kendaraan. Contoh: BE1234AB.');
        }

        return $this->atomik(function () use ($pengguna, $plat, $jenis): int {
            $this->pastikanPengguna($pengguna);
            $this->db->query(
                'INSERT INTO kendaraan (plat_nomor, jenis) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)',
                [$plat, $jenis],
            );
            $kendaraan = $this->db->query(
                'SELECT * FROM kendaraan WHERE plat_nomor = ? FOR UPDATE', [$plat],
            )->getRowArray();
            if ($kendaraan['jenis'] !== $jenis) {
                throw new AturanParkirException('Plat sudah tercatat dengan jenis kendaraan berbeda.');
            }
            $owner = $this->db->query(
                'SELECT * FROM kendaraan_pengguna WHERE kendaraan_id = ? FOR UPDATE',
                [$kendaraan['id']],
            )->getRowArray();
            if ($owner) {
                throw new AturanParkirException((int) $owner['pengguna_id'] === $pengguna
                    ? 'Kendaraan sudah ada di daftar kendaraan.'
                    : 'Plat sudah terdaftar pada akun lain. Hubungi pengelola.');
            }
            $this->db->table('kendaraan_pengguna')->insert([
                'pengguna_id' => $pengguna,
                'kendaraan_id' => $kendaraan['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return (int) $this->db->insertID();
        });
    }

    public function hapusKendaraan(int $pengguna, int $id): void
    {
        $this->atomik(function () use ($pengguna, $id): void {
            $this->pastikanPengguna($pengguna);
            $row = $this->db->query(
                'SELECT * FROM kendaraan_pengguna WHERE id = ? AND pengguna_id = ? FOR UPDATE',
                [$id, $pengguna],
            )->getRowArray();
            if (!$row) {
                throw new AturanParkirException('Kendaraan tidak ditemukan pada akun ini.');
            }
            $this->db->query('SELECT id FROM kendaraan WHERE id = ? FOR UPDATE', [$row['kendaraan_id']]);
            if ($this->db->table('permintaan_parkir')
                ->where('kendaraan_terbuka_id', $row['kendaraan_id'])->countAllResults() > 0) {
                throw new AturanParkirException('Selesaikan atau batalkan parkir sebelum melepas kendaraan.');
            }
            // Unlink the saved vehicle; the vehicle master and past visits remain intact.
            $this->db->table('kendaraan_pengguna')->where('id', $id)->delete();
        });
    }

    public function ajukan(int $pengguna, int $kendaraanTersimpan): int
    {
        return $this->atomik(function () use ($pengguna, $kendaraanTersimpan): int {
            $this->pastikanPengguna($pengguna);
            $saved = $this->db->query(
                'SELECT kp.*, k.jenis FROM kendaraan_pengguna kp
                 JOIN kendaraan k ON k.id = kp.kendaraan_id
                 WHERE kp.id = ? AND kp.pengguna_id = ? FOR UPDATE',
                [$kendaraanTersimpan, $pengguna],
            )->getRowArray();
            if (!$saved) {
                throw new AturanParkirException('Pilih kendaraan yang terdaftar pada akun ini.');
            }
            $existing = $this->db->query(
                'SELECT id, pengguna_id FROM permintaan_parkir
                 WHERE kendaraan_terbuka_id = ? FOR UPDATE', [$saved['kendaraan_id']],
            )->getRowArray();
            if ($existing) {
                if ((int) $existing['pengguna_id'] === $pengguna) {
                    return (int) $existing['id'];
                }
                throw new AturanParkirException('Kendaraan masih memiliki permintaan parkir terbuka.');
            }
            if ($this->db->table('tiket_parkir')
                ->where('kendaraan_aktif_id', $saved['kendaraan_id'])->countAllResults() > 0) {
                throw new AturanParkirException('Kendaraan sudah tercatat parkir. Hubungi petugas di lokasi.');
            }
            $slot = $this->db->query(
                'SELECT s.id FROM slot_parkir s
                 LEFT JOIN tiket_parkir t ON t.slot_aktif_id = s.id
                 WHERE s.jenis = ? AND s.aktif = 1 AND t.id IS NULL LIMIT 1', [$saved['jenis']],
            )->getRowArray();
            if (!$slot) {
                throw new AturanParkirException('Slot ' . $saved['jenis'] . ' sedang penuh. Coba kembali nanti.');
            }
            $now = date('Y-m-d H:i:s');
            $this->db->table('permintaan_parkir')->insert([
                'kode_permintaan' => 'PG-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(6))),
                'pengguna_id' => $pengguna,
                'kendaraan_id' => $saved['kendaraan_id'],
                'status' => 'menunggu', 'created_at' => $now, 'updated_at' => $now,
            ]);

            return (int) $this->db->insertID();
        });
    }

    public function batalkan(int $pengguna, int $id): void
    {
        $this->ubahMenunggu($id, 'dibatalkan', $pengguna);
    }

    public function tolak(int $id, int $petugas, string $alasan): void
    {
        $this->pastikanPetugas($petugas);
        $alasan = trim($alasan);
        if ($alasan === '' || mb_strlen($alasan) > 200) {
            throw new AturanParkirException('Isi alasan penolakan, maksimal 200 karakter.');
        }
        $this->ubahMenunggu($id, 'ditolak', null, $alasan);
    }

    private function ubahMenunggu(int $id, string $status, ?int $pengguna, ?string $alasan = null): void
    {
        $this->atomik(function () use ($id, $status, $pengguna, $alasan): void {
            if ($pengguna !== null) {
                $this->pastikanPengguna($pengguna);
            }
            $row = $this->db->query('SELECT * FROM permintaan_parkir WHERE id = ? FOR UPDATE', [$id])
                ->getRowArray();
            if (!$row || ($pengguna !== null && (int) $row['pengguna_id'] !== $pengguna)) {
                throw new AturanParkirException('Permintaan parkir tidak ditemukan.');
            }
            if ($row['status'] === $status) {
                return;
            }
            if ($row['status'] !== 'menunggu') {
                throw new AturanParkirException('Permintaan sudah diproses. Muat ulang status parkir.');
            }
            $this->db->table('permintaan_parkir')->where('id', $id)->update([
                'status' => $status, 'alasan_penolakan' => $alasan, 'updated_at' => date('Y-m-d H:i:s'),
            ]);
        });
    }

    public function terima(int $id, int $petugas): int
    {
        $this->pastikanPetugas($petugas);
        $row = $this->kueri()->where('r.id', $id)->get()->getRowArray();
        if (!$row) {
            throw new AturanParkirException('Permintaan parkir tidak ditemukan.');
        }

        return (new ParkirService($this->db))->kendaraanMasuk(
            $row['plat_nomor'], $row['jenis'], $petugas, $id,
        );
    }

    public function mintaKeluar(int $pengguna, int $id): void
    {
        $this->atomik(function () use ($pengguna, $id): void {
            $this->pastikanPengguna($pengguna);
            $row = $this->detail($pengguna, $id);
            if (!$row['tiket_id']) {
                throw new AturanParkirException('Kendaraan belum dikonfirmasi masuk oleh petugas.');
            }
            // Same lock order as cash checkout: ticket first, then the linked request.
            $ticket = $this->db->query('SELECT status FROM tiket_parkir WHERE id = ? FOR UPDATE',
                [$row['tiket_id']])->getRowArray();
            $row = $this->db->query('SELECT * FROM permintaan_parkir WHERE id = ? FOR UPDATE', [$id])
                ->getRowArray();
            if (!$ticket || $ticket['status'] !== 'aktif'
                || !in_array($row['status'], ['aktif', 'minta_keluar'], true)) {
                throw new AturanParkirException('Parkir sudah selesai atau tidak aktif.');
            }
            if ($row['status'] === 'minta_keluar') {
                return;
            }
            $now = date('Y-m-d H:i:s');
            $this->db->table('permintaan_parkir')->where('id', $id)->update([
                'status' => 'minta_keluar', 'waktu_minta_keluar' => $now, 'updated_at' => $now,
            ]);
        });
    }

    public function detail(int $pengguna, int $id): array
    {
        $row = $this->kueri()->where('r.id', $id)->where('r.pengguna_id', $pengguna)
            ->get()->getRowArray();
        if (!$row) {
            throw new AturanParkirException('Permintaan parkir tidak ditemukan.');
        }

        return $row;
    }

    public function milikPengguna(int $pengguna, bool $riwayat = false): BaseBuilder
    {
        return $this->kueri()->where('r.pengguna_id', $pengguna)
            ->whereIn('r.status', $riwayat ? self::RIWAYAT : self::TERBUKA);
    }

    public function antrean(string $status): BaseBuilder
    {
        return $this->kueri()->where('r.status', $status);
    }

    public function slot(): array
    {
        return $this->db->query(
            'SELECT s.jenis, COUNT(*) AS kapasitas, COUNT(t.id) AS terisi
             FROM slot_parkir s LEFT JOIN tiket_parkir t ON t.slot_aktif_id = s.id
             WHERE s.aktif = 1 GROUP BY s.jenis ORDER BY s.jenis DESC',
        )->getResultArray();
    }

    private function kueri(): BaseBuilder
    {
        return $this->db->table('permintaan_parkir r')
            ->select('r.*, k.plat_nomor, k.jenis, u.nama AS nama_pengguna, u.username,
                t.kode_tiket, t.waktu_masuk, t.waktu_keluar, t.tarif_awal_snapshot,
                t.tarif_lanjutan_snapshot, s.kode_slot, s.area,
                p.total AS total_bayar, p.durasi_menit, p.uang_diterima, p.kembalian, p.waktu_bayar')
            ->join('kendaraan k', 'k.id = r.kendaraan_id')
            ->join('users u', 'u.id = r.pengguna_id')
            ->join('tiket_parkir t', 't.id = r.tiket_id', 'left')
            ->join('slot_parkir s', 's.id = t.slot_id', 'left')
            ->join('pembayaran p', 'p.tiket_id = t.id', 'left');
    }

    private function pastikanPengguna(int $id): void
    {
        $row = $this->db->query('SELECT role, aktif FROM users WHERE id = ? FOR UPDATE', [$id])
            ->getRowArray();
        if (!$row || !$row['aktif'] || $row['role'] !== 'pengguna') {
            throw new AturanParkirException('Akun pengguna tidak aktif atau tidak memiliki izin.');
        }
    }

    private function pastikanPetugas(int $id): void
    {
        $row = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        if (!$row || !$row['aktif'] || !in_array($row['role'], ['admin', 'petugas'], true)) {
            throw new AturanParkirException('Konfirmasi hanya dapat dilakukan petugas atau admin.');
        }
    }

    private function atomik(callable $aksi): mixed
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->db->transException(true)->transBegin();
            try {
                $hasil = $aksi();
                if (!$this->db->transStatus() || !$this->db->transCommit()) {
                    throw new AturanParkirException('Perubahan belum tersimpan. Coba kembali.');
                }

                return $hasil;
            } catch (Throwable $error) {
                $this->db->transRollback();
                $this->db->resetTransStatus();
                if ($error instanceof AturanParkirException) {
                    throw $error;
                }
                if (in_array((int) $error->getCode(), [1062, 1205, 1213], true) && $attempt < 2) {
                    usleep(random_int(10000, 40000));
                    continue;
                }
                throw new AturanParkirException('Perubahan belum tersimpan. Muat ulang lalu coba kembali.');
            }
        }
        throw new AturanParkirException('Layanan sedang sibuk. Coba kembali.');
    }
}
