<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class OtpVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10,15}$/'
            ],
            'otp' => [
                'required',
                'string',
                'size:6',
                'digits:6'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Phone number must be 10-15 digits',
            'otp.required' => 'OTP code is required',
            'otp.size' => 'OTP code must be 6 digits',
            'otp.digits' => 'OTP code must contain only digits',
        ];
    }
}
