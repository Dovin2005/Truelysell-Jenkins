<?php

namespace Modules\Leads\app\Repositories\Eloquent;

use Modules\Leads\app\Repositories\Contracts\LeadsInterface;
use Modules\Leads\app\Models\UserFormInput;
use Modules\Leads\app\Models\ProviderFormsInput;
use Modules\Leads\app\Models\Payments;
use Modules\Categories\app\Models\Categories;
use Modules\Categories\app\Models\CategoryFormInput;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeadsRepository implements LeadsInterface
{
    public function getFormInputsByCategory(int $categoryId): array
    {
        $category = Categories::find($categoryId);
        
        if (!$category) {
            return [
                'success' => false,
                'message' => 'Category not found',
                'data' => null
            ];
        }

        $subCategories = Categories::where('parent_id', $categoryId)
            ->select('id', 'name')
            ->get();

        $formInputs = CategoryFormInput::where('categories_id', $categoryId)
            ->select('id', 'categories_id', 'type', 'label', 'placeholder', 'name', 'is_required', 'options', 'file_size', 'order_no', 'other_option', 'language_id')
            ->orderBy('order_no', 'asc')
            ->get()
            ->map(function ($formInput) {
                if ($formInput->options) {
                    $formInput->options = json_decode($formInput->options, true);
                }

                return [
                    'id' => $formInput->id,
                    'categories_id' => $formInput->categories_id,
                    'type' => $formInput->type,
                    'title' => $formInput->label,
                    'placeholder' => $formInput->placeholder,
                    'description' => $formInput->name,
                    'is_required' => $formInput->is_required,
                    'options' => $formInput->options,
                    'file_size' => $formInput->file_size,
                    'order_no' => $formInput->order_no,
                    'other_option' => $formInput->other_option,
                    'language_id' => $formInput->language_id,
                ];
            });

        return [
            'success' => true,
            'message' => 'Form inputs and subcategories retrieved successfully',
            'data' => [
                'form_inputs' => $formInputs,
                'sub_categories' => $subCategories
            ]
        ];
    }

    public function storeUserFormInputs(array $data): array
    {
        DB::beginTransaction();
        try {
            $formInputs = [];
            foreach ($data['form_inputs'] as $input) {
                $inputValue = $input['value'] ?? '';

                if (is_array($inputValue)) {
                    $inputValue = implode(', ', $inputValue);
                }

                if (!empty($inputValue)) {
                    if ($this->isValidBase64Image($inputValue)) {
                        $filePath = $this->storeBase64Image($inputValue);
                        $formInputs[] = [
                            'id' => $input['id'],
                            'value' => $filePath,
                        ];
                    } elseif ($this->isValidBase64Pdf($inputValue)) {
                        $filePath = $this->storeBase64Pdf($inputValue);
                        $formInputs[] = [
                            'id' => $input['id'],
                            'value' => $filePath,
                        ];
                    } elseif (!empty($inputValue) && $this->isValidBase64Document($inputValue)) {
                        $filePath = $this->storeBase64Document($inputValue);
                        $formInputs[] = [
                            'id' => $input['id'],
                            'value' => $filePath,
                        ];
                    } else {
                        $formInputs[] = [
                            'id' => $input['id'],
                            'value' => $inputValue,
                        ];
                    }
                }
            }

            $userFormInputId = DB::table('user_form_inputs')->insertGetId([
                'user_id' => $data['user_id'],
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'form_inputs' => json_encode($formInputs),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Form inputs stored successfully',
                'data' => [
                    'id' => $userFormInputId,
                    'category_id' => $data['category_id'],
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error storing form inputs',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getUserFormInputs(array $filters): LengthAwarePaginator
    {
        $query = UserFormInput::with([
            'user',
            'category',
            'subCategory' => function ($query) {
                $query->whereNotNull('parent_id');
            },
            'providerFormsInputs.provider',
        ])
        ->select('id', 'user_id', 'category_id', 'sub_category_id', 'status', 'form_inputs', 'created_at')
        ->whereNull('deleted_at')
        ->whereHas('providerFormsInputs');

        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['provider_id'])) {
            $query->whereHas('providerFormsInputs', function ($q) use ($filters) {
                $q->where('provider_id', $filters['provider_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('form_inputs', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('category', function ($categoryQuery) use ($filters) {
                        $categoryQuery->where('name', 'like', '%' . $filters['search'] . '%');
                    })
                    ->orWhereHas('user', function ($userQuery) use ($filters) {
                        $userQuery->where('name', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        return $query->orderBy($filters['sort_by'] ?? 'id', $filters['order_by'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getProviderFormInputs(array $filters): LengthAwarePaginator
    {
        $query = ProviderFormsInput::with([
            'userFormInput.user',
            'userFormInput.category',
            'userFormInput.subCategory' => function ($query) {
                $query->whereNotNull('parent_id');
            },
        ]);

        if (!empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['user_form_inputs_id'])) {
            $query->where('user_form_inputs_id', $filters['user_form_inputs_id']);
        }

        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        return $query->orderBy($filters['sort_by'] ?? 'id', $filters['order_by'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function updateUserFormInputStatus(int $id, int $status): UserFormInput
    {
        $userFormInput = UserFormInput::findOrFail($id);
        $userFormInput->status = $status;
        $userFormInput->save();
        return $userFormInput;
    }

    public function updateProviderFormInputStatus(int $id, int $status): ProviderFormsInput
    {
        $providerFormInput = ProviderFormsInput::findOrFail($id);
        $providerFormInput->status = $status;
        $providerFormInput->save();

        $totalRecords = ProviderFormsInput::where('user_form_inputs_id', $providerFormInput->user_form_inputs_id)->count();
        $userStatuses = ProviderFormsInput::where('user_form_inputs_id', $providerFormInput->user_form_inputs_id)->pluck('status');

        $statusSummary = [
            'status_2_count' => $userStatuses->filter(fn($status) => $status == 2)->count(),
            'status_3_count' => $userStatuses->filter(fn($status) => $status == 3)->count(),
            'total' => $totalRecords,
        ];

        if (($statusSummary['total']-1 === $statusSummary['status_3_count']) && $status == 3) {
            $record = UserFormInput::findOrFail($providerFormInput->user_form_inputs_id);
            $record->status = $status;
            $record->save();
        }

        return $providerFormInput;
    }

    public function getProvidersByCategory(int $categoryId): array
    {
        $providers = User::with('category')
            ->where('user_type', 'provider')
            ->where('category_id', $categoryId)
            ->get();

        return [
            'success' => true,
            'message' => 'Providers retrieved successfully',
            'data' => $providers
        ];
    }

    public function storeProviderFormInputs(array $data): array
    {
        try {
            $inputs = [];
            foreach ($data['provider_id'] as $providerId) {
                $inputs[] = ProviderFormsInput::create([
                    'user_form_inputs_id' => $data['user_form_inputs_id'],
                    'provider_id' => $providerId,
                    'status' => 1,
                ]);
            }

            return [
                'success' => true,
                'message' => 'Provider form inputs saved successfully',
                'data' => $inputs
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error saving provider form inputs',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getStatusCounts(?int $userId, ?int $providerId): array
    {
        $baseQuery = UserFormInput::query()
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->when($providerId, function ($q) use ($providerId) {
                $q->whereHas('providerFormsInputs', function ($q) use ($providerId) {
                    $q->where('provider_id', $providerId);
                });
            })
            ->whereHas('providerFormsInputs');

        return [
            'all' => $baseQuery->count(),
            'new' => $baseQuery->clone()->where('status', 1)->count(),
            'accept' => $baseQuery->clone()->where('status', 2)->count(),
            'reject' => $baseQuery->clone()->where('status', 3)->count(),
        ];
    }

    public function storePayment(array $data): array
    {
        try {
            $paymentId = Payments::insertGetId([
                'user_id' => $data['user_id'],
                'user_type' => $data['user_type'],
                'amount' => $data['amount'],
                'reference_id' => $data['reference_id'],
                'payment_date' => $data['payment_date'],
                'status' => $data['status'],
                'created_by' => $data['created_by'],
                'created_at' => $data['created_at'],
            ]);

            return [
                'success' => true,
                'message' => 'Payment stored successfully',
                'data' => $paymentId
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error storing payment',
                'error' => $e->getMessage()
            ];
        }
    }

    private function isValidBase64Image($base64String): bool
    {
        if (is_string($base64String) && !empty($base64String)) {
            $imageData = base64_decode($base64String, true);
            if ($imageData === false) {
                return false;
            }

            $imageType = finfo_buffer(finfo_open(), $imageData, FILEINFO_MIME_TYPE);
            return in_array($imageType, ['image/jpeg', 'image/png', 'image/gif']);
        }

        return false;
    }

    private function isValidBase64Pdf($base64String): bool
    {
        if (is_string($base64String) && !empty($base64String)) {
            $pdfData = base64_decode($base64String, true);
            if ($pdfData === false) {
                return false;
            }

            return strpos(bin2hex($pdfData), '25504446') === 0;
        }

        return false;
    }

    private function storeBase64Image($base64String): string
    {
        $fileName = 'image_' . Str::random(10) . '.png';
        $imageData = base64_decode($base64String);
        $filePath = 'uploads/leads/' . $fileName;
        Storage::disk('public')->put($filePath, $imageData);
        return $filePath;
    }

    private function storeBase64Pdf($base64String): string
    {
        $fileName = 'document_' . Str::random(10) . '.pdf';
        $pdfData = base64_decode($base64String);
        $filePath = 'uploads/leads/' . $fileName;
        Storage::disk('public')->put($filePath, $pdfData);
        return $filePath;
    }

    private function isValidBase64Document($base64String): bool
    {
        if (is_string($base64String) && !empty($base64String)) {
            $documentData = base64_decode($base64String, true);
            if ($documentData === false) {
                return false;
            }

            $mimeType = finfo_buffer(finfo_open(), $documentData, FILEINFO_MIME_TYPE);

            return in_array($mimeType, [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
        }

        return false;
    }

    private function storeBase64Document($base64String): string
    {
        $mimeTypes = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'text/plain' => 'txt'
        ];

        $documentData = base64_decode($base64String);
        $mimeType = finfo_buffer(finfo_open(), $documentData, FILEINFO_MIME_TYPE);

        $extension = $mimeTypes[$mimeType] ?? 'pdf';
        $fileName = 'document_' . Str::random(10) . '.' . $extension;
        $filePath = 'uploads/leads/' . $fileName;

        Storage::disk('public')->put($filePath, $documentData);

        return $filePath;
    }
}