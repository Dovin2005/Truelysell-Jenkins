<?php

namespace Modules\Categories\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Categories\app\Repositories\Contracts\CategoryRepositoryInterface;
use Modules\Categories\app\Repositories\Eloquent\CategoryRepository;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            CategoryRepositoryInterface::class,
        ];
    }
}