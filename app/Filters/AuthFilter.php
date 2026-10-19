<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $user = $session->get("user");
        $current = $user ? (new UserModel())->find($user["id"]) : null;
        if (
            !$current ||
            !$current["aktif"] ||
            (int) $current["auth_version"] !== (int) ($user["auth_version"] ?? 0)
        ) {
            $session->remove("user");

            return redirect()->route("login")->with("error", "Silakan login untuk melanjutkan.");
        }
        unset($current["password_hash"]);
        $session->set("user", $current);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader("Cache-Control", "no-store, private");
    }
}
