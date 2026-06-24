<?php

namespace App\Support\Api;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function success(mixed $data = null, string $message = 'OK', array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    /**
     * @param  array<string, mixed>  $errors
     * @param  array<string, mixed>  $meta
     */
    public static function error(string $message, int $status = 400, array $errors = [], array $meta = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * @return array<string, mixed>
     */
    public static function paginationMeta(Paginator $paginator): array
    {
        return [
            'current_page' => method_exists($paginator, 'currentPage') ? $paginator->currentPage() : null,
            'per_page' => $paginator->perPage(),
            'total' => method_exists($paginator, 'total') ? $paginator->total() : null,
            'last_page' => method_exists($paginator, 'lastPage') ? $paginator->lastPage() : null,
        ];
    }
}
