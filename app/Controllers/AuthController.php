<?php

namespace App\Controllers;

use App\Models\UserModel;
use Throwable;

final class AuthController extends BaseController
{
    public function login()
    {
        if (session('user')) {
            return redirect()->route(session('user.role') === 'pengguna' ? 'akun' : 'dashboard');
        }

        $this->response->setHeader('Cache-Control', 'no-store, private');

        return view('auth/login', ['title' => 'Masuk', 'privatePage' => true]);
    }

    public function register()
    {
        if (session('user')) {
            return redirect()->route(session('user.role') === 'pengguna' ? 'akun' : 'dashboard');
        }

        $this->response->setHeader('Cache-Control', 'no-store, private');

        return view('auth/register', ['title' => 'Daftar akun pengguna', 'privatePage' => true]);
    }

    public function storeRegistration()
    {
        if (session('user')) {
            return redirect()->route(session('user.role') === 'pengguna' ? 'akun' : 'dashboard');
        }

        $ipKey = hash('sha256', $this->request->getIPAddress());
        if (!service('throttler')->check('register-ip-' . $ipKey, 10, 600)) {
            return $this->registrationError('Terlalu banyak percobaan daftar. Coba lagi nanti.');
        }

        $input = [];
        foreach (['nama', 'username', 'password', 'password_confirmation'] as $field) {
            $value = $this->request->getPost($field);
            $input[$field] = is_string($value) ? $value : '';
        }
        $input['nama'] = trim($input['nama']);
        $input['username'] = strtolower(trim($input['username']));

        $rules = [
            'nama' => [
                'rules' => 'required|max_length[100]',
                'errors' => [
                    'required' => 'Nama lengkap wajib diisi.',
                    'max_length' => 'Nama maksimal 100 karakter.',
                ],
            ],
            'username' => [
                'rules' => 'required|regex_match[/^[a-z0-9_]{3,50}$/]|is_unique[users.username]',
                'errors' => [
                    'required' => 'Username wajib diisi.',
                    'regex_match' => 'Gunakan 3 sampai 50 huruf, angka, atau underscore.',
                    'is_unique' => 'Username sudah dipakai. Pilih username lain.',
                ],
            ],
            'password' => [
                'rules' => 'required|min_length[12]|max_length[72]|regex_match[/^[^\x00]+$/]',
                'errors' => [
                    'required' => 'Password wajib diisi.',
                    'min_length' => 'Password minimal 12 karakter.',
                    'max_length' => 'Password terlalu panjang. Kurangi jumlah karakter.',
                    'regex_match' => 'Password mengandung karakter yang tidak didukung.',
                ],
            ],
            'password_confirmation' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Ulangi password untuk konfirmasi.',
                    'matches' => 'Konfirmasi password belum sama.',
                ],
            ],
        ];
        if (!$this->validateData($input, $rules)) {
            return $this->registrationError(
                'Periksa kembali data pendaftaran.',
                $this->validator->getErrors(),
            );
        }
        if (strlen($input['password']) > 72) {
            return $this->registrationError('Periksa kembali data pendaftaran.', [
                'password' => 'Password terlalu panjang. Kurangi jumlah karakter.',
            ]);
        }

        $model = new UserModel();
        $now = date('Y-m-d H:i:s');
        try {
            // Public registration always creates a regular user, never a staff account.
            $saved = $model->insert([
                'nama' => $input['nama'],
                'username' => $input['username'],
                'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
                'role' => 'pengguna',
                'aktif' => 1,
                'auth_version' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (Throwable) {
            return $this->registrationError(
                'Pendaftaran belum dapat diproses. Hubungi pengelola lokasi parkir.',
            );
        }
        if (!$saved) {
            return $this->registrationError('Akun belum tersimpan. Silakan coba lagi.');
        }

        session()->setFlashdata('_ci_old_input', [
            'get' => [],
            'post' => ['username' => $input['username']],
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat. Silakan masuk.');
    }

    private function registrationError(string $message, array $errors = [])
    {
        // Retain only non-sensitive fields, even when the request contains extra parameters.
        $old = [];
        foreach (['nama', 'username'] as $field) {
            $value = $this->request->getPost($field);
            $old[$field] = is_string($value) ? $value : '';
        }
        session()->setFlashdata('_ci_old_input', ['get' => [], 'post' => $old]);

        return redirect()->route('daftar')->with('error', $message)->with('errors', $errors);
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

        return redirect()->route($user['role'] === 'pengguna' ? 'akun' : 'dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->route('login');
    }
}
