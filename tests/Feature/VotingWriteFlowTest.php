<?php

namespace Tests\Feature;

use App\Repositories\Api\ApiVotingRepository;
use App\Repositories\Mock\JsonVotingRepository;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Slice B — write path of the voting flow.
 *
 * Contract under test:
 *  - identity is carried by the bearer token only, never by the JSON body;
 *  - identity is never invented (no hardcoded user 2);
 *  - a failing backend is reported as a failure, never smoothed over by the mock;
 *  - status changes go through POST /voting/{id}/buka|tutup only, never a raw PUT.
 */
class VotingWriteFlowTest extends TestCase
{
    private const BASE = 'https://configured.test/api';

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        config(['services.voting.url' => self::BASE, 'services.backend_api.url' => null]);
        session()->forget(['auth_token', 'auth_user_id']);
    }

    /**
     * A fallback that would loudly claim success if any write ever reached it,
     * so that "no mock fallback for writes" can be asserted by call count.
     */
    private function writeSpy(): JsonVotingRepository
    {
        return new class extends JsonVotingRepository
        {
            public int $castVoteCalls = 0;

            public int $updateStatusCalls = 0;

            public function castVote(int $votingId, int $optionId, ?int $userId = null): array
            {
                $this->castVoteCalls++;

                return ['success' => true, 'message' => 'MOCK WRITE SUCCESS'];
            }

            public function updateStatus(int $votingId, string $status): array
            {
                $this->updateStatusCalls++;

                return ['success' => true, 'message' => 'MOCK WRITE SUCCESS'];
            }
        };
    }

    public function test_cast_vote_sends_bearer_token_and_keeps_identity_out_of_the_body(): void
    {
        session()->put('auth_token', 'test-token');

        Http::fake([self::BASE . '/suara-voting' => Http::response(['message' => 'ok'], 201)]);

        $result = (new ApiVotingRepository())->castVote(16, 3);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->method() === 'POST'
                && $request->url() === self::BASE . '/suara-voting'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && ! array_key_exists('id_user', $data)
                && (int) ($data['id_voting'] ?? 0) === 16
                && (int) ($data['id_opsi'] ?? 0) === 3;
        });
    }

    public function test_cast_vote_never_invents_an_identity_when_there_is_no_session(): void
    {
        $this->assertGuest();
        session()->forget(['auth_token', 'auth_user_id']);

        Http::fake(['*' => Http::response(['message' => 'ok'])]);

        $result = (new ApiVotingRepository())->castVote(16, 1);

        $this->assertFalse($result['success'] ?? true);
        $this->assertNotEmpty($result['message'] ?? null);

        // Nothing may be sent on behalf of an unidentified caller ...
        Http::assertNothingSent();
        // ... and in particular never as the hardcoded demo user 2.
        Http::assertNotSent(fn ($request) => (int) ($request->data()['id_user'] ?? 0) === 2);
    }

    public function test_cast_vote_reports_backend_failures_without_mock_fallback(): void
    {
        session()->put('auth_token', 'test-token');
        $fallback = $this->writeSpy();

        Http::fake([self::BASE . '/suara-voting' => Http::response(['message' => 'Backend error'], 500)]);

        $result = (new ApiVotingRepository($fallback))->castVote(16, 2);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['message']);
        $this->assertNotSame('MOCK WRITE SUCCESS', $result['message']);

        Http::fake([self::BASE . '/suara-voting' => Http::failedConnection('connection refused')]);

        $unreachable = (new ApiVotingRepository($fallback))->castVote(16, 2);

        $this->assertFalse($unreachable['success']);
        $this->assertSame('Backend tidak terjangkau', $unreachable['message']);

        $this->assertSame(0, $fallback->castVoteCalls);
        $this->assertSame(0, $fallback->updateStatusCalls);
    }

    public function test_update_status_only_uses_buka_and_tutup_and_never_puts_a_raw_status(): void
    {
        session()->put('auth_token', 'test-token');
        $fallback = $this->writeSpy();

        Http::fake([
            self::BASE . '/voting/16/buka' => Http::response(['message' => 'ok']),
            self::BASE . '/voting/16/tutup' => Http::response(['message' => 'ok']),
        ]);

        $opened = (new ApiVotingRepository($fallback))->updateStatus(16, 'dibuka');

        $this->assertTrue($opened['success']);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === self::BASE . '/voting/16/buka');

        $closed = (new ApiVotingRepository($fallback))->updateStatus(16, 'ditutup');

        $this->assertTrue($closed['success']);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === self::BASE . '/voting/16/tutup');

        foreach (['berlangsung', 'asdf', 'draft'] as $rejected) {
            $result = (new ApiVotingRepository($fallback))->updateStatus(16, $rejected);

            $this->assertFalse($result['success'] ?? true, "status '{$rejected}' must be rejected");
            $this->assertNotEmpty($result['message'] ?? null);

            Http::assertNotSent(fn ($request) => $request->method() === 'PUT' && $request->url() === self::BASE . '/voting/16');
        }

        Http::assertNotSent(fn ($request) => $request->method() === 'PUT');
        Http::assertSentCount(2);
        $this->assertSame(0, $fallback->updateStatusCalls);
        $this->assertSame(0, $fallback->castVoteCalls);
    }

    public function test_json_repository_writes_never_claim_success(): void
    {
        $repository = new JsonVotingRepository();

        foreach ([null, 2, 16] as $userId) {
            $vote = $repository->castVote(16, 1, $userId);

            $this->assertArrayHasKey('success', $vote);
            $this->assertFalse($vote['success']);
            $this->assertNotEmpty($vote['message'] ?? null);
        }

        foreach (['dibuka', 'ditutup', 'berlangsung', 'asdf', 'draft'] as $status) {
            $result = $repository->updateStatus(16, $status);

            $this->assertArrayHasKey('success', $result);
            $this->assertFalse($result['success']);
            $this->assertNotEmpty($result['message'] ?? null);
        }
    }
}
