<?php

namespace Tests\Feature;

use App\Repositories\Api\ApiVotingRepository;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VotingRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.backend_api.url' => 'https://backend.test', 'services.voting.url' => null]);
        session()->forget('auth_token');
    }

    public function test_empty_and_failed_public_lists_never_return_demo_data(): void
    {
        Http::fakeSequence('*')
            ->push(['data' => []])
            ->push(['data' => []])
            ->push(['message' => 'Offline'], 503)
            ->push(['message' => 'Offline'], 503);

        $repository = new ApiVotingRepository();
        $this->assertSame([], $repository->all());
        $this->assertTrue($repository->isAvailable());
        $this->assertNull($repository->lastError());
        $this->assertNull($repository->getActive());
        $this->assertTrue($repository->isAvailable());

        $this->assertSame([], $repository->all());
        $this->assertFalse($repository->isAvailable());
        $this->assertNotEmpty($repository->lastError());
        $this->assertNull($repository->getActive());
        $this->assertFalse($repository->isAvailable());
    }

    public function test_public_lists_use_config_and_all_without_losing_session_sixteen(): void
    {
        config(['services.voting.url' => 'https://configured.test/api/']);
        $items = array_map(fn ($id) => ['id_voting' => $id, 'status' => $id === 16 ? 'dibuka' : 'ditutup'], range(1, 17));
        Http::fake(['https://configured.test/api/voting*' => Http::response(['data' => $items])]);
        $repository = new ApiVotingRepository();
        $this->assertCount(17, $repository->all());
        $this->assertSame(16, $repository->getActive()['id']);
        Http::assertSent(fn ($request) => $request->url() === 'https://configured.test/api/voting?all=1');
        Http::assertSentCount(2);
    }

    public function test_truncated_or_malformed_lists_are_unavailable_and_never_follow_links(): void
    {
        foreach ([
            ['data' => [['id_voting' => 1]], 'next_page_url' => 'https://hostile.test/steal'],
            ['data' => ['data' => [['id_voting' => 1]], 'current_page' => 1, 'last_page' => 2]],
            ['data' => [['id_voting' => 1]], 'meta' => ['total' => 17]],
            ['data' => 'invalid'],
            ['message' => 'not a list'],
            ['data' => [null]],
        ] as $payload) {
            Http::fake(['*' => Http::response($payload)]);
            $repository = new ApiVotingRepository();
            $this->assertSame([], $repository->all());
            $this->assertFalse($repository->isAvailable());
            Http::assertSentCount(1);
            Http::assertNotSent(fn ($request) => str_contains($request->url(), 'hostile.test'));
        }
    }

    public function test_connection_failure_is_unavailable_without_exposing_exception(): void
    {
        Http::fake(['*' => Http::failedConnection('secret-token')]);
        $repository = new ApiVotingRepository();
        $this->assertSame([], $repository->all());
        $this->assertFalse($repository->isAvailable());
        $this->assertStringNotContainsString('secret-token', $repository->lastError());
    }
}
