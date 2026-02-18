<?php

namespace Modules\Leads\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Leads\app\Http\Requests\UserStatusRequest;
use Modules\Leads\app\Repositories\Contracts\UserLeadsInterface;
use App\Models\User;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Modules\GlobalSetting\app\Models\Templates;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $userLeadsRepository;

    public function __construct(UserLeadsInterface $userLeadsRepository)
    {
        $this->userLeadsRepository = $userLeadsRepository;
    }

    public function userStatus(UserStatusRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $statusSummary = $this->userLeadsRepository->getStatusSummary($data['id']);

            
            if ($data['status'] == 2 || (($statusSummary['total']-1 === $statusSummary['status_3_count']) && $data['status'] == 3)) {
                $userFormInput = $this->userLeadsRepository->updateUserStatus($data);
            } else {
                $userFormInput = $this->userLeadsRepository->getUserFormInput($data['id']);
            }

            $providerFormInput = $this->userLeadsRepository->updateProviderStatus($data['provider_forms_input'], $data['status']);

            $this->handleEmailNotification($request, $data);
            $this->handleAppNotification($request, $data);

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'user_form_input' => $userFormInput,
                    'provider_forms_input' => $providerFormInput,
                ],
            ], 200);

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    protected function handleEmailNotification(Request $request, array $data): void
    {
        $source = 'Leads Notification';
        $notificationsettings = (new Controller())->getnotificationsettings(1, $source);

        if ($notificationsettings == 1 && isset($data['provider_email'])) {
            $notificationType = ($data['status'] == 3) ? 8 : 6;
            $template = Templates::query()
                ->select('subject', 'content')
                ->where('type', 1)
                ->where('notification_type', $notificationType)
                ->first();

            if ($template) {
                $provider = User::select('name')->where('email', $data['provider_email'])->first();
                $companyName = GlobalSetting::where('key', 'company_name')->value('value') ?? 'Default Company Name';

                $replacements = [
                    '{{provider_name}}' => $provider->name ?? 'DemoUser',
                    '{{company_name}}' => $companyName,
                    '{{category_name}}' => $data['category_name'] ?? 'No Category',
                    '{{user_name}}' => $data['user_name'] ?? 'DemoUser',
                    '{{quote_amount}}' => $data['quote_amount'] ?? 'No Quote Amount',
                    '{{accepted_date}}' => $data['accepted_date'] ?? now()->toDateString(),
                    '{{leads_id}}' => $data['id'] ?? 'No Reference Number'
                ];

                $content = str_replace(array_keys($replacements), array_values($replacements), $template->content);
                $subject = str_replace(
                    ['{{provider_name}}', '{{company_name}}'],
                    [$provider->name ?? 'DemoUser', $companyName],
                    $template->subject
                );

                $emailData = [
                    'to_email' => $data['provider_email'],
                    'subject' => $subject,
                    'content' => $content,
                ];

                try {
                    $emailRequest = new Request($emailData);
                    (new EmailController())->sendEmail($emailRequest);
                } catch (Exception $e) {
                    Log::error('Error sending email: ' . $e->getMessage());
                }
            }
        }
    }

    protected function handleAppNotification(Request $request, array $data): void
    {
        $source = 'Leads Notification';
        $notificationsettings = (new Controller())->getnotificationsettings(3, $source);

        if ($notificationsettings == 1 && isset($data['provider_email']) && isset($data['user_id'])) {
            $provider = User::select('name', 'id')->where('email', $data['provider_email'])->first();
            
            if ($provider) {
                $sourcenotify = ($data['status'] == 2) ? "Accept Leads User" : "Reject Leads User";
                $description = ($data['status'] == 2) 
                    ? "Customer has accepted your Quote" 
                    : "Customer has rejected your Quote";

                $templateQuery = Templates::select('subject', 'content')
                    ->where('type', 3)
                    ->where('title', $sourcenotify);

                $fromdescription = (clone $templateQuery)->where('recipient_type', 1)->first();
                $todescription = (clone $templateQuery)->where('recipient_type', 2)->first();

                if ($fromdescription && $todescription) {
                    $notificationData = [
                        'communication_type' => '3',
                        'source' => $sourcenotify,
                        'reference_id' => $data['id'],
                        'user_id' => $data['user_id'],
                        'to_user_id' => $provider->id,
                        'from_description' => $fromdescription->content,
                        'to_description' => $todescription->content,
                    ];

                    try {
                        $notificationRequest = new Request($notificationData);
                        (new NotificationController())->Storenotification($notificationRequest);
                    } catch (Exception $e) {
                        Log::error('Error sending notification: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    protected function errorResponse(string $message, int $code): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'success' => false,
            'message' => $code === 404 ? 'Record not found' : 'An error occurred while updating the status',
            'error' => $message,
        ], $code);
    }
}