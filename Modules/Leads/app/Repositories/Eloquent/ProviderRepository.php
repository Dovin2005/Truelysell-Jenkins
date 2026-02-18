<?php

namespace Modules\Leads\app\Repositories\Eloquent;

use Modules\Leads\app\Models\ProviderFormsInput;
use Modules\Leads\app\Repositories\Contracts\ProviderInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProviderRepository implements ProviderInterface
{
    public function findProviderFormInput(int $id): ?ProviderFormsInput
    {
        return ProviderFormsInput::find($id);
    }

    public function updateQuote(int $id, array $data): ProviderFormsInput
    {
        $providerFormInput = $this->findProviderFormInput($id);
        
        if (!$providerFormInput) {
            throw new ModelNotFoundException("Provider form input not found with ID: {$id}");
        }

        $providerFormInput->quote = $data['quote'];
        $providerFormInput->start_date = $data['start_date'];
        $providerFormInput->description = $data['description'];
        $providerFormInput->status = '2';
        $providerFormInput->save();

        return $providerFormInput;
    }
}