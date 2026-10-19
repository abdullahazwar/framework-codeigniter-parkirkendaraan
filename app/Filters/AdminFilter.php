<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session("user.role") !== "admin") {
            return service("response")->setStatusCode(403)->setBody(view("errors/html/forbidden"));
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null,
    ) {}
}
