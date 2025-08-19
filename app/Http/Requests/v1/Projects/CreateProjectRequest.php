<?php

namespace App\Http\Requests\v1\Projects;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
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
            'description'    => 'required|string|min:10',
            'project_type'   => 'required|string|in:open,closed',
            'github_url'     => 'nullable|url',
            'demo_url'       => 'nullable|url',
            'is_sellable'    => 'required|boolean',
            'original_price' => 'nullable|numeric|min:0|required_if:is_sellable,true',
            'selling_price'  => 'nullable|numeric|min:0|lte:original_price|required_if:is_sellable,true',
            'technologies'   => 'nullable|array',
            'technologies.*' => 'string|max:50',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Project title is required',
            'description.required' => 'Project description is required',
            'description.min' => 'Description must be at least 10 characters',
            'project_type.in' => 'Project type must be either open or closed',
            'github_url.url' => 'Please provide a valid GitHub URL',
            'demo_url.url' => 'Please provide a valid demo URL',
            'original_price.required_if' => 'Original price is required for sellable projects',
            'selling_price.required_if' => 'Selling price is required for sellable projects',
            'selling_price.lte' => 'Selling price must be less than or equal to original price',
            'featured_image.image' => 'Featured image must be an image file',
            'featured_image.max' => 'Featured image size must not exceed 2MB',
        ];
    }
}
