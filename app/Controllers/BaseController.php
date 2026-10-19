<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;

abstract class BaseController extends Controller
{
    protected $helpers = ['url', 'form', 'parkir'];

    protected function gagal(string $pesan): RedirectResponse
    {
        $input = $this->request->getPost();
        unset($input['password'], $input['password_baru']);
        session()->setFlashdata('_ci_old_input', ['get' => [], 'post' => $input]);

        return redirect()->back()->with('error', $pesan);
    }

    protected function userId(): int
    {
        return (int) session('user.id');
    }
}
