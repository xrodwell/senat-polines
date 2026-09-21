<?php

namespace App\Providers;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\CommitteeRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use App\Repositories\Mock\JsonArchiveRepository;
use App\Repositories\Mock\JsonAspirationRepository;
use App\Repositories\Mock\JsonCommitteeRepository;
use App\Repositories\Mock\JsonMeetingRepository;
use App\Repositories\Mock\JsonPostRepository;
use App\Repositories\Mock\JsonRegulationRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PostRepositoryInterface::class, JsonPostRepository::class);
        $this->app->bind(MeetingRepositoryInterface::class, JsonMeetingRepository::class);
        $this->app->bind(RegulationRepositoryInterface::class, JsonRegulationRepository::class);
        $this->app->bind(CommitteeRepositoryInterface::class, JsonCommitteeRepository::class);
        $this->app->bind(AspirationRepositoryInterface::class, JsonAspirationRepository::class);
        $this->app->bind(ArchiveRepositoryInterface::class, JsonArchiveRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
