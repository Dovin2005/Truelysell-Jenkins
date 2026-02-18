<?php

namespace Modules\Leads\app\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Leads\app\Models\ProviderFormsInput;

class ProviderLeadsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_form_inputs_id' => 'required|exists:user_form_inputs,id',
            'provider_id' => 'required|array|min:1',
            'provider_id.*' => 'exists:users,id',
            'user_id' => 'required|exists:users,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('provider_id')) {
                $existingProviders = ProviderFormsInput::where('user_form_inputs_id', $this->input('user_form_inputs_id'))
                    ->whereIn('provider_id', $this->input('provider_id'))
                    ->get();

                if ($existingProviders->isNotEmpty()) {
                    foreach ($existingProviders as $provider) {
                        $providerName = User::find($provider->provider_id)->name ?? 'Unknown Provider';
                        $validator->errors()->add('provider_id', "Request already sent to {$providerName}.");
                    }
                }
            }
        });
    }
}