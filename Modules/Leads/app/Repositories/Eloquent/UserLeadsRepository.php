<?php

namespace Modules\Leads\app\Repositories\Eloquent;

use Modules\Leads\app\Models\ProviderFormsInput;
use Modules\Leads\app\Models\UserFormInput;
use Modules\Leads\app\Repositories\Contracts\UserLeadsInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserLeadsRepository implements UserLeadsInterface
{
    public function updateUserStatus(array $data): array
    {
        $userFormInput = $this->getUserFormInput($data['id']);
        $userFormInput->status = $data['status'];
        $userFormInput->save();

        return $userFormInput->toArray();
    }

    public function getStatusSummary(int $userFormInputId): array
    {
        $totalRecords = ProviderFormsInput::where('user_form_inputs_id', $userFormInputId)->count();
        $userStatuses = ProviderFormsInput::where('user_form_inputs_id', $userFormInputId)->pluck('user_status');

        return [
            'status_2_count' => $userStatuses->filter(fn($status) => $status == 2)->count(),
            'status_3_count' => $userStatuses->filter(fn($status) => $status == 3)->count(),
            'total' => $totalRecords
        ];
    }

    public function updateProviderStatus(int $providerFormInputId, int $status)
    {
        $providerRecord = $this->getProviderFormInput($providerFormInputId);
        $providerRecord->user_status = $status;
        $providerRecord->save();

        return $providerRecord;
    }

    public function getUserFormInput(int $id)
    {
        $record = UserFormInput::find($id);
        if (!$record) {
            throw new ModelNotFoundException("User form input not found with ID: {$id}");
        }
        return $record;
    }

    public function getProviderFormInput(int $id)
    {
        $record = ProviderFormsInput::find($id);
        if (!$record) {
            throw new ModelNotFoundException("Provider form input not found with ID: {$id}");
        }
        return $record;
    }
}