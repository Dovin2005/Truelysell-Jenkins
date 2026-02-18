<?php

namespace Modules\Blogs\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Blogs\app\Repositories\Contracts\BlogRepositoryInterface;
use Modules\Blogs\app\Repositories\Eloquent\BlogRepository;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(BlogRepositoryInterface::class, BlogRepository::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            BlogRepositoryInterface::class,
        ];
    }
}