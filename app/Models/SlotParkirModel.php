<?php

namespace App\Models;

use CodeIgniter\Model;

final class SlotParkirModel extends Model
{
    protected $table = "slot_parkir";
    protected $primaryKey = "id";
    protected $returnType = "array";
    protected $allowedFields = ["kode_slot", "area", "jenis", "aktif"];
}
