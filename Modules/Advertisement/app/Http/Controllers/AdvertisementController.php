<?php

namespace Modules\Advertisement\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Advertisement\app\Http\Requests\CreateAdvertisementRequest;
use Modules\Advertisement\app\Http\Requests\UpdateAdvertisementRequest;
use Modules\Advertisement\app\Http\Requests\DeleteAdvertisementRequest;
use Modules\Advertisement\app\Repositories\Contracts\AdvertisementRepositoryInterface;

class AdvertisementController extends Controller
{
    protected AdvertisementRepositoryInterface $advertisementRepository;

    public function __construct(AdvertisementRepositoryInterface $advertisementRepository)
    {
        $this->advertisementRepository = $advertisementRepository;
    }

    public function index(): JsonResponse
    {
        $response = $this->advertisementRepository->index();
        return response()->json($response, $response['code']);
    }

    public function advertisement()
    {
        return view('advertisement::advertisement.create');
    }

    public function create(CreateAdvertisementRequest $request): JsonResponse
    {
        $response = $this->advertisementRepository->createAd($request);
        return response()->json($response, $response['code']);
    }

    public function edit(UpdateAdvertisementRequest $request): JsonResponse
    {
        $response = $this->advertisementRepository->editAd($request);
        return response()->json($response, $response['code']);
    }

    public function delete(DeleteAdvertisementRequest $request): JsonResponse
    {
        $response = $this->advertisementRepository->deleteAd($request);
        return response()->json($response, $response['code']);
    }
}