<?php

namespace App\Controllers;

final class ProfilController extends BaseController
{
    public function index()
    {
        if (session('user')) {
            $this->response->setHeader('Cache-Control', 'no-store, private');
        }

        return view('profil/index', [
            'title' => 'Parkir tertata, dari masuk sampai pulang',
            'description' => 'Kenali sistem Parkir untuk motor dan mobil. '
                . 'Pelajari pencatatan tiket, pengaturan slot, perhitungan tarif, dan alur pembayaran tunai.',
        ]);
    }
}
