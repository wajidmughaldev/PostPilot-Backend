<?php

namespace App\Support\Http;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(string $message, mixed $data = [], int $status = 200): JsonResponse
    {
        $normalizedData = $data;

        if (is_array($data) && ! array_is_list($data)) {
            $normalizedData = (object) $data;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $normalizedData,
        ], $status);
    }

    protected function errorResponse(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
