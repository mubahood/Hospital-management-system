<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CacheService;

/**
 * PerformanceMonitoring Middleware
 * 
 * Monitors application performance including:
 * - Request response times
 * - Memory usage tracking
 * - Database query monitoring
 * - Cache hit/miss ratios
 * - Resource utilization
 */
class PerformanceMonitoring
{
    /**
     * Performance thresholds for alerting
     */
    const SLOW_REQUEST_THRESHOLD = 2000; // 2 seconds in milliseconds
    const HIGH_MEMORY_THRESHOLD = 128; // 128 MB
    const HIGH_QUERY_COUNT_THRESHOLD = 50;

    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next)
    {
        // Start performance monitoring
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $startQueries = $this->getQueryCount();

        // Enable query logging for this request
        DB::enableQueryLog();

        // Process the request
        $response = $next($request);

        // Calculate performance metrics
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $endQueries = $this->getQueryCount();

        $metrics = [
            'duration_ms' => round(($endTime - $startTime) * 1000, 2),
            'memory_used_mb' => round(($endMemory - $startMemory) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'query_count' => $endQueries - $startQueries,
            'response_size_kb' => round(strlen($response->getContent()) / 1024, 2),
            'status_code' => $response->getStatusCode()
        ];

        // Log performance data
        $this->logPerformanceMetrics($request, $metrics);

        // Check for performance issues
        $this->checkPerformanceThresholds($request, $metrics);

        // Update performance statistics
        $this->updatePerformanceStats($request, $metrics);

        // Add performance headers to response (in debug mode)
        if (config('app.debug')) {
            $this->addPerformanceHeaders($response, $metrics);
        }

        return $response;
    }

    /**
     * Log performance metrics
     */
    protected function logPerformanceMetrics(Request $request, array $metrics): void
    {
        $logData = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route' => $request->route()?->getName(),
            'user_id' => auth()->id(),
            'enterprise_id' => auth()->user()?->enterprise_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metrics' => $metrics,
            'timestamp' => now()->toISOString()
        ];

        // Log to performance channel
        Log::channel('performance')->info('Request performance', $logData);

        // Store in database for analysis
        $this->storePerformanceMetrics($logData);
    }

    /**
     * Check performance thresholds and alert if exceeded
     */
    protected function checkPerformanceThresholds(Request $request, array $metrics): void
    {
        $alerts = [];

        // Check response time
        if ($metrics['duration_ms'] > self::SLOW_REQUEST_THRESHOLD) {
            $alerts[] = [
                'type' => 'slow_request',
                'threshold' => self::SLOW_REQUEST_THRESHOLD,
                'actual' => $metrics['duration_ms'],
                'severity' => $metrics['duration_ms'] > 5000 ? 'critical' : 'warning'
            ];
        }

        // Check memory usage
        if ($metrics['peak_memory_mb'] > self::HIGH_MEMORY_THRESHOLD) {
            $alerts[] = [
                'type' => 'high_memory',
                'threshold' => self::HIGH_MEMORY_THRESHOLD,
                'actual' => $metrics['peak_memory_mb'],
                'severity' => $metrics['peak_memory_mb'] > 256 ? 'critical' : 'warning'
            ];
        }

        // Check query count
        if ($metrics['query_count'] > self::HIGH_QUERY_COUNT_THRESHOLD) {
            $alerts[] = [
                'type' => 'high_query_count',
                'threshold' => self::HIGH_QUERY_COUNT_THRESHOLD,
                'actual' => $metrics['query_count'],
                'severity' => $metrics['query_count'] > 100 ? 'critical' : 'warning'
            ];
        }

        // Log alerts
        if (!empty($alerts)) {
            $this->logPerformanceAlerts($request, $alerts, $metrics);
        }
    }

    /**
     * Update performance statistics cache
     */
    protected function updatePerformanceStats(Request $request, array $metrics): void
    {
        $enterpriseId = auth()->user()?->enterprise_id ?? 'global';
        
        try {
            // Update hourly statistics
            $this->updateHourlyStats($enterpriseId, $metrics);
            
            // Update daily statistics
            $this->updateDailyStats($enterpriseId, $metrics);
            
            // Update route-specific statistics
            $this->updateRouteStats($request, $enterpriseId, $metrics);
            
        } catch (\Exception $e) {
            Log::error('Failed to update performance stats', [
                'error' => $e->getMessage(),
                'enterprise_id' => $enterpriseId
            ]);
        }
    }

    /**
     * Add performance headers to response
     */
    protected function addPerformanceHeaders($response, array $metrics): void
    {
        $response->headers->set('X-Response-Time', $metrics['duration_ms'] . 'ms');
        $response->headers->set('X-Memory-Usage', $metrics['memory_used_mb'] . 'MB');
        $response->headers->set('X-Query-Count', $metrics['query_count']);
        $response->headers->set('X-Peak-Memory', $metrics['peak_memory_mb'] . 'MB');
    }

    /**
     * Get current database query count
     */
    protected function getQueryCount(): int
    {
        return count(DB::getQueryLog());
    }

    /**
     * Store performance metrics in database
     */
    protected function storePerformanceMetrics(array $logData): void
    {
        try {
            DB::table('performance_metrics')->insert([
                'url' => $logData['url'],
                'method' => $logData['method'],
                'route_name' => $logData['route'],
                'user_id' => $logData['user_id'],
                'enterprise_id' => $logData['enterprise_id'],
                'ip_address' => $logData['ip'],
                'duration_ms' => $logData['metrics']['duration_ms'],
                'memory_used_mb' => $logData['metrics']['memory_used_mb'],
                'peak_memory_mb' => $logData['metrics']['peak_memory_mb'],
                'query_count' => $logData['metrics']['query_count'],
                'response_size_kb' => $logData['metrics']['response_size_kb'],
                'status_code' => $logData['metrics']['status_code'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store performance metrics', [
                'error' => $e->getMessage(),
                'metrics' => $logData
            ]);
        }
    }

    /**
     * Log performance alerts
     */
    protected function logPerformanceAlerts(Request $request, array $alerts, array $metrics): void
    {
        $alertData = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route' => $request->route()?->getName(),
            'user_id' => auth()->id(),
            'enterprise_id' => auth()->user()?->enterprise_id,
            'alerts' => $alerts,
            'metrics' => $metrics,
            'timestamp' => now()->toISOString()
        ];

        $severity = collect($alerts)->max('severity');
        
        if ($severity === 'critical') {
            Log::critical('Critical performance issue detected', $alertData);
        } else {
            Log::warning('Performance threshold exceeded', $alertData);
        }

        // Store alerts for dashboard
        $this->storePerformanceAlert($alertData);
    }

    /**
     * Store performance alert in database
     */
    protected function storePerformanceAlert(array $alertData): void
    {
        try {
            DB::table('performance_alerts')->insert([
                'url' => $alertData['url'],
                'method' => $alertData['method'],
                'route_name' => $alertData['route'],
                'user_id' => $alertData['user_id'],
                'enterprise_id' => $alertData['enterprise_id'],
                'alert_type' => collect($alertData['alerts'])->pluck('type')->implode(','),
                'severity' => collect($alertData['alerts'])->max('severity'),
                'metrics' => json_encode($alertData['metrics']),
                'alerts' => json_encode($alertData['alerts']),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store performance alert', [
                'error' => $e->getMessage(),
                'alert_data' => $alertData
            ]);
        }
    }

    /**
     * Update hourly performance statistics
     */
    protected function updateHourlyStats(int $enterpriseId, array $metrics): void
    {
        $hour = now()->format('Y-m-d H:00:00');
        $key = $this->cacheService->key('stats', "hourly:{$hour}", $enterpriseId);
        
        $stats = $this->cacheService->remember($key, function () {
            return [
                'request_count' => 0,
                'total_duration' => 0,
                'total_memory' => 0,
                'total_queries' => 0,
                'error_count' => 0
            ];
        }, 3600);

        $stats['request_count']++;
        $stats['total_duration'] += $metrics['duration_ms'];
        $stats['total_memory'] += $metrics['memory_used_mb'];
        $stats['total_queries'] += $metrics['query_count'];
        
        if ($metrics['status_code'] >= 400) {
            $stats['error_count']++;
        }

        $this->cacheService->cacheStats("hourly:{$hour}", $enterpriseId, $stats, 3600);
    }

    /**
     * Update daily performance statistics
     */
    protected function updateDailyStats(int $enterpriseId, array $metrics): void
    {
        $date = now()->format('Y-m-d');
        $key = $this->cacheService->key('stats', "daily:{$date}", $enterpriseId);
        
        $stats = $this->cacheService->remember($key, function () {
            return [
                'request_count' => 0,
                'avg_duration' => 0,
                'avg_memory' => 0,
                'avg_queries' => 0,
                'error_rate' => 0
            ];
        }, 86400);

        // Update daily averages
        $newCount = $stats['request_count'] + 1;
        $stats['avg_duration'] = (($stats['avg_duration'] * $stats['request_count']) + $metrics['duration_ms']) / $newCount;
        $stats['avg_memory'] = (($stats['avg_memory'] * $stats['request_count']) + $metrics['memory_used_mb']) / $newCount;
        $stats['avg_queries'] = (($stats['avg_queries'] * $stats['request_count']) + $metrics['query_count']) / $newCount;
        
        if ($metrics['status_code'] >= 400) {
            $stats['error_rate'] = (($stats['error_rate'] * $stats['request_count']) + 1) / $newCount;
        } else {
            $stats['error_rate'] = ($stats['error_rate'] * $stats['request_count']) / $newCount;
        }
        
        $stats['request_count'] = $newCount;

        $this->cacheService->cacheStats("daily:{$date}", $enterpriseId, $stats, 86400);
    }

    /**
     * Update route-specific performance statistics
     */
    protected function updateRouteStats(Request $request, int $enterpriseId, array $metrics): void
    {
        $routeName = $request->route()?->getName();
        if (!$routeName) return;

        $key = $this->cacheService->key('stats', "route:{$routeName}", $enterpriseId);
        
        $stats = $this->cacheService->remember($key, function () {
            return [
                'hit_count' => 0,
                'avg_duration' => 0,
                'avg_memory' => 0,
                'avg_queries' => 0,
                'last_accessed' => null
            ];
        }, 3600);

        $newCount = $stats['hit_count'] + 1;
        $stats['avg_duration'] = (($stats['avg_duration'] * $stats['hit_count']) + $metrics['duration_ms']) / $newCount;
        $stats['avg_memory'] = (($stats['avg_memory'] * $stats['hit_count']) + $metrics['memory_used_mb']) / $newCount;
        $stats['avg_queries'] = (($stats['avg_queries'] * $stats['hit_count']) + $metrics['query_count']) / $newCount;
        $stats['hit_count'] = $newCount;
        $stats['last_accessed'] = now()->toISOString();

        $this->cacheService->cacheStats("route:{$routeName}", $enterpriseId, $stats, 3600);
    }
}
