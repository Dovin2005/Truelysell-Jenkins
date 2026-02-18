<?php

namespace Modules\Faq\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Faq\app\Repositories\Contracts\FaqRepositoryInterface;
use Modules\Faq\app\Repositories\Eloquent\FaqRepository;

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
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
    }
}
