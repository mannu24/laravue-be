<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\PortfolioCoupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    use HttpResponse;

    public function index(): JsonResponse
    {
        $coupons = PortfolioCoupon::latest()->paginate(20);
        return $this->success($coupons);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|min:4|max:20|unique:portfolio_coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'sometimes|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'applicable_plans' => 'nullable|array',
            'applicable_plans.*' => 'string|exists:portfolio_plans,slug',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $coupon = PortfolioCoupon::create($validated);

        return $this->success($coupon, 'Coupon created.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $coupon = PortfolioCoupon::findOrFail($id);

        $validated = $request->validate([
            'discount_type' => 'sometimes|in:percentage,fixed',
            'discount_value' => 'sometimes|numeric|min:0.01',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'sometimes|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'applicable_plans' => 'nullable|array',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
        ]);

        $coupon->update($validated);

        return $this->success($coupon->fresh(), 'Coupon updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $coupon = PortfolioCoupon::findOrFail($id);
        $coupon->update(['is_active' => false]);

        return $this->success(null, 'Coupon deactivated.');
    }
}
