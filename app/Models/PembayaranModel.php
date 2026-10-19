<?php

namespace App\Models;

use CodeIgniter\Model;

final class PembayaranModel extends Model
{
    protected $table = "pembayaran";
    protected $primaryKey = "id";
    protected $returnType = "array";
    protected $allowedFields = [
        "tiket_id",
        "petugas_id",
        "durasi_menit",
        "total",
        "uang_diterima",
        "kembalian",
        "metode",
        "waktu_bayar",
    ];
}
