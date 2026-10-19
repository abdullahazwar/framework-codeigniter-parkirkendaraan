<?php

namespace App\Controllers;

use App\Models\TarifModel;

final class AkunController extends BaseController
{
    public function index()
    {
        if (session('user.role') === 'pengguna') {
            return redirect()->route('pengguna.beranda');
        }

        return view('akun/index', [
            'title' => 'Akun saya',
            'privatePage' => true,
            'user' => session('user'),
            'tarif' => (new TarifModel())->orderBy('jenis', 'DESC')->findAll(),
        ]);
    }
}
