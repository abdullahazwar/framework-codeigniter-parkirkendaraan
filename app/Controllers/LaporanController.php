<?php

namespace App\Controllers;

use App\Exceptions\AturanParkirException;
use App\Services\LaporanService;

final class LaporanController extends BaseController
{
    public function index()
    {
        $service = new LaporanService();
        try {
            $periode = $service->periode(
                $this->request->getGet('dari'),
                $this->request->getGet('sampai'),
            );
        } catch (AturanParkirException $e) {
            return redirect()->route('laporan')->with('error', $e->getMessage());
        }
        $page = max(1, (int) $this->request->getGet('page'));
        $summary = $service->ringkasan($periode);
        $page = min($page, max(1, (int) ceil($summary['transaksi'] / 20)));
        $rows = $service
            ->pembayaran($periode)
            ->select('p.*, t.kode_tiket, k.plat_nomor, k.jenis, u.nama AS petugas')
            ->orderBy('p.id', 'DESC')
            ->limit(20, ($page - 1) * 20)
            ->get()
            ->getResultArray();

        return view('laporan/index', [
            'title' => 'Laporan pendapatan',
            'periode' => $periode,
            'summary' => $summary,
            'harian' => $service->harian($periode),
            'bulanan' => $service->bulanan($periode),
            'rows' => $rows,
            'pagination' => service('pager')->makeLinks($page, 20, (int) $summary['transaksi']),
        ]);
    }

    public function csv()
    {
        $service = new LaporanService();
        try {
            $periode = $service->periode(
                $this->request->getGet('dari'),
                $this->request->getGet('sampai'),
            );
        } catch (AturanParkirException $e) {
            return redirect()->route('laporan')->with('error', $e->getMessage());
        }
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv(
            $stream,
            [
                'Tiket',
                'Plat',
                'Jenis',
                'Waktu bayar WIB',
                'Durasi menit',
                'Pendapatan IDR',
                'Tunai IDR',
                'Kembalian IDR',
                'Petugas',
            ],
            ',',
            '"',
            '',
        );
        $result = $service
            ->pembayaran($periode)
            ->select(
                't.kode_tiket, k.plat_nomor, k.jenis, p.waktu_bayar, p.durasi_menit, p.total, p.uang_diterima, p.kembalian, u.nama',
            )
            ->orderBy('p.id', 'DESC')
            ->get();
        while ($row = $result->getUnbufferedRow('array')) {
            fputcsv(
                $stream,
                array_map(
                    static fn($v) => LaporanService::csvAman((string) $v),
                    array_values($row),
                ),
                ',',
                '"',
                '',
            );
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader(
                'Content-Disposition',
                'attachment; filename="laporan-parkir-' .
                    $periode['dari'] .
                    '-' .
                    $periode['sampai'] .
                    '.csv"',
            )
            ->setBody($csv);
    }
}
