<?php

namespace App\Filters;

use App\Services\PenggunaParkirService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class PenggunaFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session('user.role') !== 'pengguna') {
            return service('response')->setStatusCode(403)->setBody(view('errors/html/forbidden', [
                'errorMessage' => 'Area ini tersedia untuk akun pengguna parkir.',
            ]));
        }
        if (!(new PenggunaParkirService())->siap()) {
            return service('response')->setStatusCode(503)->setBody(view('errors/html/pengguna_belum_siap'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
