<?php

namespace Tests\Feature;

use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\ParkirTestCase;

final class AksesTest extends ParkirTestCase
{
    use FeatureTestTrait;

    public function testTamuWajibLogin(): void
    {
        $this->get('parkir/aktif')->assertRedirectTo('/login');
    }

    public function testPetugasTidakBolehMembukaAdmin(): void
    {
        $this->withSession(['user' => $this->user($this->petugasId)])
            ->get('admin/tarif')
            ->assertStatus(403);
    }

    public function testAdminBolehMembukaPengaturan(): void
    {
        $result = $this->withSession(['user' => $this->user($this->adminId)])->get('admin/tarif');
        $result->assertStatus(200);
        self::assertStringContainsString(
            'Simpan tarif motor',
            preg_replace('/\s+/', ' ', strip_tags($result->getBody())),
        );
    }

    public function testAkunDinonaktifkanMengakhiriSesi(): void
    {
        $session = ['user' => $this->user($this->petugasId)];
        $this->db
            ->table('users')
            ->where('id', $this->petugasId)
            ->update(['aktif' => 0]);
        $this->withSession($session)->get('dashboard')->assertRedirectTo('/login');
    }

    public function testVersiSesiLamaDitolak(): void
    {
        $session = ['user' => $this->user($this->petugasId)];
        $this->db
            ->table('users')
            ->where('id', $this->petugasId)
            ->update(['auth_version' => 2]);
        $this->withSession($session)->get('dashboard')->assertRedirectTo('/login');
    }

    public function testPostTanpaCsrfDitolak(): void
    {
        try {
            $this->withSession(['user' => $this->user($this->adminId)])->post('admin/tarif/motor', [
                'tarif_awal' => '0',
                'tarif_lanjutan' => '0',
            ]);
            self::fail('POST tanpa token CSRF seharusnya ditolak.');
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            self::assertSame(403, $e->getCode());
        }
        self::assertSame(
            3000,
            (int) $this->db->table('tarif')->where('jenis', 'motor')->get()->getRowArray()[
                'tarif_awal'
            ],
        );
    }
}
