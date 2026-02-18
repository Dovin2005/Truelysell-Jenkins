<?php

namespace Modules\Newsletter\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Newsletter\app\Repositories\Contracts\NewsletterRepositoryInterface;
use Modules\Newsletter\app\Repositories\Eloquent\NewsletterRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(NewsletterRepositoryInterface::class, NewsletterRepository::class);
    }
}
