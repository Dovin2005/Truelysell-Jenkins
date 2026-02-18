<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\AddonRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class AddonController extends Controller
{
    protected AddonRepositoryInterface $addonRepository;

    public function __construct(AddonRepositoryInterface $addonRepository)
    {
        $this->addonRepository = $addonRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->addonRepository->index($request);
        return response()->json($response, $response['code']);
    }

    public function changeAddonStatus(Request $request): JsonResponse
    {
        $response = $this->addonRepository->changeAddonStatus($request);
        return response()->json($response, $response['code']);
    }

    public function listNewAddonModules(Request $request): JsonResponse
    {
        $response = $this->addonRepository->listNewAddonModules($request);
        return response()->json($response, $response['code']);
    }

    public function purchaseModule(Request $request): JsonResponse
    {
        $response = $this->addonRepository->purchaseModule($request);
        return response()->json($response, $response['code']);
    }

    public function updateModule(Request $request)
    {
        $response = $this->addonRepository->updateModule($request);
        return $response;
    }
}
