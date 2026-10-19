<?php

namespace Tests\Unit;

use App\Entities\Kendaraan;
use App\Services\Tarif\TarifMobil;
use App\Services\Tarif\TarifMotor;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TarifTest extends TestCase
{
    public static function durasi(): array
    {
        return [
            [0, 3000, 5000],
            [1, 3000, 5000],
            [60, 3000, 5000],
            [61, 6000, 8000],
            [120, 6000, 8000],
            [121, 9000, 11000],
            [135, 9000, 11000],
            [1440, 72000, 74000],
        ];
    }

    #[DataProvider('durasi')]
    public function testBatasPembulatan(int $menit, int $motor, int $mobil): void
    {
        self::assertSame($motor, (new TarifMotor(3000))->hitung($menit));
        self::assertSame($mobil, (new TarifMobil(5000, 3000))->hitung($menit));
    }

    public function testTarifNegatifDitolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TarifMotor(-1);
    }

    public function testTarifMobilNegatifDitolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TarifMobil(5000, -1);
    }

    public function testDurasiNegatifDitolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TarifMotor(3000))->hitung(-1);
    }

    public function testPlatDinormalisasiDanInputBerbahayaDitolak(): void
    {
        self::assertSame('BE1234AB', Kendaraan::normalisasiPlat(' be 1234 ab '));
        self::assertTrue(Kendaraan::platValid('BE1234AB'));
        self::assertFalse(Kendaraan::platValid("' OR 1=1 --"));
        self::assertFalse(Kendaraan::platValid('<script>'));
    }
}
