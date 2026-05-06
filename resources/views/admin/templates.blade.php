@extends('admin.layout')
@section('title', 'Templates')

@section('content')
<h1 class="text-2xl font-bold text-white mb-6">Portfolio Templates</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($templates as $template)
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="h-32 flex items-center justify-center {{ $template->slug === 'developer' ? 'bg-[#0a0a0f]' : 'bg-gray-100' }}">
            @if($template->slug === 'minimal')
                <div class="text-center">
                    <div class="w-8 h-8 rounded-full bg-gray-300 mx-auto mb-1"></div>
                    <div class="h-2 w-20 bg-gray-300 rounded mx-auto mb-1"></div>
                    <div class="h-1.5 w-14 bg-gray-200 rounded mx-auto"></div>
                </div>
            @elseif($template->slug === 'developer')
                <div class="bg-[#161b22] border border-[#30363d] rounded p-2 text-center">
                    <div class="flex gap-1 mb-1"><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div><div class="w-1.5 h-1.5 rounded-full bg-yellow-500"></div><div class="w-1.5 h-1.5 rounded-full bg-green-500"></div></div>
                    <div class="h-1.5 w-12 bg-vue rounded mb-0.5"></div>
                    <div class="h-1.5 w-16 bg-[#30363d] rounded"></div>
                </div>
            @else
                <span class="text-4xl">🎨</span>
            @endif
        </div>
        <div class="p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-white">{{ $template->name }}</h3>
                <div class="flex gap-2">
                    @if($template->is_premium)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-900/40 text-yellow-400">Premium</span>
                    @endif
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $template->is_active ? 'bg-green-900/40 text-green-400' : 'bg-gray-800 text-gray-500' }}">
                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mb-3">{{ $template->description ?? 'No description' }}</p>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600 font-mono">{{ $template->slug }}</span>
                <form action="{{ route('admin.templates.toggle', $template->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs px-2 py-1 rounded bg-gray-800 text-gray-300 hover:text-white">
                        {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
