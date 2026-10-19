<?php

declare(strict_types=1);

namespace App\Services\Tarif;

final class TarifMobil extends TarifParkir
{
    public function __construct(
        private readonly int $tarifAwal,
        private readonly int $tarifLanjutan,
    ) {
        $this->validasiTarif($tarifAwal, $tarifLanjutan);
    }

    public function hitung(int $menit): int
    {
        return $this->tarifAwal + ($this->bulatJam($menit) - 1) * $this->tarifLanjutan;
    }
}
