<?php

declare(strict_types=1);

// Check a fresh checkout before any development tools are installed.
$root = dirname(__DIR__);

try {
    $lock = json_decode(file_get_contents($root . '/composer.lock'), true, 512, JSON_THROW_ON_ERROR);
    $installed = json_decode(file_get_contents($root . '/vendor/composer/installed.json'), true, 512, JSON_THROW_ON_ERROR);
    if ($installed['dev'] !== false || $installed['dev-package-names'] !== []) {
        throw new RuntimeException('Pemeriksaan ini memerlukan vendor runtime tanpa paket pengembangan.');
    }

    $versions = static function (array $packages): array {
        $result = [];
        foreach ($packages as $package) {
            $result[$package['name']] = [$package['version'], $package['source']['reference']];
        }
        ksort($result);

        return $result;
    };

    if ($versions($installed['packages']) !== $versions($lock['packages'])) {
        throw new RuntimeException('Dependensi bawaan tidak sesuai composer.lock.');
    }

    require $root . '/vendor/autoload.php';
    if (
        !class_exists(CodeIgniter\Boot::class)
        || !class_exists(Laminas\Escaper\Escaper::class)
        || !interface_exists(Psr\Log\LoggerInterface::class)
        || !class_exists(App\Services\ParkirService::class)
    ) {
        throw new RuntimeException('Autoloader atau dependensi runtime tidak lengkap.');
    }

    foreach ($installed['packages'] as $package) {
        echo $package['name'], ' ', $package['version'], PHP_EOL;
    }
    echo 'Runtime siap tanpa menjalankan Composer.', PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
