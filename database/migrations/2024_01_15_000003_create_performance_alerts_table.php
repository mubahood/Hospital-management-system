<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for performance alerts table
 * 
 * Creates table to track performance threshold violations
 */
class CreatePerformanceAlertsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('url', 1000);
            $table->string('method', 10);
            $table->string('route_name', 255)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('enterprise_id')->nullable()->index();
            
            // Alert information
            $table->string('alert_type', 100)->index(); // slow_request, high_memory, etc.
            $table->enum('severity', ['warning', 'critical'])->index();
            $table->json('metrics'); // Full metrics data
            $table->json('alerts'); // Alert details
            
            // Status tracking
            $table->enum('status', ['open', 'acknowledged', 'resolved'])->default('open')->index();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for alert management
            $table->index(['enterprise_id', 'status', 'created_at']);
            $table->index(['severity', 'status', 'created_at']);
            $table->index(['alert_type', 'created_at']);
            
            // Foreign key constraints - commented out as users table may not exist
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('acknowledged_by')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_alerts');
    }
}
