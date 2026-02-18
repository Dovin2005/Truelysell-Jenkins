<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|alpha|max:100',
            'email' => 'required|email',
            'phone_number' => 'required|numeric|digits_between:10,12',
            'message' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('Name is required.'),
            'name.alpha' => __('Name must contain only alphabets.'),
            'name.max' => __('Name cannot be exceed 100 characters.'),
            'email.required' => __('Email is required.'),
            'email.email' => __('Please enter a valid email.'),
            'phone_number.required' => __('phone_number_required'),
            'phone_number.numeric' => __('Phone number must contain only numbers.'),
            'phone_number.digits_between' => __('Phone number must be between 10 and 12 digits.'),
            'message.required' => __('Message is required.')
        ];
    }
}