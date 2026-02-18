<?php

namespace Modules\GlobalSetting\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\GlobalSetting\app\Repositories\Contracts\CommunicationSettingsInterface;
use Modules\GlobalSetting\app\Repositories\Contracts\CredentialSettingInterface;
use Modules\GlobalSetting\app\Repositories\Contracts\CurrencyInterface;
use Modules\GlobalSetting\app\Repositories\Contracts\DbbackupInterface;
use Modules\GlobalSetting\app\Repositories\Contracts\FileStorageInterface;
use Modules\GlobalSetting\app\Repositories\Contracts\GlobalSettingInterface;
use Modules\GlobalSetting\app\Repositories\Contracts\InvoiceTemplateInterface;
use Modules\GlobalSetting\app\Repositories\Contracts\LanguageInterface;
use Modules\GlobalSetting\app\Repositories\Contracts\SitemapInterface;
use Modules\GlobalSetting\app\Repositories\Contracts\SubscriptionPackageInterface;
use Modules\GlobalSetting\app\Repositories\Eloquent\CommunicationSettingsRepository;
use Modules\GlobalSetting\app\Repositories\Eloquent\CredentialSettingRepository;
use Modules\GlobalSetting\app\Repositories\Eloquent\CurrencyRepository;
use Modules\GlobalSetting\app\Repositories\Eloquent\DbbackupRepository;
use Modules\GlobalSetting\app\Repositories\Eloquent\FileStorageRepository;
use Modules\GlobalSetting\app\Repositories\Eloquent\GlobalSettingRepository;
use Modules\GlobalSetting\app\Repositories\Eloquent\InvoiceTemplateRepository;
use Modules\GlobalSetting\app\Repositories\Eloquent\LanguageRepository;
use Modules\GlobalSetting\app\Repositories\Eloquent\SitemapRepository;
use Modules\GlobalSetting\app\Repositories\Eloquent\SubscriptionPackageRepository;
use Modules\GlobalSetting\app\Repositories\Contracts\SocialLinkRepositoryInterface;
use Modules\GlobalSetting\app\Repositories\Eloquent\SocialLinkRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerBindings();
    }

    /**
     * Register repository bindings.
     */
    protected function registerBindings(): void
    {
        $this->app->bind(SubscriptionPackageInterface::class, SubscriptionPackageRepository::class);
        $this->app->bind(InvoiceTemplateInterface::class, InvoiceTemplateRepository::class);
        $this->app->bind(LanguageInterface::class, LanguageRepository::class);
        $this->app->bind(SitemapInterface::class, SitemapRepository::class);
        $this->app->bind(FileStorageInterface::class, FileStorageRepository::class);
        $this->app->bind(DbbackupInterface::class, DbbackupRepository::class);
        $this->app->bind(CurrencyInterface::class,  CurrencyRepository::class);
        $this->app->bind(CredentialSettingInterface::class,  CredentialSettingRepository::class);
        $this->app->bind(CommunicationSettingsInterface::class,  CommunicationSettingsRepository::class);
        $this->app->bind(SocialLinkRepositoryInterface::class, SocialLinkRepository::class);
        $this->app->bind(GlobalSettingInterface::class,  GlobalSettingRepository::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            SocialLinkRepositoryInterface::class,
        ];
    }
}
