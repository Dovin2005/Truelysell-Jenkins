<?php

namespace Modules\Service\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Service\app\Repositories\Contracts\ServiceRepositoryInterface;
use Modules\Service\app\Repositories\Eloquent\ServiceRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerBindings();
    }

    protected function registerBindings(): void
    {
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
    }
}
