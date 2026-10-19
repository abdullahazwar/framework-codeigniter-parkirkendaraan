<?php

// Separate PHP process and database connection for actual concurrent requests.
require dirname(__DIR__, 2) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

use App\Exceptions\AturanParkirException;
use App\Services\ParkirService;
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
    $service = new ParkirService(Database::connect('tests', false));
    if ($action === 'masuk') {
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
