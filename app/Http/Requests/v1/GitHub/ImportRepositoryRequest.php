<?php

namespace App\Http\Requests\v1\GitHub;

use Illuminate\Foundation\Http\FormRequest;

class ImportRepositoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'owner' => ['required', 'string', 'max:255'],
            'repo' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'long_description' => ['nullable', 'string'],
            'demo_url' => ['nullable', 'url', 'max:500'],
            'project_type' => ['nullable', 'in:open,closed'],
            'is_sellable' => ['nullable', 'boolean'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:500'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'owner.required' => 'Repository owner is required',
            'repo.required' => 'Repository name is required',
            'demo_url.url' => 'Demo URL must be a valid URL',
            'project_type.in' => 'Project type must be either open or closed',
        ];
    }
}
