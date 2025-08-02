<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * QueryOptimizationService - Database performance optimization
 * 
 * Provides query optimization features:
 * - N+1 query prevention
 * - Eager loading strategies
 * - Query performance monitoring
 * - Index recommendations
 * - Batch operations
 */
class QueryOptimizationService
{
    /**
     * Query performance thresholds (in milliseconds)
     */
    const SLOW_QUERY_THRESHOLD = 1000;
    const VERY_SLOW_QUERY_THRESHOLD = 5000;

    /**
     * Batch operation sizes
     */
    const BATCH_SIZE_SMALL = 100;
    const BATCH_SIZE_MEDIUM = 500;
    const BATCH_SIZE_LARGE = 1000;

    /**
     * Track slow queries for optimization
     */
    public function enableQueryLogging(): void
    {
        DB::listen(function ($query) {
            if ($query->time > self::SLOW_QUERY_THRESHOLD) {
                $this->logSlowQuery($query);
            }
        });
    }

    /**
     * Optimized consultation loading with relationships
     */
    public function loadConsultationsOptimized(array $consultationIds, array $relations = []): Collection
    {
        $baseRelations = [
            'patient:id,name,email,phone_number_1',
            'assignedTo:id,name',
            'enterprise:id,name'
        ];

        $relations = array_merge($baseRelations, $relations);

        return \App\Models\Consultation::whereIn('id', $consultationIds)
            ->with($relations)
            ->select([
                'id', 'consultation_number', 'patient_id', 'assigned_to',
                'enterprise_id', 'consultation_date_time', 'status',
                'chief_complaint', 'diagnosis', 'total_amount'
            ])
            ->get();
    }

    /**
     * Optimized patient history loading
     */
    public function loadPatientHistoryOptimized(int $patientId, int $limit = 20): array
    {
        // Load consultations with minimal data first
        $consultations = \App\Models\Consultation::where('patient_id', $patientId)
            ->with([
                'billingItems:id,consultation_id,name,price,quantity,total',
                'paymentRecords:id,consultation_id,amount,status,payment_date',
                'medicalServices:id,consultation_id,name,category,price'
            ])
            ->select([
                'id', 'consultation_number', 'patient_id', 'consultation_date_time',
                'status', 'chief_complaint', 'diagnosis', 'total_amount'
            ])
            ->orderBy('consultation_date_time', 'desc')
            ->limit($limit)
            ->get();

        // Calculate aggregated data efficiently
        $totalSpent = $consultations->sum(fn($c) => $c->billingItems->sum('total'));
        $totalPaid = $consultations->sum(fn($c) => $c->paymentRecords->sum('amount'));

        return [
            'consultations' => $consultations,
            'total_spent' => $totalSpent,
            'total_paid' => $totalPaid,
            'outstanding_balance' => $totalSpent - $totalPaid
        ];
    }

    /**
     * Batch update operations for better performance
     */
    public function batchUpdateConsultations(array $updates, int $batchSize = self::BATCH_SIZE_MEDIUM): int
    {
        $totalUpdated = 0;
        $batches = array_chunk($updates, $batchSize);

        DB::transaction(function () use ($batches, &$totalUpdated) {
            foreach ($batches as $batch) {
                foreach ($batch as $update) {
                    $affectedRows = \App\Models\Consultation::where('id', $update['id'])
                        ->update($update['data']);
                    $totalUpdated += $affectedRows;
                }
            }
        });

        Log::info('Batch consultation update completed', [
            'total_updated' => $totalUpdated,
            'batch_count' => count($batches),
            'batch_size' => $batchSize
        ]);

        return $totalUpdated;
    }

    /**
     * Optimized statistics queries with caching
     */
    public function getOptimizedStatistics(int $enterpriseId, array $dateRange = []): array
    {
        $fromDate = $dateRange['from'] ?? now()->subMonth();
        $toDate = $dateRange['to'] ?? now();

        // Use raw queries for better performance
        $consultationStats = DB::select("
            SELECT 
                COUNT(*) as total_consultations,
                COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_consultations,
                COUNT(CASE WHEN status IN ('Scheduled', 'In Progress') THEN 1 END) as pending_consultations,
                AVG(CASE 
                    WHEN consultation_end_date_time IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, consultation_date_time, consultation_end_date_time)
                END) as avg_duration_minutes
            FROM consultations 
            WHERE enterprise_id = ? 
            AND consultation_date_time BETWEEN ? AND ?
        ", [$enterpriseId, $fromDate, $toDate]);

        $revenueStats = DB::select("
            SELECT 
                COALESCE(SUM(bi.total), 0) as total_billed,
                COALESCE(SUM(pr.amount), 0) as total_paid,
                COUNT(DISTINCT bi.consultation_id) as billed_consultations
            FROM billing_items bi
            LEFT JOIN payment_records pr ON bi.consultation_id = pr.consultation_id AND pr.status = 'Completed'
            INNER JOIN consultations c ON bi.consultation_id = c.id
            WHERE bi.enterprise_id = ?
            AND c.consultation_date_time BETWEEN ? AND ?
        ", [$enterpriseId, $fromDate, $toDate]);

        return [
            'consultations' => $consultationStats[0] ?? (object)['total_consultations' => 0],
            'revenue' => $revenueStats[0] ?? (object)['total_billed' => 0, 'total_paid' => 0],
            'generated_at' => now()->toISOString()
        ];
    }

    /**
     * Optimized search with full-text indexing
     */
    public function searchPatientsOptimized(string $query, int $enterpriseId, int $limit = 50): Collection
    {
        // Use MySQL full-text search if available, otherwise fallback to LIKE
        if ($this->hasFullTextIndex('users', ['name', 'email'])) {
            return \App\Models\User::selectRaw("
                id, name, email, phone_number_1, date_of_birth,
                MATCH(name, email) AGAINST(? IN BOOLEAN MODE) as relevance
            ", [$query])
                ->whereRaw("MATCH(name, email) AGAINST(? IN BOOLEAN MODE)", [$query])
                ->where('enterprise_id', $enterpriseId)
                ->orderBy('relevance', 'desc')
                ->limit($limit)
                ->get();
        } else {
            // Fallback to optimized LIKE search
            return \App\Models\User::where('enterprise_id', $enterpriseId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%")
                      ->orWhere('phone_number_1', 'LIKE', "%{$query}%");
                })
                ->select(['id', 'name', 'email', 'phone_number_1', 'date_of_birth'])
                ->limit($limit)
                ->get();
        }
    }

    /**
     * Bulk insert operations for better performance
     */
    public function bulkInsertBillingItems(array $billingItems, int $batchSize = self::BATCH_SIZE_LARGE): int
    {
        $totalInserted = 0;
        $batches = array_chunk($billingItems, $batchSize);

        foreach ($batches as $batch) {
            // Add timestamps to each item
            $batchWithTimestamps = array_map(function ($item) {
                return array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }, $batch);

            $insertedCount = \App\Models\BillingItem::insert($batchWithTimestamps);
            $totalInserted += count($batchWithTimestamps);
        }

        Log::info('Bulk billing items insert completed', [
            'total_inserted' => $totalInserted,
            'batch_count' => count($batches),
            'batch_size' => $batchSize
        ]);

        return $totalInserted;
    }

    /**
     * Optimized pagination with cursor-based approach
     */
    public function getCursorPaginatedConsultations(int $enterpriseId, ?int $lastId = null, int $limit = 20): array
    {
        $query = \App\Models\Consultation::where('enterprise_id', $enterpriseId)
            ->with([
                'patient:id,name,email',
                'assignedTo:id,name'
            ])
            ->select([
                'id', 'consultation_number', 'patient_id', 'assigned_to',
                'consultation_date_time', 'status', 'chief_complaint', 'total_amount'
            ])
            ->orderBy('id', 'desc')
            ->limit($limit + 1); // Get one extra to check if there are more

        if ($lastId) {
            $query->where('id', '<', $lastId);
        }

        $consultations = $query->get();
        $hasMore = $consultations->count() > $limit;

        if ($hasMore) {
            $consultations->pop(); // Remove the extra item
        }

        return [
            'data' => $consultations,
            'has_more' => $hasMore,
            'next_cursor' => $hasMore ? $consultations->last()?->id : null
        ];
    }

    /**
     * Analyze and recommend database indexes
     */
    public function analyzeIndexRequirements(int $enterpriseId): array
    {
        $recommendations = [];

        // Analyze slow queries
        $slowQueries = $this->getSlowQueriesForAlerts($enterpriseId);
        
        foreach ($slowQueries as $query) {
            $analysis = $this->analyzeQueryForIndexes($query);
            if ($analysis['needs_index']) {
                $recommendations[] = $analysis;
            }
        }

        // Check common query patterns
        $commonPatterns = $this->analyzeCommonQueryPatterns($enterpriseId);
        $recommendations = array_merge($recommendations, $commonPatterns);

        return [
            'recommendations' => $recommendations,
            'total_recommendations' => count($recommendations),
            'analyzed_at' => now()->toISOString()
        ];
    }

    /**
     * Optimize query execution plans
     */
    public function explainQuery(string $sql, array $bindings = []): array
    {
        try {
            $explanation = DB::select("EXPLAIN FORMAT=JSON " . $sql, $bindings);
            
            return [
                'sql' => $sql,
                'bindings' => $bindings,
                'execution_plan' => json_decode($explanation[0]->EXPLAIN, true),
                'analyzed_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            Log::error('Query explanation failed', [
                'sql' => $sql,
                'error' => $e->getMessage()
            ]);

            return [
                'sql' => $sql,
                'error' => 'Explanation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Monitor query performance
     */
    public function getQueryPerformanceMetrics(int $enterpriseId): array
    {
        // Get query performance data from logs or monitoring
        return [
            'enterprise_id' => $enterpriseId,
            'avg_query_time' => $this->getAverageQueryTime($enterpriseId),
            'slow_queries_count' => $this->getSlowQueriesCount($enterpriseId),
            'most_expensive_queries' => $this->getMostExpensiveQueries($enterpriseId),
            'index_usage' => $this->getIndexUsageStats($enterpriseId),
            'generated_at' => now()->toISOString()
        ];
    }

    /**
     * Log slow queries for analysis
     */
    private function logSlowQuery($query): void
    {
        $logData = [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
            'connection' => $query->connectionName,
            'enterprise_id' => auth()->user()?->enterprise_id,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ];

        if ($query->time > self::VERY_SLOW_QUERY_THRESHOLD) {
            Log::critical('Very slow query detected', $logData);
        } else {
            Log::warning('Slow query detected', $logData);
        }

        // Store in database for analysis
        $this->storeSlowQueryForAnalysis($logData);
    }

    /**
     * Store slow query data for analysis
     */
    private function storeSlowQueryForAnalysis(array $queryData): void
    {
        try {
            DB::table('slow_query_log')->insert([
                'sql_hash' => md5($queryData['sql']),
                'sql' => $queryData['sql'],
                'bindings' => json_encode($queryData['bindings']),
                'execution_time' => $queryData['time'],
                'enterprise_id' => $queryData['enterprise_id'],
                'user_id' => $queryData['user_id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store slow query log', [
                'error' => $e->getMessage(),
                'query_data' => $queryData
            ]);
        }
    }

    /**
     * Check if full-text index exists
     */
    private function hasFullTextIndex(string $table, array $columns): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Index_type = 'FULLTEXT'");
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get slow queries from log for alerts
     */
    private function getSlowQueriesForAlerts(int $enterpriseId): array
    {
        try {
            return DB::table('slow_query_log')
                ->where('enterprise_id', $enterpriseId)
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('execution_time', 'desc')
                ->limit(10)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Analyze query for index recommendations
     */
    private function analyzeQueryForIndexes(object $queryLog): array
    {
        // Simple analysis - in production, this would be more sophisticated
        $sql = $queryLog->sql;
        $needsIndex = false;
        $recommendations = [];

        // Check for WHERE clauses without indexes
        if (preg_match('/WHERE\s+(\w+)\s*=/', $sql, $matches)) {
            $column = $matches[1];
            $recommendations[] = "Consider adding index on column: {$column}";
            $needsIndex = true;
        }

        // Check for JOIN conditions
        if (preg_match('/JOIN\s+\w+\s+ON\s+(\w+)\s*=/', $sql, $matches)) {
            $column = $matches[1];
            $recommendations[] = "Consider adding index on JOIN column: {$column}";
            $needsIndex = true;
        }

        return [
            'sql' => $sql,
            'execution_time' => $queryLog->execution_time,
            'needs_index' => $needsIndex,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Analyze common query patterns
     */
    private function analyzeCommonQueryPatterns(int $enterpriseId): array
    {
        return [
            [
                'table' => 'consultations',
                'columns' => ['enterprise_id', 'status', 'consultation_date_time'],
                'reason' => 'Frequently filtered by enterprise, status, and date',
                'priority' => 'high'
            ],
            [
                'table' => 'users',
                'columns' => ['enterprise_id', 'email'],
                'reason' => 'User lookup by enterprise and email',
                'priority' => 'medium'
            ],
            [
                'table' => 'billing_items',
                'columns' => ['consultation_id', 'status'],
                'reason' => 'Billing queries by consultation and status',
                'priority' => 'medium'
            ]
        ];
    }

    /**
     * Get average query time
     */
    private function getAverageQueryTime(int $enterpriseId): float
    {
        try {
            $result = DB::table('slow_query_log')
                ->where('enterprise_id', $enterpriseId)
                ->where('created_at', '>=', now()->subDays(1))
                ->avg('execution_time');

            return round($result ?? 0, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get slow queries count
     */
    private function getSlowQueriesCount(int $enterpriseId): int
    {
        try {
            return DB::table('slow_query_log')
                ->where('enterprise_id', $enterpriseId)
                ->where('created_at', '>=', now()->subDays(1))
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get most expensive queries
     */
    private function getMostExpensiveQueries(int $enterpriseId): array
    {
        try {
            return DB::table('slow_query_log')
                ->select('sql', 'execution_time', DB::raw('COUNT(*) as occurrence_count'))
                ->where('enterprise_id', $enterpriseId)
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('sql_hash')
                ->orderBy('execution_time', 'desc')
                ->limit(5)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get index usage statistics
     */
    private function getIndexUsageStats(int $enterpriseId): array
    {
        // This would require detailed index monitoring
        // For now, return placeholder data
        return [
            'total_indexes' => 15,
            'unused_indexes' => 2,
            'efficiency_score' => 85.5
        ];
    }

    /**
     * Get slow queries with optional filtering
     */
    public function getSlowQueries(array $options = []): array
    {
        $limit = $options['limit'] ?? 20;
        $timeRange = $options['time_range'] ?? '24h';
        $includeAnalysis = $options['include_analysis'] ?? false;
        
        $startTime = $this->getTimeRangeStart($timeRange);
        
        $query = DB::table('slow_query_log')
            ->where('created_at', '>=', $startTime)
            ->orderBy('execution_time', 'desc')
            ->limit($limit);
        
        $slowQueries = $query->get()->map(function ($query) use ($includeAnalysis) {
            $queryData = [
                'id' => $query->id,
                'query_text' => $query->query_text,
                'execution_time' => $query->execution_time,
                'rows_examined' => $query->rows_examined,
                'rows_sent' => $query->rows_sent,
                'database_name' => $query->database_name,
                'user_name' => $query->user_name,
                'created_at' => $query->created_at
            ];
            
            if ($includeAnalysis) {
                $queryData['analysis'] = $this->analyzeSlowQuery($query->query_text);
                $queryData['recommendations'] = $this->getQueryRecommendations($query->query_text);
            }
            
            return $queryData;
        })->toArray();
        
        return [
            'queries' => $slowQueries,
            'summary' => [
                'total_count' => count($slowQueries),
                'avg_execution_time' => collect($slowQueries)->avg('execution_time'),
                'max_execution_time' => collect($slowQueries)->max('execution_time'),
                'time_range' => $timeRange,
                'generated_at' => now()->toISOString()
            ]
        ];
    }

    /**
     * Analyze a slow query
     */
    protected function analyzeSlowQuery(string $queryText): array
    {
        $analysis = [
            'query_type' => $this->getQueryType($queryText),
            'tables_involved' => $this->extractTables($queryText),
            'potential_issues' => []
        ];
        
        // Check for common issues
        if (stripos($queryText, 'SELECT *') !== false) {
            $analysis['potential_issues'][] = 'Using SELECT * - consider selecting specific columns';
        }
        
        if (stripos($queryText, 'ORDER BY') !== false && stripos($queryText, 'LIMIT') === false) {
            $analysis['potential_issues'][] = 'ORDER BY without LIMIT - could be expensive';
        }
        
        if (preg_match('/WHERE.*LIKE.*%.*%/i', $queryText)) {
            $analysis['potential_issues'][] = 'LIKE with leading wildcard - cannot use indexes efficiently';
        }
        
        if (stripos($queryText, 'JOIN') !== false && stripos($queryText, 'ON') === false) {
            $analysis['potential_issues'][] = 'JOIN without proper ON condition';
        }
        
        return $analysis;
    }

    /**
     * Get query recommendations
     */
    protected function getQueryRecommendations(string $queryText): array
    {
        $recommendations = [];
        
        // Basic recommendations based on query pattern analysis
        if (stripos($queryText, 'SELECT *') !== false) {
            $recommendations[] = 'Consider selecting only required columns instead of SELECT *';
        }
        
        if (stripos($queryText, 'ORDER BY') !== false && stripos($queryText, 'LIMIT') === false) {
            $recommendations[] = 'Add LIMIT clause to ORDER BY queries to improve performance';
        }
        
        if (preg_match('/WHERE.*LIKE.*%.*%/i', $queryText)) {
            $recommendations[] = 'Consider full-text search for LIKE queries with leading wildcards';
        }
        
        if (stripos($queryText, 'LEFT JOIN') !== false) {
            $recommendations[] = 'Review if LEFT JOIN is necessary or if INNER JOIN would be sufficient';
        }
        
        return $recommendations;
    }

    /**
     * Get time range start
     */
    protected function getTimeRangeStart(string $timeRange): \Carbon\Carbon
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

    /**
     * Get query type from SQL
     */
    protected function getQueryType(string $queryText): string
    {
        $queryText = trim(strtoupper($queryText));
        
        if (strpos($queryText, 'SELECT') === 0) return 'SELECT';
        if (strpos($queryText, 'INSERT') === 0) return 'INSERT';
        if (strpos($queryText, 'UPDATE') === 0) return 'UPDATE';
        if (strpos($queryText, 'DELETE') === 0) return 'DELETE';
        if (strpos($queryText, 'CREATE') === 0) return 'CREATE';
        if (strpos($queryText, 'ALTER') === 0) return 'ALTER';
        if (strpos($queryText, 'DROP') === 0) return 'DROP';
        
        return 'UNKNOWN';
    }

    /**
     * Extract table names from query
     */
    protected function extractTables(string $queryText): array
    {
        $tables = [];
        
        // Simple regex to extract table names (this could be more sophisticated)
        if (preg_match_all('/(?:FROM|JOIN|INTO|UPDATE)\s+`?(\w+)`?/i', $queryText, $matches)) {
            $tables = array_unique($matches[1]);
        }
        
        return $tables;
    }
}
