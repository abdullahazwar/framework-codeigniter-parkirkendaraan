<?php

namespace App\Controllers;

use App\Exceptions\AturanParkirException;
use Throwable;

final class SlotController extends BaseController
{
    public function index()
    {
        $rows = db_connect()
            ->query(
                'SELECT s.*, k.plat_nomor FROM slot_parkir s
             LEFT JOIN tiket_parkir t ON t.slot_aktif_id = s.id
             LEFT JOIN kendaraan k ON k.id = t.kendaraan_id ORDER BY s.area, s.kode_slot',
            )
            ->getResultArray();
        $edit = null;
        foreach ($rows as $row) {
            if ((int) $row['id'] === (int) $this->request->getGet('edit')) {
                $edit = $row;
            }
        }

        return view('slot/index', ['title' => 'Slot parkir', 'rows' => $rows, 'edit' => $edit]);
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
        if (
            !$this->validateData($this->request->getPost(), [
                'kode_slot' => 'required|max_length[20]|regex_match[/^[A-Za-z0-9-]+$/]',
                'area' => 'required|max_length[50]',
                'jenis' => 'required|in_list[motor,mobil]',
                'aktif' => 'required|in_list[0,1]',
            ])
        ) {
            return $this->gagal('Periksa kode slot, area, jenis, dan status aktif.');
        }
        $db = db_connect();
        $db->transException(true)->transBegin();
        try {
            if ($id !== null) {
                $exists = $db
                    ->query('SELECT id FROM slot_parkir WHERE id = ? FOR UPDATE', [$id])
                    ->getRowArray();
                if (!$exists) {
                    throw new AturanParkirException('Slot tidak ditemukan.');
                }
                if (
                    $db
                        ->query('SELECT id FROM tiket_parkir WHERE slot_aktif_id = ? FOR UPDATE', [
                            $id,
                        ])
                        ->getRowArray()
                ) {
                    throw new AturanParkirException(
                        'Slot sedang ditempati. Selesaikan parkir sebelum mengubah slot.',
                    );
                }
            }
            $data = [
                'kode_slot' => strtoupper(trim((string) $this->request->getPost('kode_slot'))),
                'area' => trim((string) $this->request->getPost('area')),
                'jenis' => $this->request->getPost('jenis'),
                'aktif' => (int) $this->request->getPost('aktif'),
            ];
            if ($data['area'] === '') {
                throw new AturanParkirException('Nama area wajib diisi.');
            }
            if ($id === null) {
                $db->table('slot_parkir')->insert($data);
            } else {
                $db->table('slot_parkir')->where('id', $id)->update($data);
            }
            if (!$db->transStatus() || !$db->transCommit()) {
                throw new AturanParkirException(
                    'Slot belum tersimpan. Periksa kode unik dan coba kembali.',
                );
            }

            return redirect()->route('slot')->with('success', 'Slot parkir disimpan.');
        } catch (Throwable $e) {
            $db->transRollback();

            return $this->gagal(
                $e instanceof AturanParkirException
                    ? $e->getMessage()
                    : 'Slot gagal disimpan. Kode harus unik di area yang sama.',
            );
        }
    }

    public function delete(int $id)
    {
        $db = db_connect();
        $db->transException(true)->transBegin();
        try {
            $row = $db
                ->query('SELECT id FROM slot_parkir WHERE id = ? FOR UPDATE', [$id])
                ->getRowArray();
            if (!$row) {
                throw new AturanParkirException('Slot tidak ditemukan.');
            }
            if ($db->table('tiket_parkir')->where('slot_id', $id)->countAllResults() > 0) {
                throw new AturanParkirException(
                    'Slot memiliki riwayat parkir. Gunakan status Nonaktif untuk menyimpan riwayat.',
                );
            }
            $db->table('slot_parkir')->where('id', $id)->delete();
            if (!$db->transStatus() || !$db->transCommit()) {
                throw new AturanParkirException('Slot belum dihapus. Coba kembali.');
            }

            return redirect()->route('slot')->with('success', 'Slot dihapus.');
        } catch (Throwable $e) {
            $db->transRollback();

            return redirect()
                ->route('slot')
                ->with(
                    'error',
                    $e instanceof AturanParkirException
                        ? $e->getMessage()
                        : 'Slot gagal dihapus. Coba kembali.',
                );
        }
    }
}
