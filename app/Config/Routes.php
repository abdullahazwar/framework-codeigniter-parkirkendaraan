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
    $routes->group('pengguna', ['filter' => 'pengguna'], static function (RouteCollection $routes) {
        $routes->get('/', 'PenggunaController::index', ['as' => 'pengguna.beranda']);
        $routes->get('kendaraan', 'PenggunaController::kendaraan', ['as' => 'pengguna.kendaraan']);
        $routes->post('kendaraan', 'PenggunaController::tambahKendaraan', ['as' => 'pengguna.kendaraan.tambah']);
        $routes->post('kendaraan/(:num)/hapus', 'PenggunaController::hapusKendaraan/$1',
            ['as' => 'pengguna.kendaraan.hapus']);
        $routes->get('parkir', 'PenggunaController::parkir', ['as' => 'pengguna.parkir']);
        $routes->post('parkir', 'PenggunaController::ajukan', ['as' => 'pengguna.ajukan']);
        $routes->get('tiket/(:num)', 'PenggunaController::tiket/$1', ['as' => 'pengguna.tiket']);
        $routes->post('tiket/(:num)/batal', 'PenggunaController::batalkan/$1', ['as' => 'pengguna.batal']);
        $routes->post('tiket/(:num)/keluar', 'PenggunaController::mintaKeluar/$1',
            ['as' => 'pengguna.keluar']);
        $routes->get('riwayat', 'PenggunaController::riwayat', ['as' => 'pengguna.riwayat']);
        $routes->get('profil', 'PenggunaController::profil', ['as' => 'pengguna.profil']);
    });
    $routes->group('', ['filter' => 'staff'], static function (RouteCollection $routes) {
        $routes->get('permintaan', 'PermintaanController::index', ['as' => 'permintaan']);
        $routes->post('permintaan/(:num)/terima', 'PermintaanController::terima/$1',
            ['as' => 'permintaan.terima']);
        $routes->post('permintaan/(:num)/tolak', 'PermintaanController::tolak/$1',
            ['as' => 'permintaan.tolak']);
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
