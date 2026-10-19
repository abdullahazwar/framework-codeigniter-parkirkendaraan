<?php

namespace App\Controllers;

use App\Models\UserModel;

final class AuthController extends BaseController
{
    public function login()
    {
        if (session('user')) {
            return redirect()->route('dashboard');
        }

        return view('auth/login', ['title' => 'Masuk']);
    }

    public function authenticate()
    {
        $username = strtolower(trim((string) $this->request->getPost('username')));
        $password = (string) $this->request->getPost('password');
        $ipKey = hash('sha256', $this->request->getIPAddress());
        $throttler = service('throttler');
        if (
            !$throttler->check('login-ip-' . $ipKey, 40, 300) ||
            !$throttler->check('login-user-' . hash('sha256', $username), 10, 300)
        ) {
            return $this->gagal('Terlalu banyak percobaan. Coba lagi dalam 5 menit.');
        }
        if (
            !$this->validateData(
                ['username' => $username, 'password' => $password],
                [
                    'username' => 'required|regex_match[/^[a-z0-9_]{3,50}$/]',
                    'password' => 'required|max_length[72]',
                ],
            )
        ) {
            return $this->gagal('Isi username dan password dengan benar.');
        }
        $model = new UserModel();
        $user = $model->where('username', $username)->first();
        $hash =
            $user['password_hash'] ??
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = password_verify($password, $hash);
        if (!$user || !$valid || !$user['aktif']) {
            return $this->gagal('Username atau password salah, atau akun tidak aktif.');
        }
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $model->update($user['id'], [
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        }
        unset($user['password_hash']);
        session()->regenerate(true);
        session()->set('user', $user);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->route('login');
    }
}
