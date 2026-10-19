<?php

namespace App\Controllers;

use App\Exceptions\AturanParkirException;
use App\Services\LaporanService;
use App\Services\ParkirService;
use CodeIgniter\Exceptions\PageNotFoundException;

final class ParkirController extends BaseController
{
    public function masuk()
    {
        $slots = db_connect()
            ->query(
                'SELECT s.jenis, COUNT(*) AS kapasitas, COUNT(t.id) AS terisi
             FROM slot_parkir s LEFT JOIN tiket_parkir t ON t.slot_aktif_id = s.id
             WHERE s.aktif = 1 GROUP BY s.jenis',
            )
            ->getResultArray();

        return view('parkir/masuk', [
            'title' => 'Kendaraan masuk',
            'slots' => $slots,
            'tarif' => db_connect()
                ->table('tarif')
                ->orderBy('jenis', 'DESC')
                ->get()
                ->getResultArray(),
        ]);
    }

    public function simpanMasuk()
    {
        try {
            $id = (new ParkirService())->kendaraanMasuk(
                (string) $this->request->getPost('plat_nomor'),
                (string) $this->request->getPost('jenis'),
                $this->userId(),
            );

            return redirect()
                ->to(url_to('parkir.tiket', $id))
                ->with('success', 'Kendaraan tercatat. Tiket siap dicetak.');
        } catch (AturanParkirException $e) {
            return $this->gagal($e->getMessage());
        }
    }

    public function aktif()
    {
        return $this->daftar(false);
    }

    public function riwayat()
    {
        return $this->daftar(true);
    }

    private function daftar(bool $riwayat)
    {
        $q = substr(trim((string) $this->request->getGet('q')), 0, 100);
        $jenis = (string) $this->request->getGet('jenis');
        $page = max(1, (int) $this->request->getGet('page'));
        $builder = db_connect()
            ->table('tiket_parkir t')
            ->join('kendaraan k', 'k.id = t.kendaraan_id')
            ->join('slot_parkir s', 's.id = t.slot_id')
            ->join('pembayaran p', 'p.tiket_id = t.id', 'left')
            ->where('t.status', $riwayat ? 'selesai' : 'aktif');
        if ($q !== '') {
            $builder
                ->groupStart()
                ->like('t.kode_tiket', $q)
                ->orLike('k.plat_nomor', preg_replace('/\s+/', '', $q))
                ->groupEnd();
        }
        if (in_array($jenis, ['motor', 'mobil'], true)) {
            $builder->where('k.jenis', $jenis);
        }
        $total = $builder->countAllResults(false);
        $page = min($page, max(1, (int) ceil($total / 15)));
        $rows = $builder
            ->select('t.*, k.plat_nomor, k.jenis, s.kode_slot, s.area, p.total, p.durasi_menit')
            ->orderBy('t.id', 'DESC')
            ->limit(15, ($page - 1) * 15)
            ->get()
            ->getResultArray();

        return view('parkir/daftar', [
            'title' => $riwayat ? 'Riwayat parkir' : 'Parkir aktif',
            'rows' => $rows,
            'riwayat' => $riwayat,
            'q' => $q,
            'jenis' => $jenis,
            'total' => $total,
            'pagination' => service('pager')->makeLinks($page, 15, $total),
        ]);
    }

    public function tiket(int $id)
    {
        try {
            $tiket = (new ParkirService())->tiket($id);
        } catch (AturanParkirException) {
            throw PageNotFoundException::forPageNotFound();
        }
        $bayar = db_connect()
            ->table('pembayaran p')
            ->select('p.*, u.nama AS petugas_keluar')
            ->join('users u', 'u.id = p.petugas_id')
            ->where('p.tiket_id', $id)
            ->get()
            ->getRowArray();

        return view('parkir/tiket', [
            'title' => $bayar ? 'Bukti pembayaran' : 'Tiket masuk',
            'tiket' => $tiket,
            'bayar' => $bayar,
        ]);
    }

    public function keluar(int $id)
    {
        try {
            return view(
                'parkir/keluar',
                ['title' => 'Pembayaran keluar'] + (new ParkirService())->hitungTagihan($id),
            );
        } catch (AturanParkirException $e) {
            return redirect()->route('parkir.aktif')->with('error', $e->getMessage());
        }
    }

    public function bayar(int $id)
    {
        if (
            !$this->validateData($this->request->getPost(), [
                'uang_diterima' => 'required|is_natural|less_than_equal_to[9000000000000000]',
                'total_disetujui' => 'required|is_natural|less_than_equal_to[9000000000000000]',
                'konfirmasi' => 'required|in_list[1]',
            ])
        ) {
            return $this->gagal('Isi uang tunai dan centang konfirmasi penerimaan pembayaran.');
        }
        try {
            (new ParkirService())->kendaraanKeluar(
                $id,
                $this->userId(),
                (int) $this->request->getPost('uang_diterima'),
                (int) $this->request->getPost('total_disetujui'),
            );

            return redirect()
                ->to(url_to('parkir.tiket', $id))
                ->with('success', 'Pembayaran tersimpan. Slot kembali tersedia.');
        } catch (AturanParkirException $e) {
            return $this->gagal($e->getMessage());
        }
    }
}
