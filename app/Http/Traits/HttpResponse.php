<?php

namespace App\Http\Traits;

trait HttpResponse
{
    protected function error($data = null, ?string $message = null, int $code = 401)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => 'error',
        ], $code);
    }

    protected function notFound($data = null, ?string $message = null, int $code = 404)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => 'not_found',
        ], $code);
    }

    protected function success($data = null, ?string $message = null, int $code = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => 'success',
        ], $code);
    }

    protected function internalError($message)
    {
        return response()->json([
            'message' => $message,
            'status' => 'internal_error',
        ], 500);
    }
}
