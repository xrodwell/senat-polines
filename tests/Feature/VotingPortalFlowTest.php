<?php

namespace Tests\Feature;

use App\Http\Controllers\Portal\Traits\HasVotingAndAttendance;
use App\Repositories\Api\ApiVotingRepository;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Alur portal e-voting terhadap kontrak backend nyata:
 * my-ballot privat, keputusan dari rekap backend, tanpa angka demo,
 * indikator offline, identitas presensi dari sesi, dan validasi token.
 */
class VotingPortalFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.backend_api.url' => 'https://backend.test', 'services.voting.url' => null]);
        session()->flush();
    }

    private function loginSession(int $userId = 6): void
    {
        session([
            'auth_token' => 'portal-token',
            'auth_user_id' => $userId,
            'auth_user' => ['id_user' => $userId, 'nama' => 'Anggota Uji', 'role' => 'anggota'],
            'auth_user_name' => 'Anggota Uji',
            'auth_user_role' => 'anggota',
            'auth_verified_at' => time(),
            'auth_token_checked_at' => time(),
        ]);
    }

    public function test_my_ballot_requires_token_and_calls_private_endpoint(): void
    {
        Http::fake(['https://backend.test/api/voting/7/my-ballot' => Http::response([
            'has_voted' => true,
            'my_ballot' => ['id_opsi' => 2, 'nama_opsi' => 'Tidak Setuju'],
        ])]);

        $repo = new ApiVotingRepository();
        $this->assertNull($repo->myBallot(7));
        Http::assertNothingSent();

        session(['auth_token' => 'portal-token']);
        $ballot = $repo->myBallot(7);
        $this->assertTrue($ballot['has_voted']);
        Http::assertSent(fn ($r) => $r->url() === 'https://backend.test/api/voting/7/my-ballot'
            && $r->hasHeader('Authorization', 'Bearer portal-token'));
    }

    public function test_format_voting_never_invents_demo_numbers(): void
    {
        Http::fake(['https://backend.test/api/voting*' => Http::response(['data' => [
            ['id_voting' => 3, 'status' => 'ditutup', 'judul_voting' => 'Uji'],
        ]])]);

        $item = (new ApiVotingRepository())->all()[0];
        $this->assertSame([], $item['opsi']);
        $this->assertSame(0, $item['total_suara']);
        $this->assertNull($item['kuorum']);
        $this->assertSame('', $item['rapat']['kode']);
        $this->assertNotSame('Sidang Pleno Senat Akademik', $item['rapat']['nama_rapat']);
    }

    public function test_map_decision_uses_backend_rekap(): void
    {
        $map = fn ($r) => HasVotingAndAttendance::mapDecision($r);
        $this->assertSame(['Tidak Tersedia', 'secondary'], $map(null));
        $this->assertSame(['Kuorum tidak tercapai.', 'secondary'], $map([
            'keputusan_sidang' => null, 'seri' => false, 'keputusan_alasan' => 'Kuorum tidak tercapai.',
        ]));
        $this->assertSame(['Seri - Musyawarah Lanjutan', 'warning'], $map([
            'keputusan_sidang' => null, 'seri' => true,
        ]));
        $this->assertSame(['Ditolak', 'danger'], $map(['keputusan_sidang' => 'Tidak Setuju', 'seri' => false]));
        $this->assertSame(['Disetujui', 'success'], $map(['keputusan_sidang' => 'Setuju', 'seri' => false]));
    }

    public function test_voting_page_reads_my_ballot_and_backend_decision(): void
    {
        $this->loginSession();
        Http::fake([
            'https://backend.test/api/voting?all=1' => Http::response(['data' => [
                ['id_voting' => 9, 'status' => 'dibuka', 'judul_voting' => 'Sesi Aktif', 'opsi_votings' => [
                    ['id_opsi' => 1, 'nama_opsi' => 'Setuju'],
                    ['id_opsi' => 2, 'nama_opsi' => 'Tidak Setuju'],
                ]],
                ['id_voting' => 5, 'status' => 'ditutup', 'judul_voting' => 'Sesi Lampau', 'opsi_votings' => [
                    ['id_opsi' => 1, 'nama_opsi' => 'Setuju', 'suara_votings_count' => 3],
                ]],
            ]]),
            'https://backend.test/api/voting/9/my-ballot' => Http::response([
                'has_voted' => true, 'is_invited' => true, 'is_present' => true,
                'my_ballot' => ['id_opsi' => 2, 'nama_opsi' => 'Tidak Setuju', 'waktu_vote' => '2026-11-19 08:00:00'],
            ]),
            'https://backend.test/api/voting/5/my-ballot' => Http::response([
                'has_voted' => false, 'is_invited' => true, 'is_present' => false, 'my_ballot' => null,
            ]),
            'https://backend.test/api/voting/5/rekap' => Http::response([
                'total_suara_masuk' => 3,
                'hasil_per_opsi' => [['nama_opsi' => 'Setuju', 'total_suara' => 3, 'persentase' => 100]],
                'keputusan_sidang' => null, 'seri' => false,
                'keputusan_alasan' => 'Kuorum tidak tercapai sehingga putusan tidak sah.',
                'kuorum' => ['total_invited' => 10, 'present_count' => 3, 'is_kuorum' => false],
            ]),
            '*' => Http::response(['data' => []]),
        ]);

        $response = $this->get('/portal/e-voting');
        $response->assertOk();
        $response->assertSee('Kuorum tidak tercapai sehingga putusan tidak sah.');
        $response->assertSee('Tidak Hadir dalam Sidang');
        $response->assertSee('Kuorum Belum Terpenuhi');
    }

    public function test_history_row_maps_canonical_quorum_keys(): void
    {
        $this->loginSession();
        Http::fake([
            'https://backend.test/api/voting?all=1' => Http::response(['data' => [
                ['id_voting' => 5, 'status' => 'ditutup', 'judul_voting' => 'Sesi Lampau',
                    'kuorum' => ['total_invited' => 10, 'present_count' => 8, 'is_kuorum' => true],
                    'opsi_votings' => [['id_opsi' => 1, 'nama_opsi' => 'Setuju', 'suara_votings_count' => 3]]],
            ]]),
            'https://backend.test/api/voting/5/rekap' => Http::response([
                'keputusan_sidang' => 'Setuju', 'seri' => false,
                'kuorum' => ['total_invited' => 10, 'present_count' => 8, 'is_kuorum' => true],
            ]),
            '*' => Http::response(['data' => []]),
        ]);

        $response = $this->get('/portal/e-voting');
        $response->assertOk();
        $response->assertSee('8/10');
        $response->assertSee('Kuorum Terpenuhi');
    }

    public function test_offline_backend_shows_indicator_not_fake_quorum(): void
    {
        $this->loginSession();
        Http::fake(['*' => Http::failedConnection('down')]);

        $response = $this->get('/portal/e-voting');
        $response->assertOk();
        $response->assertSee('Backend voting tidak terjangkau');
        $response->assertDontSee('Kuorum Terpenuhi');
    }

    public function test_submit_presensi_identity_comes_from_session_not_body(): void
    {
        $this->loginSession(6);
        Http::fake(['https://backend.test/api/rapat/presensi' => Http::response([
            'success' => true, 'message' => 'Presensi berhasil dicatat.',
        ])]);

        $this->postJson('/portal/presensi/submit', ['id_user' => 999, 'token_qr' => 'qr-uji'])
            ->assertOk();
        Http::assertSent(fn ($r) => $r->url() === 'https://backend.test/api/rapat/presensi'
            && (int) $r->data()['id_user'] === 6);
    }

    public function test_submit_presensi_without_session_is_401(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->postJson('/portal/presensi/submit', ['id_user' => 999, 'token_qr' => 'x'])
            ->assertStatus(401);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'rapat/presensi'));
    }

    public function test_middleware_revoked_backend_token_invalidates_session(): void
    {
        $this->loginSession();
        session(['auth_token_checked_at' => time() - 600]);
        Http::fake([
            'https://backend.test/api/me' => Http::response(['message' => 'Unauthenticated.'], 401),
            '*' => Http::response(['data' => []]),
        ]);

        $this->get('/portal/e-voting')->assertRedirect(route('portal.login'));
        $this->assertNull(session('auth_token'));
    }

    public function test_middleware_backend_outage_does_not_logout(): void
    {
        $this->loginSession();
        session(['auth_token_checked_at' => time() - 600]);
        Http::fake(['*' => Http::response(['message' => 'Maintenance'], 503)]);

        $this->get('/portal/e-voting')->assertOk();
        $this->assertSame('portal-token', session('auth_token'));
    }

    public function test_middleware_fresh_check_skips_backend_call(): void
    {
        $this->loginSession();
        Http::fake(['*' => Http::response(['data' => []])]);

        $this->get('/portal/e-voting')->assertOk();
        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/api/me'));
    }
}