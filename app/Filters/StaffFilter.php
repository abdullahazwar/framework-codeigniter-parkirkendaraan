<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class StaffFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!in_array(session('user.role'), ['admin', 'petugas'], true)) {
            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/html/forbidden', [
                    'errorMessage' => 'Panel operasional hanya tersedia untuk admin dan petugas.',
                ]));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
