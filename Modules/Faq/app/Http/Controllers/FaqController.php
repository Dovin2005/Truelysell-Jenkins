<?php

namespace Modules\Faq\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Faq\app\Http\Requests\FaqRequest;
use Modules\Faq\app\Http\Requests\FaqUpdateRequest;
use Modules\Faq\app\Repositories\Contracts\FaqRepositoryInterface;
use Modules\GlobalSetting\app\Models\Language;

class FaqController extends Controller
{
    protected $faqRepository;

    public function __construct(FaqRepositoryInterface $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $faqs = $this->faqRepository->getAll($request);
        if (empty($faqs)) {
            return response()->json(['code' => 200, 'message' => 'Faq Not Found!', 'data' => []], 200);
        }
        return response()->json(['code' => 200, 'message' => __('Faq details retrieved successfully.'), 'data' => $faqs], 200);
    }

    public function store(FaqRequest $request): JsonResponse
    {
        $faq = $this->faqRepository->store($request);

        if (isset($faq['exists'])) {
            return response()->json(['code' => 422, 'message' => 'Faq Question already exists!', 'data' => []], 200);
        }

        return response()->json(['code' => 200, 'message' => __('truelysell_validation.success_response.faq_setting_success'), 'data' => []], 200);
    }

    public function update(FaqUpdateRequest $request): JsonResponse
    {
        $updated = $this->faqRepository->update($request);

        $langCode = Language::where('id', $request->language_id)->value('code') ?? 'en';

        if (!$updated) {
            return response()->json(['message' => __('faq_update_error', [], $langCode)], 500);
        }

        return response()->json(['code' => 200, 'message' => __('faq_update_success', [], $langCode), 'data' => []], 200);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|integer|exists:faqs,id']);
        $this->faqRepository->delete($request);

        $langCode = Language::where('id', $request->language_id)->value('code') ?? 'en';
        return response()->json(['code' => 200, 'success' => true, 'message' => __('faq_delete_success', [], $langCode)], 200);
    }

    public function getFaq(Request $request): JsonResponse
    {
        try {
            $faq = $this->faqRepository->getById($request);
            return response()->json([
                'code' => 200,
                'message' => __('Faq details retrieved successfully.'),
                'data' => $faq,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('An error occurred while retrieving faq.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

