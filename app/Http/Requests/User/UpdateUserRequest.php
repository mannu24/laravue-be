<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => 'required|max:255|min:3|alpha:ascii',
            'username' => 'required|max:255|min:5|alpha_dash|unique:users,username,'.auth()->guard('api')->id(),
            'profile_photo' => 'nullable|mimes:jpeg,jpg,png|max:2048'
        ];
    }
}
