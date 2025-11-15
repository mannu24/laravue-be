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
            // SEO
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'excerpt' => 'nullable|string|max:500',
            // Content
            'short_description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string',
            'features' => 'nullable|array',
            'requirements' => 'nullable|string',
            'installation_guide' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'changelog' => 'nullable|string',
            'current_version' => 'nullable|string|max:50',
            'license_type' => 'nullable|string|max:100',
            'license_url' => 'nullable|url',
            'documentation_url' => 'nullable|url',
            'support_url' => 'nullable|url',
            // Categorization
            'category_id' => 'nullable|exists:project_categories,id',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'estimated_build_time' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            // Maintenance
            'language' => 'nullable|string|max:10',
            'update_frequency' => 'nullable|string|max:50',
            'is_maintained' => 'nullable|boolean',
            'maintenance_status' => 'nullable|string|max:50',
            'deprecation_notice' => 'nullable|string',
            'migration_guide_url' => 'nullable|url',
            // Commerce
            'currency' => 'nullable|string|size:3',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after_or_equal:discount_start_date',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_digital' => 'nullable|boolean',
            'delivery_method' => 'nullable|string|max:50',
            'affiliate_enabled' => 'nullable|boolean',
            'affiliate_commission' => 'nullable|numeric|min:0|max:100',
            // Status
            'status' => 'nullable|in:draft,pending,published',
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
