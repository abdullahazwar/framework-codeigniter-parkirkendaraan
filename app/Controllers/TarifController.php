<?php

namespace App\Controllers;

use App\Models\TarifModel;

final class TarifController extends BaseController
{
    public function index()
    {
        return view('tarif/index', [
            'title' => 'Tarif parkir',
            'rows' => (new TarifModel())->orderBy('jenis', 'DESC')->findAll(),
        ]);
    }

    public function update(string $jenis)
    {
        if (
            !in_array($jenis, ['motor', 'mobil'], true) ||
            !$this->validateData($this->request->getPost(), [
                'tarif_awal' => 'required|is_natural|less_than_equal_to[1000000000]',
                'tarif_lanjutan' => 'required|is_natural|less_than_equal_to[1000000000]',
            ])
        ) {
            return $this->gagal('Tarif harus berupa angka 0 sampai 1.000.000.000.');
        }
        $awal = (int) $this->request->getPost('tarif_awal');
        $lanjutan = $jenis === 'motor' ? $awal : (int) $this->request->getPost('tarif_lanjutan');
        $model = new TarifModel();
        $saved = $model
            ->where('jenis', $jenis)
            ->set([
                'tarif_awal' => $awal,
                'tarif_lanjutan' => $lanjutan,
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
        if (!$saved) {
            return $this->gagal('Tarif belum tersimpan. Coba kembali.');
        }

        return redirect()
            ->route('tarif')
            ->with(
                'success',
                'Tarif diperbarui. Tiket yang sudah masuk tetap memakai tarif sebelumnya.',
            );
    }
}
