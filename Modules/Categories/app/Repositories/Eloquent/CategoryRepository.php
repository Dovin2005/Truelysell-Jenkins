<?php

namespace Modules\Categories\app\Repositories\Eloquent;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\Categories\app\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Categories\app\Models\Categories;
use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Validation\Rule;
use Modules\Product\app\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function index(Request $request): JsonResponse
    {
        try {
            $languageId = $request->language_id;

            $data = Categories::where('parent_id', '=', 0)
                ->where('language_id', $languageId)
                ->orderBy('id', 'desc')
                ->get()->map(function ($category) {
                    $category->image = $category->image && file_exists(public_path('storage/' . $category->image))
                        ? url('storage/' . $category->image)
                        : url('front/img/default-placeholder-image.png');

                    $category->icon = $category->icon && file_exists(public_path('storage/' . $category->icon))
                        ? url('storage/' . $category->icon)
                        : url('front/img/default-placeholder-image.png');

                    return $category;
                });

            return response()->json([
                'code' => 200,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to retrieve categories.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $language_id = $request->get('language_id');

            $category = Categories::where('parent_language_id', $id)
                ->where('language_id', $language_id)
                ->first();

            if (!$category) {
                $category = Categories::where('id', $id)
                    ->where('language_id', $language_id)
                    ->first();
            }

            if (!$category) {
                $parentCategory = Categories::find($id); // Fetch the record using the primary ID.
                if ($parentCategory && $parentCategory->parent_language_id) {
                    $category = Categories::where('id', $parentCategory->parent_language_id)
                        ->first();
                }
            }

            $data = $category ?? [];

            return response()->json([
                'code' => '200',
                'message' => 'Category retrieved successfully.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => 'An error occurred while retrieving the category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $id = $request->id ?? '';
        $languageCode = $request->language_code ?? app()->getLocale();
        if ($request->has('slug')) {
            $request->merge(['slug' => Str::slug($request->slug)]);
        }

        $validator = Validator::make($request->all(), [
            'language_id' => 'required',
            'category_name' => [
                'required',
                Rule::unique('categories', 'name')->ignore($id)
                    ->where('language_id', $request->language_id)
                    ->where('parent_id', '=', 0)
                    ->whereNull('deleted_at'),
            ],
            'slug' => [
                'required',
                Rule::unique('categories', 'slug')->ignore($id)
                    ->whereNull('deleted_at'),

            ],
            'description' => 'required',
        ], [
            'category_name.unique' => __('category_name_exists', [], $languageCode),
            'slug.unique' => __('slug_exists', [], $languageCode),
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'success' => false,
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $successMessage = empty($id) ? __('category_create_success', [], $languageCode) : __('category_update_success', [], $languageCode);
        $errorMessage = empty($id) ? __('An error occurred while creating!') : __('An error occurred while updating!');

        try {
            $data = [
                'name' => $request->category_name,
                'parent_id' => 0,
                'slug' => $request->slug,
                'status' => $request->status,
                'source_type' => $request->source_type,
                'description' => $request->description,
                'featured' => $request->featured,
                'language_id' => $request->language_id,
            ];

            if ($request->hasFile('category_image')) {
                $path = $request->file('category_image')->store('categories', 'public');
                $data['image'] = $path;
            }

            if ($request->hasFile('category_icon')) {
                $path = $request->file('category_icon')->store('categories', 'public');
                $data['icon'] = $path;
            }

            if (empty($id)) {
                Categories::create($data);
            } else {
                Categories::where('id', $id)->update($data);
            }

            Cache::forget('categoriess');

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => $successMessage
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:categories,id',
        ]);

        $languageCode = $request->language_code ?? app()->getLocale();

        try {
            $category = Categories::where('id', $request->input('id'))->first();
            $category->delete();
            Cache::forget('categoriess');

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('delete_success', [], $languageCode),
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'An error occurred while deleting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function changeFeatured(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:categories,id',
            'featured' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->first()
            ], 500);
        }

        $languageCode = $request->language_code ?? app()->getLocale();

        try {
            $category = Categories::findOrFail($request->input('id'));
            $category->featured = $request->input('featured');
            $category->save();

            Cache::forget('categoriess');

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('feature_status_update_success', [], $languageCode)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Failed to update category featured.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function subcategoryList(Request $request): JsonResponse
    {
        $categoryId = $request->category_id ?? '';
        $languageId = $request->language_id ?? '';

        try {
            $data = Categories::with('parentCategory')
                ->where('parent_id', '!=', 0)
                ->when($categoryId, function ($query) use ($categoryId) {
                    $query->where('parent_id', $categoryId);
                })
                ->when($languageId, function ($query) use ($languageId) {
                    $query->where('language_id', $languageId);
                })
                ->whereHas('parentCategory')
                ->orderBy('id', 'desc')
                ->get()->map(function ($category) {
                    $category->image = $category->image && file_exists(public_path('storage/' . $category->image))
                        ? url('storage/' . $category->image)
                        : url('front/img/default-placeholder-image.png');

                    $category->icon = $category->icon && file_exists(public_path('storage/' . $category->icon))
                        ? url('storage/' . $category->icon)
                        : url('front/img/default-placeholder-image.png');

                    return $category;
                });

            return response()->json([
                'code' => 200,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to retrieve categories.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function subcategoryStore(Request $request): JsonResponse
    {
        $id = $request->id ?? '';
        $languageCode = $request->language_code ?? app()->getLocale();

        if ($request->has('slug')) {
            $request->merge(['slug' => Str::slug($request->slug)]);
        }

        $validator = Validator::make($request->all(), [
            'language_id' => 'required',
            'category_id' => 'required',
            'subcategory_name' => [
                'required',
                Rule::unique('categories', 'name')->ignore($id)
                    ->where('language_id', $request->language_id)
                    ->where('parent_id', '!=', 0)
                    ->whereNull('deleted_at'),
            ],
            'slug' => [
                'required',
                Rule::unique('categories', 'slug')->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'description' => 'required',
        ], [
            'subcategory_name.unique' => __('sub_category_name_exists', [], $languageCode),
            'slug.unique' => __('slug_exists', [], $languageCode),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'success' => false,
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $successMessage = empty($id) ? __('category_create_success', [], $languageCode) : __('category_update_success', [], $languageCode);
        $errorMessage = empty($id) ? __('An error occurred while creating!') : __('An error occurred while updating!');

        try {
            $data = [
                'name' => $request->subcategory_name,
                'parent_id' => $request->category_id,
                'slug' => $request->slug,
                'status' => $request->status,
                'source_type' => $request->source_type,
                'description' => $request->description,
                'featured' => $request->featured,
                'language_id' => $request->language_id,
            ];

            if ($request->hasFile('category_image')) {
                $path = $request->file('category_image')->store('categories', 'public');
                $data['image'] = $path;
            }

            if ($request->hasFile('category_icon')) {
                $path = $request->file('category_icon')->store('categories', 'public');
                $data['icon'] = $path;
            }

            if (empty($id)) {
                Categories::create($data);
            } else {
                Categories::where('id', $id)->update($data);
            }

            Cache::forget('categoriess');

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => $successMessage
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSubcategories(Request $request): JsonResponse
    {
        if ($request->category_id == "" || empty($request->category_id)) {
            $subcategories = [];
            return response()->json($subcategories);
        }
        if (is_array($request->category_id)) {
            $categories = $request->category_id;
            $subcategories = Categories::select('id', 'name')
                ->whereIn('parent_id', $categories)
                ->when($request->language_id, function ($query) use ($request) {
                    $query->where('language_id', $request->language_id);
                })
                ->get();

            return response()->json($subcategories);
        }

        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        $subcategories = Categories::select('id', 'name', 'parent_language_id', 'language_id')
            ->where('parent_id', $request->category_id)
            ->where('language_id', $request->language_id)
            ->get();

        // Check if subcategories exist
        if ($subcategories->isEmpty()) {
            // Find the record with the given category_id
            $category = Categories::select('parent_language_id')
                ->where('id', $request->category_id)
                ->first();

            // If a parent_language_id exists, use it to find subcategories
            if ($category && $category->parent_language_id) {
                $subcategories = Categories::select('id', 'name', 'parent_language_id', 'language_id')
                    ->where('parent_id', $category->parent_language_id)
                    ->where('language_id', $request->language_id)
                    ->get();
            }
        }

        return response()->json($subcategories);
    }

    public function categories(Request $request): JsonResponse
    {
        $categories = Categories::select('id', 'name', 'parent_language_id', 'language_id')
            ->where('parent_id', 0)
            ->where('language_id', $request->language_id)
            ->get();

        return response()->json($categories);
    }

    public function getRegisterSubcategories(Request $request): JsonResponse
    {
        if (is_array($request->category_id)) {
            $categories = $request->category_id;
            $subcategories = Categories::select('id', 'name')
                ->whereIn('parent_id', $categories)
                ->get();

            return response()->json($subcategories);
        }

        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        $language_id = Language::select('id')->where('code', $request->language_code)->first();

        $subcategories = Categories::select('id', 'name', 'parent_language_id', 'language_id')
            ->where('parent_id', $request->category_id)
            ->where('language_id', $language_id->id)
            ->get();

        if ($subcategories->isEmpty()) {
            $category = Categories::select('parent_language_id')
                ->where('id', $request->category_id)
                ->first();

            if ($category && $category->parent_language_id) {
                $subcategories = Categories::select('id', 'name', 'parent_language_id', 'language_id')
                    ->where('parent_id', $category->parent_language_id)
                    ->where('language_id', $language_id->id)
                    ->get();
            }
        }

        return response()->json($subcategories);
    }

    public function getAllLanguages(): JsonResponse
    {
        $languages = Language::where('status', 1)
            ->select('id', 'name', 'direction', 'code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $languages,
        ]);
    }
}
