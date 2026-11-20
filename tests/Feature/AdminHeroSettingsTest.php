<?php

namespace Tests\Feature;

use App\Livewire\AdminPreview;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AdminHeroSettingsTest extends TestCase
{
    private array $data = [
        'eyebrow' => 'Normatif Akademik',
        'title' => "Judul\nKedua",
        'description' => 'Deskripsi',
        'background_image' => '/images/hero-custom.jpg',
        'primary_label' => 'Profil',
        'secondary_label' => 'Hukum',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        session(['auth_token' => 'hero-test-secret', 'auth_user' => ['id_user' => 1, 'nama' => 'Admin', 'role' => 'admin'], 'auth_user_role' => 'admin']);
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*hero-settings' => Http::response($this->data), '*' => Http::response(['data' => []])]);
    }

    private function editor()
    {
        return Livewire::withQueryParams(['tab' => 'pengaturan-informasi-hero'])->test(AdminPreview::class);
    }

    public function test_mount_select_preserve_draft_and_explicit_reload(): void
    {
        $this->editor()->assertSet('hero', $this->data)->assertSet('heroLoaded', true)
            ->assertSee('wire:submit="saveHeroSettings"', false)->assertDontSee('hero-test-secret')
            ->set('hero.title', 'Draft')->call('select', 'Dashboard Rapat')
            ->call('select', 'pengaturan-informasi-hero')->assertSet('hero.title', 'Draft')
            ->call('reloadHeroSettings')->assertSet('hero', $this->data);
    }

    public function test_editor_exposes_only_the_four_editable_fields(): void
    {
        $this->editor()->assertSee('wire:model="hero.eyebrow"', false)
            ->assertSee('wire:model="hero.title"', false)
            ->assertSee('wire:model="hero.description"', false)
            ->assertSee('wire:model="hero.background_image"', false)
            ->assertDontSee('wire:model="hero.primary_label"', false)
            ->assertDontSee('wire:model="hero.secondary_label"', false)
            ->assertDontSee('id="hero-primary_label"', false)
            ->assertDontSee('id="hero-secondary_label"', false);
    }

    public function test_save_uses_server_token_and_sends_only_editable_fields(): void
    {
        $saved = array_replace($this->data, ['eyebrow' => 'Pengantar baru', 'description' => 'Deskripsi baru', 'background_image' => 'https://images.example.com/hero.jpg']);
        $editor = $this->editor()->set('hero.eyebrow', $saved['eyebrow'])->set('hero.title', "Judul\r\nKedua")
            ->set('hero.description', $saved['description'])->set('hero.background_image', $saved['background_image'])
            ->set('hero.primary_label', 'Tampered primary')->set('hero.secondary_label', 'Tampered secondary');
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*hero-settings' => Http::response($saved), '*' => Http::response(['data' => []])]);
        $editor->call('saveHeroSettings')->assertSet('heroMessage', 'Pengaturan hero berhasil disimpan.')->assertSet('hero', $saved);
        Http::assertSent(fn ($request) => $request->method() === 'PUT'
            && $request->hasHeader('Authorization', 'Bearer hero-test-secret')
            && $request->data() == array_intersect_key($saved, array_flip(['eyebrow', 'title', 'description', 'background_image'])));
    }

    public function test_failed_read_blocks_save_and_successful_public_get_is_not_auth_proof(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*hero-settings' => Http::response(['title' => 'invalid']), '*' => Http::response(['data' => []])]);
        $this->editor()->assertSet('heroLoaded', false)->call('saveHeroSettings')->assertSet('heroMessage', '');
        Http::assertNotSent(fn ($request) => $request->method() === 'PUT');
    }

    public function test_write_failures_keep_draft_and_never_claim_success(): void
    {
        foreach ([401, 403, 422, 500, 200] as $status) {
            $editor = $this->editor()->set('hero.title', 'Draft');
            Http::swap(new \Illuminate\Http\Client\Factory());
            Http::fake(['*hero-settings' => Http::response(['message' => 'Error', 'errors' => ['title' => ['Judul invalid']]], $status), '*' => Http::response(['data' => []])]);
            $editor->call('saveHeroSettings')->assertSet('hero.title', 'Draft')->assertSet('heroMessage', '');
            if ($status === 422) {
                $editor->assertHasErrors('hero.title');
            }
            Http::swap(new \Illuminate\Http\Client\Factory());
            Http::fake(['*hero-settings' => Http::response($this->data), '*' => Http::response(['data' => []])]);
        }
        $editor = $this->editor()->set('hero.title', 'Draft');
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*hero-settings' => Http::failedConnection(), '*' => Http::response(['data' => []])]);
        $editor->call('saveHeroSettings')->assertSet('hero.title', 'Draft')->assertSet('heroMessage', '')->assertSee('tidak pasti');
    }

    public function test_local_validation_prevents_write(): void
    {
        $this->editor()->set('hero.title', "A\n\nB")->call('saveHeroSettings')->assertHasErrors('hero.title');
        Http::assertNotSent(fn ($request) => $request->method() === 'PUT');
    }

    public function test_unsafe_background_image_prevents_write(): void
    {
        foreach (['javascript:alert(1)', 'data:image/svg+xml,<svg onload="alert(1)">', '//untrusted.example/hero.jpg', '/images/hero.jpg" onerror="alert(1)'] as $image) {
            $this->editor()->set('hero.background_image', $image)->call('saveHeroSettings')
                ->assertHasErrors('hero.background_image')->assertSet('heroMessage', '');
        }
        Http::assertNotSent(fn ($request) => $request->method() === 'PUT');
    }

    public function test_full_hero_is_validated_before_selecting_editable_fields(): void
    {
        $this->editor()->set('hero.primary_label', '')->call('saveHeroSettings')->assertHasErrors('hero.primary_label');
        Http::assertNotSent(fn ($request) => $request->method() === 'PUT');
    }
}
