<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\GlobalSetting\app\Http\Requests\AwsSettingsRequest;
use Modules\GlobalSetting\app\Http\Requests\StorageStatusRequest;
use Modules\GlobalSetting\app\Repositories\Contracts\FileStorageInterface;

class FileStorageController extends Controller
{
    protected $fileStorageRepository;

    public function __construct(FileStorageInterface $fileStorageRepository)
    {
        $this->fileStorageRepository = $fileStorageRepository;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $groupId = $request->input('group_id', 20);
            $settings = $this->fileStorageRepository->getSettingsByGroup($groupId);

            return response()->json([
                'code' => 200,
                'message' => __('truelysell_validation.success_response.global_setting_retrived_success'),
                'data' => ['settings' => $settings]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to retrieve settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeAws(AwsSettingsRequest $request): JsonResponse
    {
        try {
            $this->fileStorageRepository->updateAwsSettings($request->validated());

            return response()->json([
                'code' => 200,
                'message' => __('Global setting updated successfully.'),
                'data' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to update AWS settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statuslocal(StorageStatusRequest $request): JsonResponse
    {
        try {
            $status = $this->fileStorageRepository->setLocalStatus($request->local_status);

            return response()->json([
                'code' => 200,
                'message' => $status == 1 ? 'Activated' : 'Deactivated',
                'data' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to update local storage status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function statusAws(StorageStatusRequest $request): JsonResponse
    {
        try {
            $localStatus = $request->input('local_status');
            $awsStatus = $request->input('aws_status');

            $this->fileStorageRepository->updateStorageStatus($awsStatus, $localStatus);

            return response()->json([
                'code' => 200,
                'message' => __('Storage status updated successfully.'),
                'data' => []
            ]);

        } catch (\Exception $e) {
            \Log::error('Storage status update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'code' => 500,
                'message' => 'Failed to update storage status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}