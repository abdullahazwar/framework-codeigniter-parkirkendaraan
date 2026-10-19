<?php

namespace App\Controllers;

use App\Exceptions\AturanParkirException;
use App\Models\TarifModel;
use App\Services\ParkirService;
use App\Services\PenggunaParkirService;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Exceptions\PageNotFoundException;

final class PenggunaController extends BaseController
{
    public function index()
    {
        $service = new PenggunaParkirService();
        $terbuka = $service->milikPengguna($this->userId())->orderBy('r.id', 'DESC')
            ->limit(3)->get()->getResultArray();
        $selesai = $service->milikPengguna($this->userId(), true)->where('r.status', 'selesai');

        return view('pengguna/index', [
            'title' => 'Beranda pengguna', 'user' => session('user'),
            'kendaraanCount' => count($service->kendaraan($this->userId())),
            'parkirCount' => $service->milikPengguna($this->userId())
                ->whereIn('r.status', ['aktif', 'minta_keluar'])->countAllResults(),
            'selesaiCount' => $selesai->countAllResults(false),
            'recent' => $selesai->orderBy('r.id', 'DESC')->limit(3)->get()->getResultArray(),
            'terbuka' => $terbuka,
        ]);
    }

    public function kendaraan()
    {
        return view('pengguna/kendaraan', [
            'title' => 'Kendaraan saya',
            'kendaraan' => (new PenggunaParkirService())->kendaraan($this->userId()),
        ]);
    }

    public function tambahKendaraan()
    {
        if (!$this->validateData($this->request->getPost(), [
            'plat_nomor' => 'required|max_length[16]', 'jenis' => 'required|in_list[motor,mobil]',
        ])) {
            return $this->gagal('Isi plat nomor dan pilih jenis kendaraan.');
        }
        try {
            (new PenggunaParkirService())->tambahKendaraan(
                $this->userId(), (string) $this->request->getPost('plat_nomor'),
                (string) $this->request->getPost('jenis'),
            );

            return redirect()->route('pengguna.kendaraan')->with('success', 'Kendaraan berhasil ditambahkan.');
        } catch (AturanParkirException $error) {
            return $this->gagal($error->getMessage());
        }
    }

    public function hapusKendaraan(int $id)
    {
        try {
            (new PenggunaParkirService())->hapusKendaraan($this->userId(), $id);

            return redirect()->route('pengguna.kendaraan')
                ->with('success', 'Kendaraan dilepas dari daftar. Riwayat kunjungan tetap tersimpan.');
        } catch (AturanParkirException $error) {
            return $this->gagal($error->getMessage());
        }
    }

    public function parkir()
    {
        $service = new PenggunaParkirService();
        [$rows, $total, $pagination] = $this->halaman($service->milikPengguna($this->userId()));

        return view('pengguna/parkir', [
            'title' => 'Parkir saya', 'kendaraan' => $service->kendaraan($this->userId()),
            'slots' => $service->slot(), 'tarif' => (new TarifModel())->orderBy('jenis', 'DESC')->findAll(),
            'rows' => $rows, 'total' => $total, 'pagination' => $pagination,
        ]);
    }

    public function ajukan()
    {
        if (!$this->validateData($this->request->getPost(), [
            'kendaraan_id' => 'required|is_natural_no_zero', 'sudah_tiba' => 'required|in_list[1]',
        ])) {
            return $this->gagal('Pilih kendaraan dan konfirmasi sudah tiba di pintu masuk.');
        }
        try {
            $id = (new PenggunaParkirService())->ajukan(
                $this->userId(), (int) $this->request->getPost('kendaraan_id'),
            );

            return redirect()->to(url_to('pengguna.tiket', $id))
                ->with('success', 'Permintaan tercatat. Tunjukkan kode kepada petugas pintu masuk.');
        } catch (AturanParkirException $error) {
            return $this->gagal($error->getMessage());
        }
    }

    public function tiket(int $id)
    {
        $row = $this->milikSaya($id);
        $tagihan = null;
        if ($row['tiket_id'] && in_array($row['status'], ['aktif', 'minta_keluar'], true)) {
            try {
                $tagihan = (new ParkirService())->hitungTagihan((int) $row['tiket_id']);
            } catch (AturanParkirException) {
                $row = $this->milikSaya($id);
            }
        }

        return view('pengguna/tiket', [
            'title' => $row['status'] === 'selesai' ? 'Bukti pembayaran saya' : 'Detail parkir saya',
            'row' => $row, 'tagihan' => $tagihan,
        ]);
    }

    public function batalkan(int $id)
    {
        $this->milikSaya($id);
        try {
            (new PenggunaParkirService())->batalkan($this->userId(), $id);

            return redirect()->to(url_to('pengguna.tiket', $id))->with('success', 'Permintaan parkir dibatalkan.');
        } catch (AturanParkirException $error) {
            return $this->gagal($error->getMessage());
        }
    }

    public function mintaKeluar(int $id)
    {
        $this->milikSaya($id);
        try {
            (new PenggunaParkirService())->mintaKeluar($this->userId(), $id);

            return redirect()->to(url_to('pengguna.tiket', $id))
                ->with('success', 'Permintaan keluar terkirim. Bayar tunai kepada petugas di pintu keluar.');
        } catch (AturanParkirException $error) {
            return $this->gagal($error->getMessage());
        }
    }

    public function riwayat()
    {
        [$rows, $total, $pagination] = $this->halaman(
            (new PenggunaParkirService())->milikPengguna($this->userId(), true),
        );

        return view('pengguna/riwayat', [
            'title' => 'Riwayat parkir saya', 'rows' => $rows, 'total' => $total, 'pagination' => $pagination,
        ]);
    }

    public function profil()
    {
        return view('akun/index', [
            'title' => 'Profil saya', 'layout' => 'layouts/pengguna',
            'user' => session('user'), 'tarif' => (new TarifModel())->orderBy('jenis', 'DESC')->findAll(),
        ]);
    }

    private function milikSaya(int $id): array
    {
        try {
            return (new PenggunaParkirService())->detail($this->userId(), $id);
        } catch (AturanParkirException) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    private function halaman(BaseBuilder $builder): array
    {
        $total = $builder->countAllResults(false);
        $page = min(max(1, (int) $this->request->getGet('page')), max(1, (int) ceil($total / 10)));
        $rows = $builder->orderBy('r.id', 'DESC')->limit(10, ($page - 1) * 10)->get()->getResultArray();

        return [$rows, $total, service('pager')->makeLinks($page, 10, $total)];
    }
}
