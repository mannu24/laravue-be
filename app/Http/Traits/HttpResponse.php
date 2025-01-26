<?php

namespace App\Http\Traits;

trait HttpResponse
{
    protected function error($data = null, ?string $message = null, int $code = 401)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function notFound($data = null, ?string $message = null, int $code = 404)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function success($data = null, ?string $message = null, int $code = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function internalError($message)
    {
        return response()->json([
            'message' => $message,
        ], 500);
    }
}
