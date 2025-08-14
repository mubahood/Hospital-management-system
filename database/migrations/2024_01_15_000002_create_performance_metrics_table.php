<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration for performance metrics table
 * 
 * Creates table to track application performance metrics
 */
class CreatePerformanceMetricsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('url', 1000);
            $table->string('method', 10)->index();
            $table->string('route_name', 255)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('enterprise_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            
            // Performance metrics
            $table->decimal('duration_ms', 10, 2)->index(); // Response time in milliseconds
            $table->decimal('memory_used_mb', 8, 2); // Memory used in MB
            $table->decimal('peak_memory_mb', 8, 2); // Peak memory in MB
            $table->integer('query_count')->index(); // Number of database queries
            $table->decimal('response_size_kb', 10, 2); // Response size in KB
            $table->integer('status_code')->index(); // HTTP status code
            
            $table->timestamps();
            
            // Indexes for performance analysis - URL not indexed due to size
            $table->index(['enterprise_id', 'created_at']);
            $table->index(['route_name', 'created_at']);
            $table->index(['duration_ms', 'created_at']);
            $table->index(['status_code', 'created_at']);
            
            // Composite indexes for complex queries
            $table->index(['enterprise_id', 'route_name', 'created_at']);
            $table->index(['enterprise_id', 'duration_ms']);
            
            // Foreign key constraints - commented out as users table may not exist
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
}
