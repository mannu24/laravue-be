<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — LaraVue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{vue:'#41B883',laravel:'#E23744'}}}}</script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="bg-gray-900 border border-gray-800 rounded-xl shadow-2xl overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-laravel to-vue"></div>
            <div class="p-8">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-800 flex items-center justify-center text-2xl">🛡️</div>
                    <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                    <p class="text-sm text-gray-500 mt-1">Sign in with your admin credentials</p>
                </div>

                <form method="POST" action="/admin/login" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-medium text-gray-400 block mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue focus:ring-1 focus:ring-vue"
                            placeholder="admin@laravue.in">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-400 block mb-1">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-vue focus:ring-1 focus:ring-vue"
                            placeholder="••••••••">
                    </div>

                    @if($errors->any())
                        <div class="text-sm text-red-400 bg-red-900/20 border border-red-800 rounded-lg px-3 py-2">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-laravel to-laravel/80 hover:from-laravel/90 hover:to-laravel/70 text-white font-medium rounded-lg text-sm transition-all">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="/" class="text-sm text-gray-500 hover:text-gray-300">← Back to LaraVue</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
