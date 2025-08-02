<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Gen;
use Carbon\Carbon;

class CacheWarmupService
{
    protected $cacheService;
    protected $queryOptimizationService;

    public function __construct(CacheService $cacheService, QueryOptimizationService $queryOptimizationService)
    {
        $this->cacheService = $cacheService;
        $this->queryOptimizationService = $queryOptimizationService;
    }

    /**
     * Warm up all critical caches
     */
    public function warmupAll(): array
    {
        $results = [
            'started_at' => now(),
            'status' => 'running',
            'tasks' => []
        ];

        try {
            // Warm up system configuration
            $results['tasks']['system_config'] = $this->warmupSystemConfig();
            
            // Warm up departments
            $results['tasks']['departments'] = $this->warmupDepartments();
            
            // Warm up recent patients
            $results['tasks']['recent_patients'] = $this->warmupRecentPatients();
            
            // Warm up consultation statistics
            $results['tasks']['consultation_stats'] = $this->warmupConsultationStats();
            
            // Warm up dashboard data
            $results['tasks']['dashboard_data'] = $this->warmupDashboardData();
            
            // Warm up frequently accessed data
            $results['tasks']['frequent_data'] = $this->warmupFrequentData();

            $results['status'] = 'completed';
            $results['completed_at'] = now();
            
            Log::info('Cache warmup completed successfully', $results);
            
        } catch (\Exception $e) {
            $results['status'] = 'failed';
            $results['error'] = $e->getMessage();
            $results['failed_at'] = now();
            
            Log::error('Cache warmup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Store warmup results in cache for monitoring
        Cache::put('cache_warmup_last_run', $results, now()->addDay());

        return $results;
    }

    /**
     * Warm up system configuration data
     */
    protected function warmupSystemConfig(): array
    {
        $startTime = microtime(true);
        $count = 0;

        try {
            // System settings
            $settings = Gen::all();
            foreach ($settings as $setting) {
                $this->cacheService->store(
                    "system_setting_{$setting->id}",
                    $setting,
                    3600 // 1 hour
                );
                $count++;
            }

            // Department configurations
            $departments = Department::with(['company'])->get();
            $this->cacheService->store('all_departments', $departments, 1800);
            $count++;

            return [
                'status' => 'success',
                'items_cached' => $count,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }
    }

    /**
     * Warm up department-related caches
     */
    protected function warmupDepartments(): array
    {
        $startTime = microtime(true);
        $count = 0;

        try {
            $departments = Department::with(['company'])->get();

            foreach ($departments as $department) {
                // Cache individual department
                $this->cacheService->store(
                    "department_{$department->id}",
                    $department,
                    1800
                );

                // Cache department statistics
                $stats = $this->calculateDepartmentStats($department->id);
                $this->cacheService->store(
                    "department_stats_{$department->id}",
                    $stats,
                    900 // 15 minutes
                );

                $count += 2;
            }

            return [
                'status' => 'success',
                'items_cached' => $count,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }
    }

    /**
     * Warm up recent patients cache
     */
    protected function warmupRecentPatients(): array
    {
        $startTime = microtime(true);
        $count = 0;

        try {
            $companies = DB::table('companies')->pluck('id');

            foreach ($companies as $companyId) {
                // Recent patients for each company
                $recentPatients = Patient::where('company_id', $companyId)
                    ->with(['company'])
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get();

                $this->cacheService->store(
                    "recent_patients_company_{$companyId}",
                    $recentPatients,
                    600 // 10 minutes
                );

                // Active patients count
                $activeCount = Patient::where('company_id', $companyId)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();

                $this->cacheService->store(
                    "active_patients_count_company_{$companyId}",
                    $activeCount,
                    900 // 15 minutes
                );

                $count += 2;
            }

            return [
                'status' => 'success',
                'items_cached' => $count,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }
    }

    /**
     * Warm up consultation statistics
     */
    protected function warmupConsultationStats(): array
    {
        $startTime = microtime(true);
        $count = 0;

        try {
            $companies = DB::table('companies')->pluck('id');
            $timeRanges = ['today', 'week', 'month'];

            foreach ($companies as $companyId) {
                foreach ($timeRanges as $range) {
                    $stats = $this->calculateConsultationStats($companyId, $range);
                    $this->cacheService->store(
                        "consultation_stats_{$companyId}_{$range}",
                        $stats,
                        $range === 'today' ? 300 : 900 // 5 min for today, 15 min for others
                    );
                    $count++;
                }
            }

            return [
                'status' => 'success',
                'items_cached' => $count,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }
    }

    /**
     * Warm up dashboard data
     */
    protected function warmupDashboardData(): array
    {
        $startTime = microtime(true);
        $count = 0;

        try {
            $companies = DB::table('companies')->pluck('id');

            foreach ($companies as $companyId) {
                // Dashboard summary
                $summary = [
                    'total_patients' => Patient::where('company_id', $companyId)->count(),
                    'consultations_today' => Consultation::where('company_id', $companyId)
                        ->whereDate('created_at', today())
                        ->count(),
                    'consultations_week' => Consultation::where('company_id', $companyId)
                        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                        ->count(),
                    'active_departments' => Department::where('company_id', $companyId)->count()
                ];

                $this->cacheService->store(
                    "dashboard_summary_company_{$companyId}",
                    $summary,
                    300 // 5 minutes
                );

                // Recent activity
                $recentActivity = Consultation::where('company_id', $companyId)
                    ->with(['patient', 'department'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                $this->cacheService->store(
                    "recent_activity_company_{$companyId}",
                    $recentActivity,
                    600 // 10 minutes
                );

                $count += 2;
            }

            return [
                'status' => 'success',
                'items_cached' => $count,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }
    }

    /**
     * Warm up frequently accessed data based on analytics
     */
    protected function warmupFrequentData(): array
    {
        $startTime = microtime(true);
        $count = 0;

        try {
            // Get most accessed patients from performance metrics
            $frequentPatients = DB::table('performance_metrics')
                ->where('created_at', '>=', now()->subDays(7))
                ->where('url', 'like', '%/patients/%')
                ->select('url')
                ->groupBy('url')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(50)
                ->pluck('url');

            foreach ($frequentPatients as $url) {
                // Extract patient ID from URL
                if (preg_match('/\/patients\/(\d+)/', $url, $matches)) {
                    $patientId = $matches[1];
                    
                    try {
                        $patient = Patient::with(['company', 'patientRecords'])->find($patientId);
                        if ($patient) {
                            $this->cacheService->store(
                                "patient_{$patientId}_detailed",
                                $patient,
                                1800
                            );
                            $count++;
                        }
                    } catch (\Exception $e) {
                        // Skip if patient not found or other error
                        continue;
                    }
                }
            }

            // Preload common search results
            $commonSearchTerms = [
                'consultation',
                'patient',
                'department',
                'today',
                'recent'
            ];

            foreach ($commonSearchTerms as $term) {
                $searchResults = $this->performCommonSearch($term);
                $this->cacheService->store(
                    "search_results_{$term}",
                    $searchResults,
                    900 // 15 minutes
                );
                $count++;
            }

            return [
                'status' => 'success',
                'items_cached' => $count,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }
    }

    /**
     * Queue cache warmup for background processing
     */
    public function queueWarmup(array $categories = null): string
    {
        $jobId = uniqid('warmup_');
        
        $categories = $categories ?: [
            'system_config',
            'departments',
            'recent_patients',
            'consultation_stats',
            'dashboard_data',
            'frequent_data'
        ];

        Queue::push('CacheWarmupJob', [
            'job_id' => $jobId,
            'categories' => $categories,
            'started_at' => now()->toISOString()
        ]);

        Log::info('Cache warmup queued', ['job_id' => $jobId, 'categories' => $categories]);

        return $jobId;
    }

    /**
     * Get cache warmup status
     */
    public function getWarmupStatus(): array
    {
        $lastRun = Cache::get('cache_warmup_last_run', []);
        
        $status = [
            'last_run' => $lastRun,
            'cache_stats' => $this->cacheService->getStatistics(),
            'recommended_warmup' => $this->shouldWarmup()
        ];

        return $status;
    }

    /**
     * Check if cache warmup is recommended
     */
    protected function shouldWarmup(): bool
    {
        $lastRun = Cache::get('cache_warmup_last_run');
        
        if (!$lastRun) {
            return true; // Never run before
        }

        $lastRunTime = Carbon::parse($lastRun['completed_at'] ?? $lastRun['started_at']);
        
        // Recommend warmup if last run was more than 4 hours ago
        return $lastRunTime->diffInHours(now()) > 4;
    }

    /**
     * Calculate department statistics
     */
    protected function calculateDepartmentStats(int $departmentId): array
    {
        return [
            'total_consultations' => Consultation::where('department_id', $departmentId)->count(),
            'consultations_today' => Consultation::where('department_id', $departmentId)
                ->whereDate('created_at', today())->count(),
            'consultations_week' => Consultation::where('department_id', $departmentId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'avg_consultations_daily' => Consultation::where('department_id', $departmentId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count() / 30,
            'last_consultation' => Consultation::where('department_id', $departmentId)
                ->latest()->first()?->created_at
        ];
    }

    /**
     * Calculate consultation statistics for a time range
     */
    protected function calculateConsultationStats(int $companyId, string $range): array
    {
        $query = Consultation::where('company_id', $companyId);

        switch ($range) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
        }

        $consultations = $query->get();

        return [
            'total_count' => $consultations->count(),
            'by_department' => $consultations->groupBy('department_id')->map->count(),
            'by_day' => $consultations->groupBy(function($consultation) {
                return $consultation->created_at->format('Y-m-d');
            })->map->count(),
            'avg_per_day' => $range === 'today' ? $consultations->count() : 
                ($consultations->count() / max(1, $consultations->pluck('created_at')->map->format('Y-m-d')->unique()->count()))
        ];
    }

    /**
     * Perform common search for caching
     */
    protected function performCommonSearch(string $term): array
    {
        $results = [];

        // Search patients
        $patients = Patient::where('first_name', 'like', "%{$term}%")
            ->orWhere('last_name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email']);

        if ($patients->isNotEmpty()) {
            $results['patients'] = $patients;
        }

        // Search departments
        $departments = Department::where('name', 'like', "%{$term}%")
            ->limit(5)
            ->get(['id', 'name']);

        if ($departments->isNotEmpty()) {
            $results['departments'] = $departments;
        }

        return $results;
    }
}
