<?php

namespace Modules\MenuBuilder\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\MenuBuilder\app\Repositories\Contracts\MenuBuilderRepositoryInterface;
use Modules\MenuBuilder\app\Repositories\Eloquent\MenuBuilderRepository;

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
        $this->app->bind(MenuBuilderRepositoryInterface::class, MenuBuilderRepository::class);
    }
}
