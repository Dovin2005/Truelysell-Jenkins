<?php
namespace Modules\Chat\Providers;

use Modules\Chat\app\Repositories\Contracts\ChatRepositoryInterface;
use Modules\Chat\app\Repositories\Eloquent\ChatRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}