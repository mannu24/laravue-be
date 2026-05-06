@extends('admin.layout')
@section('title', 'Coupons')

@section('content')
<h1 class="text-2xl font-bold text-white mb-6">Coupon Codes</h1>

<!-- Create Coupon -->
<div class="bg-gray-900 border border-gray-800 rounded-xl p-5 mb-6">
    <h2 class="font-semibold text-white mb-4">Create New Coupon</h2>
    <form action="{{ route('admin.coupons.store') }}" method="POST" class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @csrf
        <div>
            <label class="text-xs text-gray-500 block mb-1">Code</label>
            <input type="text" name="code" required placeholder="LAUNCH50" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm uppercase focus:outline-none focus:border-vue">
        </div>
        <div>
            <label class="text-xs text-gray-500 block mb-1">Type</label>
            <select name="discount_type" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue">
                <option value="percentage">Percentage</option>
                <option value="fixed">Fixed (₹)</option>
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-500 block mb-1">Value</label>
            <input type="number" step="0.01" name="discount_value" required placeholder="50" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue">
        </div>
        <div>
            <label class="text-xs text-gray-500 block mb-1">Max Uses</label>
            <input type="number" name="max_uses" placeholder="∞" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue">
        </div>
        <div>
            <label class="text-xs text-gray-500 block mb-1">Per User</label>
            <input type="number" name="max_uses_per_user" value="1" required class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue">
        </div>
        <div>
            <label class="text-xs text-gray-500 block mb-1">Expires At</label>
            <input type="date" name="expires_at" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue">
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox" name="is_active" checked class="rounded bg-gray-800 border-gray-600">
                Active
            </label>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full py-2 bg-vue text-white rounded-lg text-sm font-medium hover:bg-vue/90 transition-colors">
                Create Coupon
            </button>
        </div>
    </form>
    @if($errors->any())
        <div class="mt-3 text-sm text-red-400">{{ $errors->first() }}</div>
    @endif
</div>

<!-- Coupons List -->
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 border-b border-gray-800">
                    <th class="text-left px-5 py-3 font-medium">Code</th>
                    <th class="text-left px-5 py-3 font-medium">Discount</th>
                    <th class="text-left px-5 py-3 font-medium">Used</th>
                    <th class="text-left px-5 py-3 font-medium">Expires</th>
                    <th class="text-left px-5 py-3 font-medium">Status</th>
                    <th class="text-left px-5 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                <tr class="border-b border-gray-800/50 hover:bg-gray-800/30">
                    <td class="px-5 py-3 font-mono text-vue font-medium">{{ $coupon->code }}</td>
                    <td class="px-5 py-3 text-gray-300">
                        {{ $coupon->discount_type === 'percentage' ? $coupon->discount_value . '%' : '₹' . $coupon->discount_value }}
                    </td>
                    <td class="px-5 py-3 text-gray-400">{{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}</td>
                    <td class="px-5 py-3 text-gray-400">{{ $coupon->expires_at?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $coupon->is_active ? 'bg-green-900/40 text-green-400' : 'bg-gray-800 text-gray-500' }}">
                            {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <form action="{{ route('admin.coupons.toggle', $coupon->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs px-2 py-1 rounded bg-gray-800 text-gray-300 hover:text-white">
                                {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">No coupons yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
    <div class="px-5 py-3 border-t border-gray-800">{{ $coupons->links('pagination::tailwind') }}</div>
    @endif
</div>
@endsection
