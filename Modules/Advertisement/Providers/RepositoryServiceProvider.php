<?php

namespace Modules\Advertisement\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Advertisement\app\Repositories\Contracts\AdvertisementRepositoryInterface;
use Modules\Advertisement\app\Repositories\Eloquent\AdvertisementRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerBindings();
    }

    /**
     * Register repository bindings.
     */
    protected function registerBindings(): void
    {
        $this->app->bind(AdvertisementRepositoryInterface::class, AdvertisementRepository::class);
    }

     public function provides(): array
    {
        return [
            AdvertisementRepositoryInterface::class,
        ];
    }

    public function boot(): void {}
}
