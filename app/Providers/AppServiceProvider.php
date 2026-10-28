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
use App\Repositories\Api\ApiChatbotRepository;
use App\Repositories\Api\ApiCommitteeRepository;
use App\Repositories\Api\ApiMeetingRepository;
use App\Repositories\Api\ApiPostRepository;
use App\Repositories\Api\ApiRegulationRepository;
use App\Repositories\Mock\JsonArchiveRepository;
use App\Repositories\Mock\JsonAspirationRepository;
use App\Repositories\Mock\JsonCommitteeRepository;
use App\Repositories\Mock\JsonFeatureRepository;
use App\Repositories\Mock\JsonMeetingRepository;
use App\Repositories\Mock\JsonPostRepository;
use App\Repositories\Mock\JsonRegulationRepository;
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
        $this->app->bind(AspirationRepositoryInterface::class, JsonAspirationRepository::class);
        $this->app->bind(ArchiveRepositoryInterface::class, JsonArchiveRepository::class);
        $this->app->bind(ChatbotRepositoryInterface::class, ApiChatbotRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
