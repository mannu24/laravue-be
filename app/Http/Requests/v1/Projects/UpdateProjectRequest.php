<?php

namespace App\Http\Requests\v1\Projects;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'project_type'   => 'required|string|in:open,closed',
            'github_url'     => 'nullable|url',
            'demo_url'       => 'nullable|url',
            'is_sellable'    => 'required|boolean',
            'original_price' => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0|lte:original_price',
        ];
    }
}
