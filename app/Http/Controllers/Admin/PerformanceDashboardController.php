<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use App\Services\QueryOptimizationService;
use App\Services\CacheWarmupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerformanceDashboardController extends Controller
{
    protected $cacheService;
    protected $queryOptimizationService;
    protected $cacheWarmupService;

    public function __construct(
        CacheService $cacheService,
        QueryOptimizationService $queryOptimizationService,
        CacheWarmupService $cacheWarmupService
    ) {
        $this->cacheService = $cacheService;
        $this->queryOptimizationService = $queryOptimizationService;
        $this->cacheWarmupService = $cacheWarmupService;
    }

    /**
     * Show performance dashboard
     */
    public function index(Request $request)
    {
        $timeRange = $request->get('range', '24h');
        
        $dashboardData = $this->cacheService->remember(
            "performance_dashboard_{$timeRange}",
            function () use ($timeRange) {
                return $this->buildDashboardData($timeRange);
            },
            300 // 5 minutes
        );

        return view('admin.performance.dashboard', [
            'data' => $dashboardData,
            'timeRange' => $timeRange
        ]);
    }

    /**
     * Get performance metrics API
     */
    public function metrics(Request $request)
    {
        $timeRange = $request->get('range', '1h');
        $metric = $request->get('metric', 'response_time');
        
        $metrics = $this->getPerformanceMetrics($timeRange, $metric);
        
        return response()->json([
            'success' => true,
            'data' => $metrics,
            'meta' => [
                'range' => $timeRange,
                'metric' => $metric,
                'generated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get cache statistics
     */
    public function cacheStats()
    {
        $stats = $this->cacheService->getStatistics();
        $warmupStatus = $this->cacheWarmupService->getWarmupStatus();
        
        return response()->json([
            'success' => true,
            'data' => [
                'cache_stats' => $stats,
                'warmup_status' => $warmupStatus,
                'cache_keys_count' => $this->getCacheKeysCount(),
                'memory_usage' => $this->getCacheMemoryUsage()
            ]
        ]);
    }

    /**
     * Get slow queries report
     */
    public function slowQueries(Request $request)
    {
        $limit = $request->get('limit', 20);
        $timeRange = $request->get('range', '24h');
        
        $slowQueries = $this->queryOptimizationService->getSlowQueries([
            'limit' => $limit,
            'time_range' => $timeRange,
            'include_analysis' => true
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $slowQueries
        ]);
    }

    /**
     * Trigger cache warmup
     */
    public function warmupCache(Request $request)
    {
        $categories = $request->get('categories', []);
        
        if (empty($categories)) {
            // Warm up everything
            $result = $this->cacheWarmupService->warmupAll();
        } else {
            // Queue specific categories
            $jobId = $this->cacheWarmupService->queueWarmup($categories);
            $result = [
                'status' => 'queued',
                'job_id' => $jobId,
                'categories' => $categories
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Clear cache
     */
    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'all');
        $enterpriseId = auth()->user()->enterprise_id ?? null;
        
        try {
            switch ($type) {
                case 'all':
                    Cache::flush();
                    $message = 'All cache cleared successfully';
                    break;
                    
                case 'enterprise':
                    if ($enterpriseId) {
                        $this->cacheService->invalidateEnterprise($enterpriseId);
                        $message = "Enterprise {$enterpriseId} cache cleared successfully";
                    } else {
                        throw new \Exception('No enterprise ID available');
                    }
                    break;
                    
                case 'users':
                    Cache::tags(['users'])->flush();
                    $message = 'User cache cleared successfully';
                    break;
                    
                case 'consultations':
                    Cache::tags(['consultations'])->flush();
                    $message = 'Consultation cache cleared successfully';
                    break;
                    
                default:
                    throw new \Exception('Invalid cache type');
            }
            
            Log::info('Cache cleared via dashboard', [
                'type' => $type,
                'user_id' => auth()->id(),
                'enterprise_id' => $enterpriseId
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Cache clear failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance alerts
     */
    public function alerts(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $alerts = DB::table('performance_alerts')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $alerts
        ]);
    }

    /**
     * Update performance alert thresholds
     */
    public function updateThresholds(Request $request)
    {
        $request->validate([
            'response_time_threshold' => 'required|numeric|min:100',
            'memory_threshold' => 'required|numeric|min:10',
            'query_count_threshold' => 'required|numeric|min:5'
        ]);
        
        // Store thresholds in cache/config
        $thresholds = [
            'response_time' => $request->response_time_threshold,
            'memory_usage' => $request->memory_threshold,
            'query_count' => $request->query_count_threshold,
            'updated_at' => now()->toISOString(),
            'updated_by' => auth()->id()
        ];
        
        Cache::put('performance_thresholds', $thresholds, now()->addDays(30));
        
        Log::info('Performance thresholds updated', $thresholds);
        
        return response()->json([
            'success' => true,
            'message' => 'Thresholds updated successfully',
            'data' => $thresholds
        ]);
    }

    /**
     * Export performance report
     */
    public function exportReport(Request $request)
    {
        $format = $request->get('format', 'json');
        $timeRange = $request->get('range', '24h');
        
        $reportData = [
            'generated_at' => now()->toISOString(),
            'time_range' => $timeRange,
            'performance_metrics' => $this->getPerformanceMetrics($timeRange),
            'cache_statistics' => $this->cacheService->getStatistics(),
            'slow_queries' => $this->queryOptimizationService->getSlowQueries(['limit' => 50]),
            'alerts' => DB::table('performance_alerts')
                ->where('created_at', '>=', $this->getTimeRangeStart($timeRange))
                ->orderBy('created_at', 'desc')
                ->get(),
            'system_info' => [
                'php_version' => phpversion(),
                'laravel_version' => app()->version(),
                'cache_driver' => config('cache.default'),
                'database_driver' => config('database.default')
            ]
        ];
        
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($reportData);
            case 'pdf':
                return $this->exportToPdf($reportData);
            default:
                return response()->json([
                    'success' => true,
                    'data' => $reportData
                ]);
        }
    }

    /**
     * Build dashboard data
     */
    protected function buildDashboardData(string $timeRange): array
    {
        $startTime = $this->getTimeRangeStart($timeRange);
        
        return [
            'overview' => [
                'avg_response_time' => $this->getAverageResponseTime($startTime),
                'total_requests' => $this->getTotalRequests($startTime),
                'cache_hit_rate' => $this->getCacheHitRate(),
                'slow_queries_count' => $this->getSlowQueriesCount($startTime),
                'alerts_count' => $this->getAlertsCount($startTime)
            ],
            'charts' => [
                'response_time_trend' => $this->getResponseTimeTrend($startTime),
                'request_volume' => $this->getRequestVolume($startTime),
                'cache_performance' => $this->getCachePerformanceChart($startTime),
                'top_slow_queries' => $this->getTopSlowQueries($startTime)
            ],
            'current_status' => [
                'cache_status' => $this->getCacheStatus(),
                'database_status' => $this->getDatabaseStatus(),
                'server_resources' => $this->getServerResources()
            ]
        ];
    }

    /**
     * Get performance metrics for a time range
     */
    protected function getPerformanceMetrics(string $timeRange, string $metric = null): array
    {
        $startTime = $this->getTimeRangeStart($timeRange);
        
        $query = DB::table('performance_metrics')
            ->where('created_at', '>=', $startTime);
        
        if ($metric) {
            switch ($metric) {
                case 'response_time':
                    return $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") as time, AVG(response_time) as value')
                        ->groupBy('time')
                        ->orderBy('time')
                        ->get()
                        ->toArray();
                case 'memory_usage':
                    return $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") as time, AVG(memory_usage) as value')
                        ->groupBy('time')
                        ->orderBy('time')
                        ->get()
                        ->toArray();
                case 'query_count':
                    return $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") as time, AVG(query_count) as value')
                        ->groupBy('time')
                        ->orderBy('time')
                        ->get()
                        ->toArray();
            }
        }
        
        return $query->orderBy('created_at', 'desc')->limit(100)->get()->toArray();
    }

    /**
     * Get time range start based on string
     */
    protected function getTimeRangeStart(string $timeRange): Carbon
    {
        switch ($timeRange) {
            case '1h':
                return now()->subHour();
            case '6h':
                return now()->subHours(6);
            case '24h':
                return now()->subDay();
            case '7d':
                return now()->subWeek();
            case '30d':
                return now()->subMonth();
            default:
                return now()->subDay();
        }
    }

    // Additional helper methods would be implemented here
    protected function getAverageResponseTime($startTime) { 
        return DB::table('performance_metrics')
            ->where('created_at', '>=', $startTime)
            ->avg('response_time') ?? 0;
    }
    
    protected function getTotalRequests($startTime) { 
        return DB::table('performance_metrics')
            ->where('created_at', '>=', $startTime)
            ->count();
    }
    
    protected function getCacheHitRate() { 
        $stats = $this->cacheService->getStatistics();
        return $stats['hit_rate'] ?? 0;
    }
    
    protected function getSlowQueriesCount($startTime) { 
        return DB::table('slow_query_log')
            ->where('created_at', '>=', $startTime)
            ->count();
    }
    
    protected function getAlertsCount($startTime) { 
        return DB::table('performance_alerts')
            ->where('created_at', '>=', $startTime)
            ->count();
    }

    // Placeholder methods for additional functionality
    protected function getResponseTimeTrend($startTime) { return []; }
    protected function getRequestVolume($startTime) { return []; }
    protected function getCachePerformanceChart($startTime) { return []; }
    protected function getTopSlowQueries($startTime) { return []; }
    protected function getCacheStatus() { return ['status' => 'healthy']; }
    protected function getDatabaseStatus() { return ['status' => 'healthy']; }
    protected function getServerResources() { return []; }
    protected function getCacheKeysCount() { return 0; }
    protected function getCacheMemoryUsage() { return 0; }
    protected function exportToCsv($data) { return response()->json(['message' => 'CSV export not implemented']); }
    protected function exportToPdf($data) { return response()->json(['message' => 'PDF export not implemented']); }
}
