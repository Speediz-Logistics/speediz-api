<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait BaseApiResponse
{
    public function success($data, ?string $title = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $alert = ($title !== null || $message !== null) ? ['title' => $title, 'message' => $message] : null;

        return response()->json([
            'data' => $data,
            'meta' => [
                'status' => true,
                'message' => $alert,
            ],
        ], $code);
    }

    // Success response for login
    public function successAuth($data, $token, ?string $title = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $alert = ($title !== null || $message !== null) ? ['title' => $title, 'message' => $message] : null;

        return response()->json([
            'data' => [
                'user' => $data,
                'token' => $token,
            ],
            'meta' => [
                'status' => true,
                'message' => $alert,
            ],
        ], $code);
    }

    public function failed(?string $data = null, ?string $title = null, ?string $message = null, int $code = 500): JsonResponse
    {
        $alert = ($title !== null || $message !== null) ? ['title' => $title, 'message' => $message] : null;

        return response()->json([
            'data' => $data,
            'meta' => [
                'status' => false,
                'message' => $alert,
            ],
        ], $code);
    }
}

