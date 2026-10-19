<?php

namespace App\Models;

use CodeIgniter\Model;

final class KendaraanModel extends Model
{
    protected $table = "kendaraan";
    protected $primaryKey = "id";
    protected $returnType = \App\Entities\Kendaraan::class;
    protected $allowedFields = ["plat_nomor", "jenis"];
}
