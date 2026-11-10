<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortalMiddlewareTest extends TestCase
{
    private function anggotaSession(): array
    {
        return [
            'auth_token'     => 'test-token-anggota',
            'auth_user'      => ['id_user' => 2, 'nama' => 'Dr. Sukamto', 'email' => 'sukamto@senat.test', 'role' => 'anggota'],
            'auth_user_id'   => 2,
            'auth_user_role' => 'anggota',
        ];
    }

    private function adminSession(): array
    {
        return [
            'auth_token'     => 'test-token-admin',
            'auth_user'      => ['id_user' => 1, 'nama' => 'Admin Senat', 'email' => 'admin@senat.test', 'role' => 'admin'],
            'auth_user_id'   => 1,
            'auth_user_role' => 'admin',
        ];
    }

    public function test_guest_is_redirected_to_login_when_accessing_portal_dashboard(): void
    {
        $response = $this->get(route('portal.dashboard'));
        $response->assertRedirect(route('portal.login'));
        $response->assertSessionHasErrors('login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_preview(): void
    {
        $response = $this->get(route('admin.preview'));
        $response->assertRedirect(route('portal.login'));
        $response->assertSessionHasErrors('login');
    }

    public function test_authenticated_anggota_can_access_portal_dashboard(): void
    {
        $response = $this->withSession($this->anggotaSession())->get(route('portal.dashboard'));
        $response->assertOk();
    }

    public function test_authenticated_anggota_is_blocked_from_admin_preview(): void
    {
        $response = $this->withSession($this->anggotaSession())->get(route('admin.preview'));
        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_authenticated_admin_can_access_admin_preview(): void
    {
        $response = $this->withSession($this->adminSession())->get(route('admin.preview'));
        $response->assertOk();
    }

    public function test_authenticated_user_visiting_login_page_is_redirected_away(): void
    {
        // Anggota gets redirected to dashboard
        $this->withSession($this->anggotaSession())
            ->get(route('portal.login'))
            ->assertRedirect(route('portal.dashboard'));

        // Admin gets redirected to admin preview
        $this->withSession($this->adminSession())
            ->get(route('portal.login'))
            ->assertRedirect(route('admin.preview'));
    }
}
