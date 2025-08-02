<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PerformanceDashboardController;

/*
|--------------------------------------------------------------------------
| Performance Dashboard Routes
|--------------------------------------------------------------------------
|
| These routes handle performance monitoring and optimization features
| for the hospital management system.
|
*/

Route::group([
    'prefix' => 'admin/performance',
    'middleware' => ['web', 'auth', 'admin'],
    'namespace' => 'App\Http\Controllers\Admin'
], function () {
    
    // Performance Dashboard
    Route::get('/', [PerformanceDashboardController::class, 'index'])->name('admin.performance.dashboard');
    
    // API Endpoints
    Route::get('/api/metrics', [PerformanceDashboardController::class, 'metrics'])->name('admin.performance.api.metrics');
    Route::get('/api/cache-stats', [PerformanceDashboardController::class, 'cacheStats'])->name('admin.performance.api.cache-stats');
    Route::get('/api/slow-queries', [PerformanceDashboardController::class, 'slowQueries'])->name('admin.performance.api.slow-queries');
    Route::get('/api/alerts', [PerformanceDashboardController::class, 'alerts'])->name('admin.performance.api.alerts');
    
    // Cache Management
    Route::post('/cache/warmup', [PerformanceDashboardController::class, 'warmupCache'])->name('admin.performance.cache.warmup');
    Route::post('/cache/clear', [PerformanceDashboardController::class, 'clearCache'])->name('admin.performance.cache.clear');
    
    // Configuration
    Route::post('/thresholds', [PerformanceDashboardController::class, 'updateThresholds'])->name('admin.performance.thresholds.update');
    
    // Reports
    Route::get('/export', [PerformanceDashboardController::class, 'exportReport'])->name('admin.performance.export');
    
});
