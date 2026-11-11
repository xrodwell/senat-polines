<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    public function test_unauthorized_member_cannot_impersonate(): void
    {
        $response = $this->withSession([
            'auth_token' => 'member-token',
            'auth_user' => ['id_user' => 2, 'nama' => 'Dosen Senat', 'role' => 'anggota'],
            'auth_user_id' => 2,
            'auth_user_name' => 'Dosen Senat',
            'auth_user_role' => 'anggota',
        ])->post('/admin/impersonate/3');

        $response->assertRedirect('/portal/dashboard')
            ->assertSessionHas('error');
    }

    public function test_admin_can_impersonate_member_successfully(): void
    {
        Http::fake([
            '*/admin/impersonate/3' => Http::response([
                'status' => 'success',
                'message' => 'Impersonasi berhasil dimulai.',
                'token' => 'impersonated-plain-token',
                'user' => [
                    'id_user' => 3,
                    'nim_nip' => '198001012010121001',
                    'nama' => 'Ir. Budi Santoso, M.T.',
                    'email' => 'budi.santoso@polines.ac.id',
                    'role' => 'anggota',
                ],
                'impersonator' => [
                    'id_user' => 1,
                    'nama' => 'Admin Senat',
                    'role' => 'admin',
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'auth_token' => 'admin-token',
            'auth_user' => ['id_user' => 1, 'nama' => 'Admin Senat', 'role' => 'admin'],
            'auth_user_id' => 1,
            'auth_user_name' => 'Admin Senat',
            'auth_user_role' => 'admin',
            'auth_user_nip' => '197001011995011001',
            'auth_user_email' => 'admin@polines.ac.id',
        ])->post('/admin/impersonate/3');

        $response->assertRedirect('/portal/dashboard')
            ->assertSessionHas('success')
            ->assertSessionHas('impersonator')
            ->assertSessionHas('auth_token', 'impersonated-plain-token')
            ->assertSessionHas('auth_user_id', 3)
            ->assertSessionHas('auth_user_name', 'Ir. Budi Santoso, M.T.')
            ->assertSessionHas('auth_user_role', 'anggota');

        // Impersonator backup holds original admin info
        $this->assertEquals(1, session('impersonator.id'));
        $this->assertEquals('Admin Senat', session('impersonator.name'));
        $this->assertEquals('admin-token', session('impersonator.token'));
    }

    public function test_portal_views_reflect_impersonation_banner_and_dynamic_profile(): void
    {
        $impersonatedSession = [
            'auth_token' => 'impersonated-token',
            'auth_user' => [
                'id_user' => 3,
                'nim_nip' => '198001012010121001',
                'nama' => 'Ir. Budi Santoso, M.T.',
                'email' => 'budi.santoso@polines.ac.id',
                'role' => 'anggota',
            ],
            'auth_user_id' => 3,
            'auth_user_name' => 'Ir. Budi Santoso, M.T.',
            'auth_user_role' => 'anggota',
            'auth_user_nip' => '198001012010121001',
            'auth_user_email' => 'budi.santoso@polines.ac.id',
            'impersonator' => [
                'token' => 'admin-token',
                'id' => 1,
                'name' => 'Admin Senat',
                'role' => 'admin',
            ],
        ];

        // 1. Dashboard renders the impersonation banner
        $dashboardResponse = $this->withSession($impersonatedSession)->get('/portal/dashboard');
        $dashboardResponse->assertOk()
            ->assertSee('Mode Impersonasi')
            ->assertSee('Ir. Budi Santoso, M.T.')
            ->assertSee('Kembali ke Akun Admin');

        // 2. Profile renders target member's dynamic information instead of hardcoded chair
        $profileResponse = $this->withSession($impersonatedSession)->get('/portal/profil');
        $profileResponse->assertOk()
            ->assertSee('Ir. Budi Santoso, M.T.')
            ->assertSee('198001012010121001')
            ->assertSee('budi.santoso@polines.ac.id')
            ->assertSee('Mode Impersonasi');
    }

    public function test_leave_impersonate_restores_admin_session(): void
    {
        Http::fake([
            '*/logout' => Http::response(['message' => 'Logged out'], 200),
        ]);

        $impersonatedSession = [
            'auth_token' => 'target-token',
            'auth_user' => ['id_user' => 3, 'nama' => 'Ir. Budi Santoso, M.T.', 'role' => 'anggota'],
            'auth_user_id' => 3,
            'auth_user_name' => 'Ir. Budi Santoso, M.T.',
            'auth_user_role' => 'anggota',
            'impersonator' => [
                'token' => 'original-admin-token',
                'user' => ['id_user' => 1, 'nama' => 'Admin Senat', 'role' => 'admin'],
                'id' => 1,
                'name' => 'Admin Senat',
                'role' => 'admin',
                'nip' => '197001011995011001',
                'email' => 'admin@polines.ac.id',
            ],
        ];

        $response = $this->withSession($impersonatedSession)->post('/portal/leave-impersonate');

        $response->assertRedirect('/admin')
            ->assertSessionHas('success')
            ->assertSessionMissing('impersonator')
            ->assertSessionHas('auth_token', 'original-admin-token')
            ->assertSessionHas('auth_user_id', 1)
            ->assertSessionHas('auth_user_name', 'Admin Senat')
            ->assertSessionHas('auth_user_role', 'admin');
    }

    public function test_leave_impersonate_without_impersonator_redirects_safely(): void
    {
        $response = $this->withSession([
            'auth_token' => 'regular-member-token',
            'auth_user' => ['id_user' => 2, 'nama' => 'Dosen Senat', 'role' => 'anggota'],
            'auth_user_id' => 2,
            'auth_user_role' => 'anggota',
        ])->post('/portal/leave-impersonate');

        $response->assertRedirect('/portal/dashboard');
    }
}
