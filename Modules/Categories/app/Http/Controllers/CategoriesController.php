<?php

namespace Modules\Categories\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Categories\app\Models\Categories;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Product\app\Models\Category;
use Modules\Categories\app\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\View\View;

class CategoriesController extends Controller
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function servicecategories(): View
    {
        return view('admin.servicecategories-settings');
    }

    public function serviceSubcategories(): View
    {
        $languageId = Auth::user()->user_language_id ?? '1';
        $categories = Categories::select('id', 'name')
            ->where('parent_id', '=', 0)
            ->where('language_id', $languageId)
            ->get();

        return view('admin.servicesubcategories', compact('categories'));
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->categoryRepository->index($request);
        return $response;
    }

    public function show(Request $request, $id): JsonResponse
    {
        $response = $this->categoryRepository->show($request, $id);
        return $response;
    }

    public function store(Request $request): JsonResponse
    {
        $response = $this->categoryRepository->store($request);
        return $response;
    }

    public function destroy(Request $request): JsonResponse
    {
        $response = $this->categoryRepository->destroy($request);
        return $response;
    }

    public function changeFeatured(Request $request): JsonResponse
    {
        $response = $this->categoryRepository->changeFeatured($request);
        return $response;
    }

    public function subcategoryList(Request $request): JsonResponse
    {
        $response = $this->categoryRepository->subcategoryList($request);
        return $response;
    }

    public function subcategoryStore(Request $request): JsonResponse
    {
        $response = $this->categoryRepository->subcategoryStore($request);
        return $response;
    }

    public function getSubcategories(Request $request): JsonResponse
    {
        $response = $this->categoryRepository->getSubcategories($request);
        return $response;
    }

    public function categories(Request $request): JsonResponse
    {
        $response = $this->categoryRepository->categories($request);
        return $response;
    }

    public function getRegisterSubcategories(Request $request): JsonResponse
    {
        $response = $this->categoryRepository->getRegisterSubcategories($request);
        return $response;
    }

    public function getAllLanguages(): JsonResponse
    {
        $response = $this->categoryRepository->getAllLanguages();
        return $response;
    }
}
