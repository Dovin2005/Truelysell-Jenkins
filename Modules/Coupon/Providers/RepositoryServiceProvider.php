<?php
namespace Modules\Coupon\Providers;

use Modules\Coupon\app\Repositories\Contracts\CouponRepositoryInterface;
use Modules\Coupon\app\Repositories\Eloquent\CouponRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}