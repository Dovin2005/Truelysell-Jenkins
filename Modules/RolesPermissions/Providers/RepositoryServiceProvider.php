<?php

namespace Modules\RolesPermissions\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\RolesPermissions\app\Repositories\Contracts\RolesPermissionsRepositoryInterface;
use Modules\RolesPermissions\app\Repositories\Eloquent\RolesPermissionsRepository;

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
        $this->app->bind(RolesPermissionsRepositoryInterface::class, RolesPermissionsRepository::class);
    }
}
