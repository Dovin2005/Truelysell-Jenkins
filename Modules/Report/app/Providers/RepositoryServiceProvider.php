<?php

namespace Modules\Report\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Report\app\Repositories\Contracts\ReportRepositoryInterface;
use Modules\Report\app\Repositories\Eloquent\ReportRepository;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ReportRepositoryInterface::class,
        ];
    }
}