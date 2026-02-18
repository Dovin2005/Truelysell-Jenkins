<?php

namespace Modules\Service\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branches;
use App\Models\PackageTrx;
use App\Models\ServiceBranch;
use App\Models\ServiceStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Service\app\Models\Productmeta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Service\app\Models\AdditionalService as ModelsAdditionalService;
use Modules\Service\app\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Product\app\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Categories\app\Models\Categories;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\GlobalSetting\app\Models\Language;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Modules\GlobalSetting\app\Models\Templates;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Product\app\Models\Category;
use Modules\Service\app\Repositories\Contracts\ServiceRepositoryInterface;

class ServiceController extends Controller
{
    protected ServiceRepositoryInterface $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function setdefault(Request $request)
    {
        $response = $this->serviceRepository->setDefault($request);
        return response()->json($response, $response['code']);
    }

    public function delete(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->delete($request);
        return response()->json($response, $response['code']);
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->index($request);
        return response()->json($response, $response['code']);
    }

    public function store(Request $request)
    {
        return $this->serviceRepository->store($request);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        return $this->serviceRepository->update($request);
    }

    /**+
     *
     * Remove the specified resource from storage.
     */
    public function providerServiceIndex(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->providerServiceIndex($request);
        return response()->json($response, $response['code']);
    }

    public function providerService(): View
    {
        $userId = Auth::id();
        return view("provider.providerService", compact('userId'));
    }

    public function providerAddServiceIndex(): View
    {
        $serviceSlot = DB::table('general_settings')->where('key', 'service_slot')->value('value');
        $servicePackage = DB::table('general_settings')->where('key', 'service_package')->value('value');

        $userId = Auth::id();
        $userLangId = User::where('id', $userId)->value('user_language_id');

        $chatstatus = GlobalSetting::where('group_id', 4)
            ->where('key', 'chatgpt_status')
            ->pluck('value')
            ->first();
        return view("provider.providerAddService", [
            'show_slot' => $serviceSlot == 1,
            'show_package' => $servicePackage == 1,
            'userLangId' => $userLangId,
            'chat_status' => $chatstatus
        ]);
    }

    public function providerEditService(): View
    {
        $serviceSlot = DB::table('general_settings')->where('key', 'service_slot')->value('value');
        $servicePackage = DB::table('general_settings')->where('key', 'service_package')->value('value');

        $userId = Auth::id();
        $userLangId = User::where('id', $userId)->value('user_language_id');
        $editCategories = Categories::select('id', 'name', 'slug')->where('status', 1)->where('language_id', $userLangId)->where('parent_id', 0)->get() ?? collect();

        $chatstatus = GlobalSetting::where('group_id', 4)
            ->where('key', 'chatgpt_status')
            ->pluck('value')
            ->first();
        return view("provider.providerEditService", ['show_slot' => $serviceSlot == 1], ['show_package' => $servicePackage == 1, 'userLangId' => $userLangId, 'editCategories' => $editCategories, 'chat_status' => $chatstatus]);
    }


    public function getDetails(Request $request, string $slug): JsonResponse
    {
        $response = $this->serviceRepository->getDetails($request, $slug);
        return response()->json($response, $response['code']);
    }

    public function providerServiceStore(Request $request): JsonResponse
    {
        return $this->serviceRepository->providerServiceStore($request);
    }

    public function verifyService(Request $request)
    {
        $response = $this->serviceRepository->verifyService($request);
        return response()->json($response, $response['code']);
    }

    public function providerServiceUpdate(Request $request): JsonResponse
    {
        return $this->serviceRepository->providerServiceUpdate($request);
    }

    public function deleteServiceImage(string $id): JsonResponse
    {
        $response = $this->serviceRepository->deleteServiceImage($id);
        return response()->json($response, $response['code']);
    }

    public function deleteSlot(string $id): JsonResponse
    {
        $response = $this->serviceRepository->deleteSlot($id);
        return response()->json($response, $response['code']);
    }

    public function deleteAdditionalServices(string $id): JsonResponse
    {
        $response = $this->serviceRepository->deleteAdditionalServices($id);
        return response()->json($response, $response['code']);
    }

    public function deleteServices(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->deleteServices($request);
        return response()->json($response, $response['code']);
    }

    public function status(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->status($request);
        return response()->json($response, $response['code']);
    }

    public function checkUnique(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->checkUnique($request);
        return response()->json($response);
    }

    public function checkEditUnique(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->checkEditUnique($request);
        return response()->json($response);
    }

    public function providerSub(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->providerSub($request);
        return response()->json($response, $response['code']);
    }

    public function providerSubApi(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->providerSubApi($request);
        return response()->json($response, $response['code']);
    }

    public function translate(Request $request)
    {
        $response = $this->serviceRepository->translate($request);
        return response()->json($response, $response['code']);
    }

    public function deleteImage(Request $request)
    {
        $response = $this->serviceRepository->deleteImage($request);
        return response()->json($response);
    }

    public function checkCoupon(Request $request)
    {
        $response = $this->serviceRepository->checkCoupon($request);
        return response()->json($response);
    }
}
