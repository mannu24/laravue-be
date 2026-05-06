<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminWebMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = session('admin_user_id');

        if (!$adminId) {
            return redirect('/admin/login');
        }

        $user = User::find($adminId);
        if (!$user || !$user->is_admin) {
            session()->forget(['admin_user_id', 'admin_user_name']);
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
