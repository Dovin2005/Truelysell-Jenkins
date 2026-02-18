<?php

namespace Modules\Testimonials\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Testimonials\app\Repositories\Contracts\TestimonialRepositoryInterface;
use Modules\Testimonials\app\Repositories\Eloquent\TestimonialRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TestimonialRepositoryInterface::class, TestimonialRepository::class);
    }
}
