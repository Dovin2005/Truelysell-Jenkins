<?php

namespace Modules\MenuBuilder\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\MenuBuilder\app\Repositories\Contracts\MenuBuilderRepositoryInterface;

class MenuBuilderController extends Controller
{
    protected $menuBuilderRepository;

    public function __construct(MenuBuilderRepositoryInterface $menuBuilderRepository)
    {
        $this->menuBuilderRepository = $menuBuilderRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $resoponse = $this->menuBuilderRepository->index($request);
        return response()->json($resoponse, $resoponse['code']);
    }

    public function getBuiltMenus(Request $request): JsonResponse
    {
        $resoponse = $this->menuBuilderRepository->getBuiltMenus($request);
        return response()->json($resoponse, $resoponse['code']);
    }

    public function store(Request $request): JsonResponse
    {
        $resoponse = $this->menuBuilderRepository->store($request);
        return response()->json($resoponse, $resoponse['code']);
    }
}
