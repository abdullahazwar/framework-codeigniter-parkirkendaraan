<?php

// Separate PHP process and database connection for actual concurrent requests.
require dirname(__DIR__, 2) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

// bootTest does not initialize a web/CLI application. Match its configured clock.
date_default_timezone_set(config('App')->appTimezone);

use App\Exceptions\AturanParkirException;
use App\Services\ParkirService;
use App\Services\PenggunaParkirService;
use Config\Database;

[$script, $gate, $worker, $action, $value, $petugas] = $argv;
file_put_contents($gate . '.' . $worker . '.ready', 'ready');
$deadline = microtime(true) + 10;
while (!is_file($gate)) {
    if (microtime(true) > $deadline) {
        fwrite(STDERR, 'Barrier timeout');
        exit(2);
    }
    usleep(10000);
}

try {
    $db = Database::connect('tests', false);
    $service = new ParkirService($db);
    if ($action === 'ajukan') {
        [$pengguna, $kendaraan] = explode(':', $value);
        $id = (new PenggunaParkirService($db))->ajukan((int) $pengguna, (int) $kendaraan);
    } elseif ($action === 'terima' || $action === 'batal_atau_terima') {
        if ($action === 'batal_atau_terima' && $worker === '0') {
            [$pengguna, $permintaan] = explode(':', $value);
            (new PenggunaParkirService($db))->batalkan((int) $pengguna, (int) $permintaan);
            $id = (int) $permintaan;
        } else {
            $id = (new PenggunaParkirService($db))->terima((int) $value, (int) $petugas);
        }
    } elseif ($action === 'keluar_atau_bayar' && $worker === '0') {
        [$pengguna, $permintaan] = explode(':', $value);
        (new PenggunaParkirService($db))->mintaKeluar((int) $pengguna, (int) $permintaan);
        $id = (int) $permintaan;
    } elseif ($action === 'masuk') {
        $id = $service->kendaraanMasuk($value, 'motor', (int) $petugas);
    } else {
        $service->kendaraanKeluar((int) $value, (int) $petugas, 10000, 3000);
        $id = (int) $value;
    }
    echo json_encode(['ok' => true, 'id' => $id], JSON_THROW_ON_ERROR);
} catch (AturanParkirException $e) {
    echo json_encode(['ok' => false, 'pesan' => $e->getMessage()], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    fwrite(STDERR, (string) $e);
    exit(2);
}
