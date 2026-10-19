<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AturanParkirException;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateTimeImmutable;

final class LaporanService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function periode(?string $dari, ?string $sampai): array
    {
        $dari = $dari ?: date('Y-m-01');
        $sampai = $sampai ?: date('Y-m-d');
        foreach ([$dari, $sampai] as $tanggal) {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal);
            if (!$date || $date->format('Y-m-d') !== $tanggal) {
                throw new AturanParkirException(
                    'Tanggal tidak valid. Gunakan format tahun-bulan-tanggal.',
                );
            }
        }
        if (
            $dari > $sampai ||
            (new DateTimeImmutable($dari))->diff(new DateTimeImmutable($sampai))->days > 366
        ) {
            throw new AturanParkirException('Periode harus berurutan dan maksimal 367 hari.');
        }

        return ['dari' => $dari, 'sampai' => $sampai];
    }

    public function pembayaran(array $periode): BaseBuilder
    {
        return $this->db
            ->table('pembayaran p')
            ->join('tiket_parkir t', 't.id = p.tiket_id')
            ->join('kendaraan k', 'k.id = t.kendaraan_id')
            ->join('users u', 'u.id = p.petugas_id')
            ->where('p.waktu_bayar >=', $periode['dari'] . ' 00:00:00')
            ->where(
                'p.waktu_bayar <',
                (new DateTimeImmutable($periode['sampai']))->modify('+1 day')->format('Y-m-d') .
                    ' 00:00:00',
            );
    }

    public function ringkasan(array $periode): array
    {
        $summary = $this->pembayaran($periode)
            ->select('COUNT(p.id) AS transaksi, COALESCE(SUM(p.total),0) AS pendapatan', false)
            ->get()
            ->getRowArray();
        $jenis = $this->pembayaran($periode)
            ->select('k.jenis, COUNT(p.id) AS transaksi, SUM(p.total) AS pendapatan', false)
            ->groupBy('k.jenis')
            ->get()
            ->getResultArray();

        return $summary + ['jenis' => $jenis];
    }

    public function harian(array $periode): array
    {
        return $this->pembayaran($periode)
            ->select(
                'DATE(p.waktu_bayar) AS tanggal, COUNT(p.id) AS transaksi, SUM(p.total) AS pendapatan',
                false,
            )
            ->groupBy('DATE(p.waktu_bayar)')
            ->orderBy('tanggal', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function bulanan(array $periode): array
    {
        return $this->pembayaran($periode)
            ->select(
                "DATE_FORMAT(p.waktu_bayar, '%Y-%m') AS bulan, COUNT(p.id) AS transaksi, SUM(p.total) AS pendapatan",
                false,
            )
            ->groupBy('bulan')
            ->orderBy('bulan', 'DESC')
            ->get()
            ->getResultArray();
    }

    public static function csvAman(string $value): string
    {
        return preg_match('/^[\s]*[=+@\-\t\r\n]/u', $value) ? "'" . $value : $value;
    }
}
