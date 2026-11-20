<?php

namespace Tests\Feature;

use App\Support\HeroSettings;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicHeroSettingsTest extends TestCase
{
    private function fakeHero(mixed $data, int $status = 200): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*hero-settings' => Http::response($data, $status), '*' => Http::response(['data' => []])]);
    }

    public function test_six_fields_escaped_two_lines_links_and_sections_unchanged(): void
    {
        $this->fakeHero([
            'eyebrow' => '<b>Normatif & Akademik</b>',
            'title' => "<script>A</script>\nB & C",
            'description' => '<b>Description</b>',
            'background_image' => 'https://images.example.com/hero.jpg?width=1600&fit=cover',
            'primary_label' => '<i>Profil</i>',
            'secondary_label' => 'Hukum & Arsip',
        ]);
        $this->get('/')->assertOk()->assertSee('&lt;b&gt;Normatif &amp; Akademik&lt;/b&gt;', false)
            ->assertDontSee('<b>Normatif & Akademik</b>', false)->assertSee('&lt;script&gt;A&lt;/script&gt;', false)
            ->assertSee('<span class="text-senat-orange">B &amp; C</span>', false)
            ->assertSee('&lt;b&gt;Description&lt;/b&gt;', false)->assertSee('&lt;i&gt;Profil&lt;/i&gt;', false)
            ->assertSee('Hukum &amp; Arsip', false)->assertDontSee('<script>A</script>', false)
            ->assertSee('src="https://images.example.com/hero.jpg?width=1600&amp;fit=cover"', false)
            ->assertSee('href="'.route('public.profile').'"', false)->assertSee('href="'.route('public.regulations').'"', false)
            ->assertSee('ANGGOTA SENAT')->assertSee('Agenda Sidang');
    }

    public function test_single_line_title_root_relative_image_and_api_url_normalization(): void
    {
        foreach (['http://127.0.0.1:8001', 'http://127.0.0.1:8001/api', 'http://127.0.0.1:8001/api/'] as $base) {
            config(['services.backend_api.url' => $base]);
            $this->fakeHero(array_replace(HeroSettings::DEFAULTS, ['title' => 'Satu Baris', 'background_image' => '/images/custom-hero.jpg']));
            $this->get('/')->assertOk()->assertSee('Satu Baris')->assertDontSee('<span class="text-senat-orange">', false)
                ->assertSee('src="/images/custom-hero.jpg"', false);
            Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:8001/api/hero-settings');
        }
    }

    public function test_atomic_fallback_for_invalid_http_and_connection_failure(): void
    {
        foreach ([[['title' => 'Partial'], 200], [HeroSettings::DEFAULTS + ['extra' => 'x'], 200], [array_replace(HeroSettings::DEFAULTS, ['title' => "A\n\nB"]), 200], ['broken json', 200], [HeroSettings::DEFAULTS, 503]] as [$data, $status]) {
            $this->fakeHero($data, $status);
            $this->get('/')->assertOk()->assertSee('Senat Akademik')->assertSee('Politeknik Negeri Semarang')
                ->assertSee(HeroSettings::DEFAULTS['eyebrow'])->assertSee(HeroSettings::DEFAULTS['description'])
                ->assertSee('src="/images/gedung-polines.jpg"', false)
                ->assertSee('Profil &amp; Komisi', false)->assertSee('Repositori Produk Hukum')->assertDontSee('Partial');
        }
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*hero-settings' => Http::failedConnection(), '*' => Http::response(['data' => []])]);
        $this->get('/')->assertOk()->assertSee(HeroSettings::DEFAULTS['description']);
    }

    public function test_unsafe_background_images_fall_back_without_rendering_untrusted_content(): void
    {
        foreach (['javascript:alert(1)', 'data:image/svg+xml,<svg onload="alert(1)">', '//untrusted.example/hero.jpg', '/images/hero.jpg" onerror="alert(1)'] as $image) {
            $this->fakeHero(array_replace(HeroSettings::DEFAULTS, ['eyebrow' => 'Rejected eyebrow', 'background_image' => $image]));
            $this->get('/')->assertOk()->assertSee(HeroSettings::DEFAULTS['eyebrow'])
                ->assertSee('src="/images/gedung-polines.jpg"', false)->assertDontSee('Rejected eyebrow')
                ->assertDontSee('src="'.$image.'"', false)->assertDontSee('onerror="alert(1)"', false);
        }
    }

    public function test_frontend_and_backend_validation_contract_are_identical(): void
    {
        $this->assertSame(file_get_contents(base_path('app/Support/HeroSettings.php')), file_get_contents(dirname(base_path()).'/Backend-Senat-Polines/app/Support/HeroSettings.php'));
    }
}
