<?php

namespace App\Support\Http;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(array $payload = [], int $status = 200): JsonResponse
    {
        return response()->json(array_merge(['success' => true], $payload), $status);
    }

    public static function error(string $message, int $status = 400, array $errors = [], array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $extra), $status);
    }
}
