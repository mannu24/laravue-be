<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    use HttpResponse;

    /**
     * Admin login with email + password.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error(null, 'Invalid credentials.', 401);
        }

        if (!$user->is_admin) {
            return $this->error(null, 'Access denied. Admin privileges required.', 403);
        }

        $token = $user->createToken('Admin Access Token')->accessToken;

        return $this->success([
            'token' => $token,
            'user' => $user,
        ], 'Admin login successful.');
    }
}
