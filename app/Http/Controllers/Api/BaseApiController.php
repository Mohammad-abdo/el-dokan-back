<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Helpers\Sanitizer;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    use ApiResponse;

    /**
     * Sanitize request input
     */
    protected function sanitizeRequest(array $fields): array
    {
        $sanitized = [];
        
        foreach ($fields as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = Sanitizer::sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = Sanitizer::sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Respond with validation errors
     */
    protected function respondWithValidationError(
        \Illuminate\Validation\ValidationException $e
    ): JsonResponse {
        return $this->validationErrorResponse($e->errors());
    }
}
