<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function user(): JsonResponse
    {
        return response()->json([
            'user' => auth()->guard('api')->user()
        ]);
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = request()->user();
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
            ]);
            if ($request->hasFile('profile_photo')) {
                $user->clearMediaCollection('profile_photo');
                $user->addMedia($request->file('profile_photo'))
                ->usingFileName(Str::random(15) . '.' . $request->file('profile_photo')->getClientOriginalExtension())
                ->toMediaCollection('profile_photo');
            }

            DB::commit();
            return response()->json([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
