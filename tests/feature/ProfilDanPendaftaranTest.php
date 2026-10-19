<?php

namespace Tests\Feature;

use App\Services\ParkirService;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Tests\Support\ParkirTestCase;

final class ProfilDanPendaftaranTest extends ParkirTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        service('cache')->clean();
    }

    protected function tearDown(): void
    {
        // The migration deliberately refuses to remove a role still used by an account.
        $this->db->table('users')->where('role', 'pengguna')->delete();
        parent::tearDown();
    }

    public function testTamuBisaMembacaProfilDanMembukaForm(): void
    {
        foreach (['/', 'profil', 'login', 'daftar'] as $path) {
            $result = $this->get($path);
            $result->assertStatus(200);
            self::assertStringContainsString('href="' . url_to('login') . '"', $result->getBody());
            self::assertStringContainsString('href="' . url_to('daftar') . '"', $result->getBody());
        }
        $this->get('akun')->assertRedirectTo('/login');
    }

    public function testPendaftaranMembuatPenggunaTanpaHakPetugas(): void
    {
        $this->daftar([
            'nama' => ' Pengguna Baru ',
            'username' => ' Pengguna_BARU ',
            'role' => 'admin',
            'aktif' => 0,
            'auth_version' => 999,
        ])->assertRedirectTo('/login');
        $user = $this->db->table('users')->where('username', 'pengguna_baru')->get()->getRowArray();
        self::assertNotNull($user);
        self::assertSame('Pengguna Baru', $user['nama']);
        self::assertSame('pengguna', $user['role']);
        self::assertSame(1, (int) $user['aktif']);
        self::assertSame(1, (int) $user['auth_version']);
        self::assertTrue(password_verify('PasswordKhususTes123', $user['password_hash']));
        self::assertNull(session('user'));
        self::assertSame(['username' => 'pengguna_baru'], session('_ci_old_input')['post']);
    }

    public function testUsernameDuplikatTidakMenimpaAkun(): void
    {
        $count = $this->db->table('users')->countAllResults();
        $this->daftar(['username' => 'ADMIN_UJI'])->assertRedirectTo('/daftar');
        self::assertSame($count, $this->db->table('users')->countAllResults());
        self::assertArrayHasKey('username', session('errors'));
        self::assertSame('admin', $this->user($this->adminId)['role']);
    }

    public function testSqlMemperbaikiPendaftaranPadaSkemaLamaTanpaMengubahData(): void
    {
        $this->db->query(
            "ALTER TABLE users MODIFY role ENUM('admin','petugas') NOT NULL DEFAULT 'petugas'",
        );
        $parkir = new ParkirService($this->db);
        $tiket = $parkir->kendaraanMasuk('BE9912AA', 'motor', $this->petugasId);
        $parkir->kendaraanKeluar($tiket, $this->petugasId, 10000, 3000);

        $tables = ['users', 'kendaraan', 'slot_parkir', 'tarif', 'tiket_parkir', 'pembayaran'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = $this->db->table($table)->orderBy('id')->get()->getResultArray();
        }

        // A valid account is rejected while the existing column only accepts staff roles.
        $this->daftar()->assertRedirectTo('/daftar');
        self::assertSame(2, $this->db->table('users')->countAllResults());
        self::assertStringContainsString('Pendaftaran belum dapat diproses', session('error'));

        $sql = file_get_contents(APPPATH . 'Database/perbaikan_daftar_user.sql');
        self::assertNotFalse($sql);
        $this->db->query($sql);
        $this->db->query($sql);

        foreach ($tables as $table) {
            self::assertSame(
                $before[$table],
                $this->db->table($table)->orderBy('id')->get()->getResultArray(),
                'SQL pembaruan tidak boleh mengubah data ' . $table,
            );
        }

        $this->daftar()->assertRedirectTo('/login');
        $user = $this->db->table('users')->where('username', 'pengguna_baru')->get()->getRowArray();
        self::assertSame('pengguna', $user['role']);
        self::assertTrue(password_verify('PasswordKhususTes123', $user['password_hash']));
    }

    public function testKonfirmasiSalahTidakMenyimpanPasswordDalamSession(): void
    {
        $this->daftar(['password_confirmation' => 'KonfirmasiBerbeda123'])->assertRedirectTo('/daftar');
        self::assertArrayHasKey('password_confirmation', session('errors'));
        self::assertSame(['nama', 'username'], array_keys(session('_ci_old_input')['post']));
        self::assertStringNotContainsString('PasswordKhususTes123', serialize($_SESSION));
        self::assertStringNotContainsString('KonfirmasiBerbeda123', serialize($_SESSION));
        self::assertSame(2, $this->db->table('users')->countAllResults());
    }

    public function testInputTidakValidDitolakOlehServer(): void
    {
        $this->daftar(['nama' => '  ', 'username' => 'ab!', 'password' => 'pendek'])
            ->assertRedirectTo('/daftar');
        foreach (['nama', 'username', 'password'] as $field) {
            self::assertArrayHasKey($field, session('errors'));
        }
        self::assertSame(2, $this->db->table('users')->countAllResults());
    }

    public function testPasswordMultibyteTidakDipotongDiamDiam(): void
    {
        $password = str_repeat('界', 25);
        $this->daftar(['password' => $password, 'password_confirmation' => $password])
            ->assertRedirectTo('/daftar');
        self::assertArrayHasKey('password', session('errors'));
        self::assertSame(2, $this->db->table('users')->countAllResults());
    }

    public function testPendaftaranTanpaCsrfDitolak(): void
    {
        try {
            $this->post('daftar', ['username' => 'tanpa_csrf']);
            self::fail('Pendaftaran tanpa token CSRF seharusnya ditolak.');
        } catch (SecurityException $error) {
            self::assertSame(403, $error->getCode());
        }
        self::assertSame(2, $this->db->table('users')->countAllResults());
    }

    public function testPendaftaranDibatasiPerAlamatIp(): void
    {
        $key = 'register-ip-' . hash('sha256', service('request')->getIPAddress());
        for ($i = 0; $i < 10; $i++) {
            service('throttler')->check($key, 10, 600);
        }
        $this->daftar()->assertRedirectTo('/daftar');
        self::assertStringContainsString('Terlalu banyak', session('error'));
        self::assertSame(2, $this->db->table('users')->countAllResults());
    }

    public function testLoginPenggunaMenujuAkunDanMenampilkanTarifAktual(): void
    {
        $id = $this->buatPengguna();
        $token = csrf_hash();
        $this->withSession([csrf_token() => $token])->post('login', [
            'username' => 'pengguna_uji',
            'password' => 'PasswordKhususTes123',
            csrf_token() => $token,
        ])->assertRedirectTo('/pengguna');
        self::assertArrayNotHasKey('password_hash', session('user'));
        $this->db->table('tarif')->where('jenis', 'motor')->update([
            'tarif_awal' => 7000,
            'tarif_lanjutan' => 7000,
        ]);
        $this->withSession(['user' => $this->user($id)])->get('akun')->assertRedirectTo('/pengguna');
        $result = $this->withSession(['user' => $this->user($id)])->get('pengguna/profil');
        $result->assertStatus(200);
        $result->assertSee('Rp7.000');
        $result->assertSee('pengguna_uji');
        $result->assertDontSee('password_hash');
    }

    public function testPenggunaTidakBisaMengaksesOperasionalDanAdministrasi(): void
    {
        $user = $this->user($this->buatPengguna());
        foreach ([
            'dashboard', 'parkir/masuk', 'parkir/aktif', 'parkir/tiket/1',
            'parkir/keluar/1', 'riwayat', 'admin/tarif', 'admin/slot',
            'admin/petugas', 'admin/laporan', 'admin/laporan/csv',
        ] as $path) {
            $this->withSession(['user' => $user])->get($path)->assertStatus(403);
        }
        foreach ([
            'parkir/masuk', 'parkir/keluar/1', 'admin/tarif/motor', 'admin/slot',
            'admin/slot/1', 'admin/slot/1/hapus', 'admin/petugas', 'admin/petugas/1',
        ] as $path) {
            $token = csrf_hash();
            $this->withSession(['user' => $user, csrf_token() => $token])
                ->post($path, [csrf_token() => $token])
                ->assertStatus(403);
        }
        self::assertSame(0, $this->db->table('tiket_parkir')->countAllResults());
    }

    public function testPenggunaNonaktifTidakBisaMembukaAkun(): void
    {
        $id = $this->buatPengguna();
        $session = ['user' => $this->user($id)];
        $this->db->table('users')->where('id', $id)->update(['aktif' => 0]);
        $this->withSession($session)->get('akun')->assertRedirectTo('/login');
    }

    public function testPenggunaLoginTidakMembukaFormDaftarLagi(): void
    {
        $user = $this->user($this->buatPengguna());
        foreach (['login', 'daftar'] as $path) {
            $this->withSession(['user' => $user])->get($path)->assertRedirectTo('/pengguna');
        }
    }

    private function daftar(array $overrides = []): TestResponse
    {
        $token = csrf_hash();

        return $this->withSession([csrf_token() => $token])->post('daftar', array_replace([
            'nama' => 'Pengguna Baru',
            'username' => 'pengguna_baru',
            'password' => 'PasswordKhususTes123',
            'password_confirmation' => 'PasswordKhususTes123',
            csrf_token() => $token,
        ], $overrides));
    }

    private function buatPengguna(): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'nama' => 'Pengguna Uji',
            'username' => 'pengguna_uji',
            'password_hash' => password_hash('PasswordKhususTes123', PASSWORD_DEFAULT),
            'role' => 'pengguna',
            'aktif' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }
}
