<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$files = [$root . '/index.php'];
foreach (['app', 'tests', 'scripts', 'public'] as $folder) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $folder));
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}
$failed = false;
foreach ($files as $file) {
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1';
    exec($command, $output, $status);
    if ($status !== 0) {
        echo implode(PHP_EOL, $output), PHP_EOL;
        $failed = true;
    }
    $output = [];
}
echo count($files), ' file PHP diperiksa.', PHP_EOL;
exit($failed ? 1 : 0);
