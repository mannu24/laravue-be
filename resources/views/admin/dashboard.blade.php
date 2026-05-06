@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-bold text-white mb-6">Dashboard</h1>

<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-500">Active Subscriptions</p>
        <p class="text-2xl font-bold text-white mt-1">{{ $stats['active_subscriptions'] }}</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-500">Published Portfolios</p>
        <p class="text-2xl font-bold text-white mt-1">{{ $stats['published_portfolios'] }} / {{ $stats['total_portfolios'] }}</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-500">Revenue (This Month)</p>
        <p class="text-2xl font-bold text-white mt-1">₹{{ number_format($stats['revenue_this_month'], 2) }}</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-500">Revenue (All Time)</p>
        <p class="text-2xl font-bold text-white mt-1">₹{{ number_format($stats['revenue_all_time'], 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-500">Total Orders</p>
        <p class="text-xl font-bold text-white mt-1">{{ $stats['paid_orders'] }} paid / {{ $stats['total_orders'] }} total</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-500">⚠️ Expiring in 7 Days</p>
        <p class="text-xl font-bold text-yellow-400 mt-1">{{ $stats['expiring_in_7_days'] }}</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-sm text-gray-500">Quick Links</p>
        <div class="flex gap-2 mt-2">
            <a href="{{ route('admin.portfolios') }}" class="text-xs px-2 py-1 bg-gray-800 rounded text-gray-300 hover:text-white">Portfolios</a>
            <a href="{{ route('admin.orders') }}" class="text-xs px-2 py-1 bg-gray-800 rounded text-gray-300 hover:text-white">Orders</a>
            <a href="{{ route('admin.coupons') }}" class="text-xs px-2 py-1 bg-gray-800 rounded text-gray-300 hover:text-white">Coupons</a>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-800">
        <h2 class="font-semibold text-white">Recent Orders</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 border-b border-gray-800">
                    <th class="text-left px-5 py-3 font-medium">User</th>
                    <th class="text-left px-5 py-3 font-medium">Plan</th>
                    <th class="text-left px-5 py-3 font-medium">Amount</th>
                    <th class="text-left px-5 py-3 font-medium">Status</th>
                    <th class="text-left px-5 py-3 font-medium">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                <tr class="border-b border-gray-800/50 hover:bg-gray-800/30">
                    <td class="px-5 py-3 text-gray-300">{{ $order->user->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-300">{{ $order->plan->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-300">₹{{ $order->final_amount }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($order->status->value === 'paid') bg-green-900/40 text-green-400
                            @elseif($order->status->value === 'pending') bg-yellow-900/40 text-yellow-400
                            @elseif($order->status->value === 'failed') bg-red-900/40 text-red-400
                            @else bg-gray-800 text-gray-400 @endif">
                            {{ $order->status->value }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">No orders yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
