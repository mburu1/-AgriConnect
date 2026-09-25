<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Send a successful response.
     */
    protected function successResponse(mixed $data = null, string $message = 'Operation successful.', int $code = 200, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Send a paginated collection response.
     */
    protected function paginatedResponse(mixed $paginator, string $message = 'Data retrieved successfully.'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more_pages' => $paginator->hasMorePages(),
            ]
        ], 200);
    }

    /**
     * Send an error response.
     */
    protected function errorResponse(string $message = 'An error occurred.', int $code = 400, string $errorCode = 'BAD_REQUEST', mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Send a not found response.
     */
    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, 404, 'NOT_FOUND');
    }

    /**
     * Send an unauthorized / unauthenticated response.
     */
    protected function unauthorizedResponse(string $message = 'Unauthenticated.'): JsonResponse
    {
        return $this->errorResponse($message, 401, 'UNAUTHENTICATED');
    }

    /**
     * Send a forbidden response.
     */
    protected function forbiddenResponse(string $message = 'Action forbidden.'): JsonResponse
    {
        return $this->errorResponse($message, 403, 'FORBIDDEN');
    }

    /**
     * Send a validation error response.
     */
    protected function validationErrorResponse(mixed $errors, string $message = 'Validation failed.'): JsonResponse
    {
        return $this->errorResponse($message, 422, 'VALIDATION_ERROR', $errors);
    }

    /**
     * Send a business rule conflict response.
     */
    protected function businessRuleErrorResponse(string $message = 'Business rule violation.', string $errorCode = 'BUSINESS_RULE_ERROR'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errorCode);
    }
}
