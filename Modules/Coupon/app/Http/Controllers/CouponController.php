<?php

namespace Modules\Coupon\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Categories\app\Models\Categories;
use Modules\Coupon\app\Http\Requests\CouponStoreRequest;
use Modules\Coupon\app\Http\Requests\CouponListRequest;
use Modules\Coupon\app\Http\Requests\CouponStatusRequest;
use Modules\Coupon\app\Http\Requests\CouponUniqueRequest;
use Modules\Coupon\app\Repositories\Contracts\CouponRepositoryInterface;
use Modules\GlobalSetting\app\Models\Language;
use Modules\Product\app\Models\Product;

class CouponController extends Controller
{
    protected $couponRepository;

    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    public function index(): View
    {
        $currentRouteName = Route::currentRouteName();
        $companySeo = __("Coupon");

        if ($currentRouteName == 'admin.coupon') {
            return view('coupon::admin.coupon_list', compact('companySeo'));
        } else {
            return view('coupon::provider.coupon_list', compact('companySeo'));
        }
    }

    public function couponList(CouponListRequest $request): JsonResponse
    {
        try {
            $data = $this->couponRepository->getCouponsByUser(
                $request->user_id,
                $request->is_valid ?? 1,
                $request->order_by ?? 'desc'
            );

            return response()->json([
                'code' => 200,
                'message' => __('Coupon details retrieved successfully.'),
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('An error occurred while retrieving coupon.'),
            ], 500);
        }
    }

    public function create(Request $request): View
    {
        $languageId = $this->getLanguageId();
        $categories = $this->getCategories($languageId);
        $subcategories = $this->getSubcategories($languageId);
        $products = $this->getProducts($languageId);

        $currentRouteName = Route::currentRouteName();

        if ($currentRouteName == 'admin.create-coupon') {
            return view('coupon::admin.create_coupon', compact('categories', 'subcategories', 'products'));
        } else {
            return view('coupon::provider.create_coupon', compact('categories', 'subcategories', 'products'));
        }
    }

    public function edit(Request $request): View
    {
        $languageId = $this->getLanguageId();
        $categories = $this->getCategories($languageId);
        $subcategories = $this->getSubcategories($languageId);
        $products = $this->getProducts($languageId);

        $data = $this->couponRepository->findCoupon($request->id ?? '');

        $currentRouteName = Route::currentRouteName();

        if ($currentRouteName == 'admin.edit-coupon') {
            return view('coupon::admin.edit_coupon', compact('categories', 'subcategories', 'products', 'data'));
        } else {
            return view('coupon::provider.edit_coupon', compact('categories', 'subcategories', 'products', 'data'));
        }
    }

    public function store(CouponStoreRequest $request): JsonResponse
    {
        try {
            $data = $this->prepareCouponData($request);

            $successMsg = $request->id ? __('coupon_update_success') : __('coupon_create_success');

            $this->couponRepository->createOrUpdateCoupon($data, $request->id);

            return response()->json([
                'code' => 200,
                'message' => $successMsg,
            ], 200);

        } catch (\Exception $e) {
            $errorMsg = $request->id ? __('coupon_update_error') : __('coupon_create_error');

            return response()->json([
                'code' => 500,
                'message' => $errorMsg,
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $deleted = $this->couponRepository->deleteCoupon($request->id);

            if (!$deleted) {
                return response()->json([
                    'code' => 200,
                    'message' => __('Coupon not found!', [], $request->language_code ?? 'en')
                ], 200);
            }

            return response()->json([
                'code' => 200,
                'message' => __('coupon_delete_success', [], $request->language_code ?? 'en')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('coupon_delete_error', [], $request->language_code ?? 'en'),
            ], 500);
        }
    }

    public function checkUnique(CouponUniqueRequest $request): JsonResponse
    {
        $isUnique = $this->couponRepository->checkCodeUnique($request->code, $request->id);
        return response()->json($isUnique);
    }

    public function changeCouponStatus(CouponStatusRequest $request): JsonResponse
    {
        try {
            $this->couponRepository->updateCouponStatus($request->id, $request->status);

            return response()->json([
                'code' => 200,
                'message' => __('coupon_status_success', [], $request->language_code ?? 'en')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('coupon_status_error', [], $request->language_code ?? 'en'),
            ], 500);
        }
    }

    // Helper methods
    protected function getLanguageId(): int
    {
        if (Auth::check()) {
            return Auth::user()->user_language_id;
        } elseif (Cookie::get('languageId')) {
            return Cookie::get('languageId');
        }

        $defaultLanguage = Language::select('id', 'code')->where('status', 1)->where('is_default', 1)->first();
        return $defaultLanguage ? $defaultLanguage->id : 1;
    }

    protected function getCategories($languageId)
    {
        return Categories::where([
            'language_id' => $languageId,
            'parent_id' => 0,
            'status' => 1
        ])
        ->where('source_type', 'service')
        ->get(['id', 'name']);
    }

    protected function getSubcategories($languageId)
    {
        return Categories::where('language_id', $languageId)
            ->where('parent_id', '!=', 0)
            ->where('status', 1)
            ->where('source_type', 'service')
            ->get(['id', 'name']);
    }

    protected function getProducts($languageId)
    {
        if (Auth::user()->user_type == 1 || Auth::user()->user_type == 5) {
            return Product::where(['language_id' => $languageId, 'status' => 1])->get(['id', 'source_name']);
        }

        $userId = Auth::id();
        return Product::where(['language_id' => $languageId, 'status' => 1, 'user_id' => $userId])->get(['id', 'source_name']);
    }

    protected function prepareCouponData(Request $request): array
    {
        $data = [
            "code" => $request->code,
            "product_type" => $request->product_type,
            "coupon_type" => $request->coupon_type,
            "coupon_value" => $request->coupon_value,
            "quantity" => $request->quantity,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
        ];

        if (!$request->id) {
            $data['created_by'] = Auth::id() ?? $request->created_by;
        }

        $data['quantity_value'] = $request->quantity == 'limited' ? $request->quantity_value : null;

        // Handle product, category, subcategory IDs
        $this->processRelationIds($data, $request);

        return $data;
    }

    protected function processRelationIds(array &$data, Request $request): void
    {
        $productId = $request->product_id ?? null;
        $categoryId = $request->category_id ?? null;
        $subcategoryId = $request->subcategory_id ?? null;

        if (is_array($productId) && $request->product_type == 'service') {
            $data['product_id'] = implode(',', $productId);
        } else {
            $data['product_id'] = null;
        }

        if (is_array($categoryId) && $request->product_type == 'category') {
            $data['category_id'] = implode(',', $categoryId);
        } else {
            $data['category_id'] = null;
        }

        if (is_array($subcategoryId) && $request->product_type == 'subcategory') {
            $data['subcategory_id'] = implode(',', $subcategoryId);
        } else {
            $data['subcategory_id'] = null;
        }
    }
}