<?php

namespace Modules\Communication\app\Repositories\Eloquent;

use App\Models\PackageTrx;
use App\Models\ProviderDetail;
use Modules\Communication\app\Models\OtpSetting;
use Modules\Communication\app\Repositories\Contracts\CommunicationInterface;
use App\Models\User;
use App\Models\UserDetail;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Modules\GlobalSetting\app\Models\Templates;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CommunicationRepository implements CommunicationInterface
{
    public function getOtpSettings(array $data): array
    {
        $email = $data['email'];
        $type = $data['type'] ?? null;

        $user = User::where('email', $email)->first();
        
        if (!$user || ($type === 'forgot' && ($email === 'demouser@gmail.com' || $email === 'demoprovider@gmail.com'))) {
            throw new \Exception('The given email is not registered.');
        }

        $settings = GlobalSetting::whereIn('key', ['otp_digit_limit', 'otp_expire_time', 'otp_type'])
            ->pluck('value', 'key');

        if (!in_array($settings['otp_type'], ['email', 'sms'])) {
            throw new \Exception('Unsupported OTP type');
        }

        $otp = $this->getOtpForEmail($email, $settings['otp_digit_limit']);

        $otpExpireMinutes = (int) filter_var($settings['otp_expire_time'], FILTER_SANITIZE_NUMBER_INT);
        $expiresAt = now()
            ->addMinutes($otpExpireMinutes)
            ->setTimezone('Asia/Kolkata')
            ->format('Y-m-d H:i:s');

        $this->storeOrUpdateOtp($email, $otp, $expiresAt);

        $templateData = $this->getTemplateData($settings['otp_type'], 2);
        $subject = $templateData['subject'] ?? null;
        $content = str_replace(
            ['{{user_name}}', '{{otp}}'],
            [$user->name, $otp],
            $templateData['content'] ?? ''
        );

        return [
            'name' => $user->name,
            'phone_number' => $user->phone_number,
            'otp_digit_limit' => $settings['otp_digit_limit'],
            'otp_expire_time' => $settings['otp_expire_time'],
            'otp_type' => $settings['otp_type'],
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'email_subject' => $subject,
            'email_content' => $content,
        ];
    }

    public function getRegisterOtpSettings(array $data): array
    {
        $email = $data['email'];

        $settings = GlobalSetting::whereIn('key', ['otp_digit_limit', 'otp_expire_time', 'otp_type'])
            ->pluck('value', 'key');

        if (!in_array($settings['otp_type'], ['email', 'sms'])) {
            throw new \Exception('Unsupported OTP type');
        }

        $otp = $this->generateOtp($settings['otp_digit_limit']);

        $otpExpireMinutes = (int) filter_var($settings['otp_expire_time'], FILTER_SANITIZE_NUMBER_INT);
        $expiresAt = now()
            ->addMinutes($otpExpireMinutes)
            ->setTimezone('Asia/Kolkata')
            ->format('Y-m-d H:i:s');

        $this->storeOrUpdateOtp($email, $otp, $expiresAt);

        $templateData = $this->getTemplateData($settings['otp_type'], 2);
        $subject = $templateData['subject'] ?? null;
        $content = str_replace(
            ['{{user_name}}', '{{otp}}'],
            [$data['name'], $otp],
            $templateData['content'] ?? ''
        );

        return [
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'name' => $data['name'],
            'phone_number' => $data['phone_number'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'] ?? null,
            'otp_digit_limit' => $settings['otp_digit_limit'],
            'otp_expire_time' => $settings['otp_expire_time'],
            'otp_type' => $settings['otp_type'],
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'email_subject' => $subject,
            'email_content' => $content,
        ];
    }

    public function getProviderRegisterOtpSettings(array $data): array
    {
        $email = $data['email'];

        $settings = GlobalSetting::whereIn('key', ['otp_digit_limit', 'otp_expire_time', 'otp_type'])
            ->pluck('value', 'key');

        if (!in_array($settings['otp_type'], ['email', 'sms'])) {
            throw new \Exception('Unsupported OTP type');
        }

        $otp = $this->generateOtp($settings['otp_digit_limit']);

        $otpExpireMinutes = (int) filter_var($settings['otp_expire_time'], FILTER_SANITIZE_NUMBER_INT);
        $expiresAt = now()
            ->addMinutes($otpExpireMinutes)
            ->setTimezone('Asia/Kolkata')
            ->format('Y-m-d H:i:s');

        $this->storeOrUpdateOtp($email, $otp, $expiresAt);

        $templateData = $this->getTemplateData($settings['otp_type'], 2);
        $subject = $templateData['subject'] ?? null;
        $content = str_replace(
            ['{{user_name}}', '{{otp}}'],
            [$data['provider_name'], $otp],
            $templateData['content'] ?? ''
        );

        return [
            'provider_first_name' => $data['provider_first_name'] ?? null,
            'provider_last_name' => $data['provider_last_name'] ?? null,
            'name' => $data['name'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'subcategory_ids' => $data['subcategory_ids'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'company_website' => $data['company_website'] ?? null,
            'otp_digit_limit' => $settings['otp_digit_limit'],
            'otp_expire_time' => $settings['otp_expire_time'],
            'otp_type' => $settings['otp_type'],
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'email_subject' => $subject,
            'email_content' => $content,
        ];
    }

    public function generateOtp(int $digitLimit): string
    {
        return str_pad((string) random_int(0, pow(10, $digitLimit) - 1), $digitLimit, '0', STR_PAD_LEFT);
    }

    private function getOtpForEmail(string $email, int $digitLimit): string
    {
        if ($email === 'demouser@gmail.com' || $email === 'demoprovider@gmail.com') {
            return '1234';
        }
        return $this->generateOtp($digitLimit);
    }

    private function storeOrUpdateOtp(string $email, string $otp, string $expiresAt): void
    {
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
    }

    private function getTemplateData(string $type, int $notificationType): array
    {
        $templateType = $type === 'email' ? 1 : 2;
        
        $template = Templates::select('subject', 'content')
            ->where('type', $templateType)
            ->where('notification_type', $notificationType)
            ->first();

        if (!$template) {
            throw new \Exception(ucfirst($type) . ' template not found');
        }

        return [
            'subject' => $template->subject,
            'content' => $template->content,
        ];
    }
    public function verifyOtp(array $data): array
    {
        $email = $this->getEmailFromRequest($data);
        $otpSetting = OtpSetting::where('email', $email)->first();

        if (!$otpSetting) {
            throw new \Exception('OTP not found for this email');
        }

        $this->validateOtp($otpSetting, $data['otp']);

        DB::table('otp_settings')->where('email', $email)->delete();

        return ['message' => 'OTP verified successfully'];
    }

    public function registerProvider(array $data): array
    {
        $user = $this->createUser($data, 2);

        $this->createProviderDetails($user->id, $data);
        $this->createUserDetails($user->id, $data, true);
        $this->createPackageTransaction($user->id);

        Auth::login($user);
        $this->cacheAuthId($user->id, true);

        $this->sendWelcomeEmail($data, true);

        return [
            'message' => 'Provider registered successfully',
            'user_id' => $user->id
        ];
    }

    public function registerUser(array $data): array
    {
        $user = $this->createUser($data, 3);
        $this->createUserDetails($user->id, $data);

        Auth::login($user);
        $this->cacheAuthId($user->id);

        $this->sendWelcomeEmail($data);

        return [
            'message' => 'User registered successfully',
            'user_id' => $user->id
        ];
    }

    public function handleForgotPassword(array $data): array
    {
        $email = $data['forgot_email'];
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new \Exception('User not found');
        }

        return [
            'message' => 'OTP verified successfully',
            'email' => $email
        ];
    }

    public function handleLogin(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw new \Exception('User not found');
        }

        Auth::login($user);
        $this->cacheAuthId($user->id, $user->user_type == 2);

        $token = $user->createToken('user-token')->plainTextToken;

        return [
            'message' => 'OTP verified successfully',
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'token' => $token
        ];
    }

    public function sendWelcomeEmail(array $data, bool $isProvider = false): void
    {
        $notificationType = 1;
        $template = Templates::where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();

        if (!$template) return;

        $settings = GlobalSetting::whereIn('key', ['company_name', 'website', 'phone_no', 'site_email'])
            ->pluck('value', 'key');

        $replacements = $this->prepareEmailReplacements($data, $settings, $isProvider);
        
        $subject = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template->subject
        );

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template->content
        );

        $this->sendEmail($data['email'], $subject, $content);
    }

    private function getEmailFromRequest(array $data): string
    {
        return $data['forgot_email'] ?? $data['email'];
    }

    private function validateOtp(OtpSetting $otpSetting, string $otp): void
    {
        $currentDateTime = now()->setTimezone('Asia/Kolkata');
        
        if ($currentDateTime->greaterThanOrEqualTo($otpSetting->expires_at)) {
            throw new \Exception('OTP is expired');
        }

        if ($otpSetting->otp !== $otp) {
            throw new \Exception('The OTP you entered is invalid. Please check and try again.');
        }
    }

    private function createUser(array $data, int $userType): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'user_type' => $userType,
        ]);
    }

    private function createProviderDetails(int $userId, array $data): void
    {
        foreach ($data['subcategory_ids'] as $subcategoryId) {
            ProviderDetail::create([
                'user_id' => $userId,
                'category_id' => $data['category_id'],
                'subcategory_id' => $subcategoryId,
            ]);
        }
    }

    private function createUserDetails(int $userId, array $data, bool $isProvider = false): void
    {
        $details = [
            'user_id' => $userId,
            'first_name' => $isProvider ? $data['provider_first_name'] : $data['first_name'],
            'last_name' => $isProvider ? $data['provider_last_name'] : $data['last_name'],
        ];

        if ($isProvider) {
            $details['category_id'] = $data['category_id'];
            $details['company_name'] = $data['company_name'];
            $details['company_website'] = $data['company_website'];
        }

        UserDetail::create($details);
    }

    private function createPackageTransaction(int $providerId): void
    {
        $feeSub = SubscriptionPackage::where('is_default', 1)
            ->whereNull('deleted_at')
            ->first();

        if (!$feeSub) return;

        $currentDate = Carbon::now();
        $endDate = $this->calculateEndDate($feeSub, $currentDate);

        PackageTrx::create([
            'provider_id' => $providerId,
            'package_id' => $feeSub->id,
            'transaction_id' => null,
            'trx_date' => $currentDate->toDateString(),
            'end_date' => $endDate,
            'amount' => $feeSub->price,
            'payment_status' => 2,
            'created_by' => $providerId,
        ]);
    }

    private function calculateEndDate(SubscriptionPackage $package, Carbon $currentDate): string
    {
        return match ($package->package_term) {
            'day' => $currentDate->copy()->addDays($package->package_duration)->toDateTimeString(),
            'month' => $currentDate->copy()->addMonths($package->package_duration)->toDateTimeString(),
            'yearly' => $currentDate->copy()->addYears($package->package_duration)->toDateTimeString(),
            'lifetime' => '9999-12-31',
            default => null,
        };
    }

    private function cacheAuthId(int $userId, bool $isProvider = false): void
    {
        $cacheKey = $isProvider ? 'provider_auth_id' : 'user_auth_id';
        Cache::forget($cacheKey);
        Cache::forever($cacheKey, $userId);
    }

    private function prepareEmailReplacements(array $data, array $settings, bool $isProvider): array
    {
        $companyName = $settings['company_name'] ?? '';
        $companyWebsite = $settings['website'] ?? '';
        $companyPhone = $settings['phone_no'] ?? '';
        $companyEmail = $settings['site_email'] ?? '';
        $contact = $companyEmail . ' | ' . $companyPhone;

        $firstName = $isProvider ? $data['provider_first_name'] : $data['first_name'];
        $lastName = $isProvider ? $data['provider_last_name'] : $data['last_name'];
        $customerName = $firstName . ' ' . $lastName;

        return [
            '{{user_name}}' => $data['name'],
            '{{first_name}}' => $firstName,
            '{{last_name}}' => $lastName,
            '{{customer_name}}' => $customerName,
            '{{phone_number}}' => $data['phone_number'],
            '{{email_id}}' => $data['email'],
            '{{company_name}}' => $companyName,
            '{{website_link}}' => $companyWebsite,
            '{{contact}}' => $contact,
        ];
    }

    private function sendEmail(string $toEmail, string $subject, string $content): void
    {
        try {
            $emailData = [
                'to_email' => $toEmail,
                'subject' => $subject,
                'content' => $content
            ];

            $emailRequest = new \Illuminate\Http\Request($emailData);
            $emailController = new \Modules\Communication\app\Http\Controllers\EmailController();
            $emailController->sendEmail($emailRequest);
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
        }
    }
}