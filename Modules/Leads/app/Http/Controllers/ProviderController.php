<?php

namespace Modules\Leads\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Leads\app\Http\Requests\UpdateQuoteRequest;
use Modules\Leads\app\Repositories\Contracts\ProviderInterface;
use Illuminate\Http\JsonResponse;

class ProviderController extends Controller
{
    protected $providerRepository;

    public function __construct(ProviderInterface $providerRepository)
    {
        $this->providerRepository = $providerRepository;
    }

    public function updateQuote(UpdateQuoteRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $providerFormInput = $this->providerRepository->updateQuote(
                $validatedData['provider_forms_inputs_id'],
                $validatedData
            );

            return response()->json([
                'code' => 200,
                'message' => 'Quote updated successfully!',
                'data' => $providerFormInput
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'message' => 'Provider form input not found.',
                'error' => $e->getMessage()
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred while updating the quote.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}