<?php

use App\Models\Bookings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\Rating;
use App\Models\User;
use Illuminate\Support\Str;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\Communication\app\Models\Templates;

if (!function_exists('mask_mobile_number')) {
    function mask_mobile_number($mobile, $visibleDigits = 4) {
        return substr($mobile, 0, $visibleDigits) . str_repeat('x', strlen($mobile) - $visibleDigits);
    }
}

if (! function_exists('getSetting')) {
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @param int|null $groupId
     * @return mixed
     */
    function getSetting($key, $default = null, $groupId = 16)
    {
        $setting = GlobalSetting::where('key', $key)
                    ->where('group_id', $groupId)
                    ->first();

        return $setting ? $setting->value : $default;
    }
}
if (!function_exists('maskEmail')) {
    function maskEmail($email) {
        [$localPart, $domain] = explode('@', $email);

        // Check the length of the local part
        $localLength = strlen($localPart);

        if ($localLength <= 2) {
            // If the local part is too short, show the first character only
            $maskedLocalPart = substr($localPart, 0, 1) . '***';
        } else {
            // Mask the local part, leaving the first and last characters visible
            $maskedLocalPart = substr($localPart, 0, 1) .
                               str_repeat('*', $localLength - 2) .
                               substr($localPart, -1);
        }

        // Combine the masked local part with the domain
        return $maskedLocalPart . '@' . $domain;
    }
}

function hasPermission($permissions, $modules, $action) {

    $userType = Auth::user()->user_type ?? '';

    if ($userType == 1 || $userType == 2) {
        return true;
    } else {
        $modules = is_array($modules) ? $modules : [$modules];

        foreach ($modules as $module) {
            $permission = $permissions->firstWhere('module', $module);
            if ($permission && $permission->$action == 1) {
                return true;
            }
        }
    }

    return false;
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($date, $includeTime = false)
    {
        if (empty($date)) {
            return null;
        }

        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? 'Y-m-d';
        $timeFormat = $timeformatSetting->value ?? 'H:i:s';

        $format = $includeTime ? $dateFormat . ' ' . $timeFormat : $dateFormat;

        return Carbon::parse($date)->format($format);
    }
}

if (!function_exists('hasAddonModule')) {

    function hasAddonModule($addons, $modules) {

        $modules = is_array($modules) ? $modules : [$modules];

        foreach ($modules as $module) {
            $addon = $addons->firstWhere('name', $module);
            if ($addon && $addon->status == 1) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('clearCache')) {

    function clearCache() {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('optimize:clear');

        return true;
    }
}
if (!function_exists('company')) {
    function company()
    {
        return GlobalSetting::where('key', 'app_name')->value('value') ?? 'TruelySell';
    }
}

function isRTL(?string $languageCode = null): int|string
{
    $language = Language::select('direction')->where('code', $languageCode)->first();
    $languageDirection = strtolower($language ? $language->direction : 'ltr');
    if ($languageDirection == 'rtl') {
        return 1;
    }
    return 0;
}

function reviewExists($productId = null, $userId = null): bool
{
    if (is_null($userId)) {
        $userId = Auth::user()->id;
    }
    return Rating::where('product_id', $productId)->where('parent_id', 0)->where('user_id', $userId)->exists();
}

function isBookingCompleted($productId = null, $userId = null, $bookingId = null) 
{
    return Bookings::when($bookingId, function ($query) use ($bookingId) {
            return $query->where('id', $bookingId);
        })
        ->where('product_id', $productId)
        ->where('user_id', $userId)
        ->where('booking_status', 6)
        ->exists();
}

function getProductUserId($productId = null) {
    return Product::where('id', $productId)->value('user_id');
}

function isAllowReply($productId = null, $userId = null) 
{
    $isAllow = 0;
    if ((getProductUserId($productId) == $userId)) {
        $isAllow = 1;
    }
    return $isAllow;
}

function getDefaultCurrencyCode(): string
{
    $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();

    if ($currecy_details) {
        return $currecy_details->code;
    }
    return 'USD';
}

function getDefaultCurrencySymbol(): string
{
    $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();

    if ($currecy_details) {
        return $currecy_details->symbol;
    }
    return '$';
}

function getLanguageId(?string $langCode = 'en'): int
{
    $languageId = Language::where('code', $langCode)->value('id');
    return $languageId ?? 1;
}

/**
 * Encrypts data using AES-128-CBC encryption securely.
 *
 * @param string|int|null $data The data to be encrypted.
 * @param string $key The encryption key.
 * @return string The encrypted and encoded string, or an empty string on failure.
 */
function customEncrypt(string|int|null $data, string $key = 'default_secret_key'): string
{
    $cipher = 'AES-128-CBC';
    $data = (string) $data;

    // Use a secure method to derive a 128-bit (16 bytes) key
    $key = substr(hash('sha256', $key, true), 0, 16); // 128-bit key

    // Generate a secure random IV
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivLength);

    // Encrypt the data
    $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    if ($encrypted === false) {
        return '';
    }

    // Prepend the IV to the encrypted data and base64-url encode it
    $output = base64_encode($iv . $encrypted);
    return rtrim(strtr($output, '+/', '-_'), '=');
}

/**
 * Decrypts data that was encrypted with customEncrypt().
 *
 * @param string|int|null $encryptedData The encrypted data.
 * @param string $key The encryption key.
 * @return string|null The decrypted string, or null on failure.
 */
function customDecrypt(string|int|null $encryptedData, string $key = 'default_secret_key'): ?string
{
    $cipher = 'AES-128-CBC';
    $encryptedData = strtr((string)$encryptedData, '-_', '+/');
    $decoded = base64_decode($encryptedData, true);

    if ($decoded === false) {
        return null;
    }

    $ivLength = openssl_cipher_iv_length($cipher);
    if (strlen($decoded) <= $ivLength) {
        return null; // Not enough data
    }

    // Extract IV and encrypted data
    $iv = substr($decoded, 0, $ivLength);
    $ciphertext = substr($decoded, $ivLength);

    // Derive key securely
    $key = substr(hash('sha256', $key, true), 0, 16); // 128-bit key

    $decrypted = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    return $decrypted !== false ? $decrypted : null;
}

function get_coupon_data($categoryId, $subcategoryId, $serviceId): Collection
{
    if (Schema::hasTable('coupons') && class_exists(\Modules\Coupon\app\Helpers\CouponHelper::class) &&
        method_exists(\Modules\Coupon\app\Helpers\CouponHelper::class, 'getAvailableCoupons')) {
        return \Modules\Coupon\app\Helpers\CouponHelper::getAvailableCoupons($categoryId, $subcategoryId, $serviceId);
    }

    return collect(); // default fallback
}

function getCommonSettingData(array $settingkeys = []): array
{
    $generalData = GlobalSetting::whereIn('key', $settingkeys)->pluck('value', 'key');

    $notifyData = [
        'company_name' => '',
        'site_email' => '',
        'phone_no' => '',
        'site_address' => '',
        'postal_code' => ''
    ];

    if ($generalData->isEmpty()) {
        return $notifyData;
    }

    foreach ($generalData as $key => $value) {
        $notifyData[$key] = $value;
    }

    return $notifyData;
}

function sendBookingNotification($bookingId)
{
    if (isset($bookingId)) {
        $bookingdata = Bookings::select(
            'bookings.*',
            DB::raw("
                CASE
                    WHEN bookings.payment_type = 1 THEN 'Paypal'
                    WHEN bookings.payment_type = 2 THEN 'Stripe'
                    WHEN bookings.payment_type = 3 THEN 'Razorpay'
                    WHEN bookings.payment_type = 4 THEN 'Bank Transfer'
                    WHEN bookings.payment_type = 5 THEN 'COD'
                    WHEN bookings.payment_type = 7 THEN 'Mollie'
                    WHEN bookings.payment_type = 8 THEN 'PayU'
                    WHEN bookings.payment_type = 9 THEN 'Cashfree'
                    WHEN bookings.payment_type = 10 THEN 'Authorize.net'
                    WHEN bookings.payment_type = 11 THEN 'Paystack'
                    WHEN bookings.payment_type = 12 THEN 'Mercado Pago'
                    ELSE 'Unknown'
                END AS paymenttype"),
            DB::raw("DATE_FORMAT(bookings.created_at, '%d-%m-%Y') AS bookingdate"),
            DB::raw("TIME_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
            DB::raw("TIME_FORMAT(bookings.to_time, '%H:%i') AS totime"),
            'products.source_name',
            'users.name as user_name',
            'user_details.first_name as user_first_name',
            'user_details.last_name as user_last_name',
            'provider.name as provider_name',
            'provider.email as provideremail',
            'provider_details.first_name as provider_first_name',
            'provider_details.last_name as provider_last_name',
            'staff.name as staff_name',
            'staff.email as staffemail',
            'staff_details.first_name as staff_first_name',
            'staff_details.last_name as staff_last_name',
            'payout_history.id as refundid',
            'products.created_by',
            DB::raw("DATE_FORMAT(payout_history.created_at, '%d-%m-%Y') AS trxdate"),
            DB::raw("DATE_FORMAT(payout_history.updated_at, '%d-%m-%Y') AS refunddate")
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by')
            ->leftJoin('user_details as provider_details', 'provider_details.user_id', '=', 'provider.id')
            ->leftJoin('users as staff', 'staff.id', '=', 'bookings.staff_id')
            ->leftJoin('user_details as staff_details', 'staff_details.user_id', '=', 'staff.id')
            ->leftJoin('payout_history', 'payout_history.reference_id', '=', 'bookings.id')
            ->where('bookings.id', $bookingId)
            ->with(['user.userDetails', 'product.createdBy.userDetails'])
            ->first();

        $settingData = getCommonSettingData(['company_name', 'site_email', 'phone_no', 'site_address', 'postal_code', 'website']);

        if (isset($bookingdata) && $bookingdata->user_email) {
            $toEmail = $bookingdata->user_email;
            sendBookingEmail('Booking Success Email to User', $bookingdata, $settingData, $toEmail);
        }

        if (isset($bookingdata) && $bookingdata->provideremail) {
            $toEmail = $bookingdata->provideremail;
            sendBookingEmail('Booking Success Email to Provider', $bookingdata, $settingData, $toEmail);
        }

        if (isset($bookingdata) && $bookingdata->staffemail) {
            $toEmail = $bookingdata->staffemail;
            sendBookingEmail('Booking Success Email to Provider Staff', $bookingdata, $settingData, $toEmail);
        }

        $adminUsers = User::with('userDetails')->where('user_type', 1)->get();
        if (isset($bookingdata) && $adminUsers->isNotEmpty()) {
            foreach ($adminUsers as $admin) {
                $toEmail = $admin->email;
                
                $adminName = isset($admin->name) ? ucwords($admin->name) : 'Admin';
                if ($admin->userDetails) {
                    $firstName = $admin->userDetails->first_name ?? '';
                    $lastName = $admin->userDetails->last_name ?? '';
                    $adminName = trim(ucwords($firstName . ' ' . $lastName));
                }

                sendBookingEmail('Booking Success Email to Admin', $bookingdata, $settingData, $toEmail, $adminName);
            }
        }

        /*Notification*/
        sendToBookingNotification($bookingdata, $settingData, $adminUsers);
    }
}

function sendBookingEmail($source, $bookingdata, $settingData, $toEmail, $adminName = '')
{
    $gettemplate = Templates::select('templates.subject', 'templates.content')
        ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
        ->where('notification_types.type', $source)
        ->where('templates.type', 1)
        ->where('templates.status', 1)
        ->first();
    
    if (isset($gettemplate) && isset($bookingdata)) {
        $tempdata = [];
        $service = "";
        $fromtime = $bookingdata->fromtime ?? "";
        $totime = $bookingdata->totime ?? "";
        $service = $bookingdata->source_name;

        $userName = ucwords($bookingdata->user_first_name . ' ' . $bookingdata->user_last_name);
        $providerName = ucwords($bookingdata->provider_first_name . ' ' . $bookingdata->provider_last_name);
        $staffName = ucwords($bookingdata->staff_first_name . ' ' . $bookingdata->staff_last_name);

        $tempdata = [
            '{{user_name}}' => $userName,
            '{{customer_name}}' => $userName,
            '{{admin_name}}' => $adminName,
            '{{staff_name}}' => $staffName,
            '{{booking_id}}' => $bookingdata->order_id,
            '{{service_name}}' => $service,
            '{{appointment_date}}' => $bookingdata->bookingdate,
            '{{appointment_time}}' => $fromtime ? $fromtime . '-' . $totime : "",
            '{{team_name}}' => $providerName,
            '{{provider_name}}' => $providerName,
            '{{contact}}' => $bookingdata->provideremail,
            '{{website_link}}' => $bookingdata['product']['createdBy']['userDetails']->company_website ?? $settingData['website'] ?? "",
            '{{company_name}}' => $bookingdata['product']['createdBy']['userDetails']->company_name ?? $settingData['company_name'] ?? "",
            '{{service_address}}' => $bookingdata['product']['createdBy']['userDetails']->company_address ?? $bookingdata['product']['createdBy']['userDetails']->address ?? $settingData['site_address'] ?? "",
        ];

        // Replace placeholders dynamically
        $finalContent = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->content);
        $subject = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->subject);

        $data = [
            'to_email' => $toEmail,
            'subject' => $subject,
            'content' => $finalContent
        ];

        try {
            $request = new Request($data);
            $emailController = new EmailController();
            $emailController->sendEmail($request);
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
        }
    }
}

function sendToBookingNotification($bookingdata, $settingData, $adminUsers)
{
    $gettemplate = Templates::select('templates.subject', 'templates.content')
        ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
        ->where('notification_types.type', 'Booking Success Notification to Provider')
        ->where('templates.type', 3)
        ->where('templates.status', 1)->first();
    
    if (isset($gettemplate) && isset($bookingdata)) {
        $fromtime = $bookingdata->fromtime ?? "";
        $totime = $bookingdata->totime ?? "";

        $userName = ucwords($bookingdata->user_first_name . ' ' . $bookingdata->user_last_name);
        $providerName = ucwords($bookingdata->provider_first_name . ' ' . $bookingdata->provider_last_name);
        $staffName = ucwords($bookingdata->staff_first_name . ' ' . $bookingdata->staff_last_name);

        $tempdata = [
            '{{customer_name}}' => $userName,
            '{{provider_name}}' => $providerName,
            '{{team_name}}' => $providerName,
            '{{contact}}' => $bookingdata->provideremail,
            '{{user_name}}' => $userName,
            '{{staff_name}}' => $staffName,
            '{{booking_id}}' => $bookingdata->order_id,
            '{{service_name}}' => $bookingdata->source_name,
            '{{appointment_date}}' => $bookingdata->bookingdate,
            '{{appointment_time}}' => $fromtime ? $fromtime . '-' . $totime : "",
            '{{website_link}}' => $bookingdata['product']['createdBy']['userDetails']->company_website ?? $settingData['website'] ?? "",
            '{{company_name}}' => $bookingdata['product']['createdBy']['userDetails']->company_name ?? $settingData['company_name'] ?? "",
            '{{service_address}}' => $bookingdata['product']['createdBy']['userDetails']->company_address ?? $bookingdata['product']['createdBy']['userDetails']->address ?? $settingData['site_address'] ?? "",
        ];

        $todescription = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->content);
        $getfromtemplate = Templates::select('templates.subject', 'templates.content')
            ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
            ->where('notification_types.type', 'Booking Success Notification to User')
            ->where('templates.type', 3)
            ->where('templates.status', 1)
            ->first();
        $fromdescription = "";
        if (isset($getfromtemplate)) {
            $fromdescription = Str::replace(array_keys($tempdata), array_values($tempdata), $getfromtemplate->content);
        }
        
        $data = [
            'communication_type' => 3,
            'source' => 'Booking Success',
            'reference_id' => $bookingdata->id,
            'user_id' =>  $bookingdata->user_id,
            'to_user_id' => $bookingdata->created_by,
            'to_description' => $todescription,
            'from_description' => $fromdescription
        ];

        try {
            $request = new Request($data);
            $notification = new NotificationController();
            $notification->Storenotification($request);
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage());
        }

        if (isset($bookingdata->staff_id) && $bookingdata->staff_id != 0 && $bookingdata->staff_id != '') {
            $getStaffTemplate = Templates::select('templates.subject', 'templates.content')
                ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
                ->where('notification_types.type', 'Booking Success Notification to Provider Staff')
                ->where('templates.type', 3)
                ->where('templates.status', 1)
                ->first();
    
            if (isset($getStaffTemplate) && isset($bookingdata->staff_id)) {
                $staffDescription = Str::replace(array_keys($tempdata), array_values($tempdata), $getStaffTemplate->content);
                $toUserId = $bookingdata->staff_id;
                $data = [
                    'communication_type' => 3,
                    'source' => 'Booking Success',
                    'reference_id' => $bookingdata->id,
                    'user_id' =>  $bookingdata->user_id,
                    'to_user_id' => $toUserId,
                    'to_description' => $staffDescription,
                    'from_description' => ""
                ];
    
                try {
                    $request = new Request($data);
                    $notification = new NotificationController();
                    $notification->Storenotification($request);
                } catch (\Exception $e) {
                    Log::error('Error creating notification: ' . $e->getMessage());
                }
            }
        }

        $getToAdminTemplate = Templates::select('templates.subject', 'templates.content')
            ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
            ->where('notification_types.type', 'Booking Success Notification to Admin')
            ->where('templates.type', 3)
            ->where('templates.status', 1)
            ->first();
        
        if (isset($getToAdminTemplate)) {
            $toAdminDescription = Str::replace(array_keys($tempdata), array_values($tempdata), $getToAdminTemplate->content);
            if ($adminUsers->isNotEmpty()) {
                foreach ($adminUsers as $admin) {
                    $toUserId = $admin->id;
                    $data = [
                        'communication_type' => 3,
                        'source' => 'Booking Success',
                        'reference_id' => $bookingdata->id,
                        'user_id' =>  $bookingdata->user_id,
                        'to_user_id' => $toUserId,
                        'to_description' => $toAdminDescription,
                        'from_description' => ""
                    ];
        
                    try {
                        $request = new Request($data);
                        $notification = new NotificationController();
                        $notification->Storenotification($request);
                    } catch (\Exception $e) {
                        Log::error('Error creating notification: ' . $e->getMessage());
                    }
                }
            }

        }
    }
}

function formatServicePriceType(?string $priceType, ?string $languageCode): string
{
    $formattedPriceType = '';
    switch ($priceType) {
        case 'Hourly':
            $formattedPriceType = __('price_type_hourly', [], $languageCode);
            break;
        case 'Minitue':
            $formattedPriceType = __('price_type_minute', [], $languageCode);
            break;
        case 'Minute':
            $formattedPriceType = __('price_type_minute', [], $languageCode);
            break;
        case 'Squre-metter':
            $formattedPriceType = __('price_type_square_meter', [], $languageCode);
            break;
        case 'Square-feet':
            $formattedPriceType = __('price_type_square_feet', [], $languageCode);
            break;
        default:
            $formattedPriceType = __('price_type_fixed', [], $languageCode);
    }
    return $formattedPriceType;
}

function providerApprovalStatus(): int
{
    $providerApproval = GlobalSetting::where('key', 'provider_approval_status')->value('value') ?? 0;
    return $providerApproval == 1 ? 1 : 0;
}

function serviceApprovalStatus(): int
{
    $providerApproval = GlobalSetting::where('key', 'service_approval_status')->value('value') ?? 0;
    return $providerApproval == 1 ? 1 : 0;
}

function module_view_exists($view) {
    return View::exists($view);
}

function getBookingOrderId(?int $orderId): string
{
    $orderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
    $bookingPrefix = GlobalSetting::where('key', 'booking_prefix')->value('value');
    $bookingPrefix = $bookingPrefix && $bookingPrefix != '' ? $bookingPrefix : 'RES';

    return $bookingPrefix . $orderId;
}

if (!function_exists('uploadFile')) {
    function uploadFile(UploadedFile $file, string $path = 'uploads', ?string $oldFileName = '', ?string $disk = ''): ?string
    {
        $storageDisk = 'public';
        if ($file->isValid()) {
            $oldFileName = $oldFileName ?? '';
            if (Storage::disk($storageDisk)->exists($oldFileName)) {
                Storage::disk($storageDisk)->delete($oldFileName);
            }
            $filename = str_replace(',', '', Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension());
            $file->storeAs($path, $filename, $storageDisk);
            return $path . "/" . $filename;
        }
        return null;
    }
}
