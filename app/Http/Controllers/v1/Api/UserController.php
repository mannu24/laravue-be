<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Traits\HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use HttpResponse;

    public function user(): JsonResponse
    {
        $data = [
            'user' => auth()->guard('api')->user()
        ];

        return $this->success(
            data: $data
        );
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

            $data = [
                'user' => $user
            ];

            return $this->success(
                data: $data
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->internalError(
                message: "An error occurred: " . $e->getMessage()
            );
        }
    }
}
