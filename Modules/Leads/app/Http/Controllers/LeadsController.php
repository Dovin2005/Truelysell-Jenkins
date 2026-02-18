<?php

namespace Modules\Leads\app\Http\Controllers;

use App\Http\Controllers\Controller;
use app\Http\Controllers;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Modules\Leads\app\Http\Requests\FormInputUserListRequest;
use Modules\Leads\app\Http\Requests\FormInputUserStoreRequest;
use Modules\Leads\app\Http\Requests\LeadsListRequest;
use Modules\Leads\app\Http\Requests\ProviderLeadsRequest;
use Modules\Leads\app\Http\Requests\ProviderLeadsStatusRequest;
use Modules\Leads\app\Http\Requests\StatusUpdateRequest;
use Modules\Leads\app\Models\UserFormInput;
use Modules\Leads\app\Models\ProviderFormsInput;
use Modules\Categories\app\Models\CategoryFormInput;
use Modules\GlobalSetting\app\Models\Templates;
use Modules\GlobalSetting\app\Models\Currency;
use App\Models\User;
use App\Models\UserDetail;
use Modules\Categories\app\Models\Categories;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Auth;
use Modules\Leads\app\Models\Payments;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Leads\app\Repositories\Contracts\LeadsInterface;

class LeadsController extends Controller
{
protected $leadsRepository;

    public function __construct(LeadsInterface $leadsRepository)
    {
        $this->leadsRepository = $leadsRepository;
    }

    public function formInputUserList(FormInputUserListRequest $request): JsonResponse
    {
        $result = $this->leadsRepository->getFormInputsByCategory($request->category_id);
        
        return response()->json([
            'code' => $result['success'] ? 200 : 404,
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null
        ], $result['success'] ? 200 : 404);
    }

    public function formInputUserStore(FormInputUserStoreRequest $request): JsonResponse
    {
        $result = $this->leadsRepository->storeUserFormInputs($request->all());
        
        return response()->json([
            'code' => $result['success'] ? 200 : 500,
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
            'error' => $result['error'] ?? null
        ], $result['success'] ? 200 : 500);
    }

    public function formInputAdminList(LeadsListRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $userFormInputs = $this->leadsRepository->getUserFormInputs($filters);

            $dateFormatSetting = GlobalSetting::where('key', 'date_format_view')->first();
            $timeFormatSetting = GlobalSetting::where('key', 'time_format_view')->first();
            $dateFormat = $dateFormatSetting->value ?? 'Y-m-d';
            $timeFormat = $timeFormatSetting->value ?? 'H:i:s';

            $allCount = UserFormInput::whereNull('deleted_at')->whereHas('providerFormsInputs')->count();
            $newCount = UserFormInput::whereNull('deleted_at')->whereHas('providerFormsInputs')->where('status', 1)->count();
            $acceptCount = UserFormInput::whereNull('deleted_at')->whereHas('providerFormsInputs')->where('status', 2)->count();
            $rejectCount = UserFormInput::whereNull('deleted_at')->whereHas('providerFormsInputs')->where('status', 3)->count();

            $currencySymbol = Cache::remember('currecy_details', 86400, function () {
                return Currency::orderBy('id', 'DESC')->where('is_default',1)->first();
            });

            $userFormInputs->getCollection()->transform(function ($userFormInput) use ($dateFormat, $timeFormat) {
                $userFormInput->form_inputs = $userFormInput->form_inputs_details;
                $userFormInput->formatted_created_at = date("{$dateFormat} {$timeFormat}", strtotime($userFormInput->created_at));
                return $userFormInput;
            });

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'User form inputs retrieved successfully',
                'data' => $userFormInputs,
                'meta' => [
                    'counts' => [
                        'all' => $allCount,
                        'new' => $newCount,
                        'accept' => $acceptCount,
                        'reject' => $rejectCount,
                    ],
                    'currencySymbol' => $currencySymbol->symbol ?? '$',
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred while retrieving user form inputs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function userList(LeadsListRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $userFormInputs = $this->leadsRepository->getUserFormInputs($filters);

            $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
            $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();

            $userFormInputs->getCollection()->transform(function ($userFormInput) use ($dateformatSetting, $timeformatSetting) {
                $userFormInput->form_inputs = $userFormInput->form_inputs_details;

                $payment = Payments::where('reference_id', $userFormInput->id)->where('status', 2)->first();
                $userFormInput->payment_success = $payment ? 1 : 0;

                $dateFormat = $dateformatSetting->value ?? 'Y-m-d';
                $timeFormat = $timeformatSetting->value ?? 'H:i:s';
                $userFormInput->formatted_created_at = date("{$dateFormat} {$timeFormat}", strtotime($userFormInput->created_at));

                if ($userFormInput->relationLoaded('providerFormsInputs') && $userFormInput->providerFormsInputs->isNotEmpty()) {
                    $userFormInput->setRelation(
                        'providerFormsInputs',
                        $userFormInput->providerFormsInputs->map(function ($providerFormInput) {
                            $providerFormInput->encrypted_provider_id = customEncrypt($providerFormInput->provider_id ?? '', User::$userSecretKey);
                            return $providerFormInput;
                        })
                    );
                }

                return $userFormInput; 
            });

            $counts = $this->leadsRepository->getStatusCounts($request->user_id, $request->provider_id);

            $currencySymbol = Cache::remember('currecy_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default',1)->first();
            });

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'User form inputs retrieved successfully',
                'data' => [
                    'user_form_inputs' => $userFormInputs,
                    'meta' => ['counts' => $counts],
                    'currencySymbol' => $currencySymbol->symbol,
                    'dateformatSetting' => $dateformatSetting->value ?? 'Y-m-d',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred while retrieving user form inputs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getStatusCount(?int $userId, ?int $providerId, ?int $status = null): int
    {
        return UserFormInput::when($userId, function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->when($providerId, function ($q) use ($providerId) {
            $q->whereHas('providerFormsInputs', function ($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            });
        })
        ->when($status !== null, function ($q) use ($status) {
            $q->where('status', $status);
        })
        ->whereHas('providerFormsInputs')
        ->count();
    }


    public function updateProviderLeads(ProviderLeadsRequest $request): JsonResponse
    {
        try {
            $result = $this->leadsRepository->storeProviderFormInputs($request->validated());
            
            if (!$result['success']) {
                return response()->json([
                    'code' => 500,
                    'success' => false,
                    'message' => 'An error occurred while saving provider form inputs.',
                    'error' => $result['error'],
                ], 500);
            }

            $providers = User::with('userDetails')
                ->select('id', 'name', 'email')
                ->whereIn('id', $request->provider_id)
                ->get();

            $user = User::select('name')->where('id', $request->user_id)->first();
            $source = 'Leads Notification';
            $controller = new Controller();
            $notificationsettings1 = $controller->getnotificationsettings(3, $source);
            
            if ($notificationsettings1 == 1) {
                $sourcenotify = "New Leads";
                $fromdescription = Templates::select('subject', 'content')
                    ->where('type', 3)
                    ->where('title', $sourcenotify)
                    ->where('recipient_type', 1)
                    ->first();
                $todescription = Templates::select('subject', 'content')
                    ->where('type', 3)
                    ->where('title', $sourcenotify)
                    ->where('recipient_type', 2)
                    ->first();

                $description = "New Leads From the User";
                $notificationType = 'New leads';

                if (isset($notificationsettings1)) {
                    if ($request->has('provider_id')) {
                        $user = User::find($request->input('user_id'));
                        $userName = $user ? $user->name : 'Unknown User';

                        foreach ($request->input('provider_id') as $providerId) {
                            $provider = User::find($providerId);
                            $providerName = $provider ? $provider->name : 'Unknown Provider';

                            $fromDescriptionContent = str_replace('{{user_name}}', $providerName, $fromdescription->content ?? '');
                            $toDescriptionContent = str_replace('{{user_name}}', $userName, $todescription->content ?? '');
                            $data = [
                                'communication_type' => '3',
                                'source' => $sourcenotify,
                                'reference_id' => $request->input('user_form_inputs_id'),
                                'user_id' => $request->input('user_id'),
                                'to_user_id' => $providerId,
                                'from_description' => $fromDescriptionContent,
                                'to_description' => $toDescriptionContent,
                            ];
                            
                            try {
                                $notificationRequest = new Request($data);
                                $notification = new NotificationController();
                                $notification->Storenotification($notificationRequest);
                            } catch (Exception $e) {
                                Log::error('Notification creation failed: ' . $e->getMessage());
                            }
                        }
                    }
                    $todescription = "New Leads From the {{User}}";

                    $user = User::find($request->input('user_id'));
                    $userName = $user ? $user->name : 'Demo User';

                    $todescriptionContent = str_replace('{{User}}', $userName, $todescription);
                    $fromdescriptionadmin = "";

                    $admins = User::where('user_type', 1)->get();

                    try {
                        foreach ($admins as $admin) {
                            $data = [
                                'communication_type' => '3',
                                'source' => $sourcenotify,
                                'reference_id' => $request->input('user_form_inputs_id'),
                                'user_id' => $request->input('user_id'),
                                'to_user_id' => $admin->id,
                                'from_description' => $fromdescriptionadmin ?? null,
                                'to_description' => $todescriptionContent  ?? null,
                            ];
                            
                            $notificationRequest = new Request($data);
                            $notification = new NotificationController();
                            $notification->Storenotification($notificationRequest);
                        }
                    } catch (Exception $e) {
                        Log::error('Notification creation failed: ' . $e->getMessage());
                    }
                }
            }
            $notificationsettings = $controller->getnotificationsettings(1, $source);

            if ($notificationsettings == 1) {
                $notificationType = 'New leads';
                $template = Templates::select('subject', 'content')
                    ->where('type', 1)
                    ->where('title', $notificationType)
                    ->first();

                $companyName = GlobalSetting::where('key', 'company_name')->value('value') ?? 'Default Company Name';

                $contentTemplate = $template->content ?? '';
                $subject = $template->subject ?? 'Notification';
                $emailsWithContent = [];
                foreach ($providers as $provider) {
                    $categoryName = $provider->userdetails->category_id
                        ? Categories::where('id', $provider->userdetails->category_id)->value('name') ?? 'No Category'
                        : 'No Category';

                    $personalizedContent = str_replace(
                        ['{{provider_name}}', '{{category_name}}', '{{company_name}}', '{{user_name}}'],
                        [
                            $provider->name,
                            $categoryName,
                            $companyName,
                            $user->name ?? 'DemoUser'
                        ],
                        $contentTemplate
                    );

                    $emailsWithContent[] = [
                        'email' => $provider->email,
                        'content' => $personalizedContent,
                    ];
                }

                if (empty($emailsWithContent)) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'message' => 'No provider emails found.',
                    ], 400);
                }

                try {
                    foreach ($emailsWithContent as $emailData) {
                        $data = [
                            'to_email' => $emailData['email'],
                            'subject' => $subject,
                            'content' => $emailData['content']
                        ];
    
                        $request = new Request($data);
                        $emailController = new EmailController();
                        $emailController->sendEmail($request);
                    }
                } catch (Exception $e) {
                    Log::error('Email sending failed: ' . $e->getMessage());
                }
            }

            $notificationType = 4;
            $template = Templates::select('subject', 'content')
                ->where('type', 1)
                ->where('notification_type', $notificationType)
                ->first();

            $companyName = GlobalSetting::where('key', 'company_name')->value('value') ?? 'Default Company Name';

            $contentTemplate = $template->content ?? '';
            $emailsWithContent = [];
            foreach ($providers as $provider) {
                $personalizedContent = str_replace(
                    ['{{provider_name}}', '{{category_name}}', '{{company_name}}'],
                    [
                        $provider->name,
                        $provider->userdetails->category_id ?? 'No Category',
                        $companyName
                    ],
                    $contentTemplate
                );
                $emailsWithContent[] = [
                    'email' => $provider->email,
                    'content' => $personalizedContent,
                ];
            }

            $finalContent = str_replace(
                ['{{user_name}}'],
                [$user->name ?? 'DemoUser'],
                $contentTemplate
            );

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Provider form inputs saved successfully.',
                'data' => $result['data'],
                'provider_emails' => $providers->pluck('email')->toArray(),
                'user_name' => $user->name ?? 'DemoUser',
                'email_template' => [
                    'email_subject' => $template->subject,
                    'email_content' => $finalContent,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'An error occurred while saving provider form inputs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listProviderLeads(LeadsListRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $providerFormsInputs = $this->leadsRepository->getProviderFormInputs($filters);

            $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
            $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();

            $providerFormsInputs->getCollection()->transform(function ($input) use ($dateformatSetting, $timeformatSetting) {
                $input->userFormInput->form_inputs_details = $input->userFormInput->form_inputs_details;

                $formattedDateTime = date(
                    $dateformatSetting->value . ' ' . $timeformatSetting->value,
                    strtotime($input->created_at)
                );
                $input->formatted_created_at = $formattedDateTime;

                $customerDetails = UserDetail::where('user_id', $input->userFormInput->user->id ?? null)
                    ->select('user_id', 'profile_image', 'first_name', 'last_name')
                    ->first();
                if ($customerDetails) {
                    $input->customer_profile_image = $customerDetails->profile_image
                        ? url('storage/profile/' . $customerDetails->profile_image)
                        : null;
                    $input->userFormInput->user->name = $customerDetails->first_name 
                        ? ucwords($customerDetails->first_name . ' ' . $customerDetails->last_name)
                        : ucwords($input->userFormInput->user->name);
                } else {
                    $input->customer_profile_image = null;
                }

                $providerDetails = UserDetail::where('user_id', $input->provider_id ?? null)
                    ->select('user_id', 'profile_image')
                    ->first();
                if ($providerDetails) {
                    $input->provider_profile_image = $providerDetails->profile_image
                        ? url('storage/profile/' . $providerDetails->profile_image)
                        : null;
                } else {
                    $input->provider_profile_image = null;
                }

                return $input;
            });

            $allCount = ProviderFormsInput::when($request->provider_id, function ($q) use ($request) {
                $q->where('provider_id', $request->provider_id);
            })->count();

            $newCount = ProviderFormsInput::when($request->provider_id, function ($q) use ($request) {
                $q->where('provider_id', $request->provider_id);
            })->where('status', 1)->count();

            $acceptCount = ProviderFormsInput::when($request->provider_id, function ($q) use ($request) {
                $q->where('provider_id', $request->provider_id);
            })->where('status', 2)->count();

            $rejectCount = ProviderFormsInput::when($request->provider_id, function ($q) use ($request) {
                $q->where('provider_id', $request->provider_id);
            })->where('status', 3)->count();

            $currencySymbol = Cache::remember('currecy_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default',1)->first();
            });

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Provider forms input list retrieved successfully.',
                'data' => [
                    'provider_forms_inputs' => $providerFormsInputs,
                    'meta' => [
                        'counts' => [
                            'all' => $allCount,
                            'new' => $newCount,
                            'accept' => $acceptCount,
                            'reject' => $rejectCount,
                            'currencySymbol' => $currencySymbol->symbol,
                        ],
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'An error occurred while retrieving provider forms input list.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listProviders(LeadsListRequest $request): JsonResponse
    {
        try {
            $result = $this->leadsRepository->getProvidersByCategory($request->category_id);
            
            return response()->json([
                'code' => $result['success'] ? 200 : 404,
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data']
            ], $result['success'] ? 200 : 404);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'An error occurred while retrieving providers.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function formInputStatus(StatusUpdateRequest $request): JsonResponse
    {
        try {
            $record = $this->leadsRepository->updateUserFormInputStatus($request->id, $request->status);
            
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $record,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'An error occurred while updating the status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function providerFormsInpuStatus(ProviderLeadsStatusRequest $request): JsonResponse
    {
        try {
            $record = $this->leadsRepository->updateProviderFormInputStatus($request->id, $request->status);

            $source = 'Leads Notification';
            $controller = new Controller();
            $notificationsettings = $controller->getnotificationsettings(1, $source);

            if ($notificationsettings == 1) {
                $notificationType = ($request->status == 3) ? 7 : 5;
                $template = Templates::select('subject', 'content')
                    ->where('type', 1)
                    ->where('notification_type', $notificationType)
                    ->first();

                if ($template) {
                    $provider = User::select('name', 'id')->where('email', $request->input('user_email'))->first();

                    $companyName = GlobalSetting::where('key', 'company_name')->value('value') ?? 'Default Company Name';
                    $categoryName = $request->input('category_name') ?? 'No Category';
                    $leadsData = $request->input('leads_data') ?? 'No Date Provided';
                    $leadsId = $request->input('id') ?? 'No Reference Number';
                    $content = str_replace(
                        ['{{user_name}}', '{{company_name}}', '{{category_name}}', '{{leads_data}}', '{{leads_id}}'],
                        [
                            $provider->name ?? 'DemoUser',
                            $companyName,
                            $categoryName,
                            $leadsData,
                            $leadsId
                        ],
                        $template->content
                    );

                    $subject = $template->subject;

                    $data = [
                        'to_email' => $request->user_email,
                        'subject' => $subject,
                        'content' => $content
                    ];

                    try {
                        $newrequest = new Request($data);
                        $emailController = new EmailController();
                        $emailController->sendEmail($newrequest);
                    } catch (Exception $e) {
                        Log::error('Email sending failed: ' . $e->getMessage());
                    }
                }
            }

            $notificationsettings1 = $controller->getnotificationsettings(3, $source);
            if ($notificationsettings1 == 1) {
                $user = User::select('name', 'id')->where('email', $request->user_email)->first();
                if( $request->input('status') === "2"){
                    $sourcenotify = "Accept Leads Provider";
                    $fromdescription = Templates::select('subject', 'content')
                    ->where('type', 3)
                    ->where('title', $sourcenotify)
                    ->where('recipient_type', 1)
                    ->first();
                    $todescription = Templates::select('subject', 'content')
                    ->where('type', 3)
                    ->where('title', $sourcenotify)
                    ->where('recipient_type', 2)
                    ->first();
                    $description = "Service Provider has accepted your request";
                }else{
                    $sourcenotify = "Reject Leads Provider";
                    $fromdescription = Templates::select('subject', 'content')
                    ->where('type', 3)
                    ->where('title', $sourcenotify)
                    ->where('recipient_type', 1)
                    ->first();
                    $todescription = Templates::select('subject', 'content')
                    ->where('type', 3)
                    ->where('title', $sourcenotify)
                    ->where('recipient_type', 2)
                    ->first();
                    $description = "Service Provider has rejected your request";
                }
                
                if (isset($notificationsettings1)) {
                     $data = [
                        'communication_type' => '3',
                        'source' => $sourcenotify,
                        'reference_id' => $request->input('user_id'),
                        'user_id'=>$request->input('provider_id'),
                        'to_user_id'=>$request->input('user_id'),
                        'from_description' => $fromdescription->content,
                        'to_description' => $todescription->content,

                    ];

                    try {
                        $request = new Request($data);
                        $notification = new NotificationController();
                        $notification->Storenotification($request);
                    } catch (Exception $e) {
                        Log::error('Notification creation failed: ' . $e->getMessage());
                    }
                }
            }

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' =>  __('Status updated successfully.'),
                'data' => $record,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'An error occurred while updating the status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storepayments(Request $request): JsonResponse
    {
        try {
            $data = [
                'user_id' => $request->user_id,
                'user_type' => '2',
                'amount' => $request->amount,
                'reference_id' => $request->refid,
                'payment_date' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $request->user_id,
                'created_at' => Carbon::now(),
            ];
            
            $result = $this->leadsRepository->storePayment($data);
            
            return response()->json([
                'code' => $result['success'] ? 200 : 500,
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'],
                'error' => $result['error'] ?? null
            ], $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
