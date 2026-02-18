<?php

namespace Modules\Categories\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Categories\app\Models\CategoryFormInput;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Modules\Categories\app\Http\Requests\FormInputRequest;
use Illuminate\Http\JsonResponse;
use Exception;


class FormInputController extends Controller
{
    public function formInputStore(FormInputRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $orderNo = null;
            if (!$request->has('id')) {
                $maxOrderNo = CategoryFormInput::where('categories_id', $validatedData['category_id'])->max('order_no');
                $orderNo = $maxOrderNo ? $maxOrderNo + 1 : 1;
            }

            $dataToSave = [
                'categories_id' => $validatedData['category_id'],
                'type' => $validatedData['input_type'],
                'label' => $validatedData['form_label'],
                'placeholder' => $validatedData['form_placeholder'] ?? null,
                'name' => $validatedData['form_description'],
                'is_required' => $validatedData['is_required'] ?? false,
                'options' => isset($validatedData['options']) ? json_encode($validatedData['options']) : null,
                'file_size' => $validatedData['file_size'] ?? null,
                'other_option' => $validatedData['has_other_option'] ?? 0,
            ];

            if (!$request->has('id')) {
                $dataToSave['order_no'] = $orderNo;
            }

            if ($request->has('id')) {
                $formInput = CategoryFormInput::findOrFail($validatedData['id']);
                $formInput->update($dataToSave);
            } else {
                $formInput = CategoryFormInput::create($dataToSave);
            }

            return response()->json([
                'code' => '200',
                'success' => true,
                'message' => $request->has('id') ? __('Form input updated successfully.') : __('Form input created successfully.'),
                'data' => $formInput
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'code' => '500',
                'success' => false,
                'message' => __('An error occurred while processing your request.'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function formInputList(Request $request): JsonResponse
    {
        try {
            $categoryId = $request->input('category_id');

            $validatedData = $request->validate([
                'category_id' => 'required|exists:categories,id',
            ]);

            $formInputs = CategoryFormInput::where('categories_id', $categoryId)
                ->select('id', 'categories_id', 'type', 'label', 'placeholder', 'name', 'is_required', 'options', 'file_size', 'order_no', 'other_option', 'language_id')
                ->orderBy('order_no', 'asc') // Sort by order_no in ascending order
                ->get();

            $formInputs = $formInputs->map(function ($formInput) {
                if ($formInput->options) {
                    $formInput->options = json_decode($formInput->options, true);
                }

                return $formInput;
            });

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('Form inputs retrieved successfully.'),
                'data' => $formInputs
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => __('An error occurred while processing your request.'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function formInputDelete(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'id' => 'required|integer|exists:category_form_inputs,id',
        ]);

        $id = $validatedData['id'];

        try {
            $formInput = CategoryFormInput::findOrFail($id);

            $formInput->delete();

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('Form Input deleted successfully.'),
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => __('Failed to delete Form Input.'),
            ], 500);
        }
    }

    public function formInputupdateOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order' => 'required|array',
            'order.*.id' => 'required|exists:category_form_inputs,id',
            'order.*.order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'message' => __('Invalid data.'),
                'errors' => $validator->errors(),
            ], 400);
        }

        foreach ($request->order as $item) {
            $formInput = CategoryFormInput::find($item['id']);
            if ($formInput instanceof CategoryFormInput) {
                $formInput->order_no = $item['order'];
                $formInput->save();
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'message' => __('Order updated successfully.'),
        ], 200);
    }


}
