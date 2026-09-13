<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Food Ordering REST API',
    description: 'Production-ready REST API for the Food Ordering platform built on Laravel and MongoDB.',
    contact: new OA\Contact(email: 'support@foodordering.com')
)]
#[OA\Server(
    url: '/api/v1',
    description: 'API Version 1 Endpoint'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your Bearer token in format: Bearer <token>'
)]
class BaseApiController extends Controller
{
    /**
     * Return a standardized success JSON response.
     */
    protected function sendResponse(mixed $data, string $message = 'Success', int $code = 200, ?array $meta = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a standardized error JSON response.
     */
    protected function sendError(string $message, mixed $errors = [], int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
