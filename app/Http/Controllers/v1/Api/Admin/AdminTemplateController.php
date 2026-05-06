<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\PortfolioTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTemplateController extends Controller
{
    use HttpResponse;

    public function index(): JsonResponse
    {
        return $this->success(PortfolioTemplate::orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:portfolio_templates,slug',
            'description' => 'nullable|string|max:1000',
            'preview_image_path' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
            'is_premium' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $template = PortfolioTemplate::create($validated);

        return $this->success($template, 'Template created.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $template = PortfolioTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'preview_image_path' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
            'is_premium' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $template->update($validated);

        return $this->success($template->fresh(), 'Template updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $template = PortfolioTemplate::findOrFail($id);
        $template->update(['is_active' => false]);

        return $this->success(null, 'Template deactivated.');
    }
}
