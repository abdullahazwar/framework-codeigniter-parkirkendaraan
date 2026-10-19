<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('/', 'ProfilController::index', ['as' => 'beranda']);
$routes->get('profil', 'ProfilController::index', ['as' => 'profil']);
$routes->get('login', 'AuthController::login', ['as' => 'login']);
$routes->post('login', 'AuthController::authenticate', ['as' => 'login.submit']);
$routes->get('daftar', 'AuthController::register', ['as' => 'daftar']);
$routes->post('daftar', 'AuthController::storeRegistration', ['as' => 'daftar.submit']);

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('logout', 'AuthController::logout', ['as' => 'logout']);
    $routes->get('akun', 'AkunController::index', ['as' => 'akun']);
    $routes->group('', ['filter' => 'staff'], static function (RouteCollection $routes) {
        $routes->get('dashboard', 'DashboardController::index', ['as' => 'dashboard']);
        $routes->get('parkir/masuk', 'ParkirController::masuk', ['as' => 'parkir.masuk']);
        $routes->post('parkir/masuk', 'ParkirController::simpanMasuk', ['as' => 'parkir.simpan']);
        $routes->get('parkir/aktif', 'ParkirController::aktif', ['as' => 'parkir.aktif']);
        $routes->get('parkir/tiket/(:num)', 'ParkirController::tiket/$1', ['as' => 'parkir.tiket']);
        $routes->get('parkir/keluar/(:num)', 'ParkirController::keluar/$1', ['as' => 'parkir.keluar']);
        $routes->post('parkir/keluar/(:num)', 'ParkirController::bayar/$1', ['as' => 'parkir.bayar']);
        $routes->get('riwayat', 'ParkirController::riwayat', ['as' => 'riwayat']);
        $routes->group('admin', ['filter' => 'admin'], static function (RouteCollection $routes) {
            $routes->get('tarif', 'TarifController::index', ['as' => 'tarif']);
            $routes->post('tarif/(:segment)', 'TarifController::update/$1', ['as' => 'tarif.update']);
            $routes->get('slot', 'SlotController::index', ['as' => 'slot']);
            $routes->post('slot', 'SlotController::create', ['as' => 'slot.create']);
            $routes->post('slot/(:num)', 'SlotController::update/$1', ['as' => 'slot.update']);
            $routes->post('slot/(:num)/hapus', 'SlotController::delete/$1', ['as' => 'slot.delete']);
            $routes->get('petugas', 'PetugasController::index', ['as' => 'petugas']);
            $routes->post('petugas', 'PetugasController::create', ['as' => 'petugas.create']);
            $routes->post('petugas/(:num)', 'PetugasController::update/$1', ['as' => 'petugas.update']);
            $routes->get('laporan', 'LaporanController::index', ['as' => 'laporan']);
            $routes->get('laporan/csv', 'LaporanController::csv', ['as' => 'laporan.csv']);
        });
    });
});
