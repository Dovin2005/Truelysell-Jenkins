<?php

namespace Modules\Communication\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Communication\app\Repositories\Contracts\CommunicationInterface;
use Modules\Communication\app\Repositories\Contracts\EmailInterface;
use Modules\Communication\app\Repositories\Contracts\NotificationInterface;
use Modules\Communication\app\Repositories\Contracts\SmsInterface;
use Modules\Communication\app\Repositories\Eloquent\CommunicationRepository;
use Modules\Communication\app\Repositories\Eloquent\EmailRepository;
use Modules\Communication\app\Repositories\Eloquent\NotificationRepository;
use Modules\Communication\app\Repositories\Eloquent\SmsRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(EmailInterface::class, EmailRepository::class);
        $this->app->bind(SmsInterface::class, SmsRepository::class);
        $this->app->bind(NotificationInterface::class, NotificationRepository::class);
        $this->app->bind(CommunicationInterface::class, CommunicationRepository::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            EmailInterface::class,
            SmsInterface::class,
            NotificationInterface::class,
        ];
    }
}