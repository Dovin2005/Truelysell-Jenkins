<?php

namespace Modules\Communication\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Communication\app\Repositories\Contracts\EmailInterface;
use Modules\Communication\app\Repositories\Eloquent\EmailRepository;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(EmailInterface::class, EmailRepository::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            EmailInterface::class,
        ];
    }
}