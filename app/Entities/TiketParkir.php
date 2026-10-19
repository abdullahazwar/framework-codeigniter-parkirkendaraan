<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

final class TiketParkir extends Entity
{
    protected $casts = [
        "id" => "integer",
        "tarif_awal_snapshot" => "integer",
        "tarif_lanjutan_snapshot" => "integer",
    ];

    public function masihAktif(): bool
    {
        return $this->attributes["status"] === "aktif";
    }
}
