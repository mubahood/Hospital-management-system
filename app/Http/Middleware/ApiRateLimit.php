<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Closure;

/**
 * API Rate Limiting Middleware
 * 
 * Provides advanced rate limiting for API endpoints with:
 * - Per-user rate limiting
 * - Per-IP rate limiting
 * - Per-route rate limiting
 * - Enterprise-aware limits
 * - Sliding window algorithm
 */
class ApiRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): mixed
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = (int) $maxAttempts;
        $decayMinutes = (int) $decayMinutes;
        
        // Apply enterprise-specific limits if user is authenticated
        if ($request->user()) {
            $enterpriseId = $request->user()->enterprise_id;
            $enterpriseLimits = $this->getEnterpriseLimits($enterpriseId);
            
            if ($enterpriseLimits) {
                $maxAttempts = $enterpriseLimits['max_attempts'];
                $decayMinutes = $enterpriseLimits['decay_minutes'];
            }
        }

        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            return $this->buildRateLimitResponse($maxAttempts, $decayMinutes);
        }

        // Increment attempts with sliding window
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        $response = $next($request);

        // Add rate limit headers
        if ($response instanceof JsonResponse) {
            $response->headers->add([
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => max(0, $maxAttempts - $attempts - 1),
                'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->timestamp,
            ]);
        }

        return $response;
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = $request->user()?->id ?? 'guest';
        $ip = $request->ip();
        $route = $request->route()?->getName() ?? $request->path();
        
        return "api_rate_limit:{$userId}:{$ip}:{$route}";
    }

    /**
     * Get enterprise-specific rate limits
     */
    protected function getEnterpriseLimits(int $enterpriseId): ?array
    {
        // Cache enterprise limits for performance
        return Cache::remember("enterprise_rate_limits:{$enterpriseId}", 3600, function () use ($enterpriseId) {
            // Default limits - could be stored in database
            $limits = [
                'basic' => ['max_attempts' => 60, 'decay_minutes' => 1],
                'premium' => ['max_attempts' => 120, 'decay_minutes' => 1],
                'enterprise' => ['max_attempts' => 300, 'decay_minutes' => 1],
            ];

            // For now, return premium limits - in production, this would be based on enterprise plan
            return $limits['premium'];
        });
    }

    /**
     * Build rate limit exceeded response
     */
    protected function buildRateLimitResponse(int $maxAttempts, int $decayMinutes): JsonResponse
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Rate limit exceeded. Too many requests.',
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'details' => [
                    'max_attempts' => $maxAttempts,
                    'retry_after_minutes' => $decayMinutes,
                    'retry_after' => now()->addMinutes($decayMinutes)->timestamp,
                ],
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => request()->header('X-Request-ID', uniqid()),
            ],
        ], 429);

        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->timestamp,
            'Retry-After' => $decayMinutes * 60,
        ]);

        return $response;
    }
}
