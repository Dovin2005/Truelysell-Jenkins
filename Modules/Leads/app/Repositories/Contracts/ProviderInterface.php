<?php

namespace Modules\Leads\app\Repositories\Contracts;

use Modules\Leads\app\Models\ProviderFormsInput;

interface ProviderInterface
{
    public function findProviderFormInput(int $id): ?ProviderFormsInput;
    public function updateQuote(int $id, array $data): ProviderFormsInput;
}