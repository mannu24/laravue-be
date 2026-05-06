<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (session('admin_user_id')) {
            return redirect('/admin');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        if (!$user->is_admin) {
            return back()->withErrors(['email' => 'Access denied. Admin privileges required.'])->withInput();
        }

        session(['admin_user_id' => $user->id, 'admin_user_name' => $user->name]);

        return redirect('/admin');
    }

    public function logout()
    {
        session()->forget(['admin_user_id', 'admin_user_name']);
        return redirect('/admin/login');
    }
}
