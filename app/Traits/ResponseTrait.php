<?php

namespace App\Traits;

trait ResponseTrait
{
    public function apiSuccess(string $message = 'Request successful', array|object|null $data = null,  int $statusCode = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'success' => true,
        ], $statusCode);
    }

    public function apiError(string $message = 'An error occurred', int $statusCode = 422, $errors = null)
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
            'success' => false,
        ], $statusCode);
    }
}
