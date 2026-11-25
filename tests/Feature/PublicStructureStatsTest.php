<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicStructureStatsTest extends TestCase
{
    public function test_structure_profile_and_directory_use_database_stats_and_active_period(): void
    {
        config(['services.backend_api.url' => 'http://example.test/api']);

        Http::fake([
            '*landing-stats*' => Http::response(['members' => 30, 'committees' => 6, 'published_news' => 0]),
            '*periode-senat*' => Http::response([
                'status' => 'success',
                'data' => [
                    ['id_periode_senat' => 1, 'nama_periode' => '2025/2026', 'is_active' => false],
                    ['id_periode_senat' => 2, 'nama_periode' => '2027–2031', 'is_active' => true],
                ],
            ]),
            '*' => Http::response(['data' => []]),
        ]);

        foreach (['/profil/struktur-organisasi', '/profil', '/direktori-komisi'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertViewHas('direktori', function (array $direktori): bool {
                    return ($direktori['members'] ?? null) === 30
                        && ($direktori['committees'] ?? null) === 6
                        && ($direktori['period'] ?? null) === '2027–2031';
                })
                ->assertSee('30')
                ->assertSee('2027–2031');
        }
    }

    public function test_structure_falls_back_gracefully_when_backend_unavailable(): void
    {
        config(['services.backend_api.url' => 'http://example.test/api']);
        Http::fake(['*' => Http::response([], 503)]);

        $this->get('/profil/struktur-organisasi')
            ->assertOk()
            ->assertViewHas('direktori', function (array $direktori): bool {
                return ($direktori['members'] ?? 0) > 0
                    && ! empty($direktori['period']);
            });
    }

    public function test_vision_committee_cards_link_card_to_structure_and_selengkapnya_to_directory(): void
    {
        config(['services.backend_api.url' => 'http://example.test/api']);
        Http::fake([
            '*committees*' => Http::response([
                'data' => [[
                    'id' => 1,
                    'code' => 'KOM-I',
                    'name' => 'Komisi Pendidikan dan Pengajaran',
                    'lead' => 'Ketua Contoh',
                    'secretary' => 'Sekretaris Contoh',
                    'focus' => 'Fokus pendidikan.',
                    'members_count' => 7,
                    'members' => [],
                ]],
            ]),
            '*' => Http::response(['data' => []]),
        ]);

        $this->get('/profil')
            ->assertOk()
            ->assertSee(route('public.profile.structure').'#tupoksi-KOM-I', false)
            ->assertSee(route('public.directory', ['komisi' => 'KOM-I']), false);
    }
}
