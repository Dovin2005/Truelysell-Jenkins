<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\GlobalSetting\app\Models\AvailableCurrency;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\GlobalSetting\app\Http\Requests\CurrencyRequest;
use Modules\GlobalSetting\app\Repositories\Contracts\CurrencyInterface;

class CurrencyController extends Controller
{
    protected $currencyRepository;

    public function __construct(CurrencyInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'order_by' => 'nullable|in:asc,desc',
                'count_per_page' => 'nullable|integer|min:1',
                'sort_by' => 'nullable|string|in:id,name,code,status',
                'search' => 'string|nullable',
            ]);

            $currencies = $this->currencyRepository->index($validatedData);

            return response()->json([
                'code' => '200',
                'message' => 'Currencies retrieved successfully.',
                'data' => $currencies->items(),
                'meta' => [
                    'current_page' => $currencies->currentPage(),
                    'last_page' => $currencies->lastPage(),
                    'per_page' => $currencies->perPage(),
                    'total' => $currencies->total(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => 'An error occurred while retrieving currencies.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function currencySettings(): View
    {
        $availableCurrencies = AvailableCurrency::get();
        return view('globalsetting::setting.currency-settings', compact('availableCurrencies'));
    }

    public function store(CurrencyRequest $request): JsonResponse
    {
        try {
            $availableCurrency = AvailableCurrency::find($request->available_currency_id);
            
            $data = [
                'name' => $availableCurrency->currency_name,
                'code' => $availableCurrency->code,
                'symbol' => $availableCurrency->symbol,
                'status' => 1,
                'is_default' => $request->is_default ?? false,
            ];

            $currency = $this->currencyRepository->store($data);

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('currency_create_success'),
                'data' => $currency
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'success' => false,
                'message' => 'An error occurred while creating currency.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function setDefault(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|exists:currencies,id',
            ]);

            $this->currencyRepository->setDefault($request->id);

            return response()->json([
                'code' => '200',
                'success' => true,
                'message' => 'Default currency set successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'success' => false,
                'message' => 'Failed to set default currency.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function changeStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:currencies,id',
                'status' => 'required|boolean',
            ]);

            $currency = $this->currencyRepository->changeStatus($request->id, $request->status);

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('Status Updated Successfully'),
                'data' => $currency
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Failed to update currency status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $currency = $this->currencyRepository->destroy($request->id);

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Currency deleted successfully.',
                'data' => $currency
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkUnique(Request $request): JsonResponse
    {
        $request->validate([
            'field' => 'required|string',
            'value' => 'required|string',
        ]);

        $exists = $this->currencyRepository->checkUnique($request->field, $request->value);

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This value is already taken.' : 'This value is available.'
        ]);
    }
}