<?php

namespace App\Providers;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\ChatbotRepositoryInterface;
use App\Contracts\CommitteeRepositoryInterface;
use App\Contracts\FeatureRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use App\Contracts\RapatWorkspaceRepositoryInterface;
use App\Contracts\VotingRepositoryInterface;
use App\Repositories\Api\ApiAspirationRepository;
use App\Repositories\Api\ApiChatbotRepository;
use App\Repositories\Api\ApiCommitteeRepository;
use App\Repositories\Api\ApiMeetingRepository;
use App\Repositories\Api\ApiPostRepository;
use App\Repositories\Api\ApiRegulationRepository;
use App\Repositories\Api\ApiRapatWorkspaceRepository;
use App\Repositories\Api\ApiVotingRepository;
use App\Repositories\Mock\JsonArchiveRepository;
use App\Repositories\Mock\JsonAspirationRepository;
use App\Repositories\Mock\JsonCommitteeRepository;
use App\Repositories\Mock\JsonFeatureRepository;
use App\Repositories\Mock\JsonMeetingRepository;
use App\Repositories\Mock\JsonPostRepository;
use App\Repositories\Mock\JsonRegulationRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PostRepositoryInterface::class, ApiPostRepository::class);
        $this->app->bind(MeetingRepositoryInterface::class, ApiMeetingRepository::class);
        $this->app->bind(RegulationRepositoryInterface::class, ApiRegulationRepository::class);
        $this->app->bind(CommitteeRepositoryInterface::class, ApiCommitteeRepository::class);
        $this->app->bind(FeatureRepositoryInterface::class, JsonFeatureRepository::class);
        $this->app->bind(AspirationRepositoryInterface::class, ApiAspirationRepository::class);
        $this->app->bind(ArchiveRepositoryInterface::class, JsonArchiveRepository::class);
        $this->app->bind(ChatbotRepositoryInterface::class, ApiChatbotRepository::class);
        $this->app->bind(VotingRepositoryInterface::class, ApiVotingRepository::class);
        $this->app->bind(RapatWorkspaceRepositoryInterface::class, ApiRapatWorkspaceRepository::class);
    }

    public function boot(): void
    {
        // Attach the portal bearer token to every outgoing backend call so the
        // API can authenticate the request instead of trusting the network.
        Http::globalRequestMiddleware(function ($request) {
            $token = session('auth_token');
            $backend = rtrim((string) config('services.backend_api.url', ''), '/');

            if ($token && $backend && str_starts_with((string) $request->url(), $backend)) {
                return $request->withHeader('Authorization', 'Bearer ' . $token);
            }

            return $request;
        });
    }
}
