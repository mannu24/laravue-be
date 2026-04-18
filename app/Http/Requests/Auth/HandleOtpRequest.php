<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class HandleOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'otp' => 'nullable|string|size:6|regex:/^\d{6}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'otp.size' => 'OTP must be exactly 6 digits.',
            'otp.regex' => 'OTP must contain only numbers.',
        ];
    }
}
