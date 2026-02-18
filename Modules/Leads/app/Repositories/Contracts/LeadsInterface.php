<?php

namespace Modules\Leads\app\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Leads\app\Models\UserFormInput;
use Modules\Leads\app\Models\ProviderFormsInput;

interface LeadsInterface
{
    public function getFormInputsByCategory(int $categoryId): array;
    
    public function storeUserFormInputs(array $data): array;
    
    public function getUserFormInputs(array $filters): LengthAwarePaginator;
    
    public function getProviderFormInputs(array $filters): LengthAwarePaginator;
    
    public function updateUserFormInputStatus(int $id, int $status): UserFormInput;
    
    public function updateProviderFormInputStatus(int $id, int $status): ProviderFormsInput;
    
    public function getProvidersByCategory(int $categoryId): array;
    
    public function storeProviderFormInputs(array $data): array;
    
    public function getStatusCounts(?int $userId, ?int $providerId): array;
    
    public function storePayment(array $data): array;
}