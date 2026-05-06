@extends('admin.layout')
@section('title', 'Plans')

@section('content')
<h1 class="text-2xl font-bold text-white mb-6">Subscription Plans</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach($plans as $plan)
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="text-xs text-gray-500 block mb-1">Name</label>
                <input type="text" name="name" value="{{ $plan->name }}" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Price (₹)</label>
                    <input type="number" step="0.01" name="price" value="{{ $plan->price }}" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Duration (months)</label>
                    <input type="number" name="duration_months" value="{{ $plan->duration_months }}" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue">
                </div>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Max Projects (empty = unlimited)</label>
                <input type="number" name="max_projects" value="{{ $plan->max_projects }}" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue">
            </div>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" name="allows_custom_domain" {{ $plan->allows_custom_domain ? 'checked' : '' }} class="rounded bg-gray-800 border-gray-600">
                    Custom Domain
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" name="is_active" {{ $plan->is_active ? 'checked' : '' }} class="rounded bg-gray-800 border-gray-600">
                    Active
                </label>
            </div>
            <button type="submit" class="w-full py-2 bg-vue/20 text-vue rounded-lg text-sm font-medium hover:bg-vue/30 transition-colors">
                Save Changes
            </button>
        </form>
    </div>
    @endforeach
</div>
@endsection
