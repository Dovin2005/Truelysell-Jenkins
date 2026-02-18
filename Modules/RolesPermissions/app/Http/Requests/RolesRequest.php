<?php

namespace Modules\RolesPermissions\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RolesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $createdBy = Auth::id() ?? $this->created_by;
        $id = $this->id ?? '';

        $rules = [
            'role_name' => [
                'required',
                'max:255',
                Rule::unique('roles')->whereNull('deleted_at')->where('created_by', $createdBy)
            ],
        ];

        if (!empty($id)) {
            $rules['role_name'] = [
                'required',
                'max:255',
                Rule::unique('roles')->whereNull('deleted_at')->where('created_by', $createdBy)->ignore($id),
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'role_name.required' => __('Role name is required.'),
            'role_name.unique' => __('Role name already exists.'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'code' => 422,
            'message' => $validator->errors()->toArray(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
