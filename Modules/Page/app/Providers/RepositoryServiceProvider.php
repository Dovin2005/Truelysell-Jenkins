<?php

namespace Modules\Page\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Page\app\Repositories\Contracts\PageRepositoryInterface;
use Modules\Page\app\Repositories\Eloquent\PageRepository;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            PageRepositoryInterface::class,
        ];
    }
}