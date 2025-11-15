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
            'title'          => 'sometimes|required|string|max:255',
            'description'    => 'nullable|string|min:10',
            'project_type'   => 'sometimes|required|string|in:open,closed',
            'github_url'     => 'nullable|url',
            'demo_url'       => 'nullable|url',
            'is_sellable'    => 'sometimes|required|boolean',
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
}
