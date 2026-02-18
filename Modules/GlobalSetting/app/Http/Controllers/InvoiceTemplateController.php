<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\GlobalSetting\app\Http\Requests\StoreInvoiceTemplateRequest;
use Modules\GlobalSetting\app\Http\Requests\SetDefaultInvoiceTemplateRequest;
use Modules\GlobalSetting\app\Repositories\Contracts\InvoiceTemplateInterface;

class InvoiceTemplateController extends Controller
{
    protected $invoiceTemplateRepository;

    public function __construct(InvoiceTemplateInterface $invoiceTemplateRepository)
    {
        $this->invoiceTemplateRepository = $invoiceTemplateRepository;
    }

    public function store(StoreInvoiceTemplateRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            if ($request->filled('template_id')) {
                $template = $this->invoiceTemplateRepository->updateTemplate($data['template_id'], [
                    'invoice_title' => $data['invoice_title'],
                    'invoice_type' => $data['invoice_type'],
                    'template_content' => $data['template_content'],
                ]);

                return response()->json([
                    'code' => 200,
                    'message' => 'Invoice template updated successfully.',
                    'data' => $template,
                ]);
            } else {
                $template = $this->invoiceTemplateRepository->createTemplate([
                    'invoice_title' => $data['invoice_title'],
                    'invoice_type' => $data['invoice_type'],
                    'template_content' => $data['template_content'],
                ]);

                return response()->json([
                    'code' => 200,
                    'message' => 'Invoice template created successfully.',
                    'data' => $template,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred while processing the request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $id = $request->input('id');
            $search = $request->input('search');

            if ($id) {
                $template = $this->invoiceTemplateRepository->getTemplateById($id);
                return response()->json([
                    'code' => 200,
                    'message' => 'Invoice template retrieved successfully.',
                    'data' => $template,
                ]);
            }

            $filters = [
                'order_by' => $request->input('order_by', 'desc'),
                'sort_by' => $request->input('sort_by', 'id'),
            ];

            $templates = $search 
                ? $this->invoiceTemplateRepository->searchTemplates($search)
                : $this->invoiceTemplateRepository->getAllTemplates($filters);

            return response()->json([
                'code' => 200,
                'message' => 'Invoice templates retrieved successfully.',
                'data' => $templates,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred while retrieving invoice templates.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|exists:invoice_templates,id',
            ]);

            $this->invoiceTemplateRepository->deleteTemplate($request->id);

            return response()->json([
                'code' => 200,
                'message' => 'Invoice template deleted successfully.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'Validation errors',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred while deleting the template.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function setDefault(SetDefaultInvoiceTemplateRequest $request): JsonResponse
    {
        try {
            $this->invoiceTemplateRepository->setDefaultTemplate($request->id);

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Default Invoice Template set successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Failed to set default Invoice Template.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}