<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use Modules\GlobalSetting\app\Http\Requests\CommunicationSettingsRequest;
use Modules\GlobalSetting\app\Http\Requests\StatusRequest;
use Modules\GlobalSetting\app\Http\Requests\TemplateListRequest;
use Modules\GlobalSetting\app\Http\Requests\TemplateRequest;
use Modules\GlobalSetting\app\Models\Templates;
use Modules\GlobalSetting\app\Repositories\Contracts\CommunicationSettingsInterface;
use Modules\GlobalSetting\app\Models\Placeholders;
use Modules\GlobalSetting\app\Models\NotificationTypes;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;

class CommunicationSettingsController extends Controller
{
    protected $communicationSettings;
    
    public function __construct(CommunicationSettingsInterface $communicationSettings)
    {
        $this->communicationSettings = $communicationSettings;
    }

    public function index(): JsonResponse
    {
        try {
            $settings = $this->communicationSettings->getDefaultSettings();
            
            return response()->json([
                'code' => 200,
                'message' => __('Data retrieved successfully.'),
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, 'An error occurred while retrieving Email Settings.');
        }
    }

    public function getsettingsdata(Request $request): JsonResponse
    {
        $settings = $this->communicationSettings->getSettingsByType($request->input('type'));
        
        return response()->json([
            'code' => 200,
            'message' => __('Data retrieved successfully'),
            'data' => ['settings' => $settings]
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $settings = $this->communicationSettings->getSettingsList(
                $request->input('id'),
                $request->input('type')
            );
            
            return response()->json([
                'code' => 200,
                'message' => __('Data retrieved successfully.'),
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, 'An error occurred while retrieving Email Settings.');
        }
    }

    public function setDefault(Request $request): JsonResponse
    {
        try {
            $success = $this->communicationSettings->setDefaultSetting(
                $request->input('id'),
                $request->input('enabled')
            );
            
            if (!$success) {
                throw new \Exception('Failed to set default email type.');
            }
            
            return response()->json([
                'code' => 200,
                'success' => true,
                'status' => $request->input('enabled') ? 'enabled' : 'disabled',
                'message' => 'Default email type set successfully.'
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to set default email type.');
        }
    }

    public function store(CommunicationSettingsRequest $request): JsonResponse
    {
        try {
            $type = $request->input('type');
            $settingsType = in_array($type, ['nexmo', 'twofactor', 'twilio']) ? 2 : 1;
            
            switch ($type) {
                case 'phpmail':
                    $this->updatePhpMailSettings($request, $settingsType);
                    break;
                case 'smtp':
                    $this->updateSmtpSettings($request, $settingsType);
                    break;
                case 'sendgrid':
                    $this->updateSendgridSettings($request, $settingsType);
                    break;
                case 'nexmo':
                case 'twofactor':
                case 'twilio':
                    $this->updateSmsSettings($request, $settingsType, $type);
                    break;
                case 'notification_settings':
                    $this->updateNotificationSettings($request);
                    break;
                case 'fcm':
                    $this->updateFcmSettings($request);
                    break;
            }
            
            return response()->json([
                'code' => 200,
                'message' => __('Global setting updated successfully.'),
                'data' => []
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Something went wrong while saving the Settings!');
        }
    }

    public function statusstore(StatusRequest $request): JsonResponse
    {
        try {
            $type = $request->input('type');
            $settingsType = in_array($type, ['nexmo', 'twofactor', 'twilio']) ? 2 : 1;
            
            $setting = $this->communicationSettings->updateStatus(
                $type,
                $request->input('status'),
                $settingsType
            );
            
            $message = $request->input('status') == 1 ? 'Activated Successfully' : 'Deactivated Successfully';
            
            return response()->json([
                'code' => 200,
                'message' => $message,
                'data' => [$setting]
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to update status');
        }
    }

    public function gettemplatelist(TemplateListRequest $request): JsonResponse
    {
        try {
            $templates = $this->communicationSettings->getTemplates([
                'order_by' => $request->input('order_by', 'desc'),
                'sort_by' => $request->input('sort_by', 'id'),
                'search' => $request->input('search')
            ]);
            
            return response()->json([
                'code' => '200',
                'message' => __('Templates retrieved successfully.'),
                'data' => $templates,
            ], 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'An error occurred while retrieving templates.');
        }
    }

    public function add(): View
    {
        $getplaceholder = Placeholders::select('placeholder_name', 'id')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get();
            
        $getnotificationtypes = NotificationTypes::select('type', 'id')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get();
            
        return view('globalsetting::communication.email-templates', compact('getplaceholder', 'getnotificationtypes'));
    }

    public function templatestore(TemplateRequest $request): JsonResponse
    {
        try {
            $data = $request->only(['type', 'notification_type', 'title', 'subject', 'status', 'content']);
            $data['created_by'] = $request->user_id ?? 1;
            $data['updated_by'] = $request->user_id ?? 1;
            
            if ($request->input('id')) {
                $template = $this->communicationSettings->updateTemplate($request->input('id'), $data);
                $message = 'Templates updated successfully.';
                $statusCode = 200;
            } else {
                $template = $this->communicationSettings->createTemplate($data);
                $message = 'Templates created successfully.';
                $statusCode = 201;
            }
            
            return response()->json([
                'code' => $statusCode,
                'success' => true,
                'message' => $message,
                'data' => $template
            ], $statusCode);
        } catch (\Exception $e) {
            return $this->handleError($e, $e->getMessage());
        }
    }

     public function edit(Request $request) :JsonResponse
    {
        $gettemplatedata=Templates::select('content')->where('id',$request->id)->where('deleted_at',null)->first();
        $data['content']="";
        if(isset($gettemplatedata)){
             $data['content']=$gettemplatedata->content;
        }
        return response()->json(['code' => 200, 'message' => 'success', 'data' => [$data]], 200);
    }

    public function deletetemplate(Request $request): JsonResponse
    {
        try {
            $this->communicationSettings->deleteTemplate($request->input('id'));
            
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Template deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to delete Template.');
        }
    }

    // Helper methods
    private function updatePhpMailSettings($request, $settingsType): void
    {
        $this->communicationSettings->updateOrCreateSetting('phpmail_from_email', $request->phpmail_from_email, $settingsType, $request->type);
        $this->communicationSettings->updateOrCreateSetting('phpmail_from_name', $request->phpmail_from_name, $settingsType, $request->type);
        $this->communicationSettings->updateOrCreateSetting('phpmail_password', $request->phpmail_password, $settingsType, $request->type);
    }

    private function updateSmtpSettings($request, $settingsType): void
    {
        $this->communicationSettings->updateOrCreateSetting('smtp_from_email', $request->smtp_from_email, $settingsType, $request->type);
        $this->communicationSettings->updateOrCreateSetting('smtp_password', $request->smtp_password, $settingsType, $request->type);
        $this->communicationSettings->updateOrCreateSetting('smtp_from_name', $request->smtp_from_name, $settingsType, $request->type);
        $this->communicationSettings->updateOrCreateSetting('port', $request->port, $settingsType, $request->type);
        $this->communicationSettings->updateOrCreateSetting('host', $request->host, $settingsType, $request->type);
    }

    private function updateSendgridSettings($request, $settingsType): void
    {
        $this->communicationSettings->updateOrCreateSetting('sendgrid_from_email', $request->sendgrid_from_email, $settingsType, $request->type);
        $this->communicationSettings->updateOrCreateSetting('sendgrid_key', $request->sendgrid_key, $settingsType, $request->type);
    }

    private function updateSmsSettings($request, $settingsType, $type): void
    {
        $this->communicationSettings->updateOrCreateSetting($type.'_api_key', $request->{$type.'_api_key'}, $settingsType, $type);
        $this->communicationSettings->updateOrCreateSetting($type.'_secret_key', $request->{$type.'_secret_key'}, $settingsType, $type);
        $this->communicationSettings->updateOrCreateSetting($type.'_sender_id', $request->{$type.'_sender_id'}, $settingsType, $type);
    }

    private function updateNotificationSettings($request): void
    {
        $this->communicationSettings->updateOrCreateSetting('emailNotifications', $request->emailNotifications == 'on' ? 1 : 0, 3, 'notification_settings');
        $this->communicationSettings->updateOrCreateSetting('pushNotifications', $request->pushNotifications == 'on' ? 1 : 0, 3, 'notification_settings');
        $this->communicationSettings->updateOrCreateSetting('smsNotifications', $request->smsNotifications == 'on' ? 1 : 0, 3, 'notification_settings');
    }

    private function updateFcmSettings($request): void
    {
        $this->communicationSettings->updateOrCreateSetting('project_id', $request->project_id, 3, 'fcm');
        $this->communicationSettings->updateOrCreateSetting('client_email', $request->client_email, 3, 'fcm');
        $this->communicationSettings->updateOrCreateSetting('private_key', $request->private_key, 3, 'fcm');
    }

    private function handleError(\Exception $e, string $message = ''): JsonResponse
    {
        $code = $e instanceof \Illuminate\Validation\ValidationException ? 422 : 500;
        
        $response = [
            'code' => $code,
            'success' => false,
            'message' => $message ?: $e->getMessage()
        ];
        
        if ($code == 422 && $e instanceof \Illuminate\Validation\ValidationException) {
            $response['errors'] = $e->errors();
        } else {
            Log::error($e->getMessage());
        }
        
        return response()->json($response, $code);
    }
}