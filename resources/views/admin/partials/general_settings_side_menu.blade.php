<div class="col-xxl-2 col-xl-3 border-end pe-3">
    <div class="pt-3 d-flex flex-column list-group mb-4">
        <a href="{{ route('admin.general-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/general-settings') ? 'active' : '' }}">{{ __('company_settings') }}</a>
        <a href="{{ route('admin.logo-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/logo-settings') ? 'active' : '' }}">{{ __('logo_favicon_settings') }}</a>
        <a href="{{ route('admin.copyright-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/copyright-settings') ? 'active' : '' }}">{{ __('copyright_settings') }}</a>
        <a href="{{ route('admin.otp-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/otp-settings') ? 'active' : '' }}">{{ __('otp_settings') }}</a>
        <a href="{{ route('admin.cookies-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/cookies-settings') ? 'active' : '' }}">{{ __('cookies_settings') }}</a>
        <a href="{{ route('admin.maintenance-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/maintenance-settings') ? 'active' : '' }}">{{ __('maintenance_settings') }}</a>
        <a href="{{ route('admin.credential-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/credential-settings') ? 'active' : '' }}">{{ __('credential_settings') }}</a>
        <a href="{{ route('admin.commission') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/commission') ? 'active' : '' }}">{{ __('admin_commission') }}</a>
        <a href="{{ route('admin.tax-options') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/tax-options') ? 'active' : '' }}">{{ __('tax_options') }}</a>
        <a href="{{ route('admin.currency-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/currency-settings') ? 'active' : '' }}">{{ __('currency_settings') }}</a>
        <a href="{{ route('admin.language-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/language-settings') ? 'active' : '' }}">{{ __('language_settings') }}</a>
        <a href="{{ route('settings.custom-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/custom-settings') ? 'active' : '' }}">{{ __('custom_settings') }}</a>
        <a href="{{ route('admin.invoice-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/invoice-settings') ? 'active' : '' }}">{{ __('invoice_settings') }}</a>
        <a href="{{ route('admin.invoice-template') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/invoice-template') ? 'active' : '' }}">{{ __('invoice_template') }}</a>
        <a href="{{ route('admin.subscription-package') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/subscription-package') ? 'active' : '' }}">{{ __('subscription_package') }}</a>
        <a href="{{ route('admin.payment-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/payment-settings') ? 'active' : '' }}">{{ __('payment_settings') }}</a>
        <a href="{{ route('admin.dt-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/localization-settings') ? 'active' : '' }}">{{ __('localization_settings') }}</a>
        <a href="{{ route('admin.sitemap-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/sitemap-settings') ? 'active' : '' }}">{{ __('sitemap_settings') }}</a>
        <a href="{{ route('admin.preference') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/preference') ? 'active' : '' }}">{{ __('Preference') }}</a>
        <a href="{{ route('admin.db-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/dbbackuplist') ? 'active' : '' }}">{{ __('DB Backup') }}</a>
        <a href="{{ route('admin.file-storage') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/file-storage') ? 'active' : '' }}">{{ __('File Storage') }}</a>
        <a href="{{ route('admin.social-links') }}" class="d-block rounded p-2 {{ request()->is('admin/social-links') ? 'active' : '' }}">{{ __('social_links') }}</a>
        <a href="{{ route('admin.social-media-shares') }}" class="d-block rounded p-2 {{ request()->is('admin/social-media-shares') ? 'active' : '' }}">{{ __('social_shares') }}</a>
        <a href="{{ route('admin.colorSettings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/color') ? 'active' : '' }}">{{ __('color_settings') }}</a>
        <a href="{{ route('admin.clear-cache-settings') }}" class="d-block rounded p-2 {{ request()->is('admin/setting/clear-cache') ? 'active' : '' }}">{{ __('clear_cache') }}</a>
    </div>
</div>
