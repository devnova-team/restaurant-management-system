<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    // Success response
    public static function success(mixed $data = null, $message = 'Success', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    // Error response
    public static function error(string $message = 'Error', int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
