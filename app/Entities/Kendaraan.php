<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

final class Kendaraan extends Entity
{
    public static function normalisasiPlat(string $plat): string
    {
        return strtoupper(preg_replace("/\s+/", "", trim($plat)) ?? "");
    }

    public static function platValid(string $plat): bool
    {
        return (bool) preg_match('/^[A-Z]{1,2}[0-9]{1,4}[A-Z]{0,3}$/D', $plat);
    }
}
