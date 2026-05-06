<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\PortfolioPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPlanController extends Controller
{
    use HttpResponse;

    public function index(): JsonResponse
    {
        return $this->success(PortfolioPlan::orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:portfolio_plans,slug',
            'duration_months' => 'required|integer|min:1|max:24',
            'price' => 'required|numeric|min:0',
            'max_projects' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'allows_custom_domain' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $plan = PortfolioPlan::create($validated);

        return $this->success($plan, 'Plan created.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $plan = PortfolioPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'duration_months' => 'sometimes|integer|min:1|max:24',
            'price' => 'sometimes|numeric|min:0',
            'max_projects' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'allows_custom_domain' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $plan->update($validated);

        return $this->success($plan->fresh(), 'Plan updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $plan = PortfolioPlan::findOrFail($id);
        $plan->update(['is_active' => false]);

        return $this->success(null, 'Plan deactivated.');
    }
}
