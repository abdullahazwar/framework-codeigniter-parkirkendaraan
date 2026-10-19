<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */

$minPhpVersion = '8.2'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

$missing = array_filter(
    ['intl', 'mbstring', 'mysqli'],
    static fn(string $extension): bool => !extension_loaded($extension),
);
if ($missing !== []) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Ekstensi PHP belum aktif: ' . implode(', ', $missing) . ".\n";
    echo 'Aktifkan ekstensi tersebut di php.ini XAMPP, lalu restart Apache.';
    exit(1);
}

if (
    !is_file(__DIR__ . '/../vendor/codeigniter4/framework/system/Boot.php')
    || !is_file(__DIR__ . '/../vendor/autoload.php')
) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Dependensi bawaan proyek tidak lengkap.\n";
    echo 'Unduh ZIP repository terbaru dan ekstrak seluruh folder, termasuk vendor, lalu muat ulang halaman.';
    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// LOAD OUR PATHS CONFIG FILE
// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . '../app/Config/Paths.php';
// ^^^ Change this line if you move your application folder

$paths = new Paths();

// LOAD THE FRAMEWORK BOOTSTRAP FILE
require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
