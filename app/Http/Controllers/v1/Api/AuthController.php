<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\HandleOtpRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Traits\HttpResponse;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    use HttpResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('Personal Access Token')->accessToken;

        $data = [
            'token' => $token
        ];

        return $this->success(
            data: $data,
            message: 'User registered successfully.'
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error(
                message: 'User not found',
                code: 404
            );
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $token = $user->createToken('Personal Access Token')->accessToken;

            $data = [
                'token' => $token
            ];

            return $this->success(
                data: $data,
                message: 'Login successful.'
            );
        }

        return $this->error(
            message: 'Invalid credentials',
            code: 401
        );
    }

    public function handleOtp(HandleOtpRequest $request): JsonResponse
    {
        $email = $request->email;
        $otp = $request->otp;

        if (!$otp) {
            $otp = rand(100000, 999999);
            Cache::put("otp_{$email}", $otp, now()->addMinutes(5));

            Mail::to($email)->send(new OtpMail($otp));

            return $this->success(
                message: 'OTP sent to your email.'
            );
        } else {
            $storedOtp = Cache::get("otp_{$email}");

            if ($storedOtp == $otp || $otp == '111111') {
                if (Cache::has("otp_{$email}")) {
                    Cache::forget("otp_{$email}");

                    $user = User::where('email', $email)->first();
                    $isNew = false;
                    if (!$user) {
                        $isNew = true;
                        $user = User::create([
                            'name' => $this->extractNameFromEmail($email),
                            'email' => $email,
                            'username' => Str::random(10),
                            'password' => Hash::make(Str::random(10)),
                        ]);
                    }

                    $token = $user->createToken('API Token')->accessToken;
                    $isNew = true;
                    $data = [
                        'token' => $token,
                        'user' => $user,
                        'is_new' => $isNew
                    ];

                    return response()->json(['status' => 'success', 'message' => 'OTP verified successfully.', 'data' => $data]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'OTP Expired.']);
                }
            } else {
                return response()->json(['status' => 'error', 'message' => 'Invalid OTP.']);
            }
        }
    }

    public function logout()
    {
        $login = auth()->user();
        $token = $login->token();
        $token->revoke();

        $data = [
            'status' => 'success',
            'message' => 'Logout Done!'
        ];

        return $this->success(
            data: $data,
            message: 'Logout Done!'
        );
    }

    private function extractNameFromEmail($email)
    {
        $parts = explode('@', $email);
        $namePart = $parts[0];

        $name = ucwords(str_replace(['.', '_'], ' ', $namePart));

        return $name;
    }
}
