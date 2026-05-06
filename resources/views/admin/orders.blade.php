@extends('admin.layout')
@section('title', 'Orders')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Orders</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.orders') }}" class="px-3 py-1.5 rounded-lg text-xs {{ !request('status') ? 'bg-gray-700 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">All</a>
        <a href="{{ route('admin.orders', ['status' => 'paid']) }}" class="px-3 py-1.5 rounded-lg text-xs {{ request('status') === 'paid' ? 'bg-green-900/40 text-green-400' : 'bg-gray-800 text-gray-400 hover:text-white' }}">Paid</a>
        <a href="{{ route('admin.orders', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs {{ request('status') === 'pending' ? 'bg-yellow-900/40 text-yellow-400' : 'bg-gray-800 text-gray-400 hover:text-white' }}">Pending</a>
        <a href="{{ route('admin.orders', ['status' => 'failed']) }}" class="px-3 py-1.5 rounded-lg text-xs {{ request('status') === 'failed' ? 'bg-red-900/40 text-red-400' : 'bg-gray-800 text-gray-400 hover:text-white' }}">Failed</a>
    </div>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 border-b border-gray-800">
                    <th class="text-left px-5 py-3 font-medium">#</th>
                    <th class="text-left px-5 py-3 font-medium">User</th>
                    <th class="text-left px-5 py-3 font-medium">Plan</th>
                    <th class="text-left px-5 py-3 font-medium">Amount</th>
                    <th class="text-left px-5 py-3 font-medium">Coupon</th>
                    <th class="text-left px-5 py-3 font-medium">Status</th>
                    <th class="text-left px-5 py-3 font-medium">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-b border-gray-800/50 hover:bg-gray-800/30">
                    <td class="px-5 py-3 text-gray-500">{{ $order->id }}</td>
                    <td class="px-5 py-3 text-gray-300">{{ $order->user->name ?? '—' }}<br><span class="text-xs text-gray-500">{{ $order->user->email ?? '' }}</span></td>
                    <td class="px-5 py-3 text-gray-300">{{ $order->plan->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-300">
                        ₹{{ $order->final_amount }}
                        @if($order->discount_amount > 0)
                            <br><span class="text-xs text-green-400">-₹{{ $order->discount_amount }} off</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-400 font-mono">{{ $order->coupon->code ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($order->status->value === 'paid') bg-green-900/40 text-green-400
                            @elseif($order->status->value === 'pending') bg-yellow-900/40 text-yellow-400
                            @elseif($order->status->value === 'failed') bg-red-900/40 text-red-400
                            @else bg-gray-800 text-gray-400 @endif">
                            {{ $order->status->value }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500">No orders found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-5 py-3 border-t border-gray-800">{{ $orders->links('pagination::tailwind') }}</div>
    @endif
</div>
@endsection
