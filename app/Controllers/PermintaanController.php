<?php

namespace App\Controllers;

use App\Exceptions\AturanParkirException;
use App\Services\PenggunaParkirService;

final class PermintaanController extends BaseController
{
    public function index()
    {
        $service = new PenggunaParkirService();
        if (!$service->siap()) {
            return $this->response->setStatusCode(503)->setBody(view('errors/html/pengguna_belum_siap'));
        }
        $status = $this->request->getGet('status') === 'minta_keluar' ? 'minta_keluar' : 'menunggu';
        $q = substr(trim((string) $this->request->getGet('q')), 0, 80);
        $builder = $service->antrean($status);
        if ($q !== '') {
            $builder->groupStart()->like('k.plat_nomor', preg_replace('/\s+/', '', strtoupper($q)))
                ->orLike('r.kode_permintaan', $q)->orLike('u.nama', $q)->groupEnd();
        }
        $total = $builder->countAllResults(false);
        $page = min(max(1, (int) $this->request->getGet('page')), max(1, (int) ceil($total / 10)));

        return view('permintaan/index', [
            'title' => 'Permintaan pengguna', 'status' => $status, 'q' => $q, 'total' => $total,
            'menungguCount' => $service->antrean('menunggu')->countAllResults(),
            'keluarCount' => $service->antrean('minta_keluar')->countAllResults(),
            'rows' => $builder->orderBy('r.updated_at', 'ASC')->orderBy('r.id', 'ASC')
                ->limit(10, ($page - 1) * 10)->get()->getResultArray(),
            'pagination' => service('pager')->makeLinks($page, 10, $total),
        ]);
    }

    public function terima(int $id)
    {
        if ($this->request->getPost('konfirmasi_masuk') !== '1') {
            return $this->gagal('Cocokkan kendaraan dan kode permintaan, lalu centang konfirmasi.');
        }
        try {
            $tiket = (new PenggunaParkirService())->terima($id, $this->userId());

            return redirect()->to(url_to('parkir.tiket', $tiket))
                ->with('success', 'Kendaraan dikonfirmasi masuk. Tiket terhubung ke akun pengguna.');
        } catch (AturanParkirException $error) {
            return $this->gagal($error->getMessage());
        }
    }

    public function tolak(int $id)
    {
        if (!$this->validateData($this->request->getPost(), ['alasan' => 'required|max_length[200]'])) {
            return $this->gagal('Isi alasan penolakan, maksimal 200 karakter.');
        }
        try {
            (new PenggunaParkirService())->tolak($id, $this->userId(), (string) $this->request->getPost('alasan'));

            return redirect()->route('permintaan')->with('success', 'Permintaan ditolak. Alasan tampil pada akun pengguna.');
        } catch (AturanParkirException $error) {
            return $this->gagal($error->getMessage());
        }
    }
}
