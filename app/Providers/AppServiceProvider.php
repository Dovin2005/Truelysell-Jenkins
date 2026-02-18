<?php

namespace App\Providers;

use App\Models\AddonModule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\MenuBuilder\app\Models\Menu;
use Modules\Categories\app\Models\Categories;
use Modules\Product\app\Models\Product;
use Modules\Page\app\Models\Footer;
use App\Models\ProviderSocialLink;
use Modules\GlobalSetting\app\Models\Language;
use Illuminate\Support\Facades\Cookie;
use Modules\GlobalSetting\app\Models\SocialLink;
use App\Models\SocialMediaShare;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $modulesStatusPath = base_path('modules_statuses.json');
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

        if (File::exists($modulesStatusPath)) {
            $modulesStatus = json_decode(File::get($modulesStatusPath), true);

            // Check if 'Installer' key exists and is true
            if (isset($modulesStatus['Installer']) && $modulesStatus['Installer'] === true) {
                $this->loadRoutesFrom(base_path('Modules/Installer/routes/web.php'));
            } else {
                // Execute the fallback logic if 'Installer' is false or not enabled
                $this->loadHelpers();
                $this->composeHeaderView();
                $this->composeFooterView();
                $this->composeGlobalViews();
                $this->shareCategories();
                $this->cookies();
            }
        } else {
            // Execute the fallback logic if the file doesn't exist
            $this->loadHelpers();
            $this->composeHeaderView();
            $this->composeFooterView();
            $this->composeGlobalViews();
            $this->shareCategories();
            $this->cookies();
        }
    }


    /**
     * Load custom helpers.
     */
    private function loadHelpers(): void
    {
        $helpersPath = app_path('Helpers/Helpers.php');

        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }
    }

    /**
     * Compose the header view.
     */
    private function composeHeaderView(): void
    {
        View::composer('user.partials.header', function ($view) {

            $languages = Language::select('code')->where('status', 1)
                ->whereNull('deleted_at')
                ->get();
            $selectedLanguageId = 1;
            if (Auth::check()) {
                $selectedLanguageId = Auth::user()->user_language_id;
            } elseif (Cookie::get('languageId')) {
                $selectedLanguageId = Cookie::get('languageId');
            } elseif ($selectedLanguageId == null) {
                $defaultLanguage = Language::select('id', 'code')->where('is_default', 1)->whereNull('deleted_at')->first();
                $selectedLanguageId = $defaultLanguage ? $defaultLanguage->id : null;
            } else {
                $defaultLanguage = Language::select('id', 'code')->where('is_default', 1)->whereNull('deleted_at')->first();
                $selectedLanguageId = $defaultLanguage ? $defaultLanguage->id : null;
            }

            $selectedLanguage = $languages->firstWhere('id', $selectedLanguageId);

            if ($selectedLanguage) {
                app()->setLocale($selectedLanguage->code);
            }
            if ($selectedLanguageId === null || strlen($selectedLanguageId) == 0) {
                $selectedLanguageId = 1;
            }

            $categoriess = Cache::remember('categoriess', 86400, function ()  use ($selectedLanguageId) {
                return Categories::select('id', 'name', 'slug')->where('status', 1)->where('language_id',  $selectedLanguageId)->where('source_type', 'service')->where('parent_id', 0)->get();
            });
            view()->share('categoriess', $categoriess);

            $menuList = Menu::select('id', 'menus')->where('language_id', $selectedLanguageId)->get()->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'menus' => is_string($menu->menus) ? json_decode($menu->menus, true) : $menu->menus,
                ];
            });

            $view->with('menuList', $menuList);
        });
    }

    private function cookies(): void
    {
        View::composer('cookie.cookie-consent.index', function ($view) {
            $languages = Language::where('status', 1)
                ->whereNull('deleted_at')
                ->get();

            $selectedLanguageId = null;
            if (Auth::check()) {
                $selectedLanguageId = Auth::user()->user_language_id;
            } elseif (Cookie::get('languageId')) {
                $selectedLanguageId = Cookie::get('languageId');
            } else {
                $defaultLanguage = $languages->firstWhere('is_default', 1);
                $selectedLanguageId = $defaultLanguage ? $defaultLanguage->id : null;
            }

            $selectedLanguage = $languages->firstWhere('id', $selectedLanguageId);

            if ($selectedLanguage) {
                app()->setLocale($selectedLanguage->code);
            }

            $langCode = $selectedLanguage ? $selectedLanguage->code : 'en';
            $cookiesKeys = [
                'cookies_content_text_' . $langCode,
                'lin_for_cookies_page_' . $langCode,
                'agree_button_text_' . $langCode,
                'show_decline_button_' . $langCode,
                'decline_button_text_' . $langCode
            ];

            $cookies = DB::table('general_settings')
                ->select('key', 'value')
                ->where('group_id', 10)
                ->where('language_id', $selectedLanguageId)
                ->whereIn('key', $cookiesKeys)
                ->get()
                ->pluck('value', 'key');

            $view->with([
                'cookies' => $cookies,
                'selectedLanguageCode' => $langCode,
            ]);
        });
    }
    /**
     * Compose the footer view.
     */
    private function composeFooterView(): void
    {
        View::composer('user.partials.footer', function ($view) {

            $languages = Language::where('status', 1)
                ->whereNull('deleted_at')
                ->get();
            $selectedLanguageId = null;
            if (Auth::check()) {
                $selectedLanguageId = Auth::user()->user_language_id;
            } elseif (Cookie::get('languageId')) {
                $selectedLanguageId = Cookie::get('languageId');
            } else {
                $defaultLanguage = $languages->firstWhere('is_default', 1);
                $selectedLanguageId = $defaultLanguage ? $defaultLanguage->id : null;
            }

            $selectedLanguage = $languages->firstWhere('id', $selectedLanguageId);

            if ($selectedLanguage) {
                app()->setLocale($selectedLanguage->code);
            }

            $footerList = Footer::select('footer_content', 'status')
                ->where(['status' => 1, 'language_id' => $selectedLanguageId])
                ->get()
                ->map(function ($footer) {
                    $decodedContent = json_decode($footer->footer_content, true);

                    return collect($decodedContent)->map(function ($item) {
                        return isset($item['status']) && $item['status'] == 1 ? [
                            'title' => $item['title'],
                            'footer_content' => $item['footer_content'],
                        ] : null;
                    })->filter();
                });

            $socialLinks = Cache::rememberForever("shared_social_links", function () {
                return SocialLink::where('status', 1)->get();
            });

            $view->with([
                'footerList' => $footerList,
                'socialLinks' => $socialLinks
            ]);
        });
    }

    /**
     * Compose global views for dynamic assets and SEO data.
     */
    private function composeGlobalViews(): void
    {
        View::composer('*', function ($view) {
            $logoPath = Cache::remember('logoPath', 86400, function () {
                return GlobalSetting::where('key', 'site_logo')->value('value');
            });
            $dynamicLogo = $logoPath && file_exists(public_path('storage/' . $logoPath)) ? url('storage/' . $logoPath) : asset('front/img/logo.svg');

            $faviconPath = Cache::remember('faviconPath', 86400, function () {
                return GlobalSetting::where('key', 'site_favicon')->value('value');
            });
            $dynamicFavicon = $faviconPath && file_exists(public_path('storage/' . $faviconPath)) ? url('storage/' . $faviconPath) : asset('/assets/img/favicon.png');

            $darkLogoPath = Cache::remember('darkLogoPath', 86400, function () {
                return GlobalSetting::where('key', 'site_dark_logo')->value('value');
            });
            $dynamicDarkLogo = $darkLogoPath && file_exists(public_path('storage/' . $darkLogoPath)) ? url('storage/' . $darkLogoPath) : asset('/front/img/logo-2.svg');

            $smallLogoPath = Cache::remember('smallLogoPath', 86400, function () {
                return GlobalSetting::where('key', 'site_mobile_icon')->value('value');
            });
            $dynamicSmallLogo = $smallLogoPath && file_exists(public_path('storage/' . $smallLogoPath)) ? url('storage/' . $smallLogoPath) : asset('/front/img/logo-small.svg');

            $ssoStatus = GlobalSetting::where('key', 'sso_status')->value('value');
            $otpStatus = GlobalSetting::where('key', 'login')->value('value');

            $companyName = Cache::remember('companyName', 86400, function () {
                return GlobalSetting::where('key', 'app_name')->value('value');
            });

            $companyEmail = Cache::remember('companyEmail', 86400, function () {
                return GlobalSetting::where('key', 'site_email')->value('value');
            });
            $leadStatus = GlobalSetting::where('key', 'leads_status')->value('value');

            $languages = Language::where('status', 1)
                ->whereNull('deleted_at')
                ->get();
            $language = Language::where('is_default', 1)->whereNULL('deleted_at')->first();

            $allLanguages = Cache::remember('allLanguages', 86400, function () {
                return Language::select('id', 'name', 'code')->where('status', 1)->get();
            });
            $selectedLanguageId = null;
            if (Auth::check()) {
                $selectedLanguageId = Auth::user()->user_language_id;
            } elseif (Cookie::get('languageId')) {
                $selectedLanguageId = Cookie::get('languageId');
            } else {
                $defaultLanguage = $languages->firstWhere('is_default', 1);
                $selectedLanguageId = $defaultLanguage ? $defaultLanguage->id : null;
            }
            $selectedLanguage = $languages->firstWhere('id', $selectedLanguageId);
            if ($selectedLanguage) {
                app()->setLocale($selectedLanguage->code);
            }

            $copyRight = Cache::remember('copyRight_' . $selectedLanguageId, 86400, function () use ($selectedLanguageId) {
                return GlobalSetting::where('group_id', 8)->where('language_id', $selectedLanguageId)->value('value');
            });

            $singlevendor = Cache::remember('singlevendor', 86400, function () use ($selectedLanguageId) {
                return GlobalSetting::where('key', 'save_single_vendor_status')->where('language_id', $selectedLanguageId)->value('value');
            });
            $seoTitle = "";
            $seoDesc = "";
            $seoKey = "";

            if (str_contains(request()->path(), 'servicedetail')) {
                $slug = request()->segment(2);
                $product = Product::where('slug', $slug)->first();

                if ($product) {
                    $seoTitle = $product->seo_title;
                    $seoDesc = $product->seo_description;
                    $seoKey = $product->tags;
                }
            }

            $user = Auth::user();
            $permissions = '';
            if ($user && !empty($user->role_id)) {
                $permissions = DB::table('permissions')->select('permissions.module', 'permissions.create', 'permissions.edit', 'permissions.view', 'permissions.delete')->join('roles', 'roles.id', '=', 'permissions.role_id')->where(['roles.status' => 1, 'permissions.role_id' => $user->role_id])->get();
            }

            $addonModules = Cache::remember('addonModules', 86400, function () {
                return AddonModule::get(['id', 'slug', 'name', 'status']);
            });

            $addvertismentStatus = AddonModule::where('slug', 'advertisements')->value('status') ?? 0;
            $addvertismentSection = DB::table('sections')->where('name', 'Advertisement')->update(['status' => $addvertismentStatus]);

            $recaptchaSetting = DB::table('general_settings')->where('key', 'recaptcha_status')->first();
            $reSetting = $recaptchaSetting && $recaptchaSetting->value == 1;

            $locationStatus = GlobalSetting::where('key', 'location_status')->value('value') ?? 0;

            $socialLinks = socialLink::where('status', 1)->get();
            $socialMediaShares = SocialMediaShare::where('status', 1)->get();


            $razorpaySetting = GlobalSetting::where('key', 'razorpay_status')->first();
            $razerpayStatus = $razorpaySetting ? ($razorpaySetting->value ?? 0) : 0;
            $payuSetting = GlobalSetting::where('key', 'payu_status')->first();
            $payuStatus = $payuSetting ? ($payuSetting->value ?? 0) : 0;
            $cashfreeSetting = GlobalSetting::where('key', 'cashfree_status')->first();
            $cashfreeStatus = $cashfreeSetting ? ($cashfreeSetting->value ?? 0) : 0;
            $authorizenetSetting = GlobalSetting::where('key', 'authorizenet_status')->first();
            $authorizenetStatus = $authorizenetSetting ? ($authorizenetSetting->value ?? 0) : 0;
            $paystackSetting = GlobalSetting::where('key', 'paystack_status')->first();
            $paystackStatus = $paystackSetting ? ($paystackSetting->value ?? 0) : 0;
            $bankSettings = GlobalSetting::where('key', 'bank_status')->first();
            $bankStatus = $bankSettings ? ($bankSettings->value ?? 0) : 0;
            $mercadoSettings = GlobalSetting::where('key', 'mercadopago_status')->first();
            $mercadoStatus = $mercadoSettings ? ($mercadoSettings->value ?? 0) : 0;
            $PaymentGatewayStatus = AddonModule::where('slug', 'paymentgateway')->value('status') ?? 0;
            
            $view->with([
                'dynamicLogo' => $dynamicLogo,
                'singlevendor' => $singlevendor,
                'dynamicFavicon' => $dynamicFavicon,
                'dynamicDarkLogo' => $dynamicDarkLogo,
                'companyName' => $companyName,
                'otpStatus' => $otpStatus,
                'sso_status' => $ssoStatus,
                'copyRight' => $copyRight,
                'leadStatus' => $leadStatus,
                'languages' => $languages,
                'selectedLanguageId' => $selectedLanguageId,
                'seo_title' => $seoTitle,
                'seo_desc' => $seoDesc,
                'seo_key' => $seoKey,
                'socialLinks' => $socialLinks,
                "allLanguages" => $allLanguages,
                "permission" => $permissions,
                'socialLinks' => $socialLinks,
                'dynamicSmallLogo' => $dynamicSmallLogo,
                'addonModules' => $addonModules,
                'addvertismentStatus' => $addvertismentStatus,
                'reSetting' => $reSetting,
                'socialMediaShares' => $socialMediaShares,
                'locationStatus' => $locationStatus,
                'razerpayStatus' => $razerpayStatus,
                'payuStatus' => $payuStatus,
                'cashfreeStatus' => $cashfreeStatus,
                'authorizenetStatus' => $authorizenetStatus,
                'paystackStatus' => $paystackStatus,
                'bankStatus' => $bankStatus,
                'PaymentGatewayStatus' => $PaymentGatewayStatus,
                'mercadoStatus' => $mercadoStatus,
                'companyEmail' => $companyEmail
            ]);
        });
    }

    /**
     * Share category-related data with views.
     */
    private function shareCategories(): void
    {

        View::composer('*', function ($view) {

            $languages = Language::where('status', 1)
                ->whereNull('deleted_at')
                ->get();
            $selectedLanguageId = null;
            if (Auth::check()) {
                $selectedLanguageId = Auth::user()->user_language_id;
            } elseif (Cookie::get('languageId')) {
                $selectedLanguageId = Cookie::get('languageId');
            } else {
                $defaultLanguage = $languages->firstWhere('is_default', 1);
                $selectedLanguageId = $defaultLanguage ? $defaultLanguage->id : null;
            }
            //echo $selectedLanguageId;
            $selectedLanguage = $languages->firstWhere('id', $selectedLanguageId);

            if ($selectedLanguage) {
                app()->setLocale($selectedLanguage->code);
            }

            $categoriesLangId = $selectedLanguageId;

            $categories = Categories::select('id', 'name', 'slug')->where('status', 1)->where('language_id', $categoriesLangId)->where('source_type', 'service')->where('parent_id', 0)->get();
            view()->share('categories', $categories);

            $categoriesLang = Categories::select('id', 'name', 'slug')->where('status', 1)->where('language_id', $selectedLanguageId)->where('source_type', 'service')->where('parent_id', 0)->get();
            view()->share('categoriesLang', $categoriesLang);

            $homeCategories = Categories::select('id', 'name', 'slug')->where('status', 1)->where('language_id', $categoriesLangId)->where('parent_id', 0)->get() ?? collect();
            view()->share('homeCategories', $homeCategories);

            $popularCategories = Cache::remember('popularCategories', 86400, function () {
                return Categories::query()
                    ->join('products', 'categories.id', '=', 'products.source_category')
                    ->join('bookings', 'products.id', '=', 'bookings.product_id')
                    ->select('categories.id', 'categories.name', 'categories.slug')
                    ->where('categories.status', 1)
                    ->where('categories.language_id', 1)
                    ->groupBy('categories.id', 'categories.name', 'categories.slug')
                    ->havingRaw('COUNT(bookings.id) >= 1')
                    ->get();
            });
            view()->share('popularCategories', $popularCategories);
        });
    }

    private function colors(): void
    {
        $primaryColor = GlobalSetting::where('key', 'primary_color')->value('value');
        $secondaryColor = GlobalSetting::where('key', 'secondary_color')->value('value');
        $buttonColor = GlobalSetting::where('key', 'button_color')->value('value');
        $buttonHoverColor = GlobalSetting::where('key', 'button_hover_color')->value('value');

        $scssFilePath = public_path('assets/scss/utils/_variables.scss');

        if (File::exists($scssFilePath)) {
            $scssContent = File::get($scssFilePath);

            $updatedScssContent = preg_replace(
                [
                    '/\$primary:.*?;/',
                    '/\$secondary:.*?;/',
                    '/\$button:.*?;/',
                    '/\$button-hover:.*?;/'
                ],
                [
                    "\$primary: {$primaryColor};",
                    "\$secondary: {$secondaryColor};",
                    "\$button: {$buttonColor};",
                    "\$button-hover: {$buttonHoverColor};"
                ],
                $scssContent
            );

            File::put($scssFilePath, $updatedScssContent);
        }

        // Share the colors with views
        View::share('primaryColor', $primaryColor);
        View::share('secondaryColor', $secondaryColor);
        View::share('buttonColor', $buttonColor);
        View::share('buttonHoverColor', $buttonHoverColor);
    }
}
