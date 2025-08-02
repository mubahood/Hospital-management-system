<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * AuditLogging Middleware
 * 
 * Comprehensive audit logging for hospital management system:
 * - All database operations (CRUD)
 * - Sensitive data access
 * - Authentication events
 * - Enterprise data isolation
 * - Compliance logging (HIPAA, GDPR)
 * - Performance monitoring
 */
class AuditLogging
{
    /**
     * Sensitive tables that require detailed logging
     */
    const SENSITIVE_TABLES = [
        'users', 'consultations', 'billing_items', 'payment_records',
        'patient_records', 'medical_services', 'dose_items', 'dose_item_records'
    ];

    /**
     * Operations that require audit logging
     */
    const AUDITABLE_OPERATIONS = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE'
    ];

    /**
     * Sensitive routes patterns
     */
    const SENSITIVE_ROUTES = [
        'admin.*', 'patients.*', 'consultations.*', 'billing.*', 
        'payments.*', 'reports.*', 'medical-services.*'
    ];

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $user = Auth::user();

        // Pre-request logging
        $auditId = $this->logRequestStart($request, $user);

        // Store original data for comparison (for sensitive routes)
        $originalData = $this->captureOriginalData($request);

        // Execute the request
        $response = $next($request);

        // Post-request logging
        $endTime = microtime(true);
        $this->logRequestComplete($auditId, $request, $response, $user, $startTime, $endTime, $originalData);

        return $response;
    }

    /**
     * Log request start
     */
    protected function logRequestStart(Request $request, $user): string
    {
        $auditId = $this->generateAuditId();

        $logData = [
            'audit_id' => $auditId,
            'event_type' => 'request_start',
            'user_id' => $user?->id,
            'enterprise_id' => $user?->enterprise_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'parameters' => $this->sanitizeParameters($request->all()),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
        ];

        // Log to audit table
        $this->writeAuditLog($logData);

        // Log to file for sensitive operations
        if ($this->isSensitiveOperation($request)) {
            Log::channel('audit')->info('Sensitive Operation Started', $logData);
        }

        return $auditId;
    }

    /**
     * Log request completion
     */
    protected function logRequestComplete(
        string $auditId, 
        Request $request, 
        $response, 
        $user, 
        float $startTime, 
        float $endTime,
        array $originalData
    ): void {
        $duration = round(($endTime - $startTime) * 1000, 2); // Duration in milliseconds

        $logData = [
            'audit_id' => $auditId,
            'event_type' => 'request_complete',
            'user_id' => $user?->id,
            'enterprise_id' => $user?->enterprise_id,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'memory_usage' => memory_get_peak_usage(true),
            'database_queries' => $this->getDatabaseQueryCount(),
            'response_size' => strlen($response->getContent()),
            'timestamp' => now()->toISOString(),
        ];

        // Add data changes for sensitive operations
        if ($this->isSensitiveOperation($request)) {
            $logData['data_changes'] = $this->detectDataChanges($request, $originalData);
            $logData['affected_records'] = $this->getAffectedRecords($request);
        }

        // Add error details if request failed
        if ($response->getStatusCode() >= 400) {
            $logData['error_details'] = $this->extractErrorDetails($response);
        }

        // Write to audit table
        $this->writeAuditLog($logData);

        // Log performance issues
        if ($duration > 5000) { // More than 5 seconds
            Log::channel('performance')->warning('Slow Request Detected', [
                'audit_id' => $auditId,
                'duration_ms' => $duration,
                'url' => $request->fullUrl(),
                'user_id' => $user?->id
            ]);
        }

        // Log errors
        if ($response->getStatusCode() >= 500) {
            Log::channel('error')->error('Server Error', [
                'audit_id' => $auditId,
                'status_code' => $response->getStatusCode(),
                'url' => $request->fullUrl(),
                'user_id' => $user?->id,
                'error_details' => $logData['error_details'] ?? null
            ]);
        }
    }

    /**
     * Capture original data for comparison
     */
    protected function captureOriginalData(Request $request): array
    {
        if (!$this->isSensitiveOperation($request)) {
            return [];
        }

        $originalData = [];

        // Capture existing record data for updates/deletes
        if (in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $resourceId = $this->extractResourceId($request);
            if ($resourceId) {
                $originalData = $this->fetchOriginalRecord($request, $resourceId);
            }
        }

        return $originalData;
    }

    /**
     * Check if operation is sensitive
     */
    protected function isSensitiveOperation(Request $request): bool
    {
        $route = $request->route()?->getName();
        
        if (!$route) {
            return false;
        }

        foreach (self::SENSITIVE_ROUTES as $pattern) {
            if (fnmatch($pattern, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize request parameters
     */
    protected function sanitizeParameters(array $parameters): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'token', 'api_key',
            'credit_card', 'ssn', 'social_security', 'bank_account'
        ];

        $sanitized = $parameters;

        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '[REDACTED]';
            }
        }

        // Sanitize nested arrays
        array_walk_recursive($sanitized, function (&$value, $key) use ($sensitiveFields) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $value = '[REDACTED]';
            }
        });

        return $sanitized;
    }

    /**
     * Sanitize request headers
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization', 'x-api-key', 'cookie', 'x-auth-token'
        ];

        $sanitized = [];

        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = is_array($value) ? $value[0] : $value;
            }
        }

        return $sanitized;
    }

    /**
     * Detect data changes
     */
    protected function detectDataChanges(Request $request, array $originalData): array
    {
        if (empty($originalData)) {
            return [];
        }

        $newData = $request->all();
        $changes = [];

        // Compare original and new data
        foreach ($newData as $field => $newValue) {
            $originalValue = $originalData[$field] ?? null;
            
            if ($originalValue !== $newValue) {
                $changes[$field] = [
                    'from' => $this->sanitizeValue($field, $originalValue),
                    'to' => $this->sanitizeValue($field, $newValue)
                ];
            }
        }

        return $changes;
    }

    /**
     * Extract resource ID from request
     */
    protected function extractResourceId(Request $request): ?int
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }

        // Try common parameter names
        $possibleParams = ['id', 'user', 'patient', 'consultation', 'billing_item', 'payment'];
        
        foreach ($possibleParams as $param) {
            $value = $route->parameter($param);
            if ($value && is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * Fetch original record data
     */
    protected function fetchOriginalRecord(Request $request, int $resourceId): array
    {
        $route = $request->route()?->getName();
        
        if (!$route) {
            return [];
        }

        try {
            // Determine table based on route
            if (str_contains($route, 'patient')) {
                return DB::table('users')->where('id', $resourceId)->first()?->toArray() ?? [];
            } elseif (str_contains($route, 'consultation')) {
                return DB::table('consultations')->where('id', $resourceId)->first()?->toArray() ?? [];
            } elseif (str_contains($route, 'billing')) {
                return DB::table('billing_items')->where('id', $resourceId)->first()?->toArray() ?? [];
            } elseif (str_contains($route, 'payment')) {
                return DB::table('payment_records')->where('id', $resourceId)->first()?->toArray() ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch original record for audit', [
                'resource_id' => $resourceId,
                'route' => $route,
                'error' => $e->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Get affected records count
     */
    protected function getAffectedRecords(Request $request): int
    {
        // This would track how many records were affected by the operation
        // Implementation depends on your specific use case
        return 1;
    }

    /**
     * Extract error details from response
     */
    protected function extractErrorDetails($response): array
    {
        $content = $response->getContent();
        
        if (empty($content)) {
            return [];
        }

        $decoded = json_decode($content, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'message' => $decoded['message'] ?? 'Unknown error',
                'errors' => $decoded['errors'] ?? [],
                'code' => $decoded['code'] ?? null
            ];
        }

        return [
            'message' => 'Error response could not be decoded',
            'raw_content' => substr($content, 0, 500) // First 500 chars
        ];
    }

    /**
     * Get database query count
     */
    protected function getDatabaseQueryCount(): int
    {
        // This would require query counting - implement with query listener
        return DB::getQueryLog() ? count(DB::getQueryLog()) : 0;
    }

    /**
     * Sanitize sensitive values
     */
    protected function sanitizeValue(string $field, $value)
    {
        $sensitiveFields = [
            'password', 'ssn', 'social_security', 'credit_card', 
            'bank_account', 'medical_history', 'notes'
        ];

        if (in_array(strtolower($field), $sensitiveFields)) {
            return '[REDACTED]';
        }

        return $value;
    }

    /**
     * Generate unique audit ID
     */
    protected function generateAuditId(): string
    {
        return 'AUD_' . now()->format('YmdHis') . '_' . uniqid();
    }

    /**
     * Write audit log to database
     */
    protected function writeAuditLog(array $logData): void
    {
        try {
            DB::table('audit_logs')->insert([
                'audit_id' => $logData['audit_id'],
                'event_type' => $logData['event_type'],
                'user_id' => $logData['user_id'],
                'enterprise_id' => $logData['enterprise_id'],
                'ip_address' => $logData['ip_address'] ?? null,
                'user_agent' => $logData['user_agent'] ?? null,
                'method' => $logData['method'] ?? null,
                'url' => $logData['url'] ?? null,
                'route' => $logData['route'] ?? null,
                'status_code' => $logData['status_code'] ?? null,
                'duration_ms' => $logData['duration_ms'] ?? null,
                'memory_usage' => $logData['memory_usage'] ?? null,
                'parameters' => isset($logData['parameters']) ? json_encode($logData['parameters']) : null,
                'data_changes' => isset($logData['data_changes']) ? json_encode($logData['data_changes']) : null,
                'error_details' => isset($logData['error_details']) ? json_encode($logData['error_details']) : null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            // Fallback to file logging if database fails
            Log::error('Failed to write audit log to database', [
                'audit_id' => $logData['audit_id'],
                'error' => $e->getMessage(),
                'log_data' => $logData
            ]);
        }
    }
}
