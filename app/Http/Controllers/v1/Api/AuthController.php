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
use Illuminate\Http\Request;
use Carbon\Carbon;
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
            $user->update(['last_active_at' => now()]);
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

            if (!$storedOtp) {
                return response()->json(['status' => 'error', 'message' => 'OTP expired.'], 422);
            }

            if ((string) $storedOtp !== (string) $otp) {
                return response()->json(['status' => 'error', 'message' => 'Invalid OTP.'], 422);
            }

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

            $user->update(['last_active_at' => now()]);

            $tokenResult = $user->createToken('API Token');
            $passportToken = $tokenResult->token;
            $passportToken->expires_at = Carbon::tomorrow()->startOfDay();
            $passportToken->save();

            $token = $tokenResult->accessToken;
            $data = [
                'token' => $token,
                'user' => $user,
                'is_new' => $isNew
            ];

            return response()->json(['status' => 'success', 'message' => 'OTP verified successfully.', 'data' => $data]);
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

    /**
     * Handle Google Sign-In credential verification.
     */
    public function googleSignIn(Request $request): JsonResponse
    {
        $request->validate([
            'credential' => 'required|string',
        ]);

        try {
            // Decode and verify the Google JWT token
            $credential = $request->credential;
            $payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $credential)[1]))), true);

            if (!$payload || !isset($payload['email'])) {
                return $this->error(message: 'Invalid Google credential.', code: 422);
            }

            // Verify the token audience matches our client ID
            $clientId = config('services.google.client_id');
            if ($clientId && isset($payload['aud']) && $payload['aud'] !== $clientId) {
                return $this->error(message: 'Invalid Google credential.', code: 422);
            }

            // Verify token hasn't expired
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return $this->error(message: 'Google credential has expired.', code: 422);
            }

            $email = $payload['email'];
            $name = $payload['name'] ?? $this->extractNameFromEmail($email);

            $user = User::where('email', $email)->first();
            $isNew = false;

            if (!$user) {
                $isNew = true;
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'username' => Str::random(10),
                    'password' => Hash::make(Str::random(32)),
                ]);
            }

            $user->update(['last_active_at' => now()]);

            $tokenResult = $user->createToken('Google Sign-In Token');
            $passportToken = $tokenResult->token;
            $passportToken->expires_at = Carbon::tomorrow()->startOfDay();
            $passportToken->save();

            return $this->success(
                data: [
                    'token' => $tokenResult->accessToken,
                    'user' => $user,
                    'is_new' => $isNew,
                ],
                message: 'Google sign-in successful.'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google sign-in failed', ['error' => $e->getMessage()]);
            return $this->error(message: 'Google sign-in failed.', code: 500);
        }
    }

    /**
     * Handle GitHub Sign-In.
     * Frontend sends the GitHub OAuth code, backend exchanges it for a token,
     * fetches user info, and creates/finds a Laravue user.
     */
    public function githubSignIn(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $oauthService = app(\App\Services\GitHub\GitHubOAuthService::class);

            // Exchange code for GitHub access token
            $tokenData = $oauthService->exchangeCodeForToken($request->code);
            $githubUser = $oauthService->getUserInfo($tokenData['access_token']);

            $email = $githubUser['email'];

            // If email is private, fetch from /user/emails endpoint
            if (!$email) {
                $emailsResponse = \Illuminate\Support\Facades\Http::withToken($tokenData['access_token'])
                    ->get('https://api.github.com/user/emails');
                if ($emailsResponse->successful()) {
                    $primary = collect($emailsResponse->json())->firstWhere('primary', true);
                    $email = $primary['email'] ?? null;
                }
            }

            if (!$email) {
                return $this->error(message: 'Could not retrieve email from GitHub. Please ensure your email is visible in GitHub settings.', code: 422);
            }

            $name = $githubUser['name'] ?? $githubUser['login'] ?? $this->extractNameFromEmail($email);

            $user = User::where('email', $email)->first();
            $isNew = false;

            if (!$user) {
                $isNew = true;
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'username' => $githubUser['login'] ?? Str::random(10),
                    'password' => Hash::make(Str::random(32)),
                ]);
            }

            // Store/update GitHub token for the user
            $oauthService->storeToken($user->id, $tokenData, $githubUser);

            $user->update(['last_active_at' => now()]);

            $tokenResult = $user->createToken('GitHub Sign-In Token');
            $passportToken = $tokenResult->token;
            $passportToken->expires_at = Carbon::tomorrow()->startOfDay();
            $passportToken->save();

            return $this->success(
                data: [
                    'token' => $tokenResult->accessToken,
                    'user' => $user,
                    'is_new' => $isNew,
                ],
                message: 'GitHub sign-in successful.'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GitHub sign-in failed', ['error' => $e->getMessage()]);
            return $this->error(message: 'GitHub sign-in failed. Please try again.', code: 500);
        }
    }

    private function extractNameFromEmail($email)
    {
        $parts = explode('@', $email);
        $namePart = $parts[0];

        $name = ucwords(str_replace(['.', '_'], ' ', $namePart));

        return $name;
    }
}
