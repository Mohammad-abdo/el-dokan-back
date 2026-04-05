<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SocialLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => [
                'required',
                'string',
                'in:google,apple'
            ],
            'provider_id' => [
                'required',
                'string',
                'max:255'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255'
            ],
            'name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^[0-9]{10,15}$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'provider.required' => 'Provider is required',
            'provider.in' => 'Provider must be google or apple',
            'provider_id.required' => 'Provider ID is required',
            'email.email' => 'Please provide a valid email address',
            'phone.regex' => 'Phone number must be 10-15 digits',
        ];
    }
}
