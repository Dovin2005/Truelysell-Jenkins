<?php

namespace Modules\Leads\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Leads\app\Repositories\Contracts\LeadsInterface;
use Modules\Leads\app\Repositories\Contracts\ProviderInterface;
use Modules\Leads\app\Repositories\Contracts\UserLeadsInterface;
use Modules\Leads\app\Repositories\Eloquent\LeadsRepository;
use Modules\Leads\app\Repositories\Eloquent\ProviderRepository;
use Modules\Leads\app\Repositories\Eloquent\UserLeadsRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(UserLeadsInterface::class, UserLeadsRepository::class);
        $this->app->bind(ProviderInterface::class, ProviderRepository::class);
        $this->app->bind(LeadsInterface::class, LeadsRepository::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            UserLeadsInterface::class,
            ProviderInterface::class,
        ];
    }
}