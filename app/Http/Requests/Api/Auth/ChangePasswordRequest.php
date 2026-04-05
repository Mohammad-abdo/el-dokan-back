<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string'
            ],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'max:100',
                'different:current_password',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'new_password_confirmation' => [
                'required',
                'same:new_password'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required',
            'new_password.required' => 'New password is required',
            'new_password.min' => 'New password must be at least 8 characters',
            'new_password.regex' => 'Password must contain at least: one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)',
            'new_password.different' => 'New password must be different from current password',
            'new_password_confirmation.required' => 'Password confirmation is required',
            'new_password_confirmation.same' => 'Password confirmation does not match',
        ];
    }
}
