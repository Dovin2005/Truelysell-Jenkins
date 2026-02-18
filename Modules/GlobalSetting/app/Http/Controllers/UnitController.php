<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use Modules\GlobalSetting\app\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\GlobalSetting\app\Http\Requests\StoreUnitRequest;
use Modules\GlobalSetting\app\Http\Requests\UpdateUnitRequest;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        return view('globalsetting::units.index');
    }

    public function list(Request $request): JsonResponse
    {
        $query = Unit::with('baseUnit');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
            });
        }

        $units = $query->latest()->paginate(10); // or ->get() if you don't want pagination

        // Format data (similar to what DataTables does)
        $units->getCollection()->transform(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'code' => $unit->code,
                'status' => $unit->status,
                'created_at' => $unit->created_at->format('Y-m-d'),
            ];
        });

        return response()->json([
            'code' => 200,
            'data' => $units,
        ]);
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        $data = $request->only(['name', 'code', 'status']);

        Unit::create($data);

        return response()->json(['code' => 200, 'message' => 'Unit created successfully.']);
    }


    public function update(UpdateUnitRequest $request): JsonResponse
    {
        $unit = Unit::findOrFail($request->id);

        $unit->update([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'status' => $request->input('status'),
        ]);

        return response()->json([
            'code' => 200,
            'message' => 'Unit updated successfully.',
            'data' => $unit
        ]);
    }

    public function edit($id): JsonResponse
    {
        $unit = Unit::with('baseUnit')->findOrFail($id);
        return response()->json(['code' => 200, 'data' => $unit]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:units,id'
        ]);

        $unit = Unit::findOrFail($request->id);

        if ($unit->derivedUnits()->exists()) {
            return response()->json([
                'code' => 403,
                'message' => 'Cannot delete unit because it is used as a base unit by other units.',
            ]);
        }

        $unit->delete();

        return response()->json(['code' => 200, 'message' => 'Unit deleted successfully.']);
    }
}
