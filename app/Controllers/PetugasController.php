<?php

namespace App\Controllers;

use App\Models\UserModel;
use Throwable;

final class PetugasController extends BaseController
{
    public function index()
    {
        $rows = (new UserModel())
            ->select('id,nama,username,role,aktif')
            ->where('role', 'petugas')
            ->orderBy('nama')
            ->findAll();
        $edit = null;
        foreach ($rows as $row) {
            if ((int) $row['id'] === (int) $this->request->getGet('edit')) {
                $edit = $row;
            }
        }

        return view('petugas/index', ['title' => 'Akun petugas', 'rows' => $rows, 'edit' => $edit]);
    }

    public function create()
    {
        return $this->simpan(null);
    }

    public function update(int $id)
    {
        return $this->simpan($id);
    }

    private function simpan(?int $id)
    {
        $rules = [
            'nama' => 'required|max_length[100]',
            'username' => 'required|regex_match[/^[A-Za-z0-9_]{3,50}$/]',
            'password' => ($id ? 'permit_empty' : 'required') . '|min_length[12]|max_length[72]',
            'aktif' => 'required|in_list[0,1]',
        ];
        if (!$this->validateData($this->request->getPost(), $rules)) {
            return $this->gagal(
                'Periksa nama, username (3 sampai 50 karakter), dan password (12 sampai 72 karakter).',
            );
        }
        $model = new UserModel();
        $existing = $id ? $model->where('role', 'petugas')->find($id) : null;
        if ($id && !$existing) {
            return $this->gagal('Akun petugas tidak ditemukan.');
        }
        $nama = trim((string) $this->request->getPost('nama'));
        if ($nama === '') {
            return $this->gagal('Nama petugas wajib diisi.');
        }
        $data = [
            'nama' => $nama,
            'username' => strtolower((string) $this->request->getPost('username')),
            'aktif' => (int) $this->request->getPost('aktif'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        try {
            if ($id) {
                // Atomic increment invalidates all previous sessions, even with simultaneous edits.
                $saved = $model
                    ->builder()
                    ->where('id', $id)
                    ->where('role', 'petugas')
                    ->set($data)
                    ->set('auth_version', 'auth_version + 1', false)
                    ->update();
            } else {
                $saved = $model->insert(
                    $data + ['role' => 'petugas', 'created_at' => date('Y-m-d H:i:s')],
                );
            }
            if (!$saved) {
                return $this->gagal('Akun belum tersimpan. Periksa username dan coba kembali.');
            }

            return redirect()
                ->route('petugas')
                ->with(
                    'success',
                    'Akun petugas disimpan. Perubahan akun mengakhiri sesi login sebelumnya.',
                );
        } catch (Throwable) {
            return $this->gagal('Akun gagal disimpan. Username harus unik.');
        }
    }
}
