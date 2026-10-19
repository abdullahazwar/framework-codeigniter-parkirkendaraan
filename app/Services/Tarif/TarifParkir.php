<?php

declare(strict_types=1);

namespace App\Services\Tarif;

use InvalidArgumentException;

abstract class TarifParkir
{
    protected function bulatJam(int $menit): int
    {
        if ($menit < 0) {
            throw new InvalidArgumentException('Durasi tidak boleh negatif.');
        }

        return max(1, intdiv($menit, 60) + ($menit % 60 > 0 ? 1 : 0));
    }

    protected function validasiTarif(int ...$tarif): void
    {
        foreach ($tarif as $nilai) {
            if ($nilai < 0 || $nilai > 1000000000) {
                throw new InvalidArgumentException('Tarif harus 0 sampai 1.000.000.000 rupiah.');
            }
        }
    }

    abstract public function hitung(int $menit): int;
}
