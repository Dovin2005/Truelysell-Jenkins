<?php

namespace App\Providers;

use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\SubscriptionInterface;
use App\Repositories\Contracts\TicketInterface;
use App\Repositories\Contracts\TransactionInterface;
use App\Repositories\Contracts\UserInterface;
use App\Repositories\Contracts\WalletInterface;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\TicketRepository;
use App\Repositories\Eloquent\TransactionRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WalletRepository;
use App\Repositories\Contracts\ContactRepositoryInterface;
use App\Repositories\Eloquent\ContactRepository;
use App\Repositories\Contracts\SocialAuthRepositoryInterface;
use App\Repositories\Eloquent\SocialAuthRepository;
use App\Repositories\Contracts\StaffRepositoryInterface;
use App\Repositories\Eloquent\BranchRepository;
use App\Repositories\Eloquent\StaffRepository;
use App\Repositories\Contracts\PaypalRepositoryInterface;
use App\Repositories\Eloquent\PaypalRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Eloquent\ServiceRepository;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Eloquent\StripeRepository;
use App\Repositories\Contracts\StripeRepositoryInterface;
use App\Repositories\Eloquent\AdminDashboardRepository;
use App\Repositories\Contracts\AdminDashboardRepositoryInterface;
use App\Repositories\Eloquent\MessageRepository;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Eloquent\AdminLoginRepository;
use App\Repositories\Contracts\AdminLoginRepositoryInterface;
use App\Repositories\Eloquent\BookRepository;
use App\Repositories\Contracts\BookRepositoryInterface;
use App\Repositories\Eloquent\AddonRepository;
use App\Repositories\Contracts\AddonRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Eloquent\BookingRepository;
use Modules\Faq\app\Repositories\Contracts\FaqRepositoryInterface;
use Modules\Faq\app\Repositories\Eloquent\FaqRepository;
use App\Repositories\Eloquent\ProviderRepository;
use App\Repositories\Contracts\ProviderRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBindings();
    }

    /**
     * Register repository bindings.
     */
    protected function registerBindings(): void
    {
        $this->app->bind(WalletInterface::class, WalletRepository::class);
        $this->app->bind(SubscriptionInterface::class, SubscriptionRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(SocialAuthRepositoryInterface::class, SocialAuthRepository::class);
        $this->app->bind(TicketInterface::class, TicketRepository::class);
        $this->app->bind(TransactionInterface::class, TransactionRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(StaffRepositoryInterface::class, StaffRepository::class);
        $this->app->bind(PaypalRepositoryInterface::class, PaypalRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(StripeRepositoryInterface::class, StripeRepository::class);
        $this->app->bind(AdminDashboardRepositoryInterface::class, AdminDashboardRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AdminLoginRepositoryInterface::class, AdminLoginRepository::class);
        $this->app->bind(BookRepositoryInterface::class, BookRepository::class);
        $this->app->bind(AddonRepositoryInterface::class, AddonRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(ProviderRepositoryInterface::class, ProviderRepository::class);
    }
}
