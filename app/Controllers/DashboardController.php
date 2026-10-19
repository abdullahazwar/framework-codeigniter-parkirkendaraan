<?php

namespace App\Controllers;

use App\Services\LaporanService;

final class DashboardController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $laporan = new LaporanService();
        $slots = $db
            ->query(
                'SELECT s.jenis, COUNT(*) AS kapasitas, COUNT(t.id) AS terisi
             FROM slot_parkir s LEFT JOIN tiket_parkir t ON t.slot_aktif_id = s.id
             WHERE s.aktif = 1 GROUP BY s.jenis ORDER BY s.jenis DESC',
            )
            ->getResultArray();
        $recent = $db
            ->table('tiket_parkir t')
            ->select('t.*, k.plat_nomor, k.jenis, s.kode_slot')
            ->join('kendaraan k', 'k.id = t.kendaraan_id')
            ->join('slot_parkir s', 's.id = t.slot_id')
            ->where('t.status', 'aktif')
            ->orderBy('t.id', 'DESC')
            ->limit(6)
            ->get()
            ->getResultArray();

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'slots' => $slots,
            'recent' => $recent,
            'summary' => $laporan->ringkasan(['dari' => $today, 'sampai' => $today]),
            'masuk' => $db
                ->table('tiket_parkir')
                ->where('waktu_masuk >=', $today . ' 00:00:00')
                ->where('waktu_masuk <', date('Y-m-d', strtotime('+1 day')) . ' 00:00:00')
                ->countAllResults(),
            'harian' => array_reverse(
                $laporan->harian([
                    'dari' => date('Y-m-d', strtotime('-6 days')),
                    'sampai' => $today,
                ]),
            ),
        ]);
    }
}
