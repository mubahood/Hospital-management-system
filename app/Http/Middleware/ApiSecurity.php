<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Closure;

/**
 * API Security Middleware
 * 
 * Provides comprehensive API security including:
 * - Request validation
 * - Security headers
 * - Input sanitization
 * - CORS handling
 * - Request logging
 */
class ApiSecurity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Validate request structure
        if (!$this->isValidApiRequest($request)) {
            return $this->buildSecurityErrorResponse('Invalid request format');
        }

        // Check for security threats
        if ($this->detectSecurityThreats($request)) {
            $this->logSecurityThreat($request);
            return $this->buildSecurityErrorResponse('Request blocked for security reasons');
        }

        // Sanitize input
        $this->sanitizeRequest($request);

        $response = $next($request);

        // Add security headers
        if ($response instanceof JsonResponse) {
            $this->addSecurityHeaders($response);
        }

        // Log API request
        $this->logApiRequest($request, $response);

        return $response;
    }

    /**
     * Validate if request is a valid API request
     */
    protected function isValidApiRequest(Request $request): bool
    {
        // Check for required headers
        $requiredHeaders = ['Accept', 'User-Agent'];
        
        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                return false;
            }
        }

        // Validate Content-Type for POST/PUT requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $contentType = $request->header('Content-Type');
            $validTypes = [
                'application/json',
                'application/x-www-form-urlencoded',
                'multipart/form-data',
            ];

            $isValid = false;
            foreach ($validTypes as $type) {
                if (str_contains($contentType, $type)) {
                    $isValid = true;
                    break;
                }
            }

            if (!$isValid) {
                return false;
            }
        }

        return true;
    }

    /**
     * Detect potential security threats
     */
    protected function detectSecurityThreats(Request $request): bool
    {
        // SQL injection patterns
        $sqlPatterns = [
            '/(\bUNION\b|\bSELECT\b|\bDROP\b|\bDELETE\b|\bINSERT\b|\bUPDATE\b)/i',
            '/(\bOR\s+1\s*=\s*1\b|\bAND\s+1\s*=\s*1\b)/i',
            '/(\bEXEC\b|\bEXECUTE\b)/i',
        ];

        // XSS patterns
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
        ];

        // Path traversal patterns
        $pathTraversalPatterns = [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/\.\.\%2f/i',
            '/\.\.\%5c/i',
        ];

        $allPatterns = array_merge($sqlPatterns, $xssPatterns, $pathTraversalPatterns);
        $requestData = json_encode($request->all());

        foreach ($allPatterns as $pattern) {
            if (preg_match($pattern, $requestData)) {
                return true;
            }
        }

        // Check User-Agent for suspicious patterns
        $userAgent = $request->userAgent();
        $suspiciousAgents = [
            'sqlmap',
            'nikto',
            'nmap',
            'masscan',
            'nessus',
            'openvas',
        ];

        foreach ($suspiciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize request input
     */
    protected function sanitizeRequest(Request $request): void
    {
        // Basic input sanitization for common attacks
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Remove null bytes
                $value = str_replace("\0", '', $value);
                
                // Trim whitespace
                $value = trim($value);
                
                // Basic HTML entity encoding for display safety
                // Note: This is basic sanitization - specific validation should be done in controllers
                if (strlen($value) > 0) {
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
                }
            }
        });

        // Replace the request input
        $request->replace($input);
    }

    /**
     * Add security headers to response
     */
    protected function addSecurityHeaders(JsonResponse $response): void
    {
        $response->headers->add([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'none';",
            'X-API-Version' => '1.0',
            'X-Request-ID' => request()->header('X-Request-ID', uniqid()),
        ]);

        // Add CORS headers if needed
        if (config('cors.exposed_headers')) {
            $response->headers->add([
                'Access-Control-Expose-Headers' => implode(', ', config('cors.exposed_headers')),
            ]);
        }
    }

    /**
     * Log security threat
     */
    protected function logSecurityThreat(Request $request): void
    {
        Log::warning('API Security Threat Detected', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'input' => $request->all(),
            'user_id' => $request->user()?->id,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log API request for monitoring
     */
    protected function logApiRequest(Request $request, $response): void
    {
        // Only log in production or when debugging is enabled
        if (!app()->environment('production') && !config('app.debug')) {
            return;
        }

        $statusCode = $response instanceof JsonResponse ? $response->getStatusCode() : 200;
        
        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'enterprise_id' => $request->user()?->enterprise_id,
            'status_code' => $statusCode,
            'response_time' => microtime(true) - LARAVEL_START,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Build security error response
     */
    protected function buildSecurityErrorResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => 'SECURITY_ERROR',
                'details' => 'Request blocked by security policies',
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => request()->header('X-Request-ID', uniqid()),
            ],
        ], 403);
    }
}
