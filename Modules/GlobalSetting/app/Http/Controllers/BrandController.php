<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Modules\GlobalSetting\app\Models\ProductBrand;
use Modules\GlobalSetting\app\Http\Requests\StoreBrandRequest;
use Modules\GlobalSetting\app\Http\Requests\UpdateBrandRequest;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        return view('globalsetting::brands.index');
    }

    public function list(Request $request): JsonResponse
    {
        $query = ProductBrand::query();

        // Filtering
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Sorting
        $columns = ['id', 'title', 'image', 'created_at'];
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDir = $request->input('order.0.dir', 'asc');
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $perPage = $request->input('length', 10);
        $page = floor($request->input('start', 0) / $perPage) + 1;

        $brands = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $brands->map(function ($brand) {
            return [
                'id' => $brand->id,
                'title' => $brand->title,
                'image_url' => $brand->image ? asset('storage/' . $brand->image) : '',
                'status' => $brand->status,
                'created_at' => $brand->created_at->format('Y-m-d'),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $brands->total(),
            'recordsFiltered' => $brands->total(),
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 422, 'message' => $validator->errors()]);
        }

        $data = $request->only('title');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        ProductBrand::create($data);

        return response()->json(['code' => 200, 'message' => 'Brand created successfully.']);
    }

    public function edit($id): JsonResponse
    {
        try {
            $brand = ProductBrand::findOrFail($id);

            return response()->json([
                'code' => 200,
                'data' => $brand
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 404,
                'message' => 'Brand not found'
            ], 404);
        }
    }


    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id'    => 'required|exists:product_brands,id',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 422, 'message' => $validator->errors()]);
        }

        $brand = ProductBrand::find($request->id);

        $data = $request->only('title');

        if ($request->hasFile('image')) {
            if ($brand->image) {
                Storage::disk('public')->delete($brand->image);
            }
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand->update($data);

        return response()->json(['code' => 200, 'message' => 'Brand updated successfully.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:product_brands,id']);

        $brand = ProductBrand::find($request->id);

        if ($brand->image) {
            Storage::disk('public')->delete($brand->image);
        }

        $brand->delete();

        return response()->json(['code' => 200, 'message' => 'Brand deleted successfully.']);
    }
}
