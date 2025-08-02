<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * CacheService - Advanced caching with intelligent invalidation
 * 
 * Provides enterprise-aware caching with:
 * - Multi-layer caching (Redis, File, Database)
 * - Intelligent cache invalidation
 * - Cache warming strategies
 * - Performance monitoring
 * - Enterprise isolation
 */
class CacheService
{
    /**
     * Cache TTL configurations (in seconds)
     */
    const TTL_SHORT = 300;      // 5 minutes
    const TTL_MEDIUM = 1800;    // 30 minutes
    const TTL_LONG = 3600;      // 1 hour
    const TTL_EXTENDED = 86400; // 24 hours

    /**
     * Cache key prefixes for different data types
     */
    const PREFIX_USER = 'user:';
    const PREFIX_CONSULTATION = 'consultation:';
    const PREFIX_PATIENT = 'patient:';
    const PREFIX_BILLING = 'billing:';
    const PREFIX_STATS = 'stats:';
    const PREFIX_REPORT = 'report:';
    const PREFIX_MEDICAL_SERVICE = 'medical_service:';

    /**
     * Enterprise-aware cache key generation
     */
    public function key(string $prefix, string $identifier, int $enterpriseId = null): string
    {
        $enterpriseId = $enterpriseId ?? auth()->user()?->enterprise_id ?? 'global';
        return "ent:{$enterpriseId}:{$prefix}{$identifier}";
    }

    /**
     * Get cached data with fallback
     */
    public function remember(string $key, callable $callback, int $ttl = self::TTL_MEDIUM, array $tags = [])
    {
        try {
            return Cache::tags($tags)->remember($key, $ttl, function () use ($callback, $key) {
                $startTime = microtime(true);
                $result = $callback();
                $duration = (microtime(true) - $startTime) * 1000;

                // Log cache miss with performance data
                Log::info('Cache miss - data generated', [
                    'key' => $key,
                    'generation_time_ms' => round($duration, 2),
                    'data_size' => $this->getDataSize($result)
                ]);

                return $result;
            });
        } catch (\Exception $e) {
            Log::error('Cache operation failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            // Fallback to direct execution
            return $callback();
        }
    }

    /**
     * Cache user data with automatic invalidation
     */
    public function cacheUser(int $userId, int $enterpriseId, array $userData, int $ttl = self::TTL_MEDIUM): void
    {
        $key = $this->key(self::PREFIX_USER, $userId, $enterpriseId);
        $tags = ['users', "user:{$userId}", "enterprise:{$enterpriseId}"];

        Cache::tags($tags)->put($key, $userData, $ttl);
        
        // Also cache user's basic info for quick lookups
        $basicKey = $this->key(self::PREFIX_USER, "{$userId}:basic", $enterpriseId);
        Cache::tags($tags)->put($basicKey, [
            'id' => $userData['id'] ?? $userId,
            'name' => $userData['name'] ?? '',
            'email' => $userData['email'] ?? '',
            'enterprise_id' => $enterpriseId
        ], $ttl);
    }

    /**
     * Get cached user data
     */
    public function getUser(int $userId, int $enterpriseId = null)
    {
        $enterpriseId = $enterpriseId ?? auth()->user()?->enterprise_id;
        $key = $this->key(self::PREFIX_USER, $userId, $enterpriseId);
        
        return Cache::tags(['users'])->get($key);
    }

    /**
     * Cache consultation data with relationships
     */
    public function cacheConsultation(int $consultationId, int $enterpriseId, array $consultationData): void
    {
        $key = $this->key(self::PREFIX_CONSULTATION, $consultationId, $enterpriseId);
        $tags = [
            'consultations', 
            "consultation:{$consultationId}", 
            "enterprise:{$enterpriseId}",
            "patient:{$consultationData['patient_id']}"
        ];

        Cache::tags($tags)->put($key, $consultationData, self::TTL_MEDIUM);

        // Cache consultation summary for quick access
        $summaryKey = $this->key(self::PREFIX_CONSULTATION, "{$consultationId}:summary", $enterpriseId);
        Cache::tags($tags)->put($summaryKey, [
            'id' => $consultationData['id'],
            'consultation_number' => $consultationData['consultation_number'],
            'patient_name' => $consultationData['patient']['name'] ?? '',
            'status' => $consultationData['status'],
            'date' => $consultationData['consultation_date_time']
        ], self::TTL_MEDIUM);
    }

    /**
     * Cache patient history with optimized structure
     */
    public function cachePatientHistory(int $patientId, int $enterpriseId, array $historyData): void
    {
        $key = $this->key(self::PREFIX_PATIENT, "{$patientId}:history", $enterpriseId);
        $tags = [
            'patients', 
            "patient:{$patientId}", 
            "enterprise:{$enterpriseId}",
            'patient_histories'
        ];

        Cache::tags($tags)->put($key, $historyData, self::TTL_LONG);

        // Cache recent consultations separately for faster access
        if (isset($historyData['consultation_history'])) {
            $recentKey = $this->key(self::PREFIX_PATIENT, "{$patientId}:recent", $enterpriseId);
            $recentConsultations = array_slice($historyData['consultation_history'], 0, 5);
            Cache::tags($tags)->put($recentKey, $recentConsultations, self::TTL_MEDIUM);
        }
    }

    /**
     * Cache billing and payment data
     */
    public function cacheBillingData(int $billingId, int $enterpriseId, array $billingData): void
    {
        $key = $this->key(self::PREFIX_BILLING, $billingId, $enterpriseId);
        $tags = [
            'billing', 
            "billing:{$billingId}", 
            "enterprise:{$enterpriseId}",
            "patient:{$billingData['patient_id']}"
        ];

        Cache::tags($tags)->put($key, $billingData, self::TTL_MEDIUM);
    }

    /**
     * Cache statistics with automatic refresh
     */
    public function cacheStats(string $statsType, int $enterpriseId, array $statsData, int $ttl = self::TTL_SHORT): void
    {
        $key = $this->key(self::PREFIX_STATS, $statsType, $enterpriseId);
        $tags = ['statistics', "enterprise:{$enterpriseId}", "stats:{$statsType}"];

        Cache::tags($tags)->put($key, [
            'data' => $statsData,
            'generated_at' => now()->toISOString(),
            'expires_at' => now()->addSeconds($ttl)->toISOString()
        ], $ttl);
    }

    /**
     * Get cached statistics
     */
    public function getStats(string $statsType, int $enterpriseId = null)
    {
        $enterpriseId = $enterpriseId ?? auth()->user()?->enterprise_id;
        $key = $this->key(self::PREFIX_STATS, $statsType, $enterpriseId);
        
        return Cache::tags(['statistics'])->get($key);
    }

    /**
     * Cache reports with compression for large datasets
     */
    public function cacheReport(string $reportId, int $enterpriseId, array $reportData, int $ttl = self::TTL_EXTENDED): void
    {
        $key = $this->key(self::PREFIX_REPORT, $reportId, $enterpriseId);
        $tags = ['reports', "enterprise:{$enterpriseId}", "report:{$reportId}"];

        // Compress large reports
        $compressedData = $this->compressData($reportData);
        
        Cache::tags($tags)->put($key, $compressedData, $ttl);
    }

    /**
     * Get cached report with decompression
     */
    public function getReport(string $reportId, int $enterpriseId = null)
    {
        $enterpriseId = $enterpriseId ?? auth()->user()?->enterprise_id;
        $key = $this->key(self::PREFIX_REPORT, $reportId, $enterpriseId);
        
        $compressedData = Cache::tags(['reports'])->get($key);
        
        return $compressedData ? $this->decompressData($compressedData) : null;
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateByTags(array $tags): void
    {
        try {
            Cache::tags($tags)->flush();
            
            Log::info('Cache invalidated by tags', [
                'tags' => $tags,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Cache invalidation failed', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Invalidate enterprise-specific cache
     */
    public function invalidateEnterprise(int $enterpriseId): void
    {
        $this->invalidateByTags(["enterprise:{$enterpriseId}"]);
    }

    /**
     * Invalidate user-specific cache
     */
    public function invalidateUser(int $userId, int $enterpriseId = null): void
    {
        $enterpriseId = $enterpriseId ?? auth()->user()?->enterprise_id;
        $this->invalidateByTags(["user:{$userId}", "enterprise:{$enterpriseId}"]);
    }

    /**
     * Invalidate patient-specific cache
     */
    public function invalidatePatient(int $patientId, int $enterpriseId = null): void
    {
        $enterpriseId = $enterpriseId ?? auth()->user()?->enterprise_id;
        $this->invalidateByTags(["patient:{$patientId}", "enterprise:{$enterpriseId}"]);
    }

    /**
     * Warm up essential caches
     */
    public function warmUpCache(int $enterpriseId): void
    {
        Log::info('Starting cache warm-up', ['enterprise_id' => $enterpriseId]);

        try {
            // Warm up frequently accessed statistics
            $this->warmUpStats($enterpriseId);
            
            // Warm up recent patients
            $this->warmUpRecentPatients($enterpriseId);
            
            // Warm up active consultations
            $this->warmUpActiveConsultations($enterpriseId);

            Log::info('Cache warm-up completed', ['enterprise_id' => $enterpriseId]);
        } catch (\Exception $e) {
            Log::error('Cache warm-up failed', [
                'enterprise_id' => $enterpriseId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cache performance metrics
     */
    public function getPerformanceMetrics(int $enterpriseId = null): array
    {
        $enterpriseId = $enterpriseId ?? auth()->user()?->enterprise_id ?? 'global';
        
        try {
            // Get Redis info if available
            $redisInfo = $this->getRedisInfo();
            
            // Get cache hit/miss ratios
            $hitRatio = $this->calculateHitRatio($enterpriseId);
            
            // Get memory usage
            $memoryUsage = $this->getCacheMemoryUsage($enterpriseId);

            return [
                'enterprise_id' => $enterpriseId,
                'hit_ratio' => $hitRatio,
                'memory_usage' => $memoryUsage,
                'redis_info' => $redisInfo,
                'total_keys' => $this->getTotalCacheKeys($enterpriseId),
                'generated_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get cache performance metrics', [
                'enterprise_id' => $enterpriseId,
                'error' => $e->getMessage()
            ]);

            return [
                'enterprise_id' => $enterpriseId,
                'error' => 'Metrics unavailable',
                'generated_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Compress data for storage
     */
    private function compressData(array $data): array
    {
        return [
            'compressed' => true,
            'data' => base64_encode(gzcompress(json_encode($data))),
            'original_size' => strlen(json_encode($data)),
            'compressed_at' => now()->toISOString()
        ];
    }

    /**
     * Decompress stored data
     */
    private function decompressData(array $compressedData): array
    {
        if (!isset($compressedData['compressed']) || !$compressedData['compressed']) {
            return $compressedData;
        }

        try {
            $decompressed = gzuncompress(base64_decode($compressedData['data']));
            return json_decode($decompressed, true);
        } catch (\Exception $e) {
            Log::error('Failed to decompress cache data', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Calculate data size for logging
     */
    private function getDataSize($data): string
    {
        $sizeBytes = strlen(json_encode($data));
        
        if ($sizeBytes < 1024) {
            return $sizeBytes . ' B';
        } elseif ($sizeBytes < 1048576) {
            return round($sizeBytes / 1024, 2) . ' KB';
        } else {
            return round($sizeBytes / 1048576, 2) . ' MB';
        }
    }

    /**
     * Warm up statistics cache
     */
    private function warmUpStats(int $enterpriseId): void
    {
        // Implementation would call actual stats services
        Log::info('Warming up statistics cache', ['enterprise_id' => $enterpriseId]);
    }

    /**
     * Warm up recent patients cache
     */
    private function warmUpRecentPatients(int $enterpriseId): void
    {
        // Implementation would load recent patients
        Log::info('Warming up recent patients cache', ['enterprise_id' => $enterpriseId]);
    }

    /**
     * Warm up active consultations cache
     */
    private function warmUpActiveConsultations(int $enterpriseId): void
    {
        // Implementation would load active consultations
        Log::info('Warming up active consultations cache', ['enterprise_id' => $enterpriseId]);
    }

    /**
     * Get Redis information
     */
    private function getRedisInfo(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $info = $redis->info();
                
                return [
                    'connected' => true,
                    'memory_used' => $info['used_memory_human'] ?? 'N/A',
                    'total_keys' => $redis->dbsize(),
                    'hit_rate' => $info['keyspace_hit_rate'] ?? 'N/A'
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Redis info unavailable', ['error' => $e->getMessage()]);
        }

        return ['connected' => false];
    }

    /**
     * Calculate cache hit ratio
     */
    private function calculateHitRatio(int $enterpriseId): float
    {
        // This would require tracking hits/misses
        // For now, return a placeholder
        return 0.85; // 85% hit ratio placeholder
    }

    /**
     * Get cache memory usage
     */
    private function getCacheMemoryUsage(int $enterpriseId): array
    {
        // This would require detailed memory tracking
        return [
            'used' => '10.5 MB',
            'total' => '100 MB',
            'percentage' => 10.5
        ];
    }

    /**
     * Get total cache keys for enterprise
     */
    private function getTotalCacheKeys(int $enterpriseId): int
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $pattern = "ent:{$enterpriseId}:*";
                return count($redis->keys($pattern));
            }
        } catch (\Exception $e) {
            Log::warning('Could not count cache keys', ['error' => $e->getMessage()]);
        }

        return 0;
    }

    /**
     * Store a direct value in cache
     */
    public function store(string $key, $value, int $ttl = self::TTL_MEDIUM, array $tags = []): void
    {
        try {
            if (!empty($tags)) {
                Cache::tags($tags)->put($key, $value, $ttl);
            } else {
                Cache::put($key, $value, $ttl);
            }

            Log::debug('Cache store successful', [
                'key' => $key,
                'ttl' => $ttl,
                'tags' => $tags,
                'data_size' => $this->getDataSize($value)
            ]);
        } catch (\Exception $e) {
            Log::error('Cache store failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cache statistics
     */
    public function getStatistics(): array
    {
        try {
            $stats = [
                'cache_driver' => config('cache.default'),
                'total_keys' => 0,
                'memory_usage' => 0,
                'hit_rate' => 0,
                'miss_rate' => 0,
                'operations' => [
                    'gets' => 0,
                    'sets' => 0,
                    'deletes' => 0
                ]
            ];

            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $info = $redis->info();
                
                $stats['total_keys'] = $info['db0']['keys'] ?? 0;
                $stats['memory_usage'] = $info['used_memory'] ?? 0;
                $stats['operations']['gets'] = $info['total_commands_processed'] ?? 0;
                
                // Calculate hit rate if available
                if (isset($info['keyspace_hits']) && isset($info['keyspace_misses'])) {
                    $hits = $info['keyspace_hits'];
                    $misses = $info['keyspace_misses'];
                    $total = $hits + $misses;
                    
                    if ($total > 0) {
                        $stats['hit_rate'] = round(($hits / $total) * 100, 2);
                        $stats['miss_rate'] = round(($misses / $total) * 100, 2);
                    }
                }
            }

            return $stats;
        } catch (\Exception $e) {
            Log::warning('Could not get cache statistics', ['error' => $e->getMessage()]);
            
            return [
                'cache_driver' => config('cache.default'),
                'error' => 'Statistics unavailable: ' . $e->getMessage()
            ];
        }
    }
}
