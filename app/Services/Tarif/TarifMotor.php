<?php

declare(strict_types=1);

namespace App\Services\Tarif;

final class TarifMotor extends TarifParkir
{
    public function __construct(private readonly int $tarifPerJam)
    {
        $this->validasiTarif($tarifPerJam);
    }

    public function hitung(int $menit): int
    {
        return $this->bulatJam($menit) * $this->tarifPerJam;
    }
}
