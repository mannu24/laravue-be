<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — LaraVue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{vue:'#41B883',laravel:'#E23744'}}}}</script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-950 text-gray-200 min-h-screen">
    <!-- Sidebar + Content -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-56 bg-gray-900 border-r border-gray-800 flex flex-col fixed h-full">
            <div class="p-4 border-b border-gray-800">
                <a href="/admin" class="text-lg font-bold text-white">🛡️ LaraVue <span class="text-laravel text-sm">Admin</span></a>
            </div>
            <nav class="flex-1 p-3 space-y-1">
                @php $current = request()->route()->getName() ?? ''; @endphp
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.dashboard' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                    📊 Dashboard
                </a>
                <a href="{{ route('admin.portfolios') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.portfolios') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                    🌐 Portfolios
                </a>
                <a href="{{ route('admin.orders') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.orders' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                    🛒 Orders
                </a>
                <a href="{{ route('admin.subscriptions') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.subscriptions' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                    💳 Subscriptions
                </a>
                <a href="{{ route('admin.plans') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.plans' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                    📋 Plans
                </a>
                <a href="{{ route('admin.coupons') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.coupons' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                    🏷️ Coupons
                </a>
                <a href="{{ route('admin.templates') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.templates' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                    🎨 Templates
                </a>
            </nav>
            <div class="p-3 border-t border-gray-800">
                <div class="text-xs text-gray-500 mb-2 px-3">{{ session('admin_user_name', 'Admin') }}</div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-gray-800/50">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-56 p-6">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-green-900/30 border border-green-800 text-green-400 text-sm">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-900/30 border border-red-800 text-red-400 text-sm">
                    ✗ {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
