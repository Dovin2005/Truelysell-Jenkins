<?php

namespace App\Repositories\Eloquent;

use App\Models\PackageTrx;
use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\Contracts\UserInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Modules\GlobalSetting\app\Models\Templates;
use Modules\Communication\app\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Models\BranchStaffs;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\Product\app\Models\Productmeta;
use App\Models\Language;
use App\Models\Bookings;
use App\Models\ProviderDetail;
use App\Models\ServicePromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Categories\app\Models\Categories;
use Modules\Communication\app\Http\Controllers\SmsController;

class UserRepository implements UserInterface
{
    public function register(array $data): array
    {
        $user = $this->createUser([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'user_type' => 3,
        ]);

        $this->createUserDetails([
            'user_id' => $user->id,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
        ]);

        return [
            'user' => $user,
            'is_mobile' => $data['is_mobile'] ?? false
        ];
    }

    public function createUser(array $userData): User
    {
        return User::create($userData);
    }

    public function createUserDetails(array $detailsData): UserDetail
    {
        return UserDetail::create($detailsData);
    }

    public function generateOtp(string $email, int $digitLimit, string $otpExpireTime): array
    {
        $otp = str_pad((string) random_int(0, pow(10, $digitLimit) - 1), $digitLimit, '0', STR_PAD_LEFT);

        $otpExpireMinutes = (int) filter_var($otpExpireTime, FILTER_SANITIZE_NUMBER_INT);
        $expiresAt = now()
            ->addMinutes($otpExpireMinutes)
            ->setTimezone('Asia/Kolkata')
            ->format('Y-m-d H:i:s');

        $existingOtp = DB::table('otp_settings')->where('email', $email)->first();

        if ($existingOtp) {
            DB::table('otp_settings')
                ->where('email', $email)
                ->update([
                    'otp' => $otp,
                    'expires_at' => $expiresAt,
                ]);
        } else {
            DB::table('otp_settings')->insert([
                'email' => $email,
                'otp' => $otp,
                'expires_at' => $expiresAt,
            ]);
        }

        return [
            'otp' => $otp,
            'expires_at' => $expiresAt
        ];
    }

    public function sendWelcomeEmail(array $userData): void
    {
        $notificationType = 1;
        $template = Templates::where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();

        if ($template) {
            $settings = DB::table('general_settings')
                ->whereIn('key', ['company_name', 'website', 'phone_no', 'site_email'])
                ->pluck('value', 'key')
                ->toArray(); // Convert Collection to array

            $emailData = [
                'to_email' => $userData['email'],
                'subject' => $this->replaceTemplatePlaceholders($template->subject, $userData, $settings),
                'content' => $this->replaceTemplatePlaceholders($template->content, $userData, $settings)
            ];

            try {
                $emailController = new EmailController();
                $emailController->sendEmail(new \Illuminate\Http\Request($emailData));
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email: ' . $e->getMessage());
            }
        }
    }

    private function replaceTemplatePlaceholders(string $content, array $userData, array $settings): string
    {
        $replacements = [
            '{{user_name}}' => $userData['name'],
            '{{first_name}}' => $userData['first_name'] ?? '',
            '{{last_name}}' => $userData['last_name'] ?? '',
            '{{customer_name}}' => ($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''),
            '{{phone_number}}' => $userData['phone_number'] ?? '',
            '{{email_id}}' => $userData['email'],
            '{{company_name}}' => $settings['company_name'] ?? '',
            '{{website_link}}' => $settings['website'] ?? '',
            '{{contact}}' => ($settings['site_email'] ?? '') . ' | ' . ($settings['phone_no'] ?? '')
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
     public function getProfileDetails(int $userId)
    {
        return User::with(['userDetails', 'documents'])->findOrFail($userId);
    }

    public function saveProfileDetails(array $data, ?int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $userData = [
                'name' => $data['user_name'],
                'email' => $data['email'],
                'phone_number' => $data['international_phone_number'],
            ];

            if (isset($data['user_type'])) {
                $userData['user_type'] = $data['user_type'];
            }

            if (isset($data['status'])) {
                $userData['status'] = $data['status'];
            }

            if (isset($data['role_id'])) {
                $userData['role_id'] = $data['role_id'];
            }

            $user = User::updateOrCreate(['id' => $userId], $userData);
            
            $userDetailData = $this->prepareUserDetailData($data);
            
            UserDetail::updateOrCreate(['user_id' => $user->id], $userDetailData);

            return $user;
        });
    }

    protected function prepareUserDetailData(array $data): array
    {
        $detailData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
            'dob' => $data['dob'],
            'bio' => $data['bio'] ?? null,
            'address' => $data['address'],
            'country' => $data['country'],
            'state' => $data['state'],
            'city' => $data['city'],
            'postal_code' => $data['postal_code'],
            'lat' => $data['lat'] ?? null,
            'lang' => $data['lang'] ?? null,
        ];

        if (isset($data['company_name'])) {
            $detailData['company_name'] = $data['company_name'];
        }

        if (isset($data['company_address'])) {
            $detailData['company_address'] = $data['company_address'];
        }

        if (isset($data['company_website'])) {
            $detailData['company_website'] = $data['company_website'];
        }

        if (isset($data['category_id'])) {
            $detailData['category_id'] = $data['category_id'];
        }

        if (isset($data['subcategory_id'])) {
            $detailData['subcategory_id'] = $data['subcategory_id'];
        }

        return $detailData;
    }

    public function updateProfileImage(string $path, int $userId)
    {
        $userDetail = UserDetail::where('user_id', $userId)->first();
        
        if ($userDetail && $userDetail->profile_image) {
            Storage::disk('public')->delete('profile/' . $userDetail->profile_image);
        }

        return UserDetail::updateOrCreate(
            ['user_id' => $userId],
            ['profile_image' => $path]
        );
    }

    public function updateCompanyImage(string $path, int $userId)
    {
        $userDetail = UserDetail::where('user_id', $userId)->first();
        
        if ($userDetail && $userDetail->company_image) {
            Storage::disk('public')->delete('company-image/' . $userDetail->company_image);
        }

        return UserDetail::updateOrCreate(
            ['user_id' => $userId],
            ['company_image' => $path]
        );
    }

    public function updateBranchStaffs(array $branchIds, int $staffId)
    {
        if (!empty($staffId)) {
            DB::table('branch_staffs')
                ->where('staff_id', $staffId)
                ->whereNotIn('branch_id', $branchIds)
                ->delete();
        }

        foreach ($branchIds as $branchId) {
            BranchStaffs::updateOrCreate(
                ['branch_id' => $branchId, 'staff_id' => $staffId]
            );
        }
    }
      public function getUserList(
        int $type,
        array|string|null $categoryIds = null,
        ?string $keywords = null,
        ?string $location = null,
        ?array $ratings = null,
        ?string $listType = null,
        int|string $languageId = '1'
    ): EloquentCollection {
        $query = User::select(
            'users.id as provider_id',
            'users.id as userid',
            'users.user_slug',
            DB::raw("CONCAT(COALESCE(user_details.first_name, ''), ' ', COALESCE(user_details.last_name, '')) as name"),
            DB::raw("CONCAT(COALESCE(user_details.first_name, ''), ' ', COALESCE(user_details.last_name, '')) as provider_name"),
            'users.email',
            'users.user_type',
            'users.hourly_rate',
            'users.phone_number',
            'categories.name as category_name',
            'categories.id',
            'user_details.profile_image',
            'user_details.mobile_number',
            'users.status',
            'user_details.dob',
            'user_details.gender',
            'user_details.address',
            'user_details.state',
            'user_details.city',
            'user_details.country',
            'user_details.postal_code',
            DB::raw('COUNT(DISTINCT products.id) as total_products'),
            DB::raw('COUNT(ratings.id) as total_ratings'),
            DB::raw('IFNULL(ROUND(AVG(ratings.rating), 1), "") as average_rating')
        )
        ->leftJoin('products', 'users.id', '=', 'products.created_by')
        ->leftJoin('ratings', function ($join) { $join->on('products.id', '=', 'ratings.product_id')->whereNull('ratings.deleted_at');})
        ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
        ->leftJoin('categories', 'user_details.category_id', '=', 'categories.id')
        ->where('users.user_type', $type)
        ->whereNull('users.deleted_at')
        ->groupBy('users.id', 'users.name', 'users.email', 'categories.name', 'users.phone_number', 
                'categories.id', 'user_details.first_name', 'user_details.last_name', 'user_details.profile_image', 
                'user_details.mobile_number', 'users.status', 'users.user_type', 'user_details.dob', 
                'user_details.gender', 'user_details.address', 'user_details.state', 'user_details.city', 
                'user_details.country', 'user_details.postal_code', 'users.user_slug', 'users.hourly_rate');

        if (is_string($categoryIds) && $categoryIds != null) {
            $categoryIds = explode(',', $categoryIds);
        }

        if ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        }

        if ($keywords) {
            $query->where('users.name', 'like', '%' . $keywords . '%');
        }

        if ($location) {
            $query->where('user_details.city', $location);
        }

        if ($ratings) {
            $query->having(function ($subQuery) use ($ratings) {
                foreach ($ratings as $rating) {
                    $min = $rating;
                    $max = $rating + 0.9;
                    $subQuery->orHavingRaw('ROUND(AVG(ratings.rating), 1) BETWEEN ? AND ?', [$min, $max]);
                }
            });
        }

        $providers = $query->get()->map(function ($provider) {
            $currentDate = now();
            $provider->encrypted_id = customEncrypt($provider->provider_id, User::$userSecretKey);
            $packageTrx = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
                ->where('package_transactions.provider_id', $provider->provider_id)
                ->where('package_transactions.status', 1)
                ->where('package_transactions.payment_status', 2)
                ->where('subscription_packages.subscription_type', 'regular')
                ->whereDate('package_transactions.end_date', '>=', $currentDate)
                ->select(
                    'package_transactions.id',
                    'package_transactions.provider_id',
                    'subscription_packages.featured',
                    'subscription_packages.badge',
                    'subscription_packages.promoted_jobber',
                    'subscription_packages.promoted_service',
                    'package_transactions.package_id',
                    'package_transactions.trx_date',
                    'package_transactions.end_date'
                )
                ->orderByDesc('package_transactions.id')
                ->first();

            $topup = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
                ->where('package_transactions.provider_id', $provider->provider_id)
                ->where('package_transactions.status', 1)
                ->where('package_transactions.payment_status', 2)
                ->where('subscription_packages.subscription_type', 'topup')
                ->whereDate('package_transactions.end_date', '>=', $currentDate)
                ->select(
                    'package_transactions.id',
                    'package_transactions.provider_id',
                    'subscription_packages.featured',
                    'subscription_packages.badge',
                    'subscription_packages.promoted_jobber',
                    'subscription_packages.promoted_service',
                    'package_transactions.package_id',
                    'package_transactions.trx_date',
                    'package_transactions.end_date'
                )
                ->orderByDesc('package_transactions.id')
                ->first();

            $featured = 0;
            $badge = 0;
            $promotedService = 0;
            $promotedJobber = 0;

            if ($packageTrx) {
                $featured = $packageTrx->featured ?? 0;
                $badge = $packageTrx->badge ?? 0;
                $promotedJobber = $packageTrx->promoted_jobber ?? 0;
                $promotedService = $packageTrx->promoted_service ?? 0;
            } else if ($topup) {
                $featured = $topup->featured ?? 0;
                $badge = $topup->badge ?? 0;
                $promotedJobber = $topup->promoted_jobber ?? 0;
                $promotedService = $topup->promoted_service ?? 0;
            }
            $promotedJobberList = ServicePromotion::where('provider_id', $provider->provider_id)
                ->whereNull('service_id')
                ->where('payment_status', 'paid')
                ->where('expires_at', '>', now())
                ->exists() ? 1 : 0;
            $provider->featured = $featured;
            $provider->badge = $badge;
            $provider->promotedJobber = $promotedJobberList;
            $provider->promotedService = $promotedService;
            return $provider;
        });

        if ($listType === 'popular') {
            $bookings = Bookings::select(
                'products.created_by as provider_id',
                DB::raw('COUNT(bookings.id) as total_completed_bookings')
            )
            ->join('products', 'bookings.product_id', '=', 'products.id')
            ->whereIn('bookings.booking_status', [5, 6])
            ->whereNull('bookings.deleted_at')
            ->whereNull('products.deleted_at')
            ->where('products.language_id', $languageId)
            ->groupBy('products.created_by')
            ->orderByDesc('total_completed_bookings')
            ->get();

            $popularProviderIds = $bookings->pluck('provider_id');
            $providers = $providers->map(function ($provider) use ($bookings) {
                $bookingData = $bookings->firstWhere('provider_id', $provider->provider_id);
                $provider->total_completed_services = $bookingData ? $bookingData->total_completed_bookings : 0;
                return $provider;
            });

            $providers = $providers->sortByDesc('total_completed_services')->values();
        }

        return $providers;
    }

    public function getUserFavorites(int $userId): EloquentCollection
    {
        return Productmeta::select('product_id')
            ->where('source_key', 'favorite')
            ->where('source_Values', $userId)
            ->get();
    }

    public function getUserDetails(int $userId): EloquentCollection
    {
        return User::with(['userDetails.category', 'documents'])
            ->where('id', $userId)
            ->get()->map(function ($user) {
                if ($user->documents) {
                    $user->documents->map(function ($document) {
                        $document->document_url = $document->document && file_exists(public_path('storage/' . $document->document))
                            ? url('storage/' . $document->document) : url('front/img/default-placeholder-image.png');
                        return $document;
                    });
                }
                return $user;
            });
    }

    public function verifyProvider(int $userId): bool
    {
        return User::where('id', $userId)
            ->update(['provider_verified_status' => 1]) > 0;
    }

    public function updateUserStatus(int $userDetailId, int $status): bool
    {
        $userDetail = UserDetail::find($userDetailId);
        if (!$userDetail) {
            return false;
        }

        return User::where('id', $userDetail->user_id)
            ->update(['status' => $status]) > 0;
    }

    public function deleteUser(int $userId): bool
    {
        try {
            DB::transaction(function () use ($userId) {
                UserDetail::where('user_id', $userId)
                    ->update(['deleted_at' => now()]);
                
                User::where('id', $userId)
                    ->update(['deleted_at' => now()]);
            });
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkUniqueField(array $data): bool
    {
        $rules = [];
        $id = $data['id'] ?? null;

        if (isset($data['user_name'])) {
            $rules['user_name'] = [
                'required',
                Rule::unique('users', 'name')->ignore($id)->whereNull('deleted_at')
            ];
        }

        if (isset($data['provider_name'])) {
            $rules['provider_name'] = [
                'required',
                Rule::unique('users', 'name')->ignore($id)->whereNull('deleted_at')
            ];
        }

        if (isset($data['email'])) {
            $rules['email'] = [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id)->whereNull('deleted_at')
            ];
        }

        if (isset($data['subscriber_email'])) {
            $rules['subscriber_email'] = [
                'required',
                'email',
                Rule::unique('email_subscriptions', 'email')->ignore($id)
            ];
        }

        $validator = Validator::make($data, $rules);
        return !$validator->fails();
    }
     public function registerProvider(
        array $providerData,
        ?array $subcategoryIds = null,
        array $companyDetails = [],
        bool $isMobile = false
    ): array {
        return DB::transaction(function () use ($providerData, $subcategoryIds, $companyDetails, $isMobile) {
            $providerApprovalStatus = providerApprovalStatus();
            $regStatus = $this->getRegistrationStatus();
            
            $providerData['user_type'] = 2;
            $providerData['password'] = Hash::make($providerData['password']);
            
            $providerData['provider_verified_status'] = $providerApprovalStatus == 1 ? 0 : 1;
            
            $user = User::create($providerData);
            
            if ($subcategoryIds) {
                foreach ($subcategoryIds as $subcategoryId) {
                    ProviderDetail::create([
                        'user_id' => $user->id,
                        'category_id' => $providerData['category_id'],
                        'subcategory_id' => $subcategoryId,
                    ]);
                }
            }
            
            $companyDetails['user_id'] = $user->id;
            $companyDetails['category_id'] = $providerData['category_id'];
            UserDetail::create($companyDetails);
            
            $this->createDefaultSubscription($user->id);
            
            $token = $isMobile ? $user->createToken('MobileLoginToken')->plainTextToken : null;
            
            return [
                'user' => $user,
                'token' => $token,
                'regStatus' => $regStatus,
                'providerApprovalStatus' => $providerApprovalStatus,
                'provider_verified_status' => $providerData['provider_verified_status']
            ];
        });
    }
    
    public function generateOtpVerification(
        string $email,
        string $name,
        ?string $firstName,
        ?string $lastName,
        string $phoneNumber,
        string $password,
        int $categoryId,
        ?array $subcategoryIds = null,
        ?string $companyName = null,
        ?string $companyWebsite = null,
        ?string $subServiceType = null,
        bool $isMobile
    ): array {
        $settings = GlobalSetting::whereIn('key', ['otp_digit_limit', 'otp_expire_time', 'otp_type'])
            ->pluck('value', 'key');
            
        $otp = $this->generateOtpr($settings['otp_digit_limit']);
        
        $otpExpireMinutes = (int) filter_var($settings['otp_expire_time'], FILTER_SANITIZE_NUMBER_INT);
        $expiresAt = now()
            ->addMinutes($otpExpireMinutes)
            ->setTimezone('Asia/Kolkata')
            ->format('Y-m-d H:i:s');
            
        DB::table('otp_settings')->updateOrInsert(
            ['email' => $email],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );
        
        $notificationType = 2;
        $template = Templates::select('subject', 'content')
            ->where('type', $settings['otp_type'] === 'email' ? 1 : 2)
            ->where('notification_type', $notificationType)
            ->first();

        if (!$isMobile) {
            if ($template) {
                $subject = str_replace('{{user_name}}', $name, $template->subject ?? '');
                $content = str_replace(
                    ['{{user_name}}', '{{otp}}'],
                    [$name, $otp],
                    $template->content ?? ''
                );
                    
                if ($settings['otp_type'] === 'email') {
        
                    try {
                        $emailRequest = new Request([
                            'subject' => $subject,
                            'content' => $content,
                            'to_email' => $email
                        ]);
                        $emailController = new EmailController();
                        $emailController->sendEmail($emailRequest);
        
                    } catch (\Exception $e) {
                        Log::error('Failed to send email: '. $e->getMessage());
                        return [
                            'code' => 500,
                            'is_otp_error' => 1,
                            'message' => 'Failed to send OTP and account creation failed',
                        ];
                    }
                    
                } elseif ($settings['otp_type'] === 'sms') {
        
                    try {
                        $smsRequest = new Request([
                            'subject' => $subject,
                            'content' => $content,
                            'to_number' => $request->phone_number ?? ''
                        ]);
                        $smsController = new SmsController();
                        $smsController->sendSms($smsRequest);
                    } catch (\Exception $e) {
                        Log::error('Error sending SMS: '.$e->getMessage());
                        return [
                            'code' => 500,
                            'message' => 'Failed to send OTP and account creation failed',
                        ];
                    }
                }
            } else {
                return [
                    'code' => 500,
                    'message' => 'Failed to send OTP and account creation failed',
                ];
            }
    
            return [
                'code' => 200,
                'otp_digit_limit' => $settings['otp_digit_limit'],
                'otp_expire_time' => $settings['otp_expire_time'],
                'otp_type' => $settings['otp_type'],
                'register_status' => '1',
            ];
        }
            
        $subject = $template->subject ?? '';
        $content = str_replace(
            ['{{user_name}}', '{{otp}}'],
            [$name, $otp],
            $template->content ?? ''
        );
        
        return [
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'otp_digit_limit' => $settings['otp_digit_limit'],
            'otp_expire_time' => $settings['otp_expire_time'],
            'otp_type' => $settings['otp_type'],
            'email_subject' => $subject,
            'email_content' => $content,
            'provider_data' => [
                'provider_first_name' => $firstName ?? explode(' ', $name)[0] ?? '',
                'provider_last_name' => $lastName ?? explode(' ', $name)[1] ?? '',
                'name' => $name,
                'phone_number' => $phoneNumber,
                'email' => $email,
                'password' => $password,
                'category_id' => $categoryId,
                'subcategory_ids' => $subcategoryIds,
                'company_name' => $companyName,
                'company_website' => $companyWebsite,
                'sub_service_type' => $subServiceType
            ]
        ];
    }
    
    public function sendProviderWelcomeEmail(
        array $providerData,
        int $notificationType = 1,
        string $signupDate = ''
    ): void {
        $template = $this->getEmailTemplate($notificationType);
        if (!$template) return;
        
        $settings = $this->getGlobalSettings();
        $customerName = ($providerData['first_name'] ?? '') . ' ' . ($providerData['last_name'] ?? '');
        
        $emailData = $this->prepareEmailData(
            $template,
            $settings,
            $providerData,
            $customerName,
            $signupDate,
            $providerData['email']
        );
        
        $this->sendEmail($emailData);
    }
    
    public function sendProviderSignupEmailToAdmin(
        array $providerData,
        int $notificationType = 32,
        string $signupDate = ''
    ): void {
        $template = $this->getEmailTemplate($notificationType);
        if (!$template) return;
        
        $settings = $this->getGlobalSettings();
        $customerName = ($providerData['provider_first_name'] ?? '') . ' ' . ($providerData['provider_last_name'] ?? '');
        
        $adminEmails = User::where('user_type', 1)
            ->orderBy('id', 'desc')
            ->pluck('email')
            ->toArray();
            
        $emailData = $this->prepareEmailData(
            $template,
            $settings,
            $providerData,
            $customerName,
            $signupDate,
            $adminEmails
        );
        
        $this->sendEmail($emailData);
    }
    
    protected function createDefaultSubscription(int $userId): void
    {
        $feeSub = SubscriptionPackage::where('price', 0)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->first();
            
        if ($feeSub) {
            $currentDate = Carbon::now();
            $endDate = $this->calculateEndDate($feeSub->package_term, $feeSub->package_duration);
            
            PackageTrx::create([
                'provider_id' => $userId,
                'package_id' => $feeSub->id,
                'transaction_id' => null,
                'trx_date' => $currentDate->toDateString(),
                'end_date' => $endDate,
                'amount' => $feeSub->price,
                'payment_status' => 2,
                'created_by' => $userId,
            ]);
        }
    }
    
    protected function calculateEndDate(string $term, int $duration): string
    {
        return match ($term) {
            'day' => now()->addDays($duration)->toDateTimeString(),
            'month' => now()->addMonths($duration)->toDateTimeString(),
            'yearly' => now()->addYears($duration)->toDateTimeString(),
            'lifetime' => Carbon::create(9999, 12, 31)->toDateTimeString(),
            default => now()->addMonths($duration)->toDateTimeString(),
        };
    }
    
    protected function generateOtpr(int $length): string
    {
        return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
    
    public function getRegistrationStatus(): string
    {
        return DB::table('general_settings')
            ->where('key', 'register')
            ->value('value') ?? '0';
    }
    
    protected function getEmailTemplate(int $notificationType): ?Templates
    {
        return Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();
    }
    
    protected function getGlobalSettings(): array
    {
        return GlobalSetting::whereIn('key', ['company_name', 'website', 'phone_no', 'site_email'])
            ->pluck('value', 'key')
            ->toArray();
    }
    
    protected function prepareEmailData(
        Templates $template,
        array $settings,
        array $providerData,
        string $customerName,
        string $signupDate,
        $toEmail
    ): array {
        $companyName = $settings['company_name'] ?? '';
        $companyWebsite = $settings['website'] ?? '';
        $companyPhone = $settings['phone_no'] ?? '';
        $companyEmail = $settings['site_email'] ?? '';
        $contact = $companyEmail . ' | ' . $companyPhone;
        
        $replacements = [
            '{{user_name}}' => $providerData['name'] ?? '',
            '{{first_name}}' => $providerData['first_name'] ?? '',
            '{{last_name}}' => $providerData['last_name'] ?? '',
            '{{customer_name}}' => $customerName,
            '{{phone_number}}' => $providerData['phone_number'] ?? '',
            '{{email_id}}' => $providerData['email'] ?? '',
            '{{company_name}}' => $companyName,
            '{{website_link}}' => $companyWebsite,
            '{{contact}}' => $contact,
            '{{signup_date}}' => $signupDate
        ];
        
        return [
            'to_email' => $toEmail,
            'subject' => str_replace(
                array_keys($replacements),
                array_values($replacements),
                $template->subject
            ),
            'content' => str_replace(
                array_keys($replacements),
                array_values($replacements),
                $template->content
            )
        ];
    }
    
    protected function sendEmail(array $emailData): void
    {
        try {
            $emailRequest = new \Illuminate\Http\Request($emailData);
            $emailController = new \Modules\Communication\app\Http\Controllers\EmailController();
            $emailController->sendEmail($emailRequest);
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
        }
    }
     public function getStaffList(int $userId): Collection
    {
        $user = User::find($userId);
        $userType = $user->user_type;

        $dateFormat = GlobalSetting::where('key', 'date_format_view')->value('value') ?? 'Y-m-d';

        $staffQuery = UserDetail::where('parent_id', $userId)
            ->whereHas('user', function ($query) {
                $query->whereColumn('id', 'user_details.user_id');
            })
            ->orderBy('id', 'DESC');

        if ($userType == 1) {
            $staffList = $staffQuery->get();
        } else {
            $staffList = $staffQuery->with(['user', 'branches' => function ($query) {
                $query->select('branch_staffs.staff_id', 'branch_staffs.branch_id');
            }])->get();
        }

        return $staffList->map(function ($item) use ($dateFormat, $userType) {
            $profileImage = $this->getProfileImageUrl($item->profile_image);

            $data = [
                'id' => $item->user->id,
                'first_name' => $item->first_name,
                'last_name' => $item->last_name,
                'email' => $item->user->email,
                'user_name' => $item->user->name,
                'phone_number' => $item->user->phone_number,
                'gender' => $item->gender,
                'dob' => $item->dob,
                'address' => $item->address,
                'country_id' => $item->country,
                'state_id' => $item->state,
                'city_id' => $item->city,
                'bio' => $item->bio,
                'postal_code' => $item->postal_code,
                'status' => $item->user->status,
                'profile_image' => $profileImage,
                'created_at' => Carbon::parse($item->created_at)->format($dateFormat),
                'role_id' => $item->user->role_id,
            ];

            if ($userType != 1) {
                $data['category_id'] = $item->category_id;
                $data['subcategory_id'] = $item->subcategory_id;
                $data['branch_id'] = $item->branches->pluck('branch_id')->implode(',');
            }

            return $data;
        });
    }

    public function deleteStaff(int $staffId): bool
    {
        return DB::transaction(function () use ($staffId) {
            User::where('id', $staffId)->delete();
            UserDetail::where('user_id', $staffId)->delete();
            return true;
        });
    }

    public function getUserDashboardData(int $userId): array
    {
        $totalBookings = Bookings::where('user_id', $userId)->count();
        $totalServiceAmount = Bookings::where('user_id', $userId)->sum('total_amount');

        // Update the with() clause to include product.source_name
        $bookings = Bookings::with(['product' => function($query) {
                $query->select('id', 'source_name', 'source_category', 'created_by');
            }, 'product.createdBy:id,name,email'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get([
                'id',
                'product_id',
                'booking_date',
                'service_amount',
                'total_amount',
                'service_qty',
                'amount_tax',
                'service_offer',
                'notes',
                'first_name',
                'last_name',
                'created_at'
            ]);

        $processedBookings = $bookings->map(function ($booking) {
            return $this->processBookingData($booking);
        })->filter()->values();

        $currencySymbol = getDefaultCurrencySymbol();

        return [
            'total_bookings' => $totalBookings,
            'total_service_amount' => number_format($totalServiceAmount ?? 0, 2, '.', ''),
            'currencySymbol' => $currencySymbol,
            'bookings' => $processedBookings,
        ];
    }

    public function getUserDashboardDataForAdmin(int $userId): array
    {
        $totalBookings = Bookings::where('user_id', $userId)->count();
        $totalServiceAmount = Bookings::where('user_id', $userId)->sum('total_amount');

        $bookings = Bookings::with(['product.createdBy:id,name,email'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get([
                'id',
                'product_id',
                'booking_date',
                'service_amount',
                'total_amount',
                'service_qty',
                'amount_tax',
                'service_offer',
                'notes',
                'first_name',
                'last_name',
                'created_at'
            ]);

        $processedBookings = $bookings->filter(function ($booking) {
            return $booking->product !== null;
        })->map(function ($booking) {
            return $this->processBookingData($booking);
        })->values();

        $currencySymbol = getDefaultCurrencySymbol();

        return [
            'total_bookings' => $totalBookings,
            'total_service_amount' => number_format($totalServiceAmount ?? 0, 2, '.', ''),
            'currencySymbol' => $currencySymbol,
            'bookings' => $processedBookings,
        ];
    }

    protected function getProfileImageUrl(?string $profileImage): string
    {
        if (empty($profileImage)) {
            return asset('assets/img/profile-default.png');
        }

        return file_exists(public_path('storage/profile/' . $profileImage)) 
            ? url('storage/profile/' . $profileImage) 
            : asset('assets/img/profile-default.png');
    }

    protected function processBookingData($booking): ?array
    {
        if (!$booking->product) {
            return null;
        }

        $addedAmount = $this->calculateAddedAmount($booking->service_offer);
        $product = $booking->product;
        $createdBy = $product->createdBy;

        $profileImage = UserDetail::where('user_id', $createdBy->id ?? null)
            ->value('profile_image');
            
        $productImage = Productmeta::where('product_id', $product->id)
            ->where('source_key', 'product_image')
            ->value('source_Values');

        $category = Categories::where('id', $product->source_category)
            ->select('name', 'image')
            ->first();

        return [
            'id' => $booking->id,
            'product_id' => $booking->product_id,
            'booking_date' => $booking->booking_date,
            'service_amount' => number_format($booking->service_amount ?? 0, 2, '.', ''),
            'total_amount' => number_format($booking->total_amount ?? 0, 2, '.', ''),
            'service_qty' => $booking->service_qty,
            'amount_tax' => $booking->amount_tax,
            'notes' => $booking->notes,
            'first_name' => $booking->first_name,
            'last_name' => $booking->last_name,
            'created_at' => $booking->created_at,
            'calculated_added_amount' => $addedAmount,
            'creator_id' => $createdBy->id ?? null,
            'creator_name' => $createdBy->name ?? null,
            'creator_email' => $createdBy->email ?? null,
            'product_image' => $productImage,
            'creator_profile_image' => $profileImage,
            'category_id' => $product->source_category,
            'category_name' => $category->name ?? null,
            'category_image' => $category->image ?? null,            
            'product_name' => $product->source_name ?? null,
        ];
    }

    protected function calculateAddedAmount(?string $serviceOffer): float
    {
        if (empty($serviceOffer)) {
            return 0;
        }

        $serviceOffers = unserialize($serviceOffer);
        $addedAmount = 0;

        foreach ($serviceOffers as $serviceOfferValue) {
            $actualValue = explode("_", $serviceOfferValue);
            $addedAmount += $actualValue[1] ?? 0;
        }

        return $addedAmount;
    }

    public function updatePassword(string $email, string $newPassword): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $user->password = Hash::make($newPassword);
        return $user->save();
    }

    public function deleteAccount(int $id, string $password, string $languageCode = 'en'): bool
    {
        return DB::transaction(function () use ($id, $password, $languageCode) {
            $user = User::find($id);

            if (!$user) {
                throw new \Exception(__('user_not_found', [], $languageCode));
            }

            // Prevent deletion of demo accounts
            if (in_array($user->email, ['demouser@gmail.com', 'demoprovider@gmail.com'])) {
                throw new \Exception(__('demo_account_cannot_be_deleted', [], $languageCode));
            }

            if (!Hash::check($password, $user->password)) {
                throw new \Exception(__('incorrect_password', [], $languageCode));
            }

            // Logout and clear session
            Session::flush();
            Auth::logout();

            // Delete user data
            UserDetail::where('user_id', $id)->delete();
            return $user->delete();
        });
    }

     public function deleteDevice(int $deviceId): bool
    {
        $device = DB::table('user_devices')->where('id', $deviceId)->first();
        
        if (!$device) {
            return false;
        }

        return DB::table('user_devices')->where('id', $deviceId)->delete() > 0;
    }

    public function verifyPassword(string $email, string $password): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }

        return Hash::check($password, $user->password);
    }

    public function updateUserPassword(int $userId, string $newPassword): bool
    {
        return User::where('id', $userId)
            ->update(['password' => Hash::make($newPassword)]) > 0;
    }

    public function getUserDevices(int $userId): SupportCollection
    {
        $dateFormat = $this->getDateFormat();
        $timeFormat = $this->getTimeFormat();

        if (!$dateFormat || !$timeFormat) {
            throw new \Exception('Date or time format settings are missing');
        }

        return DB::table('user_devices')
            ->where('user_id', $userId)
            ->orderBy('last_seen', 'desc')
            ->get()
            ->map(function ($device) use ($dateFormat, $timeFormat) {
                $device->last_seen_formatted = Carbon::parse($device->last_seen)
                    ->timezone('Asia/Kolkata')
                    ->format($dateFormat . ', ' . $timeFormat);
                return $device;
            });
    }

    protected function getDateFormat(): ?string
    {
        return DB::table('global_settings')
            ->where('key', 'date_format_view')
            ->value('value');
    }

    protected function getTimeFormat(): ?string
    {
        return DB::table('global_settings')
            ->where('key', 'time_format_view')
            ->value('value');
    }
}