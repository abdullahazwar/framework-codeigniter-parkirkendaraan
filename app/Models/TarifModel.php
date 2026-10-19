<?php

namespace App\Models;

use CodeIgniter\Model;

final class TarifModel extends Model
{
    protected $table = "tarif";
    protected $primaryKey = "id";
    protected $returnType = "array";
    protected $allowedFields = ["jenis", "tarif_awal", "tarif_lanjutan", "updated_at"];
}
