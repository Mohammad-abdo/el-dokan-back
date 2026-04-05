<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'unique:users,username',
                'regex:/^[a-zA-Z0-9_]+$/'
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10,15}$/',
                'unique:users,phone'
            ],
            'email' => [
                'nullable',
                'email',
                'unique:users,email',
                'max:255'
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:100'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username is required',
            'username.unique' => 'This username is already taken',
            'username.regex' => 'Username can only contain letters, numbers, and underscores',
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Phone number must be 10-15 digits',
            'phone.unique' => 'This phone number is already registered',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'password.min' => 'Password must be at least 8 characters',
        ];
    }
}
