<?php

namespace Modules\Communication\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PackageTrx;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\GlobalSetting\app\Models\Templates;
use Illuminate\Support\Facades\Hash;
use App\Models\ProviderDetail;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\Communication\app\Models\OtpSetting;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Communication\app\Helpers\MailConfigurator;
use Illuminate\Support\Facades\Mail;
use Modules\Communication\app\Mail\Samplemail;
use Nette\Utils\Random;

class CommunicationController extends Controller
{
    public function getOtpSettings(Request $request): JsonResponse
    {
        $email = $request->input('email');


        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email address'], 400);
        }

        $type = $request->input('type');

        if ($type == "booking_verify_user") {
            $userData = User::where('email', $email)->first();
            $firstName = $request->input('first_name');
            $lastName = $request->input('last_name');
            $phoneNumber = $request->input('phone_number');
            $postal = $request->input('postal');
            $address = $request->input('address');

            if (!$userData) {
                $userData = User::create([
                    'name' => strtolower(trim($firstName)) . strtolower(trim($lastName)),
                    'email' => $email,
                    'phone_number' => $phoneNumber,
                    'user_type' => 3,
                    'password' => Hash::make(Random::generate(8)),
                    'status' => 1
                ]);

                UserDetail::create([
                    'user_id' => $userData->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'address' => $address,
                    'postal' => $postal,
                ]);
            }
        }

        $user = User::where('email', $email)
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->select(
                'users.*',
                DB::raw("CONCAT(COALESCE(user_details.first_name, ''), ' ', COALESCE(user_details.last_name, '')) as full_name"),
            )
            ->first();

        $providerApprovalStatus = providerApprovalStatus();
        if ($user && $user->user_type == 2 && $user->provider_verified_status == 0 && $providerApprovalStatus == 1) {
            return response()->json([
                'code'    => 200,
                'message' => __('provider_not_verified_info'),
                'provider_verified_status' => 0,
            ], 200);
        }

        if (!$user || ($type === 'forgot' && ($email === 'demouser@gmail.com' || $email === 'demoprovider@gmail.com'))) {
            return response()->json(['error' => 'The given email is not registered.'], 400);
        }

        $settings = GlobalSetting::whereIn('key', ['otp_digit_limit', 'otp_expire_time', 'otp_type'])
            ->pluck('value', 'key');

        if (!in_array($settings['otp_type'], ['email', 'sms'])) {
            return response()->json(['error' => 'Unsupported OTP type'], 400);
        }

        if ($email === 'demouser@gmail.com') {
            $otp = '1234';
        } elseif ($email === 'demoprovider@gmail.com') {
            $otp = '1234';
        } else {
            $otp = $this->generateOtp($settings['otp_digit_limit']);
        }

        $otpExpireMinutes = (int) filter_var($settings['otp_expire_time'], FILTER_SANITIZE_NUMBER_INT);
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

        $subject = null;
        $content = null;

        if ($settings['otp_type'] === 'email') {
            // Retrieve email template
            $notificationType = 2;
            $template = Templates::select('subject', 'content')
                ->where('type', 1)
                ->where('notification_type', $notificationType)
                ->first();

            if (!$template) {
                return response()->json(['error' => 'Email template not found'], 404);
            }

            //for email
            $subject = $template->subject;
            $content = str_replace(
                ['{{user_name}}', '{{otp}}'],
                [$user->full_name ?? $user->name, $otp],
                $template->content
            );

            try {
                $emailRequest = new Request([
                    'subject' => $subject,
                    'content' => $content,
                    'to_email' => $email
                ]);
                $emailController = new EmailController();
                $emailResponse = $emailController->sendEmail($emailRequest);
                $responseData = $emailResponse->getData(true);
                if (!isset($responseData['code']) || $responseData['code'] == 500) {
                    Log::error($responseData['error'] ?? 'Email send failed');
                    return response()->json([
                        'code' => 500,
                        'error' => 'Failed to send OTP',
                    ], 500);
                }

            } catch (\Exception $e) {
                Log::error('Failed to send email: '. $e->getMessage());
                return response()->json([
                    'code' => 500,
                    'error' => 'Failed to send OTP',
                ], 500);
            }
        } elseif ($settings['otp_type'] === 'sms') {

            // Retrieve sms template
            $notificationType = 2;
            $template = Templates::select('subject', 'content')
                ->where('type', 2)
                ->where('notification_type', $notificationType)
                ->first();

            if (!$template) {
                return response()->json(['error' => 'SMS template not found'], 404);
            }

            //for SMS
            $subject = $template->subject;
            $content = str_replace(
                ['{{user_name}}', '{{otp}}'],
                [$user->full_name ?? $user->name, $otp],
                $template->content
            );

            try {
                $smsRequest = new Request([
                    'subject' => $subject,
                    'content' => $content,
                    'to_number' => $user->phone_number
                ]);
                $smsController = new SmsController();
                $smsController->sendSms($smsRequest);
            } catch (\Exception $e) {
                Log::error('Error sending SMS: '.$e->getMessage());
                return response()->json([
                    'code' => 500,
                    'error' => 'Failed to send OTP',
                ], 500);
            }
        }

        return response()->json([
            'phone_number' => $user->phone_number,
            'otp_digit_limit' => $settings['otp_digit_limit'],
            'otp_expire_time' => $settings['otp_expire_time'],
            'otp_type' => $settings['otp_type'],
            'user_id' => $user->id
        ]);
    }

    public function getOtpSettingsApi(Request $request): JsonResponse
    {
        $email = $request->input('email');


        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email address'], 400);
        }

        $user = User::where('email', $email)->first();
        $type = $request->input('type');

        $providerApprovalStatus = providerApprovalStatus();
        if ($user && $user->user_type == 2 && $user->provider_verified_status == 0 && $providerApprovalStatus == 1) {
            return response()->json([
                'code'    => 200,
                'message' => __('provider_not_verified_info'),
                'provider_verified_status' => 0,
            ], 200);
        }

        if (!$user || ($type === 'forgot' && ($email === 'demouser@gmail.com' || $email === 'demoprovider@gmail.com'))) {
            return response()->json(['error' => 'The given email is not registered.'], 400);
        }

        $settings = GlobalSetting::whereIn('key', ['otp_digit_limit', 'otp_expire_time', 'otp_type'])
            ->pluck('value', 'key');

        if (!in_array($settings['otp_type'], ['email', 'sms'])) {
            return response()->json(['error' => 'Unsupported OTP type'], 400);
        }

        if ($email === 'demouser@gmail.com') {
            $otp = '1234';
        } elseif ($email === 'demoprovider@gmail.com') {
            $otp = '1234';
        } else {
            $otp = $this->generateOtp($settings['otp_digit_limit']);
        }

        $otpExpireMinutes = (int) filter_var($settings['otp_expire_time'], FILTER_SANITIZE_NUMBER_INT);
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

        $subject = null;
        $content = null;

        if ($settings['otp_type'] === 'email') {
            // Retrieve email template
            $notificationType = 2;
            $template = Templates::select('subject', 'content')
                ->where('type', 1)
                ->where('notification_type', $notificationType)
                ->first();

            if (!$template) {
                return response()->json(['error' => 'Email template not found'], 404);
            }

            //for email
            $subject = $template->subject;
            $content = str_replace(
                ['{{user_name}}', '{{otp}}'],
                [$user->name, $otp],
                $template->content
            );
        } elseif ($settings['otp_type'] === 'sms') {

            // Retrieve sms template
            $notificationType = 2;
            $template = Templates::select('subject', 'content')
                ->where('type', 2)
                ->where('notification_type', $notificationType)
                ->first();

            if (!$template) {
                return response()->json(['error' => 'SMS template not found'], 404);
            }

            //for SMS
            $subject = $template->subject;
            $content = str_replace(
                ['{{user_name}}', '{{otp}}'],
                [$user->name, $otp],
                $template->content
            );
        }

        MailConfigurator::configureMail();

        $tomail = $request->input('email');
        $subject = $subject;
        $content = $content;
        $attachment = $request->input('attachment');

        $data = [
            'message' => $content,
            'subject' => $subject,
            'attachment' => $attachment,
        ];

        // Check if the email address is provided
        if (empty($tomail)) {
            return response()->json([
                'code' => 400,
                'message' => 'Recipient email is required.',
            ], 400);
        }

        if (is_array($tomail)) {
            foreach ($tomail as $email) {
                Mail::to($email)->send(new Samplemail($data));
            }
        } else {
            Mail::to($tomail)->send(new Samplemail($data));
        }

        $data = [
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

        return response()->json(['code' => 200, 'message' => __('Otp and Email sent successfully.'), 'data' => $data], 200);
    }

    public function getRegisterOtpSettings(Request $request): JsonResponse
    {

        $email = $request->email;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email address'], 400);
        }

        $settings = GlobalSetting::whereIn('key', ['otp_digit_limit', 'otp_expire_time', 'otp_type'])
            ->pluck('value', 'key');

        if (!in_array($settings['otp_type'], ['email', 'sms'])) {
            return response()->json(['error' => 'Unsupported OTP type'], 400);
        }

        $otp = $this->generateOtp($settings['otp_digit_limit']);

        $otpExpireMinutes = (int) filter_var($settings['otp_expire_time'], FILTER_SANITIZE_NUMBER_INT);
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

        $subject = null;
        $content = null;

        if ($settings['otp_type'] === 'email') {
            // Retrieve email template
            $notificationType = 2;
            $template = Templates::select('subject', 'content')
                ->where('type', 1)
                ->where('notification_type', $notificationType)
                ->first();

            if (!$template) {
                return response()->json(['error' => 'Email template not found'], 404);
            }

            //for email
            $subject = $template->subject;
            $content = str_replace(
                ['{{user_name}}', '{{otp}}'],
                [$request->name, $otp],
                $template->content
            );

            try {
                $emailRequest = new Request([
                    'subject' => $subject,
                    'content' => $content,
                    'to_email' => $email
                ]);
                $emailController = new EmailController();
                $emailResponse = $emailController->sendEmail($emailRequest);
                $responseData = $emailResponse->getData(true);
                if (!isset($responseData['code']) || $responseData['code'] == 500) {
                    Log::error($responseData['error'] ?? 'Email send failed');
                    return response()->json([
                        'code' => 500,
                        'error' => 'Failed to send OTP',
                    ], 500);
                }

            } catch (\Exception $e) {
                Log::error('Failed to send email: '. $e->getMessage());
                return response()->json([
                    'code' => 500,
                    'error' => 'Failed to send OTP',
                ], 500);
            }
        } elseif ($settings['otp_type'] === 'sms') {

            // Retrieve sms template
            $notificationType = 2;
            $template = Templates::select('subject', 'content')
                ->where('type', 2)
                ->where('notification_type', $notificationType)
                ->first();

            if (!$template) {
                return response()->json(['error' => 'SMS template not found'], 404);
            }

            //for SMS
            $subject = $template->subject;
            $content = str_replace(
                ['{{user_name}}', '{{otp}}'],
                [$request->name, $otp],
                $template->content
            );

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
                return response()->json([
                    'code' => 500,
                    'error' => 'Failed to send OTP',
                ], 500);
            }
        }

        return response()->json([
            'otp_digit_limit' => $settings['otp_digit_limit'],
            'otp_expire_time' => $settings['otp_expire_time'],
            'otp_type' => $settings['otp_type'],
        ]);
    }

    public function getProviderRegisterOtpSettings(Request $request): JsonResponse
    {
        $email = $request->email;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email address'], 400);
        }

        $settings = GlobalSetting::whereIn('key', ['otp_digit_limit', 'otp_expire_time', 'otp_type'])
            ->pluck('value', 'key');

        if (!in_array($settings['otp_type'], ['email', 'sms'])) {
            return response()->json(['error' => 'Unsupported OTP type'], 400);
        }

        $otp = $this->generateOtp($settings['otp_digit_limit']);

        $otpExpireMinutes = (int) filter_var($settings['otp_expire_time'], FILTER_SANITIZE_NUMBER_INT);
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

        $subject = null;
        $content = null;

        if ($settings['otp_type'] === 'email') {
            $notificationType = 2;
            $template = Templates::select('subject', 'content')
                ->where('type', 1)
                ->where('notification_type', $notificationType)
                ->first();

            if (!$template) {
                return response()->json(['error' => 'Email template not found'], 404);
            }

            $subject = $template->subject;
            $content = str_replace(
                ['{{user_name}}', '{{otp}}'],
                [$request->provider_name, $otp],
                $template->content
            );

            try {
                $emailRequest = new Request([
                    'subject' => $subject,
                    'content' => $content,
                    'to_email' => $email
                ]);
                $emailController = new EmailController();
                $emailResponse = $emailController->sendEmail($emailRequest);
                $responseData = $emailResponse->getData(true);
                if (!isset($responseData['code']) || $responseData['code'] == 500) {
                    Log::error($responseData['error'] ?? 'Email send failed');
                    return response()->json([
                        'code' => 500,
                        'error' => 'Failed to send OTP',
                    ], 500);
                }

            } catch (\Exception $e) {
                Log::error('Failed to send email: '. $e->getMessage());
                return response()->json([
                    'code' => 500,
                    'error' => 'Failed to send OTP',
                ], 500);
            }
        } elseif ($settings['otp_type'] === 'sms') {

            // Retrieve sms template
            $notificationType = 2;
            $template = Templates::select('subject', 'content')
                ->where('type', 2)
                ->where('notification_type', $notificationType)
                ->first();

            if (!$template) {
                return response()->json(['error' => 'SMS template not found'], 404);
            }

            //for SMS
            $subject = $template->subject;
            $content = str_replace(
                ['{{user_name}}', '{{otp}}'],
                [$request->provider_name, $otp],
                $template->content
            );

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
                return response()->json([
                    'code' => 500,
                    'error' => 'Failed to send OTP',
                ], 500);
            }
        }

        return response()->json([
            'otp_digit_limit' => $settings['otp_digit_limit'],
            'otp_expire_time' => $settings['otp_expire_time'],
            'otp_type' => $settings['otp_type'],
        ]);
    }

    private function generateOtp(int $digitLimit): string
    {
        return str_pad((string) random_int(0, pow(10, $digitLimit) - 1), $digitLimit, '0', STR_PAD_LEFT);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        if ($request->login_type == "provider_register") {

            $request->validate([
                'otp' => 'required',
            ]);

            $otpSetting = OtpSetting::where('email', $request->email)->first();

            $currentDateTime = now()->setTimezone('Asia/Kolkata');
            if (isset($otpSetting)) {
                $expire = $otpSetting->expires_at ?? "";
                if ($expire != '') {
                    if ($currentDateTime->greaterThanOrEqualTo($expire)) {
                        return response()->json(['error' => 'OTP is expired'], 400);
                    }
                }
                $otp = $otpSetting->otp ?? "";
                if ($otp != '') {
                    if ($otp !== $request->otp) {
                        return response()->json(['error' => 'The OTP you entered is invalid. Please check and try again.'], 400);
                    }
                }
            }
            $providerApprovalStatus = providerApprovalStatus();
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'user_type' => 2,
                'sub_service_type' => $request->sub_service_type ?? 'individual',
                'provider_verified_status' => $providerApprovalStatus == 1 ? 0 : 1
            ];

            if (request()->has('sub_service_type') && !empty(request()->get('sub_service_type'))) {
                $data['sub_service_type'] = request()->get('sub_service_type');
            }

            $save = User::create($data);

            if (is_array($request->subcategory_ids) && count($request->subcategory_ids) > 0) {
                foreach ($request->subcategory_ids as $subcategoryId) {
                    ProviderDetail::create([
                        'user_id' => $save->id,
                        'category_id' => $request->category_id,
                        'subcategory_id' => $subcategoryId,
                    ]);
                }
            }

            $company_details = [
                'user_id' => $save->id,
                'category_id' => $request->category_id,
                'first_name' => $request->provider_first_name,
                'last_name' => $request->provider_last_name,
                'company_name' => $request->company_name,
                'company_website' => $request->company_website,
            ];

            $company = UserDetail::create($company_details);

            $feeSub = SubscriptionPackage::select('id', 'is_default', 'price', 'package_term', 'package_duration')
                ->where('price', 0)
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->first();

            if ($feeSub) {
                $currentDate = Carbon::now();

                $trx_date = $currentDate->toDateString();

                $end_date = match ($feeSub->package_term) {
                    'day' => $currentDate->copy()->addDays($feeSub->package_duration)->toDateTimeString(),
                    'month' => $currentDate->copy()->addMonths($feeSub->package_duration)->toDateTimeString(),
                    'yearly' => $currentDate->copy()->addYears($feeSub->package_duration)->toDateTimeString(),
                    'lifetime' => Carbon::create(9999, 12, 31)->format('Y-m-d'),
                    default => $currentDate->copy()->addMonths($feeSub->package_duration)->toDateTimeString(),
                };

                $pacakge_trx = [
                    'provider_id' => $save->id,
                    'package_id' => $feeSub->id,
                    'transaction_id' => null,
                    'trx_date' => $trx_date,
                    'end_date' => $end_date,
                    'amount' => $feeSub->price,
                    'payment_status' => 2,
                    'created_by' => $save->id,
                ];

                $create = PackageTrx::create($pacakge_trx);
            }

            DB::table('otp_settings')->where('email', $request->email)->delete();

            $signupDate = formatDateTime($save->created_at);

            if ($providerApprovalStatus == 1) {
                $this->sendProviderSignupEmailToAdmin($request, 32, $signupDate);

                return response()->json([
                    'message' => 'OTP verified successfully',
                    'provider_approval_status' => $providerApprovalStatus
                ]);
            } else {
                $this->sendProviderWelcomeEmail($request, 1, $signupDate);
                $this->sendProviderSignupEmailToAdmin($request, 32, $signupDate);

                Auth::login($save);

                session(['user_id' => $save->id]);
                Cache::forget('provider_auth_id');
                Cache::forever('provider_auth_id',  $save->id);

                return response()->json([
                    'message' => 'OTP verified successfully',
                    'provider_approval_status' => 0
                ]);
            }
        } elseif ($request->login_type == "register") {
            $request->validate([
                'otp' => 'required',
            ]);

            $otpSetting = DB::table('otp_settings')->where('email', $request->email)->first();
            if (isset($otpSetting)) {
                $expire = $otpSetting->expires_at ?? "";
                if ($expire != '') {
                    $currentDateTime = now()->setTimezone('Asia/Kolkata'); // Adjust timezone if needed
                    if ($currentDateTime->greaterThanOrEqualTo($expire)) {
                        return response()->json(['error' => 'OTP is expired'], 400);
                    }
                }
                $otp = $otpSetting->otp ?? "";
                if ($otp != '') {
                    if ($otp !== $request->otp) {
                        return response()->json(['error' => 'The OTP you entered is invalid. Please check and try again.'], 400);
                    }
                }
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'user_type' => 3,
            ];

            $save = User::create($data);

            $company_details = [
                'user_id' => $save->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
            ];

            $company = UserDetail::create($company_details);

            Auth::login($save);

            session(['user_id' => $save->id]);
            Cache::forget('user_auth_id');
            Cache::forever('user_auth_id',  $save->id);
            DB::table('otp_settings')->where('email', $request->email)->delete();

            $this->sendUserWelcomeEmail($request);

            return response()->json(['message' => 'OTP verified successfully']);
        } elseif ($request->login_type == "forgot_email") {
            $request->validate([
                'forgot_email' => 'required|email',
                'otp' => 'required',
            ]);

            $user = User::where('email', $request->forgot_email)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $otpSetting = DB::table('otp_settings')->where('email', $request->forgot_email)->first();
            if (isset($otpSetting)) {
                $expire = $otpSetting->expires_at ?? "";
                if ($expire != '') {
                    $currentDateTime = now()->setTimezone('Asia/Kolkata');
                    if ($currentDateTime->greaterThanOrEqualTo($expire)) {
                        return response()->json(['error' => 'OTP is expired'], 400);
                    }
                }
                $otp = $otpSetting->otp ?? "";
                if ($otp != '') {
                    if ($otp !== $request->otp) {
                        return response()->json(['error' => 'The OTP you entered is invalid. Please check and try again.'], 400);
                    }
                }
            }
            DB::table('otp_settings')->where('email', $request->forgot_email)->delete();

            $data = "done";

            return response()->json(['message' => 'OTP verified successfully', 'data' => $data, 'email' => $request->forgot_email]);
        } else {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $otpSetting = DB::table('otp_settings')->where('email', $request->email)->first();

            if (isset($otpSetting)) {
                $expire = $otpSetting->expires_at ?? "";
                if ($expire != '') {
                    $currentDateTime = now()->setTimezone('Asia/Kolkata'); // Adjust timezone if needed
                    if ($currentDateTime->greaterThanOrEqualTo($expire)) {
                        return response()->json(['error' => 'OTP is expired'], 400);
                    }
                }
                $otp = $otpSetting->otp ?? "";
                if ($otp != '') {
                    if ($otp !== $request->otp) {
                        return response()->json(['error' => 'The OTP you entered is invalid. Please check and try again.'], 400);
                    }
                }
            }

            Auth::login($user);

            session(['user_id' => $user->id]);

            if ($user->user_type == '2') {
                Cache::forget('provider_auth_id');
                Cache::forever('provider_auth_id', $user->id);
            } else {
                Cache::forget('user_auth_id');
                Cache::forever('user_auth_id', $user->id);
            }

            $token = $user->createToken('user-token')->plainTextToken;

            DB::table('otp_settings')->where('email', $request->email)->delete();

            return response()->json([
                'message' => 'OTP verified successfully',
                'user_id' => $user->id,
                'user_type' => $user->user_type,
                'token' => $token
            ]);
        }

    }

    public function verifyOtpApi(Request $request): JsonResponse
    {
        if ($request->login_type == "provider_register") {

            $request->validate([
                'otp' => 'required',
            ]);

            $otpSetting = OtpSetting::where('email', $request->email)->first();

            $currentDateTime = now()->setTimezone('Asia/Kolkata');
            if (isset($otpSetting)) {
                $expire = $otpSetting->expires_at ?? "";
                if ($expire != '') {
                    if ($currentDateTime->greaterThanOrEqualTo($expire)) {
                        return response()->json(['error' => 'OTP is expired'], 400);
                    }
                }
                $otp = $otpSetting->otp ?? "";
                if ($otp != '') {
                    if ($otp !== $request->otp) {
                        return response()->json(['error' => 'The OTP you entered is invalid. Please check and try again.'], 400);
                    }
                }
            }
            
            $providerApprovalStatus = providerApprovalStatus();
            $providerVerifiedStatus = $providerApprovalStatus == 1 ? 0 : 1;
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'user_type' => 2,
                'provider_verified_status' => $providerVerifiedStatus
            ];
            
            if (request()->has('sub_service_type') && !empty(request()->get('sub_service_type'))) {
                $data['sub_service_type'] = request()->get('sub_service_type');
            }

            $save = User::create($data);

            if (is_array($request->subcategory_ids) && count($request->subcategory_ids) > 0) {
                foreach ($request->subcategory_ids as $subcategoryId) {
                    ProviderDetail::create([
                        'user_id' => $save->id,
                        'category_id' => $request->category_id,
                        'subcategory_id' => $subcategoryId,
                    ]);
                }
            }

            $company_details = [
                'user_id' => $save->id,
                'category_id' => $request->category_id,
                'first_name' => $request->provider_first_name,
                'last_name' => $request->provider_last_name,
                'company_name' => $request->company_name,
                'company_website' => $request->company_website
            ];

            $company = UserDetail::create($company_details);

            $feeSub = SubscriptionPackage::select('id', 'is_default', 'price', 'package_term', 'package_duration')
                ->where('price', 0)
                ->whereNull('deleted_at')
                ->first();

            if ($feeSub) {
                $currentDate = Carbon::now();

                $trx_date = $currentDate->toDateString();

                $end_date = match ($feeSub->package_term) {
                    'day' => $currentDate->copy()->addDays($feeSub->package_duration)->toDateTimeString(),
                    'month' => $currentDate->copy()->addMonths($feeSub->package_duration)->toDateTimeString(),
                    'yearly' => $currentDate->copy()->addYears($feeSub->package_duration)->toDateTimeString(),
                    'lifetime' => '9999-12-31',
                    default => null,
                };


                $pacakge_trx = [
                    'provider_id' => $save->id,
                    'package_id' => $feeSub->id,
                    'transaction_id' => null,
                    'trx_date' => $trx_date,
                    'end_date' => $end_date,
                    'amount' => $feeSub->price,
                    'payment_status' => 2,
                    'created_by' => $save->id,
                ];

                $create = PackageTrx::create($pacakge_trx);
            }

            DB::table('otp_settings')->where('email', $request->email)->delete();
            $signupDate = formatDateTime($save->created_at);

            if ($providerApprovalStatus == 1) {
                $this->sendProviderSignupEmailToAdmin($request, 32, $signupDate);

                return response()->json([
                    'message' => 'OTP verified successfully',
                    'provider_verified_status' => $providerVerifiedStatus
                ]);
            } else {
                $this->sendProviderWelcomeEmail($request, 1, $signupDate);
                $this->sendProviderSignupEmailToAdmin($request, 32, $signupDate);

                Auth::login($save);

                session(['user_id' => $save->id]);
                Cache::forget('provider_auth_id');
                Cache::forever('provider_auth_id',  $save->id);

                return response()->json([
                    'message' => 'OTP verified successfully',
                    'provider_verified_status' => 1
                ]);
            }
        } else if ($request->login_type == "register") {
            $request->validate([
                'otp' => 'required',
            ]);

            $otpSetting = DB::table('otp_settings')->where('email', $request->email)->first();
            if (isset($otpSetting)) {
                $expire = $otpSetting->expires_at ?? "";
                if ($expire != '') {
                    $currentDateTime = now()->setTimezone('Asia/Kolkata'); // Adjust timezone if needed
                    if ($currentDateTime->greaterThanOrEqualTo($expire)) {
                        return response()->json(['error' => 'OTP is expired'], 400);
                    }
                }
                $otp = $otpSetting->otp ?? "";
                if ($otp != '') {
                    if ($otp != $request->otp) {
                        return response()->json(['error' => 'The OTP you entered is invalid. Please check and try again.'], 400);
                    }
                }
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'user_type' => 3,
            ];

            $user = User::create($data);

            $company_details = [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
            ];

            $company = UserDetail::create($company_details);

            Auth::login($user);

            session(['user_id' => $user->id]);
            Cache::forget('user_auth_id');
            Cache::forever('user_auth_id',  $user->id);
            DB::table('otp_settings')->where('email', $request->email)->delete();
            $token = $user->createToken('user-token')->plainTextToken;

            $this->sendUserWelcomeEmail($request);

            return response()->json([
                'message' => 'OTP verified successfully',
                'user_id' => $user->id,
                'user_type' => $user->user_type,
                'token' => $token
            ]);
        } else if ($request->login_type == "forgot_email") {
            $request->validate([
                'forgot_email' => 'required|email',
                'otp' => 'required',
            ]);

            $user = User::where('email', $request->forgot_email)->first();

            if (!$user) {
                return response()->json([ 'code' => 200, 'error' => 'User not found'], 404);
            }

            $otpSetting = DB::table('otp_settings')->where('email', $request->forgot_email)->first();
            if (isset($otpSetting)) {
                $expire = $otpSetting->expires_at ?? "";
                if ($expire != '') {
                    $currentDateTime = now()->setTimezone('Asia/Kolkata');
                    if ($currentDateTime->greaterThanOrEqualTo($expire)) {
                        return response()->json(['error' => 'OTP is expired'], 400);
                    }
                }
                $otp = $otpSetting->otp ?? "";
                if ($otp != '') {
                    if ($otp != $request->otp) {
                        return response()->json(['code' => 422, 'error' => 'The OTP you entered is invalid. Please check and try again.'], 400);
                    }
                }
            }
            DB::table('otp_settings')->where('email', $request->forgot_email)->delete();

            $data = "done";

            return response()->json(['code' => 200, 'message' => 'OTP verified successfully', 'data' => $data, 'email' => $request->forgot_email]);
        } else {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $otpSetting = DB::table('otp_settings')->where('email', $request->email)->first();
            if (isset($otpSetting)) {
                $expire = $otpSetting->expires_at ?? "";
                if ($expire != '') {
                    $currentDateTime = now()->setTimezone('Asia/Kolkata'); // Adjust timezone if needed
                    if ($currentDateTime->greaterThanOrEqualTo($expire)) {
                        return response()->json(['error' => 'OTP is expired'], 400);
                    }
                }
                $otp = $otpSetting->otp ?? "";
                if ($otp != '') {
                    if ($otp != $request->otp) {
                        return response()->json(['error' => 'The OTP you entered is invalid. Please check and try again.'], 400);
                    }
                }
            }

            Auth::login($user);

            session(['user_id' => $user->id]);
            if ($user->user_type == '2') {
                Cache::forget('provider_auth_id');
                Cache::forever('provider_auth_id',  $user->id);
            } else {
                Cache::forget('user_auth_id');
                Cache::forever('user_auth_id',  $user->id);
            }
            DB::table('otp_settings')->where('email', $request->email)->delete();
            $token = $user->createToken('user-token')->plainTextToken;
            return response()->json([
                'message' => 'OTP verified successfully',
                'user_id' => $user->id,
                'user_type' => $user->user_type,
                'token' => $token
            ]);
        }
    }

    private function sendUserWelcomeEmail(Request $request)
    {
        // Email template
        $notificationType = 1;

        $template = Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();

        if ($template) {
            $settings = GlobalSetting::whereIn('key', ['company_name', 'website', 'phone_no', 'site_email'])->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? '';
            $companyWebsite = $settings['website'] ?? '';
            $companyPhone = $settings['phone_no'] ?? '';
            $companyEmail = $settings['site_email'] ?? '';
            $contact = $companyEmail . ' | ' . $companyPhone;
            $customerName = $request->first_name . ' ' . $request->last_name;

            // Prepare email data
            $subject = str_replace(
                ['{{user_name}}', '{{first_name}}', '{{last_name}}', '{{customer_name}}', '{{phone_number}}', '{{email_id}}', '{{company_name}}', '{{website_link}}', '{{contact}}'],
                [$request->name, $request->first_name, $request->last_name, $customerName, $request->phone_number, $request->email, $companyName, $companyWebsite, $contact],
                $template->subject
            );

            $content = str_replace(
                ['{{user_name}}', '{{first_name}}', '{{last_name}}', '{{customer_name}}', '{{phone_number}}', '{{email_id}}', '{{company_name}}', '{{website_link}}', '{{contact}}'],
                [$request->name, $request->first_name, $request->last_name, $customerName, $request->phone_number, $request->email, $companyName, $companyWebsite, $contact],
                $template->content
            );

            $emailData = [
                'to_email' => $request->email,
                'subject' => $subject,
                'content' => $content
            ];

            try {
                $emailRequest = new Request($emailData);
                $emailController = new EmailController();
                $emailController->sendEmail($emailRequest);
            } catch (\Exception $e) {
                Log::error('Failed to send registration email: ' . $e->getMessage());
            }
        }
    }

    private function sendProviderSignupEmailToAdmin(Request $request, $notificationType = '', $signupDate = '')
    {
        $template = Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();
        
        if ($template) {
            $settings = GlobalSetting::whereIn('key', ['company_name', 'website', 'phone_no', 'site_email'])->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? '';
            $companyWebsite = $settings['website'] ?? '';
            $companyPhone = $settings['phone_no'] ?? '';
            $companyEmail = $settings['site_email'] ?? '';
            $contact = $companyEmail . ' | ' . $companyPhone;
            $customerName = $request->provider_first_name . ' ' . $request->provider_last_name;

            // Prepare email data
            $subject = str_replace(
                ['{{user_name}}', '{{first_name}}', '{{last_name}}', '{{customer_name}}', '{{phone_number}}', '{{email_id}}', '{{company_name}}', '{{website_link}}', '{{contact}}', '{{signup_date}}'],
                [$request->provider_name, $request->provider_first_name, $request->provider_last_name, $customerName, $request->provider_phone_number, $request->provider_email, $companyName, $companyWebsite, $contact, $signupDate],
                $template->subject
            );
            
            $content = str_replace(
                ['{{user_name}}', '{{first_name}}', '{{last_name}}', '{{customer_name}}', '{{phone_number}}', '{{email_id}}', '{{company_name}}', '{{website_link}}', '{{contact}}', '{{signup_date}}'],
                [$request->provider_name, $request->provider_first_name, $request->provider_last_name, $customerName, $request->provider_phone_number, $request->provider_email, $companyName, $companyWebsite, $contact, $signupDate],
                $template->content
            );

            $AdminUser = User::where('user_type', 1)->orderBy('id', 'desc')->first();

            $emailData = [
                'to_email' => $AdminUser->email,
                'subject' => $subject,
                'content' => $content
            ];

            try {
                $emailRequest = new Request($emailData);
                $emailController = new EmailController();
                $emailController->sendEmail($emailRequest);
            } catch (\Exception $e) {
                Log::error('Failed to send registration email: ' . $e->getMessage());
            }
        }
    }

    private function sendProviderWelcomeEmail(Request $request, $notificationType = '', $signupDate = '')
    {
        $template = Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();
        
        if ($template) {
            $settings = GlobalSetting::whereIn('key', ['company_name', 'website', 'phone_no', 'site_email'])->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? '';
            $companyWebsite = $settings['website'] ?? '';
            $companyPhone = $settings['phone_no'] ?? '';
            $companyEmail = $settings['site_email'] ?? '';
            $contact = $companyEmail . ' | ' . $companyPhone;
            $customerName = $request->provider_first_name . ' ' . $request->provider_last_name;

            // Prepare email data
            $subject = str_replace(
                ['{{user_name}}', '{{first_name}}', '{{last_name}}', '{{customer_name}}', '{{phone_number}}', '{{email_id}}', '{{company_name}}', '{{website_link}}', '{{contact}}', '{{signup_date}}'],
                [$request->provider_name, $request->provider_first_name, $request->provider_last_name, $customerName, $request->provider_phone_number, $request->provider_email, $companyName, $companyWebsite, $contact, $signupDate],
                $template->subject
            );
            
            $content = str_replace(
                ['{{user_name}}', '{{first_name}}', '{{last_name}}', '{{customer_name}}', '{{phone_number}}', '{{email_id}}', '{{company_name}}', '{{website_link}}', '{{contact}}', '{{signup_date}}'],
                [$request->provider_name, $request->provider_first_name, $request->provider_last_name, $customerName, $request->provider_phone_number, $request->provider_email, $companyName, $companyWebsite, $contact, $signupDate],
                $template->content
            );

            $emailData = [
                'to_email' => $request->provider_email,
                'subject' => $subject,
                'content' => $content
            ];

            try {
                $emailRequest = new Request($emailData);
                $emailController = new EmailController();
                $emailController->sendEmail($emailRequest);
            } catch (\Exception $e) {
                Log::error('Failed to send registration email: ' . $e->getMessage());
            }
        }
    }
}
