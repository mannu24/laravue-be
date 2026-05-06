@extends('admin.layout')
@section('title', 'Subscriptions')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Subscriptions</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.subscriptions') }}" class="px-3 py-1.5 rounded-lg text-xs {{ !request('status') ? 'bg-gray-700 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">All</a>
        <a href="{{ route('admin.subscriptions', ['status' => 'active']) }}" class="px-3 py-1.5 rounded-lg text-xs {{ request('status') === 'active' ? 'bg-green-900/40 text-green-400' : 'bg-gray-800 text-gray-400 hover:text-white' }}">Active</a>
        <a href="{{ route('admin.subscriptions', ['status' => 'expired']) }}" class="px-3 py-1.5 rounded-lg text-xs {{ request('status') === 'expired' ? 'bg-yellow-900/40 text-yellow-400' : 'bg-gray-800 text-gray-400 hover:text-white' }}">Expired</a>
        <a href="{{ route('admin.subscriptions', ['status' => 'refunded']) }}" class="px-3 py-1.5 rounded-lg text-xs {{ request('status') === 'refunded' ? 'bg-red-900/40 text-red-400' : 'bg-gray-800 text-gray-400 hover:text-white' }}">Refunded</a>
    </div>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 border-b border-gray-800">
                    <th class="text-left px-5 py-3 font-medium">User</th>
                    <th class="text-left px-5 py-3 font-medium">Plan</th>
                    <th class="text-left px-5 py-3 font-medium">Starts</th>
                    <th class="text-left px-5 py-3 font-medium">Expires</th>
                    <th class="text-left px-5 py-3 font-medium">Status</th>
                    <th class="text-left px-5 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                <tr class="border-b border-gray-800/50 hover:bg-gray-800/30">
                    <td class="px-5 py-3 text-gray-300">{{ $sub->user->name ?? '—' }}<br><span class="text-xs text-gray-500">{{ $sub->user->email ?? '' }}</span></td>
                    <td class="px-5 py-3 text-gray-300">{{ $sub->plan->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-400">{{ $sub->starts_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3 text-gray-400">
                        {{ $sub->expires_at->format('M d, Y') }}
                        @if($sub->status->value === 'active' && $sub->expires_at->isPast())
                            <br><span class="text-xs text-yellow-400">Grace period</span>
                        @elseif($sub->status->value === 'active' && $sub->expires_at->diffInDays(now()) <= 7)
                            <br><span class="text-xs text-yellow-400">{{ $sub->expires_at->diffInDays(now()) }}d left</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($sub->status->value === 'active') bg-green-900/40 text-green-400
                            @elseif($sub->status->value === 'expired') bg-yellow-900/40 text-yellow-400
                            @elseif($sub->status->value === 'refunded') bg-red-900/40 text-red-400
                            @else bg-gray-800 text-gray-400 @endif">
                            {{ $sub->status->value }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @if($sub->status->value === 'active')
                        <form action="{{ route('admin.subscriptions.refund', $sub->id) }}" method="POST" class="inline"
                            onsubmit="return confirm('Refund this subscription? This will unpublish the portfolio.')">
                            @csrf
                            <button type="submit" class="text-xs px-2 py-1 rounded bg-red-900/30 text-red-400 hover:bg-red-900/50">
                                Refund
                            </button>
                        </form>
                        @else
                            <span class="text-xs text-gray-600">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">No subscriptions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($subscriptions->hasPages())
    <div class="px-5 py-3 border-t border-gray-800">{{ $subscriptions->links('pagination::tailwind') }}</div>
    @endif
</div>
@endsection
