<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;

interface InvoiceTemplateInterface
{
    public function getAllTemplates(array $filters = []): Collection;
    public function getTemplateById(int $id): array;
    public function createTemplate(array $data): array;
    public function updateTemplate(int $id, array $data): array;
    public function deleteTemplate(int $id): bool;
    public function setDefaultTemplate(int $id): bool;
    public function searchTemplates(string $searchTerm): Collection;
}