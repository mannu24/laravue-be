@extends('admin.layout')
@section('title', 'Portfolios')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Portfolios</h1>
    <form class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
            class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue w-64">
        <button type="submit" class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-300 hover:text-white">Search</button>
    </form>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 border-b border-gray-800">
                    <th class="text-left px-5 py-3 font-medium">Subdomain</th>
                    <th class="text-left px-5 py-3 font-medium">User</th>
                    <th class="text-left px-5 py-3 font-medium">Template</th>
                    <th class="text-left px-5 py-3 font-medium">Projects</th>
                    <th class="text-left px-5 py-3 font-medium">Status</th>
                    <th class="text-left px-5 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($portfolios as $portfolio)
                <tr class="border-b border-gray-800/50 hover:bg-gray-800/30">
                    <td class="px-5 py-3">
                        <span class="text-vue font-medium">{{ $portfolio->subdomain }}</span>.laravue.in
                    </td>
                    <td class="px-5 py-3 text-gray-300">{{ $portfolio->user->name ?? '—' }}<br><span class="text-xs text-gray-500">{{ $portfolio->user->email ?? '' }}</span></td>
                    <td class="px-5 py-3 text-gray-400 capitalize">{{ $portfolio->template_slug }}</td>
                    <td class="px-5 py-3 text-gray-400">{{ $portfolio->projects_count }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $portfolio->is_published ? 'bg-green-900/40 text-green-400' : 'bg-yellow-900/40 text-yellow-400' }}">
                            {{ $portfolio->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <form action="{{ route('admin.portfolios.toggle', $portfolio->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs px-2 py-1 rounded bg-gray-800 text-gray-300 hover:text-white">
                                {{ $portfolio->is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">No portfolios found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($portfolios->hasPages())
    <div class="px-5 py-3 border-t border-gray-800">
        {{ $portfolios->links('pagination::tailwind') }}
    </div>
    @endif
</div>
@endsection
