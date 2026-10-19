<?php

namespace App\Models;

use CodeIgniter\Model;

final class TiketParkirModel extends Model
{
    protected $table = "tiket_parkir";
    protected $primaryKey = "id";
    protected $returnType = \App\Entities\TiketParkir::class;
    protected $allowedFields = [
        "kode_tiket",
        "kendaraan_id",
        "slot_id",
        "petugas_masuk_id",
        "waktu_masuk",
        "waktu_keluar",
        "status",
        "tarif_awal_snapshot",
        "tarif_lanjutan_snapshot",
    ];
}
