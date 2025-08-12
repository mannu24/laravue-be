<?php

namespace App\Http\Requests\v1\User\Profile;

use Illuminate\Foundation\Http\FormRequest;

class SocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'social_link_type_id' => 'required|exists:social_link_types,id',
            'username' => 'required|string|max:255',
            'position' => 'nullable|integer',
            'is_visible' => 'boolean',
        ];
    }
}
