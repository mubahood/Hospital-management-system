<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Base API Controller
 * 
 * Provides common functionality for all API controllers including:
 * - Standardized JSON responses
 * - Error handling
 * - Pagination
 * - Enterprise scope management
 * - Request validation
 */
class BaseApiController extends Controller
{
    /**
     * Default pagination size
     */
    protected $defaultPerPage = 15;

    /**
     * Maximum pagination size
     */
    protected $maxPerPage = 100;

    /**
     * Current API version
     */
    protected $apiVersion = 'v1';

    /**
     * Return a success response
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'code' => $code,
            'timestamp' => now()->toISOString(),
            'version' => $this->apiVersion,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'timestamp' => now()->toISOString(),
            'version' => $this->apiVersion,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        // Log the error for debugging
        Log::warning('API Error Response', [
            'message' => $message,
            'code' => $code,
            'errors' => $errors,
            'user_id' => Auth::id(),
            'endpoint' => request()->getUri(),
        ]);

        return response()->json($response, $code);
    }

    /**
     * Return a paginated response
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return $this->successResponse([
            'items' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ], $message);
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        $perPage = min(
            (int) $request->get('per_page', $this->defaultPerPage),
            $this->maxPerPage
        );

        $page = max(1, (int) $request->get('page', 1));

        return [
            'per_page' => $perPage,
            'page' => $page,
        ];
    }

    /**
     * Validate request data
     */
    protected function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        try {
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            return $validator->validated();
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /**
     * Get current enterprise ID from authenticated user
     */
    protected function getCurrentEnterpriseId(): ?int
    {
        $user = Auth::user();
        return $user ? $user->enterprise_id : null;
    }

    /**
     * Check if user has permission for action
     */
    protected function checkPermission(string $permission): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Check if user has specific permission
        // This can be expanded based on your permission system
        return $user->hasPermission($permission);
    }

    /**
     * Apply enterprise scope to query
     */
    protected function applyEnterpriseScope($query)
    {
        $enterpriseId = $this->getCurrentEnterpriseId();
        
        if ($enterpriseId) {
            return $query->where('enterprise_id', $enterpriseId);
        }

        return $query;
    }

    /**
     * Transform model for API response
     */
    protected function transformModel($model, array $includes = []): array
    {
        if (!$model) {
            return [];
        }

        $data = $model->toArray();

        // Add computed fields if needed
        if (method_exists($model, 'getApiAttributes')) {
            $data = array_merge($data, $model->getApiAttributes());
        }

        // Include relationships if specified
        foreach ($includes as $include) {
            if ($model->relationLoaded($include)) {
                $data[$include] = $model->$include;
            }
        }

        return $data;
    }

    /**
     * Handle API exceptions
     */
    protected function handleException(\Exception $e): JsonResponse
    {
        Log::error('API Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
            'endpoint' => request()->getUri(),
        ]);

        if ($e instanceof ValidationException) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $e->validator->errors()
            );
        }

        if (config('app.debug')) {
            return $this->errorResponse(
                $e->getMessage(),
                500,
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );
        }

        return $this->errorResponse('Internal server error', 500);
    }

    /**
     * Rate limiting check
     */
    protected function checkRateLimit(string $key, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        $limiter = app('Illuminate\Cache\RateLimiter');
        
        if ($limiter->tooManyAttempts($key, $maxAttempts)) {
            return false;
        }

        $limiter->hit($key, $decayMinutes * 60);
        return true;
    }

    /**
     * Log API activity
     */
    protected function logApiActivity(string $action, array $data = []): void
    {
        Log::info('API Activity', [
            'action' => $action,
            'user_id' => Auth::id(),
            'enterprise_id' => $this->getCurrentEnterpriseId(),
            'endpoint' => request()->getUri(),
            'method' => request()->getMethod(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
